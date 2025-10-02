<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$successMessage = "";
$errorMessage   = "";

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM entries WHERE journalID = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    if (isset($_POST['archive'])) {
        $journalID = $_POST['archive_journalID'];
        $sql = "UPDATE entries SET Archive = 'YES' WHERE journalID = :journalID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':journalID', $journalID);

        try {
            $stmt->execute();
            $successMessage = "Journal entry archived successfully.";
        } catch (PDOException $e) {
            $errorMessage = "Error archiving journal entry: " . $e->getMessage();
        }
    }

   
    if (isset($_POST['update'])) {
        $journalID = $_POST['journalID'];
        $date = $_POST['date'];
        $description = $_POST['description'];
        $referenceType = $_POST['referenceType'];
        $createdBy = $_POST['createdBy'];

        if (empty($journalID) || empty($date) || empty($description) || empty($referenceType) || empty($createdBy)) {
            $errorMessage = "All fields are required.";
        } else {
            $sql = "UPDATE entries 
                    SET date = :date, description = :description, referenceType = :referenceType, createdBy = :createdBy 
                    WHERE journalID = :journalID";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':journalID', $journalID);
            $stmt->bindParam(':date', $date);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':referenceType', $referenceType);
            $stmt->bindParam(':createdBy', $createdBy);

            try {
                $stmt->execute();
                $successMessage = "Journal entry updated successfully.";
            } catch (PDOException $e) {
                $errorMessage = "Error updating journal entry: " . $e->getMessage();
            }
        }
    }
}


try {
    $sql = "SELECT * FROM entries WHERE Archive = 'NO'";
    $stmt = $pdo->query($sql);
    $journals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "Error fetching journal entries: " . $e->getMessage();
}
?>
