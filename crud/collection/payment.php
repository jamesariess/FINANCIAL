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
    }
    if(isset($_POST['update'])){
        $paymentID = $_POST['paymentID'];
        $remarks = $_POST['remarks'];
   

        if (empty($remarks)) {
            echo "Don't Leave Empty.";
            exit;
        } else {
            $sql = "UPDATE payment SET remarks = :remarks WHERE paymentID = :paymentID";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->bindParam(':paymentID', $paymentID);
     

            try {
                $stmt->execute();
                echo "✅ Remarks updated successfully.";
            } catch (PDOException $e) {
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
