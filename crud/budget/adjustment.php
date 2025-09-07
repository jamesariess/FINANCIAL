<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM adjustment WHERE adjustID = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
 if(isset($_POST['archive'])){
        $adjustID = $_POST['archive_adjustID'];
        $archive = 'YES';

        $sql = "UPDATE adjustment SET Archive = :archive WHERE adjustID = :adjustID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':archive', $archive);
        $stmt->bindParam(':adjustID', $adjustID);

        try {
            $stmt->execute();
            echo "✅ Adjustment archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    if(isset($_POST['update'])){
        $adjustID = $_POST['update_adjustID'];
        $paymentDate = $_POST['update_budgetID'];
        $particulars = $_POST['update_adjustedBy'];
        $amount = $_POST['update_adjustmentDate'];
        $remarks = $_POST['update_reason'];
        $newAmount = $_POST['update_newAmount'];
        $status = $_POST['update_status'];

        $sql = "UPDATE adjustment SET budgetID = :paymentDate, adjustedBy = :particulars, adjustmentDate = :amount, reason = :remarks,newAmount=:newAmount , status=:status WHERE adjustID = :adjustID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':paymentDate', $paymentDate);
        $stmt->bindParam(':particulars', $particulars);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':remarks', $remarks);
        $stmt->bindParam(':adjustID', $adjustID);
        $stmt->bindParam(':newAmount', $newAmount);
        $stmt->bindParam(':status', $status);

        try {
            $stmt->execute();
            echo "✅ Adjustment updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
   
}

try {
    $sql = "SELECT * FROM  adjustment WHERE Archive = 'NO'
            ORDER BY adjustmentDate Asc";
    $stmt = $pdo->query($sql);
    $adjustmentReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>
