<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

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


$sql = "SELECT IFNULL(SUM(ApprovedAmount),0) as total FROM request WHERE status IN ('Paid') AND Archive = 'NO'";
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


$sql = "SELECT requestID, requestTitle, Modules, ApprovedAmount, Requested_by, Due, status, date 
        FROM request 
        WHERE status IN ('Approved','Verified') AND Archive = 'NO'
        ORDER BY date DESC 
        LIMIT 12";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input["requestID"])) {
        $requestID = intval($input["requestID"]);
        $update = $pdo->prepare("UPDATE request SET status = 'Paid' WHERE requestID = :id");
        $update->execute([":id" => $requestID]);
        echo json_encode(["success" => true]);
        exit;
    }
}
?>