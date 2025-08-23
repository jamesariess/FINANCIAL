<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM details WHERE accountID = :id";
$stmt = $pdo['general']->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    if (isset($_POST['archive'])) {
        $accountID = $_POST['archive_entriesID'];

        $sql = "UPDATE details SET Archive = 'YES' WHERE entriesID = :accountID";
        $stmt = $pdo['general']->prepare($sql);
        $stmt->bindParam(':accountID', $accountID);

        try {
            $stmt->execute();
            echo "✅ Account archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }

    if (isset($_POST['update'])) {
        $entriesID = $_POST['update_entriesID']; // ✅ FIXED variable name
        $journalID = $_POST['update_journalID'];
        $accountID = $_POST['update_accountID'];
        $debit     = $_POST['update_debit'];
        $credit    = $_POST['update_credit'];

        $sql = "UPDATE details 
                   SET journalID = :journalID,
                       accountID = :accountID, 
                       debit     = :debit, 
                       credit    = :credit  
                 WHERE entriesID = :entriesID";

        $stmt = $pdo['general']->prepare($sql);
        $stmt->bindParam(':journalID', $journalID);
        $stmt->bindParam(':accountID', $accountID);
        $stmt->bindParam(':debit', $debit);
        $stmt->bindParam(':credit', $credit);
        $stmt->bindParam(':entriesID', $entriesID);

        try {
            $stmt->execute();
            echo "✅ Account updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
}



try {
    $sql = "SELECT * FROM  details WHERE Archive = 'NO'";
    $stmt = $pdo['general']->query($sql);
    $journalDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>
