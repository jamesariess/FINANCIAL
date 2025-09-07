<?php

include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM ap_payments WHERE payment_id  = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
 if(isset($_POST['archive'])){
        $custumerID = $_POST['archive_collectionID'];

        $sql = "UPDATE ap_payments SET Archive = 'YES' WHERE payment_id  = :custumerID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':custumerID', $custumerID);

        try {
            $stmt->execute();
            echo "✅ Custumer archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
if(isset($_POST['update'])) {
        $custumerID = $_POST['update_collectionID'];
        $custumerName = $_POST['update_invoiceID'];
        $contactNumber = $_POST['update_amount'];
        $email = $_POST['update_paymentMethod'];
        $address = $_POST['update_remarks'];

        $sql = "UPDATE ap_payments SET 
                bill_id  = :custumerName,
                amount = :contactNumber,
                method = :email,
                remarks = :address
                WHERE payment_id  = :custumerID";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':custumerName', $custumerName);
        $stmt->bindParam(':contactNumber', $contactNumber);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':custumerID', $custumerID);

        try {
            $stmt->execute();
            echo "✅ Custumer updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
   
}

try {
    $sql = "SELECT * FROM  ap_payments WHERE Archive = 'NO'
            ORDER BY created_at Asc";
    $stmt = $pdo->query($sql);
    $collectionReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>

