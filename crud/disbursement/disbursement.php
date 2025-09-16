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

        $sql = "UPDATE request SET Archive = :archive WHERE requestID = :disbursement_id";
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
        $disbursement_id = $_POST['update_request_id'];
        $amount = $_POST['update_title'];
 
        $status = $_POST['update_amount'];
        $description = $_POST['update_requested_by'];
        $approver_id = $_POST['update_due'];
  

        $sql = "UPDATE request SET requestTiTle = :amount,  Amount = :status, Requested_by	 = :description, Due = :approver_id WHERE requestID = :disbursement_id"  ;   
           $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':amount', $amount);
        
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':approver_id', $approver_id);
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
      $sql = "SELECT r.* ,
     c.Title,
    d.Name
    FROM request r
JOIN costallocation c on r.allocationID = c.allocationID
JOIN departmentbudget d on c.Deptbudget = d.Deptbudget
    
     WHERE r.Archive = 'NO'";
    $stmt = $pdo->query($sql);
    $disbursementReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>