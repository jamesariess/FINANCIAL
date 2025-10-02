<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');


$stmtTotalBudgets = $pdo->prepare("SELECT IFNULL(SUM(Amount), 0) AS total FROM departmentbudget WHERE YEAR(DateValid) = YEAR(CURDATE()) AND status = 'Proceed'");
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

$stmtCashOnHand = $pdo->query("
    SELECT IFNULL(SUM(jd.debit) - SUM(jd.credit), 0) AS cashOnHand
    FROM details jd
    JOIN chartofaccount c ON jd.accountID = c.accountID
    WHERE c.accountName = 'Cash on Hand'
");
$cashOnHand = $stmtCashOnHand->fetch(PDO::FETCH_ASSOC)['cashOnHand'];


$stmtBankBalance = $pdo->query("
    SELECT IFNULL(SUM(f.Amount - f.UsedAmount), 0) AS bankBalance
    FROM funds f
    WHERE f.Archive = 'NO'
");
$bankBalance = $stmtBankBalance->fetch(PDO::FETCH_ASSOC)['bankBalance'];

$totalGLCash = $cashOnHand + $bankBalance;


$data = [
    'totalBudgets' => '₱' . number_format($totalBudgets, 2, '.', ','),
    'approvedBudgets' => '₱' . number_format($approvedBudgets, 2, '.', ','),
    'cancelledBudgets' => '₱' . number_format($cancelledBudgets, 2, '.', ','),
    'totalDepartments' => $totalDepartments,
    'cash' => '₱' . number_format( $totalGLCash, 2, '.', ',')
];





$reload = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deptName     = $_POST['deptName'] ?? '';
    $budgetAmount = $_POST['budgetAmount'] ?? 0;
    $budgetDetails= $_POST['budgetDetails'] ?? '';
    $year         = $_POST['budgetYear'] ?? date("Y");

    if ($deptName && $budgetAmount && $budgetDetails) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM departmentbudget WHERE Name = :name AND YEAR(DateValid) = :year AND status='Proceed'");
        $check->execute([':name' => $deptName, ':year' => (int)$year]);
        $exists = $check->fetchColumn();

        if ($exists == 0) {
            $stmtExistingSum = $pdo->prepare("SELECT IFNULL(SUM(Amount), 0) AS existing FROM departmentbudget WHERE status = 'Proceed' AND YEAR(DateValid) = :year");
            $stmtExistingSum->execute([':year' => (int)$year]);
            $existing = $stmtExistingSum->fetch(PDO::FETCH_ASSOC)['existing'];

            if ($existing + $budgetAmount > $totalGLCash) {
                $error = "The proposed budget would exceed the available cash of ₱" . number_format($totalGLCash, 2) . ". Maximum additional budget allowed: ₱" . number_format($totalGLCash - $existing, 2) . ".";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO departmentbudget (Name, Amount, DateValid, Details, status) 
                    VALUES (:name, :amount, :year, :details, 'Proceed')
                ");
                $stmt->execute([
                    ':name'    => $deptName,
                    ':amount'  => $budgetAmount,
                    ':year'    => $year . '-01-01', 
                    ':details' => $budgetDetails
                ]);
                $reload = true;
            }
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


$yearSums = [];
$stmtYearSums = $pdo->prepare("SELECT YEAR(DateValid) AS yr, IFNULL(SUM(Amount), 0) AS sum_amt FROM departmentbudget WHERE status = 'Proceed' GROUP BY yr");
$stmtYearSums->execute();
foreach ($stmtYearSums->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $yearSums[(int)$row['yr']] = (float)$row['sum_amt'];
}
?>