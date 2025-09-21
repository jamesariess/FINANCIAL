<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM payment WHERE paymentID = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    if (isset($_POST['archive'])) {
        $paymentID = $_POST['paymentID'];
        $sql = "UPDATE payment SET Archive = 'YES' WHERE paymentID = :paymentID";
        $stmt =$pdo->prepare($sql);
        $stmt->bindParam(':paymentID', $paymentID);

        try {
            $stmt->execute();
            echo "✅ Payment archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }if (isset($_POST['update'])) {
    $paymentID = $_POST['paymentID'];
    $remarks = $_POST['remarks'];

    if (empty($remarks)) {
        echo "Don't Leave Empty.";
        exit;
    } else {
        try {
      
            $pdo->beginTransaction();

       
            $sql = "UPDATE payment SET remarks = :remarks WHERE paymentID = :paymentID";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->bindParam(':paymentID', $paymentID);
            $stmt->execute();


            $sqlPaymentDetails = "SELECT amount FROM payment WHERE paymentID = :paymentID";
            $stmtPaymentDetails = $pdo->prepare($sqlPaymentDetails);
            $stmtPaymentDetails->bindParam(':paymentID', $paymentID);
            $stmtPaymentDetails->execute();
            $paymentDetails = $stmtPaymentDetails->fetch(PDO::FETCH_ASSOC);
            $amount = $paymentDetails['amount'];

   
            $sqlEntries = "
                INSERT INTO entries (date, description, referenceType, createdBy, Archive)
                VALUES (CURDATE(), :description, :ref, :createdBy, 'NO')
            ";
            $stmtEntries = $pdo->prepare($sqlEntries);
            $stmtEntries->bindValue(':description', 'Customer Payment Received');
            $stmtEntries->bindValue(':ref', 'Payment-' . $paymentID);
            $stmtEntries->bindValue(':createdBy', 'User'); 
            $stmtEntries->execute();

            $journalID = $pdo->lastInsertId();


            $sqlDebit = "
                INSERT INTO details (journalID, accountID, debit, credit, Archive)
                VALUES (:journalID, :accountID, :debit, :credit, 'NO')
            ";
            $stmtDebit = $pdo->prepare($sqlDebit);
            $stmtDebit->bindParam(':journalID', $journalID);
            $stmtDebit->bindValue(':accountID', 1); 
            $stmtDebit->bindParam(':debit', $amount);
            $stmtDebit->bindValue(':credit', 0);
            $stmtDebit->execute();


            $sqlCredit = "
                INSERT INTO details (journalID, accountID, debit, credit, Archive)
                VALUES (:journalID, :accountID, :debit, :credit, 'NO')
            ";
            $stmtCredit = $pdo->prepare($sqlCredit);
            $stmtCredit->bindParam(':journalID', $journalID);
            $stmtCredit->bindValue(':accountID', 3); 
            $stmtCredit->bindValue(':debit', 0);
            $stmtCredit->bindParam(':credit', $amount);
            $stmtCredit->execute();

           
            $pdo->commit();

            echo "✅ Remarks updated successfully and journal entries created.";
        } catch (PDOException $e) {
       
            $pdo->rollBack();
            echo "❌ Error: " . $e->getMessage();
        }
    }
}
    
   
}



try {
    $sql = "SELECT * FROM  payment WHERE Archive = 'NO'
            ORDER BY paymentDate Asc";
    $stmt = $pdo->query($sql);
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>
