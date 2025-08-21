<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['plan'])) {
    $plan      = $_POST['requestTitle'];
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
    }
}



try {
    $sql = "SELECT * FROM collection_plan
            ORDER BY Date Asc";
    $stmt = $pdo->query($sql);
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}



if(isset($_GET['planID'])){
    $id=$_GET["planID"];
    $stmt = $pdo ->prepare("SELECT * FROM collection_plan WHERE id= :id ");
    $stmt ->bindParam(':id',$id);
    $stmt->execute;
    $plan =$stmt->fetch(PDO::FETCH_ASSOC);
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['requestTitle'] ?? null;
    $remaining = $_POST['remaining'] ?? null;
    $planType = $_POST['planType'] ?? null;

    if (!$id || !$title || !$remaining || !$planType) {
        echo "⚠️ Missing required fields";
        exit;
    }

    $sql = "UPDATE collection_plan SET plan = :plan, remaining_days = :remaining, plan_type = :planType WHERE planID = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':plan', $title);
    $stmt->bindParam(':remaining', $remaining);
    $stmt->bindParam(':planType', $planType);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) echo "✅ Plan updated successfully!";
    else echo "❌ Update failed!";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if (!$id) { echo "⚠️ Plan ID missing"; exit; }

    $stmt = $pdo->prepare("UPDATE collection_plan SET Archive='YES' WHERE planID=:id");
    $stmt->bindParam(':id', $id);
    if ($stmt->execute()) echo "✅ Plan archived successfully!";
    else echo "❌ Failed to archive!";
}
?>
