<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
if ($id) {
    $sql = "SELECT * FROM vendor WHERE vendor_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['archive'])) {
        $archive_vendorID = $_POST['archive_collectionID'];

        $sql = "UPDATE vendor SET 
                Archive = 'YES'
                WHERE vendor_id = :archive_vendorID";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':archive_vendorID', $archive_vendorID);

        try {
            $stmt->execute();
            echo "✅ Vendor archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    if (isset($_POST['update'])) {
        $vendor_id = $_POST['vendors_id'];
        $vendor_name = $_POST['name'];
        $contact_info = $_POST['contact'];
        $address = $_POST['address'];
        $email = $_POST['email'];
        $contact_person = $_POST['phone'];
        $status = $_POST['status'];

        $sql = "UPDATE vendor SET 
                vendor_name = :vendor_name,
                contact_info = :contact_info,
                address = :address,
                Email = :email,
                Contact_person = :contact_person,
                Status = :status
                WHERE vendor_id = :vendor_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':vendor_name', $vendor_name);
        $stmt->bindParam(':contact_info', $contact_info);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contact_person', $contact_person);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':vendor_id', $vendor_id);

        try {
            $stmt->execute();
            echo "✅ Vendor updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    if (isset($_POST['create'])) {
  
        $vendor_name = $_POST['vendorName'];
        $contact_info = $_POST['contactInfo'];
        $address = $_POST['address'];
        $email = $_POST['email'];
        $contact_person = $_POST['contactPerson'];
      

        $sql = "INSERT INTO vendor ( vendor_name, contact_info, address, Email, Contact_person) 
                VALUES ( :vendor_name, :contact_info, :address, :email, :contact_person)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':vendor_name', $vendor_name);
        $stmt->bindParam(':contact_info', $contact_info);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contact_person', $contact_person);


        try {
            $stmt->execute();
            echo "✅ Vendor created successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
}

try {
    $sql = "SELECT * FROM vendor WHERE Archive = 'NO'
            ORDER BY vendor_id ASC";
    $stmt = $pdo->query($sql);
    $adjustReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching vendors: " . $e->getMessage();
}
?>