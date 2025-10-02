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
            $successMessage = "✅ Vendor archived successfully.";
        } catch (PDOException $e) {
            $errorMessage = "❌ Error: " . $e->getMessage();
        }
    }

    if (isset($_POST['update'])) {
        $vendor_id = $_POST['vendors_id'];
        $vendor_name = trim($_POST['name'] ?? '');
        $contact_info = trim($_POST['contact'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_person = trim($_POST['phone'] ?? '');
        $status = $_POST['status'] ?? '';

      
        $validationErrors = [];
        if (empty($vendor_name)) {
            $validationErrors[] = "Vendor name is required.";
        } elseif (strlen($vendor_name) > 255) {
            $validationErrors[] = "Vendor name must not exceed 255 characters.";
        }

        if (empty($contact_info)) {
            $validationErrors[] = "Contact information is required.";
        }

        if (empty($address)) {
            $validationErrors[] = "Address is required.";
        }

        if (empty($email)) {
            $validationErrors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationErrors[] = "Invalid email format.";
        }

        if (empty($contact_person)) {
            $validationErrors[] = "Contact person/phone is required.";
        }

        if (empty($status)) {
            $validationErrors[] = "Status is required.";
        }

        if (empty($validationErrors)) {
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
                $successMessage = "✅ Vendor updated successfully.";
            } catch (PDOException $e) {
                $errorMessage = "❌ Error: " . $e->getMessage();
            }
        } else {
            $errorMessage = "❌ Validation errors: " . implode(' ', $validationErrors);
        }
    }

    if (isset($_POST['create'])) {
        $vendor_name = trim($_POST['vendorName'] ?? '');
        $contact_info = trim($_POST['contactInfo'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contact_person = trim($_POST['contactPerson'] ?? '');

        
        $validationErrors = [];
        if (empty($vendor_name)) {
            $validationErrors[] = "Vendor name is required.";
        } elseif (strlen($vendor_name) > 255) {
            $validationErrors[] = "Vendor name must not exceed 255 characters.";
        }

        if (empty($contact_info)) {
            $validationErrors[] = "Contact information is required.";
        }

        if (empty($address)) {
            $validationErrors[] = "Address is required.";
        }

        if (empty($email)) {
            $validationErrors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationErrors[] = "Invalid email format.";
        }

        if (empty($contact_person)) {
            $validationErrors[] = "Contact person/phone is required.";
        } 

        if (empty($validationErrors)) {
            $sql = "INSERT INTO vendor (vendor_name, contact_info, address, Email, Contact_person) 
                    VALUES (:vendor_name, :contact_info, :address, :email, :contact_person)";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':vendor_name', $vendor_name);
            $stmt->bindParam(':contact_info', $contact_info);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':contact_person', $contact_person);

            try {
                $stmt->execute();
                $successMessage = "✅ Vendor created successfully.";
            } catch (PDOException $e) {
                $errorMessage = "❌ Error: " . $e->getMessage();
            }
        } else {
            $errorMessage = "❌ Validation errors: " . implode(' ', $validationErrors);
        }
    }
}

try {
    $sql = "SELECT * FROM vendor WHERE Archive = 'NO'
            ORDER BY vendor_id ASC";
    $stmt = $pdo->query($sql);
    $adjustReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "❌ Error fetching vendors: " . $e->getMessage();
}
?>