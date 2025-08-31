<?php

include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM customers WHERE customer_id  = :id";
$stmt = $pdo['ar']->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
 if(isset($_POST['archive'])){
        $custumerID = $_POST['archive_custumerID'];

        $sql = "UPDATE customers SET Archive = 'YES' WHERE customer_id  = :custumerID";
        $stmt = $pdo['ar']->prepare($sql);
        $stmt->bindParam(':custumerID', $custumerID);

        try {
            $stmt->execute();
            echo "✅ Custumer archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
if(isset($_POST['update'])) {
        $custumerID = $_POST['custumerID'];
        $custumerName = $_POST['custumerName'];
        $contactPerson = $_POST['contactPerson'];
        $contactNumber = $_POST['contactNumber'];
        $email = $_POST['email'];
        $address = $_POST['address'];
        $terms = $_POST['terms'];
        $adjustmentDate = $_POST['adjustmentDate'];

        $sql = "UPDATE customers SET 
                name = :custumerName,
                contact_person = :contactPerson,
                phone = :contactNumber,
                email = :email,
                address_line = :address,
                payment_terms = :terms,
              
                updated_at = :adjustmentDate
                WHERE customer_id  = :custumerID";

        $stmt = $pdo['ar']->prepare($sql);
        $stmt->bindParam(':custumerName', $custumerName);
        $stmt->bindParam(':contactPerson', $contactPerson);
        $stmt->bindParam(':contactNumber', $contactNumber);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':terms', $terms);
        $stmt->bindParam(':creditLimit', $creditLimit);
        $stmt->bindParam(':adjustmentDate', $adjustmentDate);
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
    $sql = "SELECT * FROM  customers WHERE Archive = 'NO'
            ORDER BY created_at Asc";
    $stmt = $pdo['ar']->query($sql);
    $custumerReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>

