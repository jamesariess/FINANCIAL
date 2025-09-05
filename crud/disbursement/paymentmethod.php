<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM paymentmethod WHERE payment_method_id = :id";
$stmt = $pdo['budget']->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if(isset($_POST['archive'])){
        $payment_method_id = $_POST['archive_payment_method_id'];
        $archive = 'YES';

        $sql = "UPDATE paymentmethod SET Archive = :archive WHERE payment_method_id = :payment_method_id";
        $stmt = $pdo['budget']->prepare($sql);
        $stmt->bindParam(':archive', $archive);
        $stmt->bindParam(':payment_method_id', $payment_method_id);

        try {
            $stmt->execute();
            echo "✅ Payment method archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    if(isset($_POST['update'])){
        $payment_method_id = $_POST['update_payment_method_id'];
        $method_name = $_POST['update_method_name'];
        $account_details = $_POST['update_account_details'];
        $status = $_POST['update_status'];

        $sql = "UPDATE paymentmethod SET method_name = :method_name, account_details = :account_details, status = :status WHERE payment_method_id = :payment_method_id";
        $stmt = $pdo['budget']->prepare($sql);
        $stmt->bindParam(':method_name', $method_name);
        $stmt->bindParam(':account_details', $account_details);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':payment_method_id', $payment_method_id);

        try {
            $stmt->execute();
            echo "✅ Payment method updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    
}

try {
    $sql = "SELECT * FROM  paymentmethod WHERE Archive = 'NO'
             ORDER BY method_name Asc";
    $stmt = $pdo['budget']->query($sql);
    $paymentmethodReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>