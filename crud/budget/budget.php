<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');


$stmtTotalBudgets = $pdo->prepare("SELECT IFNULL(SUM(Amount), 0) AS total FROM departmentbudget WHERE YEAR(DateValid) = YEAR(CURDATE())");
$stmtTotalBudgets->execute();
$totalBudgets = $stmtTotalBudgets->fetch(PDO::FETCH_ASSOC)['total'];


$stmtApproved = $pdo->prepare("SELECT IFNULL(SUM(Amount), 0) AS approved FROM departmentbudget WHERE status = 'Proceed' AND YEAR(DateValid) = YEAR(CURDATE())");
$stmtApproved->execute();
$approvedBudgets = $stmtApproved->fetch(PDO::FETCH_ASSOC)['approved'];


$stmtCancelled = $pdo->prepare("SELECT IFNULL(SUM(Amount), 0) AS cancelled FROM departmentbudget WHERE status = 'Cancel' AND YEAR(DateValid) = YEAR(CURDATE())");
$stmtCancelled->execute();
$cancelledBudgets = $stmtCancelled->fetch(PDO::FETCH_ASSOC)['cancelled'];


$stmtTotalDepartments = $pdo->prepare("SELECT COUNT(DISTINCT Name) AS totalDepartments FROM departmentbudget WHERE YEAR(DateValid) = YEAR(CURDATE())");
$stmtTotalDepartments->execute();
$totalDepartments = $stmtTotalDepartments->fetch(PDO::FETCH_ASSOC)['totalDepartments'];


$data = [
    'totalBudgets' => '₱' . number_format($totalBudgets, 2, '.', ','),
    'approvedBudgets' => '₱' . number_format($approvedBudgets, 2, '.', ','),
    'cancelledBudgets' => '₱' . number_format($cancelledBudgets, 2, '.', ','),
    'totalDepartments' => $totalDepartments
];





$reload = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deptName     = $_POST['deptName'] ?? '';
    $budgetAmount = $_POST['budgetAmount'] ?? 0;
    $budgetDetails= $_POST['budgetDetails'] ?? '';
    $year         = $_POST['budgetYear'] ?? date("Y");

    if ($deptName && $budgetAmount && $budgetDetails) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM departmentbudget WHERE Name = :name AND DateValid = :year AND status='Proceed'");
        $check->execute([':name' => $deptName, ':year' => $year]);
        $exists = $check->fetchColumn();

        if ($exists == 0) {
            $stmt = $pdo->prepare("
                INSERT INTO departmentbudget (Name, Amount, DateValid, Details, status) 
                VALUES (:name, :amount, :year, :details, 'Proceed')
            ");
            $stmt->execute([
                ':name'    => $deptName,
                ':amount'  => $budgetAmount,
                ':year'    => $year,
                ':details' => $budgetDetails
            ]);
            $reload = true;
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancelId = (int) $_POST['cancel_id'];
    $stmt = $pdo->prepare("UPDATE departmentbudget SET status='Cancel' WHERE Deptbudget  = :id");
    $stmt->execute([':id' => $cancelId]);
    $reload = true;
}

$deptDetailsStmt = $pdo->query("SELECT DISTINCT Name, Details FROM departmentbudget WHERE status='Proceed' ORDER BY Name ASC");
$deptDetails = $deptDetailsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$stmt = $pdo->query("SELECT Name, Amount, Details,Deptbudget,DateValid FROM departmentbudget WHERE status='Proceed' ORDER BY Name ASC");
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
