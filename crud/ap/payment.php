<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM ap_payments WHERE payment_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['archive'])) {
        $custumerID = $_POST['archive_collectionID'];

        $sql = "UPDATE ap_payments SET Archive = 'YES' WHERE payment_id = :custumerID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':custumerID', $custumerID);

        try {
            $stmt->execute();
            $successMessage = "✅ Customer archived successfully.";
        } catch (PDOException $e) {
            $errorMessage = "❌ Error archiving customer: " . $e->getMessage();
        }
    }

    if (isset($_POST['update'])) {
        $custumerID = $_POST['update_collectionID'];
        $contactNumber = $_POST['update_amount'];
        $email = $_POST['update_paymentMethod'];
        $address = $_POST['update_remarks'];

        $sql = "UPDATE ap_payments SET 
                    amount = :contactNumber,
                    method = :email,
                    remarks = :address
                WHERE payment_id = :custumerID";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':contactNumber', $contactNumber);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':custumerID', $custumerID);

        try {
            $stmt->execute();
            $successMessage = "✅ Customer updated successfully.";
        } catch (PDOException $e) {
            $errorMessage = "❌ Error updating customer: " . $e->getMessage();
        }
    }
}

try {
    $sql = "SELECT * FROM ap_payments WHERE Archive = 'NO'
            ORDER BY created_at ASC";
    $stmt = $pdo->query($sql);
    $collectionReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "❌ Error fetching payments: " . $e->getMessage();
}
?>
