
<?php
// dashboard.php
// Modern Ledger / Trial Balance / Income Statement / Balance Sheet dashboard
// Requires: connection.php that creates $pdo (PDO)

// --- config / include ---
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

// Handle period actions (close/reopen) before anything else
if (isset($_POST['action']) && in_array($_POST['action'], ['close', 'reopen'])) {
    $month = isset($_POST['month']) ? (int)$_POST['month'] : 0;
    $year = isset($_POST['year']) ? (int)$_POST['year'] : 0;
    error_log("Action: {$_POST['action']}, Month: $month, Year: $year"); // Debug log
    if ($month > 0 && $year > 0) {
        try {
            if ($_POST['action'] === 'close') {
                // Check if period already exists for this month and year
                $checkStmt = $pdo->prepare("SELECT period_id, status FROM periods WHERE year = :year AND month = :month");
                $checkStmt->execute([':year' => $year, ':month' => $month]);
                $existingPeriod = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if (!$existingPeriod) {
                    // Insert new period with status 'Closed'
                    $insertStmt = $pdo->prepare("INSERT INTO periods (year, month, status) VALUES (:year, :month, 'Closed')");
                    $insertStmt->execute([':year' => $year, ':month' => $month]);
                    $newPeriodId = $pdo->lastInsertId();
                    error_log("New period inserted with ID: $newPeriodId");

                    // Assign unassigned entries to this new period based on date
                    $assignStmt = $pdo->prepare(
                        "UPDATE entries 
                         SET periodID = :period_id 
                         WHERE YEAR(date) = :year 
                         AND MONTH(date) = :month 
                         AND (periodID IS NULL OR periodID = 0)"
                    );
                    $assignStmt->execute([':period_id' => $newPeriodId, ':year' => $year, ':month' => $month]);
                    error_log("Assigned entries to period ID: $newPeriodId");
                } else {
                    // Update existing period to 'Closed' if it's 'Open'
                    if ($existingPeriod['status'] === 'Open') {
                        $updateStmt = $pdo->prepare("UPDATE periods SET status = 'Closed' WHERE period_id = :period_id");
                        $updateStmt->execute([':period_id' => $existingPeriod['period_id']]);
                        error_log("Updated period ID: {$existingPeriod['period_id']} to Closed");

                        // Assign unassigned entries to this period
                        $assignStmt = $pdo->prepare(
                            "UPDATE entries 
                             SET periodID = :period_id 
                             WHERE YEAR(date) = :year 
                             AND MONTH(date) = :month 
                             AND (periodID IS NULL OR periodID = 0)"
                        );
                        $assignStmt->execute([':period_id' => $existingPeriod['period_id'], ':year' => $year, ':month' => $month]);
                        error_log("Assigned entries to period ID: {$existingPeriod['period_id']}");
                    } else {
                        error_log("Period already Closed or in invalid state for month $month, year $year");
                    }
                }
            } elseif ($_POST['action'] === 'reopen') {
                // Check if a closed period exists for this month and year
                $checkStmt = $pdo->prepare("SELECT period_id FROM periods WHERE year = :year AND month = :month AND status = 'Closed'");
                $checkStmt->execute([':year' => $year, ':month' => $month]);
                $closedPeriod = $checkStmt->fetch(PDO::FETCH_ASSOC);

                if ($closedPeriod) {
                    $updateStmt = $pdo->prepare("UPDATE periods SET status = 'Open' WHERE period_id = :period_id");
                    $updateStmt->execute([':period_id' => $closedPeriod['period_id']]);
                    error_log("Reopened period ID: {$closedPeriod['period_id']}");
                } else {
                    error_log("No closed period found for month $month, year $year");
                }
            }

        
        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            // For debugging, you can uncomment the next line to display the error
            // echo "Error: " . $e->getMessage();
        }
    } else {
        error_log("Invalid month or year submitted: Month $month, Year $year");
    }
}

// --- helpers ---
function fmtMoney($centsOrFloat) {
    if (is_int($centsOrFloat)) {
        $v = $centsOrFloat / 100;
    } else {
        $v = (float)$centsOrFloat;
    }
    return '₱' . number_format($v, 2);
}

function safe($s) { return htmlspecialchars((string)$s); }

