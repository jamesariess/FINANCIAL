<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM project WHERE project_id = :id";
$stmt = $pdo['budget']->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if(isset($_POST['archive'])){
        $project_id = $_POST['archive_project_id'];
        $archive = 'YES';

        $sql = "UPDATE project SET Archive = :archive WHERE project_id = :project_id";
        $stmt = $pdo['budget']->prepare($sql);
        $stmt->bindParam(':archive', $archive);
        $stmt->bindParam(':project_id', $project_id);

        try {
            $stmt->execute();
            echo "✅ Project archived successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    if(isset($_POST['update'])){
        $project_id = $_POST['update_project_id'];
        $project_name = $_POST['update_project_name'];
        $budget_code = $_POST['update_budget_code'];
        $department = $_POST['update_department'];

        $sql = "UPDATE project SET project_name = :project_name, budget_code = :budget_code, department = :department WHERE project_id = :project_id";
        $stmt = $pdo['budget']->prepare($sql);
        $stmt->bindParam(':project_name', $project_name);
        $stmt->bindParam(':budget_code', $budget_code);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':project_id', $project_id);

        try {
            $stmt->execute();
            echo "✅ Project updated successfully.";
        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    
}

try {
    $sql = "SELECT * FROM  project WHERE Archive = 'NO'
             ORDER BY project_name Asc";
    $stmt = $pdo['budget']->query($sql);
    $projectReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}


?>