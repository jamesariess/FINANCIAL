<?php
include_once __DIR__ . '/../../utility/connection.php';

try {
    // Fetch unique years from departmentbudget for cards
    $stmt = $pdo->query("SELECT DISTINCT DateValid as year FROM departmentbudget WHERE status = 'Proceed' ORDER BY year DESC");
    $years = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $years[] = (int)$row['year'];
    }

    // Fetch unique departments from departmentbudget for cards
    $stmt = $pdo->query("SELECT DISTINCT Name FROM departmentbudget WHERE status = 'Proceed' ORDER BY Name ASC");
    $departments = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $departments[] = $row['Name'];
    }

    // Fetch budget data from departmentbudget for cards
    $stmt = $pdo->query("SELECT Name, Amount, UsedBudget, DateValid as year FROM departmentbudget WHERE status = 'Proceed'");
    $deptBudgets = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $deptBudgets[] = [
            'name' => $row['Name'],
            'amount' => (int)$row['Amount'],
            'usedBudget' => (int)$row['UsedBudget'],
            'year' => (int)$row['year']
        ];
    }

    // Fetch unique years from costallocation for graph
    $stmt = $pdo->query("SELECT DISTINCT yearlybudget as year FROM costallocation WHERE Status = 'Activate' ORDER BY year ASC");
    $graphYears = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $graphYears[] = (int)$row['year'];
    }

    // Fetch graph data from costallocation
    $stmt = $pdo->query("
        SELECT 
            allocationID,
            Title,
            Amount,
            percentage,
            yearlybudget,
            AllocationCreate,
            Status,
            usedAllocation,
            (SELECT Name FROM departmentbudget WHERE Deptbudget = c.Deptbudget LIMIT 1) as dept
        FROM costallocation c 
        WHERE Status = 'Activate'
    ");
    $graphBudgets = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $graphBudgets[] = [
            'id' => 'T' . str_pad($row['allocationID'], 3, '0', STR_PAD_LEFT),
            'title' => $row['Title'],
            'amount' => (int)$row['Amount'],
            'usedAllocation' => (int)$row['usedAllocation'],
            'year' => (int)$row['yearlybudget'],
            'dept' => $row['dept'],
            'percentage'=>$row['percentage'],
            'AllocationCreate'=>$row['AllocationCreate']
        ];
    }

    $data = [
        'years' => $years,
        'departments' => $departments,
        'deptBudgets' => $deptBudgets,
        'graphYears' => $graphYears,
        'graphBudgets' => $graphBudgets
    ];
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>