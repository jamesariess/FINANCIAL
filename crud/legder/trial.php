<?php

include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');


function getBankBalance($pdo) {
    $stmt = $pdo->query("SELECT SUM(Amount - UsedAmount) AS balance FROM funds WHERE Archive='NO'");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['balance'] ?? 0;
}


function getOwnerDeposit($pdo) {
    $sql = "
        SELECT 
            COALESCE(SUM(jd.credit),0) - COALESCE(SUM(jd.debit),0) AS balance
        FROM details jd
        JOIN chartofaccount c ON jd.accountID = c.accountID
        JOIN entries e ON jd.journalID = e.journalID
        WHERE c.accountName = 'Owner Capital'
    ";
    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['balance'] ?? 0;
}



function getCashOnHand($pdo, $selectedPeriodId = null) {
    try {      
        $sql = "
            SELECT 
                SUM(jd.debit) - SUM(jd.credit) AS balance
            FROM details jd
            JOIN chartofaccount c ON jd.accountID = c.accountID
            JOIN entries e ON jd.journalID = e.journalID
            WHERE c.accountName = 'Cash On Hand'
        ";
        if ($selectedPeriodId) {
            $sql .= " AND e.periodID = :period_id";
        }
        $stmt = $pdo->prepare($sql);
        if ($selectedPeriodId) {
            $stmt->bindValue(':period_id', $selectedPeriodId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC); 
        return $result && $result['balance'] !== null ? $result['balance'] : 0;
    } catch (Exception $e) {
        error_log("Error fetching Cash On Hand: " . $e->getMessage());
        return 0;
    }
}


try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch bank names for dropdown
    $bankNamesStmt = $pdo->query("SELECT bankName FROM bank WHERE archive='NO' AND status='Active'");
    $bankNames = $bankNamesStmt->fetchAll(PDO::FETCH_COLUMN);

   

    // Query funds joined with bank
    $sql = "
        SELECT f.fundsID, f.Amount, f.UsedAmount, f.Date, f.reference, f.Notes, f.Archive,
               b.bankName
        FROM funds f
        JOIN bank b ON f.bankID = b.bankID
        WHERE f.Archive='NO'
        ORDER BY b.bankName, f.Date
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $fundDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

   
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    $fundDetails = [];
}
if (isset($_POST['saveDeposit'])) {
    $depositType   = $_POST['depositType']; // "Bank" | "Owner" | "Petty Cash"
    $bankID        = $_POST['bankID'] ?? null;
    $newBankName   = trim($_POST['newBankName'] ?? '');
    $amount        = $_POST['amount'];
    $reference     = $_POST['reference'] ?: $depositType;
    $date          = date("Y-m-d H:i:s");

    // ---------- handle bank creation ----------
    if ($newBankName !== "" && $depositType === "Bank") {
        $normalizedBank = strtoupper($newBankName);
        $check = $pdo->prepare("SELECT bankID FROM bank 
                                WHERE UPPER(bankName) = :bankName AND Archive = 'NO'");
        $check->execute([':bankName' => $normalizedBank]);
        $existingBank = $check->fetch(PDO::FETCH_ASSOC);

        if ($existingBank) {
            echo "<script>alert('Bank name already exists!');</script>";
            $bankID = $existingBank['bankID'];
        } else {
            $cleanBank = ucwords(strtolower($newBankName));
            $stmt = $pdo->prepare("INSERT INTO bank (bankName, status, archive) 
                                   VALUES (:bankName, 'Active', 'NO')");
            $stmt->execute([':bankName' => $cleanBank]);
            $bankID = $pdo->lastInsertId();
        }
    }

    // ---------- reusable helper ----------
    function getOrCreateAccount($pdo, $accountName, $accountType) {
        $checkSql = "SELECT accountID FROM chartofaccount 
                     WHERE accountName = :name AND Archive = 'NO' LIMIT 1";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([':name' => $accountName]);
        $account = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($account) return $account['accountID'];

        $lastCodeSql = "SELECT accountCode FROM chartofaccount 
                        WHERE accounType = :type ORDER BY accountCode DESC LIMIT 1";
        $lastCodeStmt = $pdo->prepare($lastCodeSql);
        $lastCodeStmt->execute([':type' => $accountType]);
        $lastCode = $lastCodeStmt->fetch(PDO::FETCH_ASSOC);

        $newCodeNumber = $lastCode ? (int)substr($lastCode['accountCode'], 3) + 1 : 1;
        $prefix = strtoupper(substr($accountType, 0, 2));
        $newAccountCode = $prefix . '-' . str_pad($newCodeNumber, 3, '0', STR_PAD_LEFT);

        $insertSql = "INSERT INTO chartofaccount (accountCode, accountName, accounType, Archive, status) 
                      VALUES (:code, :name, :type, 'NO', 'Active')";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            ':code' => $newAccountCode,
            ':name' => $accountName,
            ':type' => $accountType
        ]);
        return $pdo->lastInsertId();
    }

    // ---------- Create Journal Entry ----------
    $entrySql = "INSERT INTO entries (date, description, referenceType, createdBy, Archive)
                 VALUES (:date, :description, :ref, :createdBy, 'NO')";
    $entryStmt = $pdo->prepare($entrySql);
    $entryStmt->execute([
        ':date' => $date,
        ':description' => "Deposit ($depositType)",
        ':ref' => $depositType,
        ':createdBy' => 'System'
    ]);
    $journalID = $pdo->lastInsertId();

    $detailSql = "INSERT INTO details (journalID, accountID, debit, credit, Archive)
                  VALUES (:journalID, :accountID, :debit, :credit, 'NO')";
    $detailStmt = $pdo->prepare($detailSql);

    // ---------- Logic per deposit type ----------
    if ($depositType === "Bank" && $bankID) {
        // funds record
        $stmt = $pdo->prepare("INSERT INTO funds (bankID, Amount, UsedAmount, reference, Archive, Date) 
                               VALUES (:bankID, :amount, 0, :reference, 'NO', :date)");
        $stmt->execute([
            ':bankID' => $bankID,
            ':amount' => $amount,
            ':reference' => $reference,
            ':date' => $date
        ]);

        // Journal: Debit Cash On Bank, Credit Cash On Hand
        $bankAccountID = getOrCreateAccount($pdo, "Cash On Bank", "Assets");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $bankAccountID,
            ':debit' => $amount,
            ':credit' => 0
        ]);

        $cashOnHandID = getOrCreateAccount($pdo, "Cash On Hand", "Assets");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $cashOnHandID,
            ':debit' => 0,
            ':credit' => $amount
        ]);

    } elseif ($depositType === "Owner") {
        // 🔹 Add also to funds, but tie to a "virtual owner bank" (or reuse null)
        $stmt = $pdo->prepare("INSERT INTO funds (bankID, Amount, UsedAmount, reference, Archive, Date) 
                               VALUES (:bankID, :amount, 0, :reference, 'NO', :date)");
        $stmt->execute([
            ':amount' => $amount,
            ':bankID' => $bankID,
            ':reference' => "Owner Deposit",
            ':date' => $date
        ]);

        // Journal: Debit Cash On Bank, Credit Owner Capital
        $bankAccountID = getOrCreateAccount($pdo, "Cash On Bank", "Assets");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $bankAccountID,
            ':debit' => $amount,
            ':credit' => 0
        ]);

        $equityAccountID = getOrCreateAccount($pdo, "Owner Capital", "Equity");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $equityAccountID,
            ':debit' => 0,
            ':credit' => $amount
        ]);

    } elseif ($depositType === "Petty Cash") {
        // 🔹 Add to funds as well (Cash On Hand)
        $stmt = $pdo->prepare("INSERT INTO funds (bankID, Amount, UsedAmount, reference, Archive, Date) 
                               VALUES (NULL, :amount, 0, :reference, 'NO', :date)");
        $stmt->execute([
            ':amount' => $amount,
            ':reference' => "Petty Cash",
            ':date' => $date
        ]);

        // Journal: Debit Cash On Hand, Credit Petty Cash Funding
        $cashOnHandID = getOrCreateAccount($pdo, "Cash On Hand", "Assets");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $cashOnHandID,
            ':debit' => $amount,
            ':credit' => 0
        ]);

        $capitalID = getOrCreateAccount($pdo, "Petty Cash Funding", "Equity");
        $detailStmt->execute([
            ':journalID' => $journalID,
            ':accountID' => $capitalID,
            ':debit' => 0,
            ':credit' => $amount
        ]);
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fundID = $_POST['fundID'];
    $amount = floatval($_POST['amount']);

    // Get current balance
    $stmt = $pdo->prepare("SELECT useAmount FROM funds WHERE fundID = ?");
    $stmt->execute([$fundID]);
    $fund = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fund) {
        die("Invalid Fund selected.");
    }

    if ($amount > $fund['useAmount']) {
        die("Insufficient balance in this fund.");
    }

    // Deduct amount
    $stmt = $pdo->prepare("UPDATE funds SET useAmount = useAmount - ? WHERE fundID = ?");
    $stmt->execute([$amount, $fundID]);

    // (Optional) Insert transaction log
    $stmt = $pdo->prepare("INSERT INTO transactions (fundID, type, amount, created_at) VALUES (?, 'withdrawal', ?, NOW())");
    $stmt->execute([$fundID, $amount]);

    header("Location: dashboard.php?msg=withdraw_success");
    exit;
}

function getBanksWithBalance($pdo) {
    $sql = "
      SELECT f.fundsID , b.bankName, (f.Amount - COALESCE(f.UsedAmount,0)) AS useAmount
      FROM funds f
      JOIN bank b ON f.bankID = b.bankID
      WHERE f.Archive = 'NO' AND (f.Amount - COALESCE(f.UsedAmount,0)) > 0
      ORDER BY b.bankName
    ";
    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
$banksForWithdraw = getBanksWithBalance($pdo);

?>