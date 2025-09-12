<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$data = [
    "totalRequest" => 0,
    "totalAmountRelease" => 0,
    "rejectedRequest" => 0,
    "newRequest" => 0,
    "request"=>0
];


$sql = "
    SELECT COUNT(*) as total
    FROM ar_invoices i
    WHERE (i.Archive IS NULL OR UPPER(TRIM(i.Archive))='NO')
      AND NOT EXISTS (
          SELECT 1 
          FROM follow f
          WHERE f.InvoiceID = i.invoice_id
            AND (f.Archive IS NULL OR UPPER(TRIM(f.Archive))='NO')
      )
";
$stmt = $pdo->query($sql);
$data["request"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


$sql = "SELECT COUNT(*) as total FROM follow WHERE Archive='NO'";
$stmt = $pdo->query($sql);
$data["totalRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


$sql = "SELECT COUNT(*) as total FROM follow WHERE paymentstatus='Paid' AND Archive='NO'";
$stmt = $pdo->query($sql);
$data["totalAmountRelease"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


$sql = "SELECT COUNT(*) as total FROM follow WHERE paymentstatus='Not Paid' AND Archive='NO'";
$stmt = $pdo->query($sql);
$data["rejectedRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


$sql = "SELECT COUNT(*) as total FROM follow WHERE Remarks='Failed To Sent' AND Archive='NO'";
$stmt = $pdo->query($sql);
$data["newRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM follow WHERE reminderID = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../../PHPMailer-master/src/Exception.php';

function sendEmailPHPMailer($to, $subject, $bodyHtml) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'slatetransportsystem@gmail.com';
        $mail->Password   = 'mfkkigrgxtoascov';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->setFrom('slatetransportsystem@gmail.com', 'Slate Finance Department');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Mailer Error: {$mail->ErrorInfo}";
    }
}



 $input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

 if (!empty($input['action']) && $input['action'] === 'sendReminder' && !empty($input['reminderID'])) {
        $reminderID = intval($input['reminderID']);
        $response = ['success' => true, 'message' => 'Reminder marked as sent'];

        try {
            // Update DB remarks to 'Reminder Sent'
            $stmt = $pdo->prepare("UPDATE follow SET Remarks='Reminder Sent' WHERE reminderID = ?");
            $stmt->execute([$reminderID]);

            // Fetch reminder details for email
            $sql = "SELECT f.reminderID, i.reference_no, i.due_date, c.email AS customer_email
                    FROM follow f
                    INNER JOIN ar_invoices i ON f.InvoiceID = i.invoice_id
                    INNER JOIN customers c ON i.customer_id  = c.customer_id 
                    WHERE f.reminderID = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $reminderID]);
            $reminder = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!empty($reminder['customer_email'])) {
                $subject = "Payment Reminder - Invoice #{$reminder['reference_no']}";
                $body = "<p>Dear Customer,</p><p>This is a reminder for your invoice <b>#{$reminder['reference_no']}</b>.</p><p>The due date is <b>" . date("F j, Y", strtotime($reminder['due_date'])) . "</b>.</p><p>Please ensure payment on time to avoid penalties.</p><p>Thank you,<br>Slate Finance Department</p>";
                $result = sendEmailPHPMailer($reminder['customer_email'], $subject, $body);

                if ($result === true) {
                    
                    $upd = $pdo->prepare("UPDATE follow SET Remarks='Emailed Sent' WHERE reminderID = :id");
                    $upd->execute([':id' => $reminderID]);
                    $response['message'] = "Email sent to {$reminder['customer_email']}";
                } else {
                    $response = ['success' => false, 'message' => "Email failed to send. " . $result];
                }
            } else {
                $response = ['success' => false, 'message' => 'Customer email not found.'];
            }
        } catch (PDOException $e) {
            $response = ['success' => false, 'message' => "Database error: " . $e->getMessage()];
        }
        
        // This is the critical part: set the header and echo the JSON, then stop execution immediately.
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }



