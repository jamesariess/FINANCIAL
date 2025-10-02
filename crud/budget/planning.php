<?php

include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');


$successMessage = '';
$errorMessage = '';
if (isset($_GET['success'])) {
    $successMessage = $_GET['message'] ?? 'Operation successful';
} elseif (isset($_GET['error'])) {
    $errorMessage = $_GET['message'] ?? 'An error occurred';
}
 

$sql = "SELECT
    r.requestID, r.requestTitle, r.ApprovedAmount, r.Requested_by, r.Due, r.status, r.date,r.Amount,
            ch.accountName as Title, c.allocationID, 	
         d.Name,
         (c.Amount - COALESCE(c.usedAllocation, 0)) as balance
        FROM request r
        JOIN costallocation c ON r.allocationID = c.allocationID
        JOIN chartofaccount ch ON c.accountID = ch.accountID 
        JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget

WHERE r.status IN ('Pending') AND r.Archive = 'NO'
ORDER BY r.date DESC 
LIMIT 12";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  
    die("Database query failed: " . $e->getMessage());
}

if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode($requests);
    exit;
}

if (isset($_GET['json_reports'])) {
    $sql_reports = "SELECT r.* ,
     ch.accountName AS Title,
    d.Name
    FROM request r
JOIN costallocation c on r.allocationID = c.allocationID
JOIN chartofaccount ch ON c.accountID = ch.accountID 
JOIN departmentbudget d on c.Deptbudget = d.Deptbudget
WHERE r.Archive = 'NO'";
    $stmt_reports = $pdo->query($sql_reports);
    $disbursementReports = $stmt_reports->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($disbursementReports);
    exit;
}

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM request WHERE requestID = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

   
if (isset($_POST['update'])) {
    $requestID = $_POST['update_request_id'];
    $approvedAmount = $_POST['update_amount'];

    $sql = "UPDATE request SET ApprovedAmount = :approvedAmount WHERE requestID = :requestID";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':approvedAmount', $approvedAmount);
    $stmt->bindParam(':requestID', $requestID);

    if ($stmt->execute()) {
        $successMessage = "Update successful";
    } else {
        $errorMessage = "Update failed";
    }
}



    $input = json_decode(file_get_contents("php://input"), true);

   if ($input && isset($input["requestID"], $input["status"])) {
    $requestID = intval($input["requestID"]);
    $status = $input["status"];
    $success = false;

    if ($status === "Approved" && isset($input["approvedAmount"])) {
        $approvedAmount = floatval($input["approvedAmount"]);
        if ($approvedAmount <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid amount"]);
            exit;
        }

  
        $checkStmt = $pdo->prepare("
            SELECT (c.Amount - COALESCE(c.usedAllocation, 0)) as balance 
            FROM costallocation c 
            JOIN request r ON r.allocationID = c.allocationID 
            WHERE r.requestID = :id
        ");
        $checkStmt->execute([':id' => $requestID]);
        $balanceRow = $checkStmt->fetch(PDO::FETCH_ASSOC);
        $balance = $balanceRow ? floatval($balanceRow['balance']) : 0;

        if ($approvedAmount > $balance) {
            echo json_encode(["success" => false, "message" => "Approved amount exceeds available balance of ₱" . number_format($balance, 2)]);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $update = $pdo->prepare("
                UPDATE request 
                SET status = :status, ApprovedAmount = :amount 
                WHERE requestID = :id
            ");
            $update->execute([
                ":status" => $status,
                ":amount" => $approvedAmount,
                ":id" => $requestID
            ]);

            
            $updateAlloc = $pdo->prepare("
                UPDATE costallocation 
                SET usedAllocation = COALESCE(usedAllocation, 0) + :amount 
                WHERE allocationID = (SELECT allocationID FROM request WHERE requestID = :id)
            ");
            $updateAlloc->execute([
                ":amount" => $approvedAmount,
                ":id" => $requestID
            ]);

            $pdo->commit();
            $success = true;
        } catch (PDOException $e) {
            $pdo->rollback();
            error_log("Database error: " . $e->getMessage());
            $success = false;
        }
    } elseif ($status === "Rejected") {
        $remarks = isset($input["remarks"]) ? trim($input["remarks"]) : "Rejected by approver";
        try {
            $update = $pdo->prepare("
                UPDATE request 
                SET status = :status, Remarks = :remarks 
                WHERE requestID = :id
            ");
            $success = $update->execute([
                ":status" => $status,
                ":remarks" => $remarks,
                ":id" => $requestID
            ]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $success = false;
        }
    }

    echo json_encode(["success" => $success, "message" => $success ? "Update successful" : "Update failed"]);
    exit;
}

}

try {
    $sql = "SELECT r.* ,
     ch.accountName AS Title,
    d.Name
    FROM request r
JOIN costallocation c on r.allocationID = c.allocationID
JOIN chartofaccount ch ON c.accountID = ch.accountID 
JOIN departmentbudget d on c.Deptbudget = d.Deptbudget

    
     WHERE r.Archive = 'NO'";
    $stmt = $pdo->query($sql);
    $disbursementReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}
?>