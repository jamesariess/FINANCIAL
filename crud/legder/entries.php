<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM entries WHERE journalID = :id";
$stmt = $pdo['general']->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
  if (isset($_POST['archive'])) {
        $journalID = $_POST['archive_journalID'];
        $sql = "UPDATE entries SET Archive = 'YES' WHERE journalID = :journalID";
        $stmt = $pdo['general']->prepare($sql);
        $stmt->bindParam(':journalID', $journalID);

        try {
            $stmt->execute();
            echo "✅ Journal entry archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
   if(isset($_POST['update'])) {
        $journalID = $_POST['journalID'];
        $date = $_POST['date'];
        $description = $_POST['description'];
        $referenceType = $_POST['referenceType'];
        $createdBy = $_POST['createdBy'];

        if (empty($journalID) || empty($date) || empty($description) || empty($referenceType) || empty($createdBy)) {
            echo "❌ All fields are required.";
            exit;
        }

        $sql = "UPDATE entries SET date = :date, description = :description, referenceType = :referenceType, createdBy = :createdBy WHERE journalID = :journalID";
        $stmt = $pdo['general']->prepare($sql);
        $stmt->bindParam(':journalID', $journalID);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':referenceType', $referenceType);
        $stmt->bindParam(':createdBy', $createdBy);

        try {
            $stmt->execute();
            echo "✅ Journal entry updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
}



try {
    $sql = "SELECT * FROM  entries WHERE Archive = 'NO'";
    $stmt = $pdo['general']->query($sql);
    $journals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>