if (isset($_POST['create'])) {
    $planID    = $_POST['planID'] ?? null;
    $invoiceID = $_POST['InvoiceID'] ?? null;
    $contactinfo = $_POST['Contactinfo'] ?? null;
  

    if (empty($planID) || empty($invoiceID) || empty($contactinfo)) {
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
                (:planID, :invoiceID, :followUpDate, :contactinfo, 'To Be Sent', 'NOT PAID', 'NO')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':planID'       => $planID,
            ':invoiceID'    => $invoiceID,
            ':followUpDate' => $followUpDateStr,
            ':contactinfo'  => $contactinfo
            
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

        $contactinfo = $_POST['Contactinfo'];
        $remarks = $_POST['Remarks'];
        $status = $_POST['status'];
        if ( empty($contactinfo) || empty($remarks) || empty($status)) {
            echo "Don't Leave Empty.";
            exit;
        } else {
            $sql = "UPDATE  follow SET Contactinfo = :contactinfo, Remarks = :remarks, paymentstatus = :status WHERE reminderID = :reminderID";
            $stmt = $pdo->prepare($sql);

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
    $sql = "
        SELECT  
            f.reminderID,
            f.FollowUpDate,
            f.Contactinfo,
            f.Remarks,
            f.paymentstatus,
            i.reference_no,
            p.plan
        FROM follow f
        INNER JOIN ar_invoices i ON f.InvoiceID = i.invoice_id
        INNER JOIN collection_plan p ON f.planID = p.planID
        WHERE f.Archive = 'NO'
        ORDER BY f.FollowUpDate ASC
    ";

    $stmt = $pdo->prepare($sql);  // ✅ safer than query()
    $stmt->execute();
    $reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . htmlspecialchars($e->getMessage());
}


$today = date("Y-m-d H:i:s");
$tomorrow = date("Y-m-d H:i:s", strtotime("+1 day"));

$now = date("Y-m-d H:i:s");

$sql = "SELECT i.reference_no,i.amount,i.due_date,f.paymentstatus,f.Contactinfo,f.Remarks,f.reminderID
        FROM follow f
        JOIN ar_invoices i ON f.InvoiceID = i.invoice_id 
        WHERE paymentstatus='Not Paid'
          AND i.due_date < :now
          AND f.Archive='NO'";

$stmt = $pdo->prepare($sql);
$stmt->execute(['now' => $now]);
$overdue = $stmt->fetchAll(PDO::FETCH_ASSOC);


$startOfToday = date("Y-m-d 00:00:00");
$endOfToday   = date("Y-m-d 23:59:59");

$sql = "SELECT i.reference_no,i.amount,f.FollowUpDate,f.paymentstatus,f.Contactinfo,f.Remarks,f.reminderID
        FROM follow f
        JOIN ar_invoices i ON f.InvoiceID = i.invoice_id 
        WHERE f.FollowUpDate BETWEEN :start AND :end
          AND f.Archive='NO'";

$stmt = $pdo->prepare($sql);
$stmt->execute(['start' => $startOfToday, 'end' => $endOfToday]);
$todayReminders = $stmt->fetchAll(PDO::FETCH_ASSOC);


$startOfTomorrow = date("Y-m-d 00:00:00", strtotime("+1 day"));
$endOfTomorrow   = date("Y-m-d 23:59:59", strtotime("+1 day"));

$sql = "SELECT i.reference_no,i.amount,f.FollowUpDate,f.paymentstatus,f.Contactinfo,f.Remarks,f.reminderID
        FROM follow f
        JOIN ar_invoices i ON f.InvoiceID = i.invoice_id 
        WHERE f.FollowUpDate BETWEEN :start AND :end
          AND f.Archive='NO'";

$stmt = $pdo->prepare($sql);
$stmt->execute(['start' => $startOfTomorrow, 'end' => $endOfTomorrow]);
$tomorrowReminders = $stmt->fetchAll(PDO::FETCH_ASSOC);




?>  