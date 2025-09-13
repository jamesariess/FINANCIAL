<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');


$departments = $pdo->query("
    SELECT Deptbudget, Name, Amount, COALESCE(UsedBudget, 0) AS UsedBudget, DateValid
    FROM departmentbudget
    WHERE status='Proceed'
")->fetchAll(PDO::FETCH_ASSOC);


$departments = array_filter($departments, function($d) {
    return ($d['Amount'] - $d['UsedBudget']) > 0;
});


$remaining = 0;
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if(empty($_POST['department'])) {
        $errors[] = "Please select a department.";
    } else {
        $deptbudget = $_POST['department'];
    }


    if(empty($_POST['year'])) {
        $errors[] = "Please select a year.";
    } else {
        $year = $_POST['year'];
    }

 
    if(empty($_POST['allocations']) || !is_array($_POST['allocations'])) {
        $errors[] = "Please add at least one allocation.";
    } else {
        $allocations = $_POST['allocations'];
    }

    if(empty($errors)) {
     
        $dept = $pdo->prepare("SELECT Amount, COALESCE(UsedBudget,0) AS UsedBudget FROM departmentbudget WHERE Deptbudget = :dept");
        $dept->execute([':dept' => $deptbudget]);
        $deptData = $dept->fetch(PDO::FETCH_ASSOC);

        if(!$deptData) {
            $errors[] = "Selected department not found.";
        } else {
            $remainingBudget = $deptData['Amount'] - $deptData['UsedBudget'];

            $totalUsed = 0;
            foreach ($allocations as $row) {
                $amount = str_replace(',', '', $row['amount']);
                if(!is_numeric($amount)) $amount = 0;
                $totalUsed += $amount;
            }

            if($totalUsed > $remainingBudget) {
                $errors[] = "Allocation exceeds remaining budget!";
            }
        }
    }


    if(empty($errors)) {
        foreach ($allocations as $row) {
            $amount = str_replace(',', '', $row['amount']);
            if(!is_numeric($amount)) $amount = 0;

            $stmt = $pdo->prepare("
                INSERT INTO costallocation (Deptbudget, Title, Amount, percentage, yearlybudget, AllocationCreate)
                VALUES (:dept, :title, :amount, :percentage, :year, NOW())
            ");
            $stmt->execute([
                ':dept' => $deptbudget,
                ':title' => $row['title'],
                ':amount' => $amount,
                ':percentage' => $row['percentage'],
                ':year' => $year
            ]);
        }

  
        $update = $pdo->prepare("
            UPDATE departmentbudget
            SET UsedBudget = COALESCE(UsedBudget, 0) + :used
            WHERE Deptbudget = :dept
        ");
        $update->execute([
            ':used' => $totalUsed,
            ':dept' => $deptbudget
        ]);

        echo "<div class='bg-green-200 text-green-800 p-3 rounded mb-4'>✅ Allocation saved successfully!</div>";
    } else {
    
        foreach($errors as $err) {
            echo "<div class='bg-red-200 text-red-800 p-3 rounded mb-2'>⚠ {$err}</div>";
        }
    }
}



$years = $pdo->query("SELECT DISTINCT yearlybudget FROM costallocation ORDER BY yearlybudget DESC")->fetchAll(PDO::FETCH_COLUMN);

$selectedYear = isset($_GET['year']) ? $_GET['year'] : ($years[0] ?? null);

// Fetch allocations grouped by department for the selected year
$allocationsByDept = [];
if($selectedYear){
    $stmt = $pdo->prepare("
        SELECT d.Name AS deptName, c.Title, c.Percentage, c.Amount
        FROM costallocation c
        INNER JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
        WHERE c.yearlybudget = :year
        ORDER BY d.Name, c.AllocationID
    ");
    $stmt->execute([':year' => $selectedYear]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($results as $row){
        $allocationsByDept[$row['deptName']][] = $row;
    }
}
?>