// --- fetch periods for dropdown ---
try {
    $periodsStmt = $pdo->query("SELECT period_id, year, month, status FROM periods ORDER BY year DESC, month DESC");
    $periods = $periodsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $periods = [];
    error_log("Error fetching periods: " . $e->getMessage());
}

// --- process incoming filters ---
$selectedPeriodId = isset($_POST['period_id']) ? (int)$_POST['period_id'] : (isset($_GET['period_id']) ? (int)$_GET['period_id'] : null);
$selectedAccountName = isset($_POST['accountName']) ? trim($_POST['accountName']) : (isset($_GET['accountName']) ? trim($_GET['accountName']) : 'all');

$periodWhere = '';
$params = [];
if ($selectedPeriodId) {
    $periodWhere = ' WHERE e.periodID = :period_id ';
    $params[':period_id'] = $selectedPeriodId;
}

// --- Fetch account list for filter ---
try {
    $sqlAccounts = "SELECT DISTINCT c.accountName
                    FROM chartofaccount c
                    JOIN details d ON c.accountID = d.accountID
                    JOIN entries e ON d.journalID = e.journalID";
    if ($selectedPeriodId) $sqlAccounts .= " WHERE e.periodID = :period_id ";
    $sqlAccounts .= " ORDER BY c.accountName";
    $stmt = $pdo->prepare($sqlAccounts);
    if ($selectedPeriodId) $stmt->bindValue(':period_id', $selectedPeriodId, PDO::PARAM_INT);
    $stmt->execute();
    $accountNames = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $accountNames = [];
    error_log("Error fetching account names: " . $e->getMessage());
}

// --- JOURNAL / LEDGER rows ---
$accountFilterSql = '';
if ($selectedAccountName && $selectedAccountName !== 'all') {
    $accountFilterSql = ' AND c.accountName = :accountName ';
    $params[':accountName'] = $selectedAccountName;
}

try {
    $sqlJournal = "SELECT
        jd.entriesID,
        jd.journalID,
        c.accountName,
        c.accountID,
        jd.debit,
        jd.credit,
        c.accounType AS accountType,
        e.date
    FROM details jd
    JOIN chartofaccount c ON jd.accountID = c.accountID
    JOIN entries e ON jd.journalID = e.journalID";
    if ($selectedPeriodId) {
        $sqlJournal .= " WHERE e.periodID = :period_id ";
    }
    if ($selectedAccountName && $selectedAccountName !== 'all') {
        $sqlJournal .= $selectedPeriodId ? ' AND ' : ' WHERE ';
        $sqlJournal .= " c.accountName = :accountName ";
    }
    $sqlJournal .= " ORDER BY c.accountID, e.date, jd.entriesID";

    $stmt = $pdo->prepare($sqlJournal);
    if ($selectedPeriodId) $stmt->bindValue(':period_id', $selectedPeriodId, PDO::PARAM_INT);
    if ($selectedAccountName && $selectedAccountName !== 'all') $stmt->bindValue(':accountName', $selectedAccountName);
    $stmt->execute();
    $journalDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $journalDetails = [];
    error_log("Error fetching journal details: " . $e->getMessage());
}

// --- TRIAL BALANCE ---
try {
    $sqlTB = "SELECT c.accountID, c.accountName, c.accounType,
                     SUM(jd.debit) AS total_debit, SUM(jd.credit) AS total_credit
              FROM details jd
              JOIN chartofaccount c ON jd.accountID = c.accountID
              JOIN entries e ON jd.journalID = e.journalID";
    if ($selectedPeriodId) {
        $sqlTB .= " WHERE e.periodID = :period_id ";
    }
    $sqlTB .= " GROUP BY  c.accountName, c.accounType
                ORDER BY c.accountName";
    $stmt = $pdo->prepare($sqlTB);
    if ($selectedPeriodId) $stmt->bindValue(':period_id', $selectedPeriodId, PDO::PARAM_INT);
    $stmt->execute();
    $tbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $tbRows = [];
    error_log("Error fetching trial balance: " . $e->getMessage());
}

