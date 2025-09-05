<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

// Check for a specific approver ID, though it may not be needed for a full list
$id = $_GET['id'] ?? null;
if ($id) {
    $sql = "SELECT * FROM approver WHERE approver_id = :id";
    $stmt = $pdo['budget']->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}

// Handle POST requests for creating, updating, or deleting an approver
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        try {
            switch ($action) {
                case 'add':
                    $approve_name = $_POST['approve_name'];
                    $title = $_POST['title'];
                    $sql = "INSERT INTO approver (approve_name, title) VALUES (:approve_name, :title)";
                    $stmt = $pdo['budget']->prepare($sql);
                    $stmt->bindParam(':approve_name', $approve_name);
                    $stmt->bindParam(':title', $title);
                    $stmt->execute();
                    echo "✅ Approver added successfully.";
                    break;

                case 'update':
                    $approver_id = $_POST['approver_id'];
                    $approve_name = $_POST['approve_name'];
                    $title = $_POST['title'];
                    $sql = "UPDATE approver SET approve_name = :approve_name, title = :title WHERE approver_id = :approver_id";
                    $stmt = $pdo['budget']->prepare($sql);
                    $stmt->bindParam(':approve_name', $approve_name);
                    $stmt->bindParam(':title', $title);
                    $stmt->bindParam(':approver_id', $approver_id);
                    $stmt->execute();
                    echo "✅ Approver updated successfully.";
                    break;

                case 'delete':
                    $approver_id = $_POST['approver_id'];
                    $sql = "DELETE FROM approver WHERE approver_id = :approver_id";
                    $stmt = $pdo['budget']->prepare($sql);
                    $stmt->bindParam(':approver_id', $approver_id);
                    $stmt->execute();
                    echo "✅ Approver deleted successfully.";
                    break;
            }
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
}

// Fetch all approvers for the table display
try {
    $sql = "SELECT * FROM approver ORDER BY approve_name ASC";
    $stmt = $pdo['budget']->query($sql);
    $approvers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching approvers: " . $e->getMessage();
}

?>