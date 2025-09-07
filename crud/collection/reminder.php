<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM follow WHERE reminderID = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
if (isset($_POST['create'])) {
    $planID    = $_POST['planID'] ?? null;
    $invoiceID = $_POST['InvoiceID'] ?? null;
    $contactinfo = $_POST['Contactinfo'] ?? null;
    $remarks     = $_POST['Remarks'] ?? null;

    if (empty($planID) || empty($invoiceID) || empty($contactinfo) || empty($remarks)) {
        echo "⚠️ Please fill in all required fields.";
        exit;
    }

    try {
        // 🔹 1. Get Invoice DueDate
        $sqlDue = "SELECT due_date FROM ar_invoices WHERE invoice_id = :invoiceID AND (Archive IS NULL OR UPPER(TRIM(Archive))='NO')";
        $stmtDue = $pdo->prepare($sqlDue);
        $stmtDue->execute([':invoiceID' => $invoiceID]);
        $invoice = $stmtDue->fetch(PDO::FETCH_ASSOC);

        if (!$invoice) {
            echo "❌ Invoice not found or archived.";
            exit;
        }
        $dueDate = new DateTime($invoice['due_date'], new DateTimeZone('Asia/Manila'));

        // 🔹 2. Get RemainingDays from collection_plan
        $sqlPlan = "SELECT remaining_days	 FROM collection_plan WHERE planID = :planID AND (Archive IS NULL OR UPPER(TRIM(Archive))='NO')";
        $stmtPlan = $pdo->prepare($sqlPlan);
        $stmtPlan->execute([':planID' => $planID]);
        $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);

        if (!$plan) {
            echo "❌ Plan not found or archived.";
            exit;
        }
        $remainingDays = (int)$plan['remaining_days'];

        // 🔹 3. Compute FollowUpDate = DueDate - RemainingDays
        $followUpDate = clone $dueDate;
        $followUpDate->modify("-{$remainingDays} days");
        $followUpDateStr = $followUpDate->format("Y-m-d");

        // ✅ Same plan check
        $checkSql = "SELECT COUNT(*) FROM follow 
                     WHERE InvoiceID = :invoiceID 
                       AND planID = :planID
                       AND (Archive IS NULL OR UPPER(TRIM(Archive)) = 'NO')";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([':invoiceID' => $invoiceID, ':planID' => $planID]);
        if ($checkStmt->fetchColumn() > 0) {
            echo "❌ This invoice already has a follow-up with the same plan.";
            exit;
        }

        // ✅ Max 2 follow-ups check
        $countSql = "SELECT COUNT(*) FROM follow 
                     WHERE InvoiceID = :invoiceID 
                       AND (Archive IS NULL OR UPPER(TRIM(Archive)) = 'NO')";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute([':invoiceID' => $invoiceID]);
        if ($countStmt->fetchColumn() >= 2) {
            echo "❌ This invoice already has 2 active follow-ups. No more allowed.";
            exit;
        }

        // ✅ Insert with calculated FollowUpDate
        $sql = "INSERT INTO follow 
                (planID, InvoiceID, FollowUpDate, Contactinfo, Remarks, paymentstatus, Archive) 
                VALUES 
                (:planID, :invoiceID, :followUpDate, :contactinfo, :remarks, 'NOT PAID', 'NO')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':planID'       => $planID,
            ':invoiceID'    => $invoiceID,
            ':followUpDate' => $followUpDateStr,
            ':contactinfo'  => $contactinfo,
            ':remarks'      => $remarks
        ]);

      
    } catch (PDOException $e) {
        echo "❌ Error inserting reminder: " . $e->getMessage();
    }
}




    if (isset($_POST['archive'])) {
        $reminderID = $_POST['reminderID'];
        $sql = "UPDATE  follow SET Archive = 'YES' WHERE reminderID = :reminderID";
        $stmt = $pdo->prepare($sql);
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
            $stmt = $pdo->prepare($sql);
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
    $stmt = $pdo->query($sql);
    $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>
