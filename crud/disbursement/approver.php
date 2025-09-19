<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

// Function to update loan (reused from loan2.php logic)
function getOutstandingForLoan($pdo, $loanId) {
    $sql = "
        SELECT LoanAmount, interestRate, paidAmount
        FROM loan
        WHERE LoanID = :loanId
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loanId', $loanId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $principal = $row['LoanAmount'];
        $rate = $row['interestRate'];
        $paid = $row['paidAmount'] ?? 0;
        $interest = $principal * ($rate / 100);
        return ($principal + $interest) - $paid;
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
         ch.accountName, c.allocationID, 	
         d.Name
        FROM request r
        JOIN costallocation c ON r.allocationID = c.allocationID
        JOIN chartofaccount ch ON c.accountID = ch.accountID 
        JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
        WHERE r.status IN ('Approved', 'Verified') AND r.Archive = 'NO'
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

          
            $sql = "SELECT ApprovedAmount, allocationID, LoanID FROM request WHERE requestID = :id AND status = 'Approved' AND Archive = 'NO'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([":id" => $requestID]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($request) {
                $approvedAmount = $request['ApprovedAmount'];
                $allocationID = $request['allocationID'];
                $loanId = $request['LoanID'];

             
                $updateRequest = $pdo->prepare("UPDATE request SET status = 'Paid' WHERE requestID = :id");
                $updateRequest->execute([":id" => $requestID]);

               
                $updateAllocation = $pdo->prepare("UPDATE costallocation SET usedAllocation = COALESCE(usedAllocation, 0) + :amount WHERE allocationID = :allocationID");
                $updateAllocation->execute([
                    ":amount" => $approvedAmount,                    
                    ":allocationID" => $allocationID
                ]);
                
                if ($loanId) {
                    $updateSql = "UPDATE loan SET paidAmount = COALESCE(paidAmount, 0) + :amount WHERE LoanID = :loanId";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->execute([':amount' => $approvedAmount, ':loanId' => $loanId]);
          
                   
                    $outstanding = getOutstandingForLoan($pdo, $loanId);
                    $newStatus = ($outstanding <= 0) ? 'Paid' : 'Partially Paid';
                    $statusSql = "UPDATE loan SET Status = :status WHERE LoanID = :loanId";
                    $statusStmt = $pdo->prepare($statusSql);
                    $statusStmt->execute([':status' => $newStatus, ':loanId' => $loanId]);

                  if ($loanId) {

    $updateSql = "UPDATE loan SET paidAmount = COALESCE(paidAmount, 0) + :amount WHERE LoanID = :loanId";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([':amount' => $approvedAmount, ':loanId' => $loanId]);


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
        ':accountID' => 8,  
        ':debit' => $approvedAmount,
        ':credit' => 0
    ]);

   
    $detailStmt->execute([
        ':journalID' => $journalID,
        ':accountID' => 1,  
        ':debit' => 0,
        ':credit' => $approvedAmount
    ]);
}

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