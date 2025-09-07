<?php

include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM ar_invoices WHERE invoice_id  = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
if(isset($_POST['archive'])) {
        $archive_InvoiceID = $_POST['archive_InvoiceID'];
        $archiveDate = date('Y-m-d H:i:s');

        $sql = "UPDATE ar_invoices SET 
                Archive = 'YES'
            
                WHERE invoice_id  = :archive_InvoiceID";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':archive_InvoiceID', $archive_InvoiceID);

        try {
            $stmt->execute();
            echo "✅ Invoice archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
 if(isset($_POST['update'])) {
        $invoice_id = $_POST['update_InvoiceID'];
        $customer_id = $_POST['update_CustumerName'];
        $amount = $_POST['update_Address'];
        $description = $_POST['update_Email'];
        $reference_number = $_POST['update_PaymentTerms'];
        $due_date = $_POST['update_ContactNumber'];
        $status = $_POST['update_Status'];
        $updated_at = date('Y-m-d H:i:s');

        $sql = "UPDATE ar_invoices SET 
                customer_id = :customer_id,
                description = :description,
                reference_no = :reference_number,
                amount = :amount,
                due_date = :due_date,
                stat = :status,
                updated_at = :updated_at
                WHERE invoice_id  = :invoice_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':due_date', $due_date);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':reference_number', $reference_number);
        $stmt->bindParam(':updated_at', $updated_at);
        $stmt->bindParam(':invoice_id', $invoice_id);

        try {
            $stmt->execute();
            echo "✅ Invoice updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
   
}

try {
    $sql = "SELECT * FROM  ar_invoices WHERE Archive = 'NO'
            ORDER BY created_at Asc";
    $stmt = $pdo->query($sql);
    $invoiceReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>

