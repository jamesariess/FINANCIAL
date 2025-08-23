<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM follow WHERE reminderID = :id";
$stmt = $pdo['collection']->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    if (isset($_POST['archive'])) {
        $reminderID = $_POST['reminderID'];
        $sql = "UPDATE  follow SET Archive = 'YES' WHERE reminderID = :reminderID";
        $stmt = $pdo['collection']->prepare($sql);
        $stmt->bindParam(':reminderID', $reminderID);

        try {
            $stmt->execute();
            echo "✅ Payment archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    if(isset($_POST['update'])){
        $reminderID = $_POST['reminderID'];
        $planID = $_POST['planID'];
        $invoiceID = $_POST['InvoiceID'];
        $followUpDate = $_POST['FollowUpDate'];
        $contactinfo = $_POST['Contactinfo'];
        $remarks = $_POST['Remarks'];
        $status = $_POST['status'];
        if (empty($followUpDate) || empty($contactinfo) || empty($remarks) || empty($status)) {
            echo "Don't Leave Empty.";
            exit;
        } else {
            $sql = "UPDATE  follow SET planID = :planID, InvoiceID = :invoiceID, FollowUpDate = :followUpDate, Contactinfo = :contactinfo, Remarks = :remarks, paymentstatus = :status WHERE reminderID = :reminderID";
            $stmt = $pdo['collection']->prepare($sql);
            $stmt->bindParam(':planID', $planID);
            $stmt->bindParam(':invoiceID', $invoiceID);
            $stmt->bindParam(':followUpDate', $followUpDate);
            $stmt->bindParam(':contactinfo', $contactinfo);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':reminderID', $reminderID);

            try {
                $stmt->execute();
                echo "✅ Reminder updated successfully.";
            } catch (PDOException $e) {
                echo "❌ Error: " . $e->getMessage();
            }
        }
   
    }
    
   
}



try {
    $sql = "SELECT * FROM  follow WHERE Archive = 'NO'
            ORDER BY FollowUpDate Asc";
    $stmt = $pdo['collection']->query($sql);
    $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>
