<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM chartofaccount WHERE accountID = :id";
$stmt = $pdo['general']->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    if (isset($_POST['archive'])) {
        $accountID = $_POST['accountID'];
        $sql = "UPDATE chartofaccount SET Archive = 'YES' WHERE accountID = :accountID";
        $stmt =$pdo['general']->prepare($sql);
        $stmt->bindParam(':accountID', $accountID);

        try {
            $stmt->execute();
            echo "✅ Payment archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    if(isset($_POST['update'])){
        $accountID = $_POST['accountID'];
        $accountCode = $_POST['accountCode'];
        $accountName = $_POST['accountName'];
        $accountType = $_POST['accountType'];
        $accountstatus = $_POST['status'];

        if (empty($accountCode) || empty($accountName) || empty($accountType)) {
            echo "Please fill in all required fields.";
            exit;
        }

        $sql = "UPDATE chartofaccount SET accountCode = :accountCode, accountName = :accountName, accounType = :accountType, status = :status WHERE accountID = :accountID";
        $stmt = $pdo['general']->prepare($sql);
        $stmt->bindParam(':accountCode', $accountCode);
        $stmt->bindParam(':accountName', $accountName);
        $stmt->bindParam(':accountType', $accountType);
        $stmt->bindParam(':status', $accountstatus);
        $stmt->bindParam(':accountID', $accountID);
    

        try {
            $stmt->execute();
            echo "✅ Account updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    if (isset($_POST['submit'])) {
        $accountCode = $_POST['accountCode'];
        $accountName = $_POST['accountName'];
        $accountType = $_POST['accountType'];

        if (empty($accountCode) || empty($accountName) || empty($accountType)) {
            echo "Please fill in all required fields.";
            exit;
        }

        $sql = "INSERT INTO chartofaccount (accountCode, accountName, accounType, Archive) VALUES (:accountCode, :accountName, :accountType, 'NO')";
        $stmt = $pdo['general']->prepare($sql);
        $stmt->bindParam(':accountCode', $accountCode);
        $stmt->bindParam(':accountName', $accountName);
        $stmt->bindParam(':accountType', $accountType);

        try {
            $stmt->execute();
            echo "✅ Account added successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
   
}



try {
    $sql = "SELECT * FROM  chartofaccount WHERE Archive = 'NO'";
    $stmt = $pdo['general']->query($sql);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>
