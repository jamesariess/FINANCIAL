<?php

include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM vendors WHERE vendor_id   = :id";
$stmt = $pdo['account']->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
if(isset($_POST['archive'])) {
        $archive_adjustID = $_POST['archive_collectionID'];

        $sql = "UPDATE  vendors SET 
                Archive = 'YES'
                WHERE vendor_id  = :archive_adjustID";

        $stmt = $pdo['account']->prepare($sql);
        $stmt->bindParam(':archive_adjustID', $archive_adjustID);

        try {
            $stmt->execute();
            echo "✅ Adjustment archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }if (isset($_POST['update'])) {
        $adjustment_id = $_POST['vendors_id'];
        $invoice_id = $_POST['name'];
        $type = $_POST['contact'];
        $amount = $_POST['email'];
        $reason = $_POST['phone'];
        $status = $_POST['address'];
        $created_at = $_POST['terms'];
        $stats =$_POST['status'];
        
        $sql = "UPDATE  vendors SET 
                name = :invoice_id,

                contact_person = :type,
                email = :amount,
                phone = :reason,
                address_line = :status,
                payment_terms = :created_at,
                is_active = :stats

                WHERE vendor_id = :adjustment_id";


        $stmt = $pdo['account']->prepare($sql);
        $stmt->bindParam(':invoice_id', $invoice_id);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':created_at', $created_at);
        $stmt->bindParam(':stats', $stats);

        $stmt->bindParam(':adjustment_id', $adjustment_id);
        try {
            $stmt->execute();
            echo "✅ Adjustment updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
       
    }
   
}

try {
    $sql = "SELECT * FROM vendors WHERE Archive = 'NO'
            ORDER BY created_at Asc";
    $stmt = $pdo['account']->query($sql);
    $adjustReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>

