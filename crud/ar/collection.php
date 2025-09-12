<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

function generateReceiptImage($receiptNumber, $invoiceRef, $amount, $method, $issuedBy, $receiptID) {
    $width = 600;
    $height = 800;
    $im = imagecreatetruecolor($width, $height);

    // Colors
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    $gray  = imagecolorallocate($im, 128, 128, 128);
    $green = imagecolorallocate($im, 0, 128, 0);

    // Fill background
    imagefilledrectangle($im, 0, 0, $width, $height, $white);

    // Text
    $y = 20;
    imagestring($im, 5, 150, $y, "SLATE Freight Mgmt System", $black);
    $y += 30;
    imagestring($im, 3, 200, $y, "123 Business Avenue, SF, CA", $black);
    $y += 20;
    imagestring($im, 3, 220, $y, "www.slatefreight.com", $black);
    $y += 30;

    imagestring($im, 3, 50, $y, "Receipt No: $receiptNumber", $black);
    imagestring($im, 3, 350, $y, "Date: " . date("F j, Y"), $black);
    $y += 20;
    imagestring($im, 3, 50, $y, "Invoice Ref: $invoiceRef", $black);
    $y += 30;

    imagestring($im, 4, 50, $y, "Bill To:", $black);
    $y += 20;
    imagestring($im, 3, 50, $y, "Client Company Ltd.", $black);
    $y += 20;
    imagestring($im, 3, 50, $y, "Attn: John Smith", $black);
    $y += 30;

    imagestring($im, 3, 50, $y, "Description", $black);
    imagestring($im, 3, 400, $y, "Amount", $black);
    $y += 20;
    imagestring($im, 3, 50, $y, "Payment", $black);
    imagestring($im, 3, 400, $y, number_format($amount, 2), $black);
    $y += 30;

    imagestring($im, 4, 50, $y, "TOTAL PAID:", $black);
    imagestring($im, 4, 400, $y, number_format($amount, 2), $green);
    $y += 30;

    imagestring($im, 3, 50, $y, "Method: $method", $black);
    $y += 20;
    imagestring($im, 3, 50, $y, "Issued By: $issuedBy", $black);
    $y += 40;

    imagestring($im, 2, 100, $y, "Thank you for your business! This is a computer-generated receipt.", $gray);

    // Save image
    $folder = __DIR__ . "/../../uploads/receipt/";
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $fileName = "receipt_" . $receiptID . ".png";
    $filePath = $folder . $fileName;

    imagepng($im, $filePath);
    imagedestroy($im);

    // Return relative path for DB (so it works in <img src>)
    return "uploads/receipt/" . $fileName;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

 
    if (isset($_POST['archive'])) {
        $custumerID = $_POST['archive_collectionID'];

        $sql = "UPDATE ar_collections SET Archive = 'YES' WHERE collection_id = :custumerID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':custumerID', $custumerID);

        try {
            $stmt->execute();
            echo "✅ Customer archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }

    if (isset($_POST['update'])) {
        $custumerID = $_POST['update_collectionID'];
        $custumerName = $_POST['update_invoiceID'];
        $contactNumber = $_POST['update_amount'];
        $email = $_POST['update_paymentMethod'];
        $address = $_POST['update_remarks'];

        $sql = "UPDATE ar_collections SET 
                    invoice_id = :custumerName,
                    amount = :contactNumber,
                    method = :email,
                    remarks = :address
                WHERE collection_id = :custumerID";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':custumerName', $custumerName);
        $stmt->bindParam(':contactNumber', $contactNumber);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':custumerID', $custumerID);

        try {
            $stmt->execute();
            echo "✅ Customer updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }

  if (isset($_POST['create'])) {
        $invoiceID = $_POST['Invoice'];
        $amount = $_POST['Amount'];
        $method = $_POST['paymethod'];
        $remarks = $_POST['Remarks'];
        $issuedBy = "Admin";

        try {
            // Insert collection
            $sql = "INSERT INTO ar_collections (invoice_id, amount, method, remarks, payment_date, created_at) 
                    VALUES (:invoiceID, :amount, :method, :remarks, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':invoiceID', $invoiceID);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':method', $method);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->execute();
            $paymentID = $pdo->lastInsertId();
           
            $stmtInvoice = $pdo->prepare("SELECT reference_no FROM ar_invoices WHERE invoice_id  = :id");
              $stmtInvoice->bindParam(':id', $invoiceID);
             $stmtInvoice->execute();
               $invoiceRef = $stmtInvoice->fetchColumn();
     
            $receiptNumber = "RCP-" . date("Y") . "-" . str_pad($paymentID, 5, "0", STR_PAD_LEFT);

            $sqlReceipt = "INSERT INTO receipt (paymentID, receiptNumber, receiptsdate, issueBy, receiptImage) 
                           VALUES (:paymentID, :receiptNumber, NOW(), :issuedBy, '')";
            $stmtReceipt = $pdo->prepare($sqlReceipt);
            $stmtReceipt->bindParam(':paymentID', $paymentID);
            $stmtReceipt->bindParam(':receiptNumber', $receiptNumber);
            $stmtReceipt->bindParam(':issuedBy', $issuedBy);
            $stmtReceipt->execute();
            $receiptID = $pdo->lastInsertId();

            $imagePath = generateReceiptImage($receiptNumber, $invoiceRef, $amount, $method, $issuedBy, $receiptID);

            $sql = "UPDATE receipt SET receiptImage = :imagePath WHERE receiptID = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':imagePath', $imagePath);
            $stmt->bindParam(':id', $receiptID);
            $stmt->execute();

            echo "✅ Collection & Receipt created successfully. <br><a href='{$imagePath}' target='_blank'>View Receipt</a>";

        } catch (PDOException $e) {
            die("❌ Error: " . $e->getMessage());
        }
    }

}

try {
    $sql = "SELECT * FROM ar_collections WHERE Archive='NO' ORDER BY created_at ASC";
    $stmt = $pdo->query($sql);
    $collectionReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Error fetching collections: " . $e->getMessage());
}
?>
