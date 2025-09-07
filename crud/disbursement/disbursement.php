<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM disbursement WHERE disbursement_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if(isset($_POST['archive'])){
        $disbursement_id = $_POST['archive_disbursement_id'];
        $archive = 'YES';

        $sql = "UPDATE disbursement SET Archive = :archive WHERE disbursement_id = :disbursement_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':archive', $archive);
        $stmt->bindParam(':disbursement_id', $disbursement_id);

        try {
            $stmt->execute();
            echo "✅ Disbursement archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    if(isset($_POST['update'])){
        $disbursement_id = $_POST['update_disbursement_id'];
        $amount = $_POST['update_amount'];
        $disbursement_date = $_POST['update_disbursement_date'];
        $status = $_POST['update_status'];
        $description = $_POST['update_description'];
        $approver_id = $_POST['update_approver_id'];
        $payment_method_id = $_POST['update_payment_method_id'];
        $project_id = $_POST['update_project_id'];

        $sql = "UPDATE disbursement SET amount = :amount, disbursement_date = :disbursement_date, status = :status, description = :description, approver_id = :approver_id, payment_method_id = :payment_method_id, project_id = :project_id WHERE disbursement_id = :disbursement_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':disbursement_date', $disbursement_date);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':approver_id', $approver_id);
        $stmt->bindParam(':payment_method_id', $payment_method_id);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':disbursement_id', $disbursement_id);

        try {
            $stmt->execute();
            echo "✅ Disbursement updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    
}

try {
    $sql = "SELECT * FROM  disbursement WHERE Archive = 'NO'
             ORDER BY disbursement_date Asc";
    $stmt = $pdo->query($sql);
    $disbursementReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>