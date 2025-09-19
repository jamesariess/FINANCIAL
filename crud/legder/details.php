<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM details WHERE accountID = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

try {
    $sql = " SELECT 
        jd.entriesID,
        jd.journalID,
        j.Description AS journalDescription,
        c.accountName,
        jd.debit,
        jd.credit
    FROM details jd
    JOIN entries j ON jd.journalID = j.journalID
    JOIN chartofaccount c ON jd.accountID=c.accountID  WHERE jd.Archive = 'NO'";
    $stmt = $pdo->query($sql);
    $journalDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>
