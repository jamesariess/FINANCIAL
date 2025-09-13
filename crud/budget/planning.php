<?php
// PHP Backend Logic
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

// Fetch requests
$sql = "SELECT requestID, requestTitle, Modules, Amount, Requested_by, Due, status, date 
        FROM request 
        WHERE status IN ('Verified') AND Archive = 'NO'
        ORDER BY date DESC 
        LIMIT 12";
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  
    die("Database query failed: " . $e->getMessage());
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
        $stmt->execute();
        exit; 
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

            try {
                $update = $pdo->prepare("
                    UPDATE request 
                    SET status = :status, ApprovedAmount = :amount 
                    WHERE requestID = :id
                ");
                $success = $update->execute([
                    ":status" => $status,
                    ":amount" => $approvedAmount,
                    ":id" => $requestID
                ]);
            } catch (PDOException $e) {
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
                $success = false;
            }
        }

        echo json_encode(["success" => $success]);
        exit;
    }
}

try {
    $sql = "SELECT * FROM  request WHERE Archive = 'NO'";
    $stmt = $pdo->query($sql);
    $disbursementReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}
?>