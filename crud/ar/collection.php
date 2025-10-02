<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

function generateReceiptImage($receiptNumber, $invoiceRef, $amount, $method, $issuedBy, $receiptID) {
    $width = 600;
    $height = 800;
    $im = imagecreatetruecolor($width, $height);

  
    $white = imagecolorallocate($im, 255, 255, 255);
    $black = imagecolorallocate($im, 0, 0, 0);
    $gray  = imagecolorallocate($im, 128, 128, 128);
    $green = imagecolorallocate($im, 0, 128, 0);


    imagefilledrectangle($im, 0, 0, $width, $height, $white);


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

 
    $folder = __DIR__ . "/../../uploads/receipt/";
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }

    $fileName = "receipt_" . $receiptID . ".png";
    $filePath = $folder . $fileName;

    imagepng($im, $filePath);
    imagedestroy($im);

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
            $successMessage = "Customer archived successfully.";
        } catch (PDOException $e) {
            $errorMessage = "Error: " . $e->getMessage();
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
            $successMessage = "Customer updated successfully.";
        } catch (PDOException $e) {
            $errorMessage = "Error: " . $e->getMessage();
        }
    }

    if (isset($_POST['create'])) {
        $invoiceID = $_POST['Invoice'];
        $amount = $_POST['Amount'];
        $method = $_POST['paymethod'];
        $issuedBy = "Admin";

        try {
            $pdo->beginTransaction();

   
            $sql = "INSERT INTO ar_collections (invoice_id, amount, method, remarks, payment_date, created_at) 
                    VALUES (:invoiceID, :amount, :method, :remarks, NOW(), NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':invoiceID', $invoiceID);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':method', $method);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->execute();
            $paymentID = $pdo->lastInsertId();

            $stmtInvoice = $pdo->prepare("SELECT reference_no, amount, stat FROM ar_invoices WHERE invoice_id = :id");
            $stmtInvoice->bindParam(':id', $invoiceID);
            $stmtInvoice->execute();
            $invoiceData = $stmtInvoice->fetch(PDO::FETCH_ASSOC);

            if (!$invoiceData) {
                throw new Exception("Invoice not found.");
            }

            $invoiceRef = $invoiceData['reference_no'];
            $invoiceAmount = $invoiceData['amount'];

  
            $stmtPaidAmount = $pdo->prepare("SELECT SUM(amount) FROM ar_collections WHERE invoice_id = :invoiceID");
            $stmtPaidAmount->bindParam(':invoiceID', $invoiceID);
            $stmtPaidAmount->execute();
            $totalPaid = $stmtPaidAmount->fetchColumn();

 
            if ($totalPaid >= $invoiceAmount) {
                $newInvoiceStat = 'Paid';
                $newRemarks = 'Full Payment';
            } else {
                $newInvoiceStat = 'Partially Paid';
                $newRemarks = 'Partial Payment';
            }
            
  
            $sql = "UPDATE ar_collections SET remarks = :remarks WHERE collection_id = :paymentID";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':remarks', $newRemarks);
            $stmt->bindParam(':paymentID', $paymentID);
            $stmt->execute();


            $sqlInvoiceUpdate = "UPDATE ar_invoices SET stat = :stat WHERE invoice_id = :invoiceID";
            $stmtInvoiceUpdate = $pdo->prepare($sqlInvoiceUpdate);
            $stmtInvoiceUpdate->bindParam(':stat', $newInvoiceStat);
            $stmtInvoiceUpdate->bindParam(':invoiceID', $invoiceID);
            $stmtInvoiceUpdate->execute();
            
 
            $sqlFollowUpdate = "UPDATE follow SET paymentstatus = :paymentstatus WHERE InvoiceID = :invoiceID AND Archive = 'NO'";
            $stmtFollowUpdate = $pdo->prepare($sqlFollowUpdate);
            $stmtFollowUpdate->bindParam(':paymentstatus', $newInvoiceStat);
            $stmtFollowUpdate->bindParam(':invoiceID', $invoiceID);
            $stmtFollowUpdate->execute();

     
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
       
            $sqlEntries = "
                INSERT INTO entries (date, description, referenceType, createdBy, Archive)
                VALUES (CURDATE(), :description, :ref, :createdBy, 'NO')
            ";
            $stmtEntries = $pdo->prepare($sqlEntries);
            $stmtEntries->bindValue(':description', 'Collection for Invoice #' . $invoiceRef);
            $stmtEntries->bindValue(':ref', 'INV-' . $invoiceRef);
            $stmtEntries->bindValue(':createdBy', $issuedBy);
            $stmtEntries->execute();
            $journalID = $pdo->lastInsertId();

          
            $detailSqlDebit = "
                INSERT INTO details (journalID, accountID, debit, credit, Archive)
                VALUES (:journalID, :accountID, :debit, :credit, 'NO')
            ";
            $stmtDetailsDebit = $pdo->prepare($detailSqlDebit);
            $stmtDetailsDebit->bindParam(':journalID', $journalID);
            $stmtDetailsDebit->bindValue(':accountID', 1); 
            $stmtDetailsDebit->bindParam(':debit', $amount);
            $stmtDetailsDebit->bindValue(':credit', 0);
            $stmtDetailsDebit->execute();

            $detailSqlCredit = "
                INSERT INTO details (journalID, accountID, debit, credit, Archive)
                VALUES (:journalID, :accountID, :debit, :credit, 'NO')
            ";
            $stmtDetailsCredit = $pdo->prepare($detailSqlCredit);
            $stmtDetailsCredit->bindParam(':journalID', $journalID);
            $stmtDetailsCredit->bindValue(':accountID', 3); 
            $stmtDetailsCredit->bindValue(':debit', 0);
            $stmtDetailsCredit->bindParam(':credit', $amount);
            $stmtDetailsCredit->execute();

            $pdo->commit();

            $successMessage = "Collection & Receipt created successfully. <a href='{$imagePath}' target='_blank'>View Receipt</a>";

        } catch (PDOException $e) {
            $pdo->rollBack();
            $errorMessage = "Error: " . $e->getMessage();
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMessage = "Error: " . $e->getMessage();
        }
    }
}

try {
    $sql = "SELECT * FROM ar_collections WHERE Archive='NO' ORDER BY created_at ASC";
    $stmt = $pdo->query($sql);
    $collectionReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Error fetching collections: " . $e->getMessage();
}
?>
