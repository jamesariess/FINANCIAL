<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$successMessage = '';
$errorMessage = '';

$id = $_GET['id'] ?? null;
if ($id) {
    $sql = "SELECT * FROM receipt WHERE receiptID = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    if (isset($_POST['archive'])) {
        $receiptID = $_POST['receiptID'];
        $sql = "UPDATE receipt SET Archive = 'YES' WHERE receiptID = :receiptID";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':receiptID', $receiptID);

        try {
            $stmt->execute();
            $successMessage = "✅ Receipt archived successfully.";
        } catch (PDOException $e) {
            $errorMessage = "❌ Error: " . $e->getMessage();
        }
    }


    if (isset($_POST['update'])) {
        $receiptID = $_POST['receiptID'];
        $issueBy = $_POST['issueBy'];

        if (empty($issueBy)) {
            $errorMessage = "Don't leave issueBy empty.";
        } else {
            $sql = "UPDATE receipt SET issueBy = :issueBy WHERE receiptID = :receiptID";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':issueBy', $issueBy);
            $stmt->bindParam(':receiptID', $receiptID);

            try {
                $stmt->execute();
                $successMessage = "✅ issueBy updated successfully.";
            } catch (PDOException $e) {
                $errorMessage = "❌ Error: " . $e->getMessage();
            }
        }
    }
}


try {
    $sql = "SELECT * FROM receipt WHERE Archive = 'NO' ORDER BY receiptsdate ASC";
    $stmt = $pdo->query($sql);
    $receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errorMessage = "❌ Error fetching receipts: " . $e->getMessage();
}
?>
