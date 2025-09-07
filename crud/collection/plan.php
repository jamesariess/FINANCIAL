<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM collection_plan WHERE planID = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['plans'])){
    $plan = $_POST['requestTitle'];
    $remaining = $_POST['remaining'];
    $planType  = $_POST['planType'];
    $created_at = date('Y-m-d H:i:s');

    if (empty($plan) || empty($remaining) || empty($planType)) {
        echo "All fields are required.";
        exit;
    } else {
        $sql = "INSERT INTO collection_plan(`plan`, remaining_days, plan_type, status, Archive,Date)
                VALUES(:plan, :remaining_days, :plan_type, 'Active', 'NO',:created_at)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plan', $plan);
        $stmt->bindParam(':remaining_days', $remaining);
        $stmt->bindParam(':plan_type', $planType);
        $stmt->bindParam(':created_at',$created_at);

        try {
            $stmt->execute();
            echo "✅ Collection plan created successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }}
    if (isset($_POST['archive'])) {
        $planID = $_POST['planID'];
        $sql = "UPDATE collection_plan SET Archive = 'YES' WHERE planID = :planID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':planID', $planID);

        try {
            $stmt->execute();
            echo "✅ Collection plan archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    if(isset($_POST['update'])){
        $planID = $_POST['planID'];
        $plan = $_POST['plan'];
        $remaining = $_POST['remaining'];
        $planType = $_POST['type'];
        $status = $_POST['status'];

        if (empty($plan) || empty($remaining) || empty($planType)) {
            echo "All fields are required.";
            exit;
        } else {
            $sql = "UPDATE collection_plan SET plan = :plan, remaining_days = :remaining_days, plan_type = :plan_type, status=:status WHERE planID = :planID";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':plan', $plan);
            $stmt->bindParam(':remaining_days', $remaining);
            $stmt->bindParam(':plan_type', $planType);
            $stmt->bindParam(':planID', $planID);
            $stmt->bindParam(':status', $status);

            try {
                $stmt->execute();
                echo "✅ Collection plan updated successfully.";
            } catch (PDOException $e) {
                echo "❌ Error: " . $e->getMessage();
            }
        }
    }
    
   
}



try {
    $sql = "SELECT * FROM collection_plan WHERE Archive = 'NO'
            ORDER BY Date Asc";
    $stmt = $pdo->query($sql);
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>