// --- Income Statement ---
try {
    $sqlIS = "SELECT c.accountID, c.accountName, LOWER(c.accounType) as acctype,
                     SUM(jd.debit) AS total_debit, SUM(jd.credit) AS total_credit
              FROM details jd
              JOIN chartofaccount c ON jd.accountID = c.accountID
              JOIN entries e ON jd.journalID = e.journalID
              WHERE LOWER(c.accounType) IN ('revenue','income','expense','expenses')";
    if ($selectedPeriodId) $sqlIS .= " AND e.periodID = :period_id ";
    $sqlIS .= " GROUP BY  c.accountName, c.accounType
                ORDER BY c.accounType, c.accountName";
    $stmt = $pdo->prepare($sqlIS);
    if ($selectedPeriodId) $stmt->bindValue(':period_id', $selectedPeriodId, PDO::PARAM_INT);
    $stmt->execute();
    $isRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $isRows = [];
    error_log("Error fetching income statement: " . $e->getMessage());
}

// --- Balance Sheet ---
try {
    $sqlBS = "SELECT c.accountID, c.accountName, LOWER(c.accounType) as acctype,
                     SUM(jd.debit) AS total_debit, SUM(jd.credit) AS total_credit
              FROM details jd
              JOIN chartofaccount c ON jd.accountID = c.accountID
              JOIN entries e ON jd.journalID = e.journalID
              WHERE LOWER(c.accounType) IN ('assets','asset','liabilities','liability','equity','owner equity')";
    if ($selectedPeriodId) $sqlBS .= " AND e.periodID = :period_id ";
    $sqlBS .= " GROUP BY  c.accountName, c.accounType
                ORDER BY FIELD(LOWER(c.accounType),'assets','asset','liabilities','liability','equity','owner equity'), c.accountName";
    $stmt = $pdo->prepare($sqlBS);
    if ($selectedPeriodId) $stmt->bindValue(':period_id', $selectedPeriodId, PDO::PARAM_INT);
    $stmt->execute();
    $bsRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $bsRows = [];
    error_log("Error fetching balance sheet: " . $e->getMessage());
}

$totalAccounts = 0;
$totalDebitBalance = 0;
$totalCreditBalance = 0;
foreach ($tbRows as $r) {
    $totalAccounts++;
    $acctType = strtolower(trim($r['accounType']));
    $debit = (float)$r['total_debit'];
    $credit = (float)$r['total_credit'];
    if (in_array($acctType, ['assets','asset','expenses','expense'])) {
        $net = $debit - $credit;
        if ($net >= 0) $totalDebitBalance += $net;
        else $totalCreditBalance += abs($net);
    } else {
        $net = $credit - $debit;
        if ($net >= 0) $totalCreditBalance += $net;
        else $totalDebitBalance += abs($net);
    }
}

$selectedPeriodStatus = null;
if ($selectedPeriodId) {
    foreach ($periods as $p) {
        if ((int)$p['period_id'] === (int)$selectedPeriodId) {
            $selectedPeriodStatus = $p['status'];
            $selectedPeriodLabel = $p['year'] . '-' . str_pad($p['month'],2,'0', STR_PAD_LEFT);
            break;
        }
    }
} else {
    $selectedPeriodLabel = 'All Periods';
}

$totalRevenue = 0;
$totalExpenses = 0;
foreach ($isRows as $r) {
    $atype = strtolower($r['acctype']);
    $debit = (float)$r['total_debit'];
    $credit = (float)$r['total_credit'];
    if (strpos($atype, 'rev') !== false || strpos($atype, 'inc') !== false) {
        $totalRevenue += ($credit - $debit);
    } else {
        $totalExpenses += ($debit - $credit);
    }
}
$netIncome = $totalRevenue - $totalExpenses;

$bsAssets = 0;
$bsLiabilities = 0;
$bsEquity = 0;
foreach ($bsRows as $r) {
    $atype = strtolower($r['acctype']);
    $debit = (float)$r['total_debit'];
    $credit = (float)$r['total_credit'];
    if (strpos($atype, 'asset') !== false) {
        $bsAssets += ($debit - $credit);
    } elseif (strpos($atype, 'liabil') !== false) {
        $bsLiabilities += ($credit - $debit);
    } else {
        $bsEquity += ($credit - $debit);
    }
}
$bsEquity += $netIncome;
?>