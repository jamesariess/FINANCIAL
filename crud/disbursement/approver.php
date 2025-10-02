<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

function getOutstandingForLoan($pdo, $loanId) {
    $sql = "
        SELECT LoanAmount, paidAmount
        FROM loan
        WHERE LoanID = :loanId
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loanId', $loanId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $principal = $row['LoanAmount'];
        $paid = $row['paidAmount'] ?? 0;
        return $principal - $paid;
    }
    return 0;
}

$data = [
    "totalRequest" => 0,
    "totalAmountRelease" => 0,
    "rejectedRequest" => 0,
    "newRequest" => 0
]; 

$sql = "SELECT COUNT(*) as total FROM request WHERE Archive = 'NO'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data["totalRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$sql = "SELECT IFNULL(SUM(ApprovedAmount), 0) as total FROM request WHERE status IN ('Paid') AND Archive = 'NO'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data["totalAmountRelease"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$sql = "SELECT COUNT(*) as total FROM request WHERE status = 'Reject' AND Archive = 'NO'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data["rejectedRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$sql = "SELECT COUNT(*) as total FROM request WHERE DATE(date) = CURDATE() AND Archive = 'NO'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data["newRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

function fetchRequests($pdo) {
    $sql = "SELECT
              r.requestID, r.requestTitle, r.ApprovedAmount, r.Requested_by, r.Due, r.status, r.date,
              ch.accountName, c.allocationID, c.accountID,  
              d.Name
            FROM request r
            JOIN costallocation c ON r.allocationID = c.allocationID
            JOIN chartofaccount ch ON c.accountID = ch.accountID 
            JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
            WHERE r.status IN ('Approved') AND r.Archive = 'NO'
            ORDER BY r.date DESC 
            LIMIT 12";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$requests = fetchRequests($pdo);

if (isset($_GET['fetch']) && $_GET['fetch'] === 'requests') {
    header('Content-Type: application/json');
    echo json_encode($requests);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input["requestID"])) {
        $requestID = intval($input["requestID"]);
        try {
            $pdo->beginTransaction();

            $sql = "
                SELECT r.ApprovedAmount, r.allocationID, r.LoanID, ca.accountID
                FROM request r
                JOIN costallocation c ON r.allocationID = c.allocationID
                JOIN chartofaccount ca ON c.accountID = ca.accountID
                WHERE r.requestID = :id AND r.status = 'Approved' AND r.Archive = 'NO'
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([":id" => $requestID]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($request) {
                $approvedAmount = $request['ApprovedAmount'];
                $allocationID = $request['allocationID'];
                $loanId = $request['LoanID'];
                $allocationAccountID = $request['accountID'];

                $stmtCashOnHand = $pdo->query("
                    SELECT IFNULL(SUM(debit) - SUM(credit), 0) AS cashOnHand
                    FROM details d
                    JOIN chartofaccount c ON d.accountID = c.accountID
                    WHERE c.accountName = 'Cash on Hand'
                ");
                $cashOnHand = $stmtCashOnHand->fetch(PDO::FETCH_ASSOC)['cashOnHand'];

                $stmtBankBalance = $pdo->query("
                    SELECT IFNULL(SUM(Amount - COALESCE(UsedAmount, 0)), 0) AS bankBalance
                    FROM funds f
                    WHERE f.Archive = 'NO' AND f.fundType = 'Bank'
                ");
                $bankBalance = $stmtBankBalance->fetch(PDO::FETCH_ASSOC)['bankBalance'];

                $totalGLCash = $cashOnHand + $bankBalance;

                if ($totalGLCash < $approvedAmount) {
                    $pdo->rollBack();
                    echo json_encode(["success" => false, "error" => "This request is hold for release. No bank balance left, no money."]);
                    exit;
                }

                $remaining = $approvedAmount;
                $bankUsed = 0;

                $sqlFunds = "SELECT fundsID, Amount, COALESCE(UsedAmount, 0) as UsedAmount FROM funds WHERE Archive = 'NO' AND fundType = 'Bank' ORDER BY fundsID ASC";
                $stmtFunds = $pdo->query($sqlFunds);
                $fundsRows = $stmtFunds->fetchAll(PDO::FETCH_ASSOC);

                foreach ($fundsRows as $row) {
                    $available = $row['Amount'] - $row['UsedAmount'];
                    if ($available <= 0) continue;

                    $useHere = min($available, $remaining);
                    $updateFund = $pdo->prepare("UPDATE funds SET UsedAmount = COALESCE(UsedAmount, 0) + :use WHERE fundsID = :id");
                    $updateFund->execute([':use' => $useHere, ':id' => $row['fundsID']]);

                    $remaining -= $useHere;
                    $bankUsed += $useHere;

                    if ($remaining <= 0) break;
                }

                $cashUsed = $remaining;

                $cashStmt = $pdo->query("SELECT accountID FROM chartofaccount WHERE accountName = 'Cash On Hand' LIMIT 1");
                $cashAccountID = $cashStmt->fetch(PDO::FETCH_ASSOC)['accountID'] ?? null;

                $bankStmt = $pdo->query("SELECT accountID FROM chartofaccount WHERE accountName = 'Cash On Bank' LIMIT 1");
                $bankAccountID = $bankStmt->fetch(PDO::FETCH_ASSOC)['accountID'] ?? null;

                if ($cashUsed > 0 && !$cashAccountID) {
                    throw new Exception("Cash On Hand account not found.");
                }
                if ($bankUsed > 0 && !$bankAccountID) {
                    throw new Exception("Cash On Bank account not found.");
                }

                $updateRequest = $pdo->prepare("UPDATE request SET status = 'Paid' WHERE requestID = :id");
                $updateRequest->execute([":id" => $requestID]);

                $updateAllocation = $pdo->prepare("UPDATE costallocation SET usedAllocation = COALESCE(usedAllocation, 0) + :amount WHERE allocationID = :allocationID");
                $updateAllocation->execute([
                    ":amount" => $approvedAmount,
                    ":allocationID" => $allocationID
                ]);

                if ($loanId) {
                    
                    $interestAccountSql = "SELECT accountID FROM chartofaccount WHERE accountName = 'Interest'";
                    $interestAccountStmt = $pdo->prepare($interestAccountSql);
                    $interestAccountStmt->execute();
                    $interestAccount = $interestAccountStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$interestAccount) {
                         
                         $lastExpenseCodeSql = "SELECT accountCode FROM chartofaccount WHERE accounType = 'Expenses' ORDER BY accountCode DESC LIMIT 1";
                         $lastExpenseCodeStmt = $pdo->prepare($lastExpenseCodeSql);
                         $lastExpenseCodeStmt->execute();
                         $lastCode = $lastExpenseCodeStmt->fetch(PDO::FETCH_ASSOC);

                         $newCodeNumber = 1;
                         if ($lastCode) {
                             $lastCodeNumber = (int)substr($lastCode['accountCode'], 3);
                             $newCodeNumber = $lastCodeNumber + 1;
                         }
                         $newAccountCode = 'EX-' . str_pad($newCodeNumber, 3, '0', STR_PAD_LEFT);
                         
                         $insertSql = "INSERT INTO chartofaccount (accountCode, accountName, accountType, Archive, status) VALUES (:code, 'Interest', 'Expenses', 'NO', 'Active')";
                         $insertStmt = $pdo->prepare($insertSql);
                         $insertStmt->execute([':code' => $newAccountCode]);
                         $interestAccountID = $pdo->lastInsertId();
                    } else {
                         $interestAccountID = $interestAccount['accountID'];
                    }

                    $loanSql = "SELECT LoanAmount, interestRate, paidAmount FROM loan WHERE LoanID = :loanId";
                    $loanStmt = $pdo->prepare($loanSql);
                    $loanStmt->execute([':loanId' => $loanId]);
                    $loanData = $loanStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$loanData) {
                         throw new Exception("Loan data not found for LoanID: " . $loanId);
                    }
                    
                    $currentPrincipalBalance = $loanData['LoanAmount'] - ($loanData['paidAmount'] ?? 0);
                    $interestRateDecimal = $loanData['interestRate'] / 100;
                    $interestPaid = $currentPrincipalBalance * ($interestRateDecimal / 12); 
                    $principalPaid = $approvedAmount - $interestPaid;
                    if ($principalPaid > $currentPrincipalBalance) {
                        $principalPaid = $currentPrincipalBalance;
                        $interestPaid = $approvedAmount - $principalPaid;
                    }

                    $updateSql = "UPDATE loan SET paidAmount = COALESCE(paidAmount, 0) + :principalPaid WHERE LoanID = :loanId";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([':principalPaid' => $principalPaid, ':loanId' => $loanId]);

                    $outstanding = getOutstandingForLoan($pdo, $loanId);
                    $newStatus = ($outstanding <= 0) ? 'Paid' : 'Partially Paid';
                    $statusSql = "UPDATE loan SET Status = :status WHERE LoanID = :loanId";
                    $statusStmt = $pdo->prepare($statusSql);
                    $statusStmt->execute([':status' => $newStatus, ':loanId' => $loanId]);

                    $entrySql = "
                         INSERT INTO entries (date, description, referenceType, createdBy, Archive)
                         VALUES (CURDATE(), :description, :ref, :createdBy, 'NO')
                    ";
                    $entryStmt = $pdo->prepare($entrySql);
                    $entryStmt->execute([
                         ':description' => "Loan Payment Request #{$requestID}",
                         ':ref' => 'Loan Payment',
                         ':createdBy' => 'System'
                    ]);
                    $journalID = $pdo->lastInsertId();

                    $detailSql = "
                         INSERT INTO details (journalID, accountID, debit, credit, Archive)
                         VALUES (:journalID, :accountID, :debit, :credit, 'NO')
                    ";
                    $detailStmt = $pdo->prepare($detailSql);

                    $detailStmt->execute([
                         ':journalID' => $journalID,
                         ':accountID' => $interestAccountID,
                         ':debit' => $interestPaid,
                         ':credit' => 0
                    ]);

                    $detailStmt->execute([
                         ':journalID' => $journalID,
                         ':accountID' => $allocationAccountID,
                         ':debit' => $principalPaid,
                         ':credit' => 0
                    ]);

                    if ($bankUsed > 0 && $bankAccountID) {
                        $detailStmt->execute([
                             ':journalID' => $journalID,
                             ':accountID' => $bankAccountID,
                             ':debit' => 0,
                             ':credit' => $bankUsed
                        ]);
                    }

                    if ($cashUsed > 0 && $cashAccountID) {
                        $detailStmt->execute([
                             ':journalID' => $journalID,
                             ':accountID' => $cashAccountID,
                             ':debit' => 0,
                             ':credit' => $cashUsed
                        ]);
                    }

                } else {
                    $entrySql = "
                         INSERT INTO entries (date, description, referenceType, createdBy, Archive)
                         VALUES (CURDATE(), :description, :ref, :createdBy, 'NO')
                    ";
                    $entryStmt = $pdo->prepare($entrySql);
                    $entryStmt->execute([
                         ':description' => "Expense Request #{$requestID}",
                         ':ref' => 'General Expense',
                         ':createdBy' => 'System'
                    ]);
                    $journalID = $pdo->lastInsertId();

                    $detailSql = "
                         INSERT INTO details (journalID, accountID, debit, credit, Archive)
                         VALUES (:journalID, :accountID, :debit, :credit, 'NO')
                    ";
                    $detailStmt = $pdo->prepare($detailSql);

                    $detailStmt->execute([
                         ':journalID' => $journalID,
                         ':accountID' => $allocationAccountID,
                         ':debit' => $approvedAmount,
                         ':credit' => 0
                    ]);

                    if ($bankUsed > 0 && $bankAccountID) {
                        $detailStmt->execute([
                             ':journalID' => $journalID,
                             ':accountID' => $bankAccountID,
                             ':debit' => 0,
                             ':credit' => $bankUsed
                        ]);
                    }

                    if ($cashUsed > 0 && $cashAccountID) {
                        $detailStmt->execute([
                             ':journalID' => $journalID,
                             ':accountID' => $cashAccountID,
                             ':debit' => 0,
                             ':credit' => $cashUsed
                        ]);
                    }
                }
                
                $pdo->commit();
                $updatedRequests = fetchRequests($pdo);
                echo json_encode([
                    "success" => true, 
                    "requests" => $updatedRequests
                ]);
            } else {
                $pdo->rollBack();
                echo json_encode(["success" => false, "error" => "Request not found or not approved"]);
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
        }
        exit;
    }
}
?>
