<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

// Function to get the current outstanding principal balance for a loan
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
        
        // Return the remaining principal balance
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

// Existing queries for dashboard data
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

$sql = "SELECT
          r.requestID, r.requestTitle, r.ApprovedAmount, r.Requested_by, r.Due, r.status, r.date,
          ch.accountName, c.allocationID, c.accountID,  
          d.Name
        FROM request r
        JOIN costallocation c ON r.allocationID = c.allocationID
        JOIN chartofaccount ch ON c.accountID = ch.accountID 
        JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
        WHERE r.status IN ('Approved', 'Pending') AND r.Archive = 'NO'
        ORDER BY r.date DESC 
        LIMIT 12";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

                    // Fetch loan details and current outstanding principal balance
                    $loanSql = "SELECT LoanAmount, interestRate, paidAmount FROM loan WHERE LoanID = :loanId";
                    $loanStmt = $pdo->prepare($loanSql);
                    $loanStmt->execute([':loanId' => $loanId]);
                    $loanData = $loanStmt->fetch(PDO::FETCH_ASSOC);

                    if (!$loanData) {
                         throw new Exception("Loan data not found for LoanID: " . $loanId);
                    }
                    
                    // --- CORRECTED AMORTIZATION CALCULATION ---
                    // The interest is calculated on the current outstanding principal.
                    $currentPrincipalBalance = $loanData['LoanAmount'] - ($loanData['paidAmount'] ?? 0);
                    $interestRateDecimal = $loanData['interestRate'] / 100;
                    
                    // Calculate the interest portion of the payment for one month
                    $interestPaid = $currentPrincipalBalance * ($interestRateDecimal / 12); 
                    
                    // The remaining portion of the payment goes to the principal
                    $principalPaid = $approvedAmount - $interestPaid;
                    
                    // Ensure that the principal paid is not more than the outstanding principal
                    if ($principalPaid > $currentPrincipalBalance) {
                        $principalPaid = $currentPrincipalBalance;
                        $interestPaid = $approvedAmount - $principalPaid;
                    }

                    // Update loan's paid amount (principal only)
                    $updateSql = "UPDATE loan SET paidAmount = COALESCE(paidAmount, 0) + :principalPaid WHERE LoanID = :loanId";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([':principalPaid' => $principalPaid, ':loanId' => $loanId]);

                    $outstanding = getOutstandingForLoan($pdo, $loanId);
                    $newStatus = ($outstanding <= 0) ? 'Paid' : 'Partially Paid';
                    $statusSql = "UPDATE loan SET Status = :status WHERE LoanID = :loanId";
                    $statusStmt = $pdo->prepare($statusSql);
                    $statusStmt->execute([':status' => $newStatus, ':loanId' => $loanId]);

                    // Fetch the 'method' string from ap_payments
                    $paymentMethodSql = "SELECT method FROM ap_payments WHERE LoanID = :loanId";
                    $paymentsmt = $pdo->prepare($paymentMethodSql);
                    $paymentsmt->execute([':loanId' => $loanId]);
                    $paymendata = $paymentsmt->fetch(PDO::FETCH_ASSOC);

                    if (!$paymendata) {
                         throw new Exception("Payment method not found for LoanID: " . $loanId);
                    }
                    $paymentMethod = $paymendata['method'];

                    $paymentAccountID = null;
                    if ($paymentMethod === 'Cash') {
                        $paymentAccountID = 1;
                    } else if ($paymentMethod === 'Bank Transfer' || $paymentMethod === 'Check') {
                        $paymentAccountID = 2;
                    }

                    if ($paymentAccountID === null) {
                         throw new Exception("Account not found for payment method: " . $paymentMethod);
                    }

                    // Create journal entry for loan payment
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

                    // Debit the Interest Expense account
                    $detailStmt->execute([
                         ':journalID' => $journalID,
                         ':accountID' => $interestAccountID,
                         ':debit' => $interestPaid,
                         ':credit' => 0
                    ]);

                    // Debit the Loan Payable account for the principal portion
                    $detailStmt->execute([
                         ':journalID' => $journalID,
                         ':accountID' => $allocationAccountID,
                         ':debit' => $principalPaid,
                         ':credit' => 0
                    ]);

                    // Credit the cash/bank account for the full payment amount
                    $detailStmt->execute([
                         ':journalID' => $journalID,
                         ':accountID' => $paymentAccountID,
                         ':debit' => 0,
                         ':credit' => $approvedAmount
                    ]);

                } else {
                    // This is a GENERAL EXPENSE
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

                    $cashOrBankID = 1;
                    $detailStmt->execute([
                         ':journalID' => $journalID,
                         ':accountID' => $cashOrBankID,
                         ':debit' => 0,
                         ':credit' => $approvedAmount
                    ]);
                }
                
                $pdo->commit();
                echo json_encode(["success" => true]);
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
