<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

$data = [
    "totalRequest" => 0,
    "totalAmountRelease" => 0,
    "rejectedRequest" => 0,
    "newRequest" => 0
];

// Total requests
$sql = "SELECT COUNT(*) as total FROM request WHERE Archive = 'NO'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data["totalRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total amount released
$sql = "SELECT IFNULL(SUM(ApprovedAmount), 0) as total FROM request WHERE status IN ('Paid') AND Archive = 'NO'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data["totalAmountRelease"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Rejected requests
$sql = "SELECT COUNT(*) as total FROM request WHERE status = 'Reject' AND Archive = 'NO'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data["rejectedRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// New requests today
$sql = "SELECT COUNT(*) as total FROM request WHERE DATE(date) = CURDATE() AND Archive = 'NO'";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$data["newRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch requests with joins
$sql = "SELECT
         r.requestID, r.requestTitle, r.ApprovedAmount, r.Requested_by, r.Due, r.status, r.date,
         c.Title, c.allocationID,
         d.Name
        FROM request r
        JOIN costallocation c ON r.allocationID = c.allocationID
        JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
        WHERE r.status IN ('Approved', 'Verified') AND r.Archive = 'NO'
        ORDER BY r.date DESC 
        LIMIT 12";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle POST request to update status and Usedallocation
// Handle POST request to update status and Usedallocation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input["requestID"])) {
        $requestID = intval($input["requestID"]);
        try {
            // Start a transaction
            $pdo->beginTransaction();

            
            $sql = "SELECT ApprovedAmount, allocationID FROM request WHERE requestID = :id AND status = 'Approved' AND Archive = 'NO'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([":id" => $requestID]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($request) {
                $approvedAmount = $request['ApprovedAmount'];
                $allocationID = $request['allocationID'];
                


                $updateRequest = $pdo->prepare("UPDATE request SET status = 'Paid' WHERE requestID = :id");
                $updateRequest->execute([":id" => $requestID]);

          
                $updateAllocation = $pdo->prepare("UPDATE costallocation SET usedAllocation = COALESCE(usedAllocation, 0) + :amount WHERE allocationID = :allocationID");
                $updateAllocation->execute([
                    ":amount" => $approvedAmount,
                    ":allocationID" => $allocationID
                ]);

               
         
             

                $pdo->commit();
                echo json_encode(["success" => true]);
            } else {
             
                $pdo->rollBack();
                echo json_encode(["success" => false, "error" => "Request not found or not approved"]);
            }
        } catch (Exception $e) {
            // Roll back on error
            $pdo->rollBack();
            echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
        }
        exit;
    }
}
?>