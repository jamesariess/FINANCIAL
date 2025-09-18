<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

if (isset($_GET['deptname']) && isset($_GET['year'])) {
    $deptname = trim($_GET['deptname']);
    $year = $_GET['year'];
    error_log("Fetching budget for Deptname: $deptname, Year: $year at " . date('Y-m-d H:i:s'));

    // Fetch the department's initial budget for the given year
    $stmt = $pdo->prepare("
        SELECT Amount, COALESCE(UsedBudget, 0) AS UsedBudget
        FROM departmentbudget
        WHERE TRIM(Name) = :deptname AND DateValid = :year AND status = 'Proceed'
    ");
    $stmt->execute([':deptname' => $deptname, ':year' => $year]);
    $deptData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($deptData) {
        $initialBudget = $deptData['Amount'];
        $usedBudgetFromDept = $deptData['UsedBudget'];

        // Fetch total used budget from costallocation for this year
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(Amount), 0) AS totalUsed
            FROM costallocation
            WHERE Deptbudget = (SELECT Deptbudget FROM departmentbudget WHERE TRIM(Name) = :deptname AND DateValid = :year)
            AND yearlybudget = :year
        ");
        $stmt->execute([':deptname' => $deptname, ':year' => $year]);
        $totalUsed = $stmt->fetchColumn();

        $remainingBudget = $initialBudget - $totalUsed;
        $response = [
            'remainingBudget' => $remainingBudget,
            'yearlyBudget' => $initialBudget,
            'usedBudget' => $totalUsed
        ];
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    } else {
        error_log("No budget data found for Deptname: $deptname, Year: $year");
        header('Content-Type: application/json');
        echo json_encode(['remainingBudget' => 0, 'yearlyBudget' => 0, 'usedBudget' => 0]);
        exit;
    }
}

if (isset($_GET['deptname'])) {
    $deptname = trim($_GET['deptname']);
    error_log("Fetching years for Deptname: $deptname at " . date('Y-m-d H:i:s'));
    $stmt = $pdo->prepare("
        SELECT DISTINCT DateValid AS yearlybudget 
        FROM departmentbudget 
        WHERE TRIM(Name) = :deptname AND status = 'Proceed'
        UNION 
        SELECT DISTINCT yearlybudget 
        FROM costallocation c
        JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
        WHERE TRIM(d.Name) = :deptname
        ORDER BY yearlybudget DESC
    ");
    $stmt->execute([':deptname' => $deptname]);
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("Years found: " . json_encode($years));
    if (empty($years)) {
        error_log("No years found for Deptname: $deptname");
    }
    header('Content-Type: application/json');
    echo json_encode($years);
    exit;
}

// Fetch departments
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
    if (empty($_POST['department'])) {
        $errors[] = "Please select a department.";
    } else {
        $deptbudget = $_POST['department'];
    }

    if (empty($_POST['year'])) {
        $errors[] = "Please select a year.";
    } else {
        $year = $_POST['year'];
    }

    if (empty($_POST['allocations']) || !is_array($_POST['allocations'])) {
        $errors[] = "Please add at least one allocation.";
    } else {
        $allocations = $_POST['allocations'];
    }

    if (empty($errors)) {
        // Fetch department data for the specific year
        $stmt = $pdo->prepare("
            SELECT Amount, COALESCE(UsedBudget, 0) AS UsedBudget
            FROM departmentbudget
            WHERE Deptbudget = :dept AND DateValid = :year AND status = 'Proceed'
        ");
        $stmt->execute([':dept' => $deptbudget, ':year' => $year]);
        $deptData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deptData) {
            $errors[] = "No budget data found for the selected department and year.";
        } else {
            $initialBudget = $deptData['Amount'];
            $usedBudgetFromDept = $deptData['UsedBudget'];

            $totalUsed = 0;
            foreach ($allocations as $row) {
                $amount = str_replace(',', '', $row['amount']);
                if (!is_numeric($amount)) $amount = 0;
                $totalUsed += $amount;
            }

            $newUsedBudget = $usedBudgetFromDept + $totalUsed;
            if ($newUsedBudget > $initialBudget) {
                $errors[] = "Allocation exceeds the budget for the selected year!";
            }
        }
    }

    if (empty($errors)) {
        foreach ($allocations as $row) {
            $amount = str_replace(',', '', $row['amount']);
            if (!is_numeric($amount)) $amount = 0;

            $stmt = $pdo->prepare("
                INSERT INTO costallocation (Deptbudget, accountID, Amount, percentage, yearlybudget, AllocationCreate)
                VALUES (:dept, :accountID, :amount, :percentage, :year, NOW())
            ");
            $stmt->execute([
                ':dept' => $deptbudget,
                ':accountID' => $row['accountID'],
                ':amount' => $amount,
                ':percentage' => $row['percentage'],
                ':year' => $year
            ]);
        }

        // Update UsedBudget for the specific year
        $update = $pdo->prepare("
            UPDATE departmentbudget
            SET UsedBudget = COALESCE(UsedBudget, 0) + :used
            WHERE Deptbudget = :dept AND DateValid = :year
        ");
        $update->execute([
            ':used' => $totalUsed,
            ':dept' => $deptbudget,
            ':year' => $year
        ]);

        echo "<div class='bg-green-200 text-green-800 p-3 rounded mb-4'>✅ Allocation saved successfully!</div>";
    } else {
        foreach ($errors as $err) {
            echo "<div class='bg-red-200 text-red-800 p-3 rounded mb-2'>⚠ {$err}</div>";
        }
    }
}

$years = $pdo->query("SELECT DISTINCT yearlybudget FROM costallocation ORDER BY yearlybudget DESC")->fetchAll(PDO::FETCH_COLUMN);
$selectedYear = isset($_GET['year']) ? $_GET['year'] : ($years[0] ?? null);

// Fetch allocations grouped by department for the selected year
$allocationsByDept = [];
if ($selectedYear) {
    $stmt = $pdo->prepare("
        SELECT d.Name AS deptName, ch.accountName AS Title, c.Percentage, c.Amount
        FROM costallocation c
        INNER JOIN departmentbudget d ON c.Deptbudget = d.Deptbudget
        JOIN chartofaccount ch ON c.accountID = ch.accountID 
        WHERE c.yearlybudget = :year
        ORDER BY d.Name, c.AllocationID
    ");
    $stmt->execute([':year' => $selectedYear]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($results as $row) {
        $allocationsByDept[$row['deptName']][] = $row;
    }
}
$stmt = $pdo->query("SELECT accountID, accountName, accounType FROM chartofaccount ");
$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>