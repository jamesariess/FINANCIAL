<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- Fetch unique departments by name ---
$departments = $pdo->query("
    SELECT MIN(Deptbudget) AS Deptbudget, Name, Amount
    FROM departmentbudget
    WHERE status='Proceed'
    GROUP BY Name
    ORDER BY Name
")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['dept']) && !isset($_GET['year'])) {
    $dept = $_GET['dept'];
    $stmt = $pdo->prepare("
        SELECT DISTINCT yearlybudget 
        FROM costallocation 
        WHERE Deptbudget = :dept 
        ORDER BY yearlybudget DESC
    ");
    $stmt->execute([':dept' => $dept]);
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
    header('Content-Type: application/json');
    echo json_encode($years);
    exit();
}

// --- AJAX: Fetch allocations by department + year ---
if (isset($_GET['dept']) && isset($_GET['year'])) {
    $dept = $_GET['dept'];
    $year = $_GET['year'];

    $stmt = $pdo->prepare("
        SELECT ca.allocationID, ca.accountID, ca.Percentage, ca.Amount, ch.accountName AS Title 
        FROM costallocation ca 
        JOIN chartofaccount ch ON ca.accountID = ch.accountID 
        WHERE ca.Deptbudget = :dept AND ca.yearlybudget = :year
    ");
    $stmt->execute([':dept' => $dept, ':year' => $year]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" 
    && isset($_POST['department'], $_POST['title_increase'], $_POST['year'], $_POST['new_percent_increase'], $_POST['reason'])) {

    $dept = $_POST['department'];
    $targetID = $_POST['title_increase'];
    $decreaseID = $_POST['title_decrease'] ?? '';
    $year = $_POST['year'];
    $newTargetPercent = floatval($_POST['new_percent_increase']);
    $reason = trim($_POST['reason']);

    // Validation
    if ($newTargetPercent < 0 || $newTargetPercent > 100) {
        echo "<div class='bg-red-200 text-red-800 p-3 rounded mb-4'>❌ Error: New percentage must be between 0 and 100.</div>";
        exit();
    }

    // Additional safety check
    if (!empty($decreaseID) && $decreaseID == $targetID) {
        echo "<div class='bg-red-200 text-red-800 p-3 rounded mb-4'>❌ Error: Cannot use the same allocation for both.</div>";
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Fetch target allocation
        $stmtTarget = $pdo->prepare("SELECT allocationID, percentage, Amount FROM costallocation WHERE allocationID = :target AND Deptbudget = :dept AND yearlybudget = :year");
        $stmtTarget->execute([':target' => $targetID, ':dept' => $dept, ':year' => $year]);
        $targetAlloc = $stmtTarget->fetch(PDO::FETCH_ASSOC);

        if (!$targetAlloc) {
            $pdo->rollBack();
            echo "<div class='bg-red-200 text-red-800 p-3 rounded mb-4'>❌ Error: Target allocation not found.</div>";
            exit();
        }

        // Total department budget
        $stmtDept = $pdo->prepare("SELECT Amount FROM departmentbudget WHERE Deptbudget = :dept");
        $stmtDept->execute([':dept' => $dept]);
        $totalBudget = floatval($stmtDept->fetchColumn());

        $oldTargetPercent = $targetAlloc['percentage'];
        $oldTargetAmount = $targetAlloc['Amount'];
        $newTargetAmount = ($newTargetPercent / 100) * $totalBudget;
        $deltaPercent = $newTargetPercent - $oldTargetPercent;

        if ($deltaPercent > 0) {
            if (empty($decreaseID)) {
                $pdo->rollBack();
                echo "<div class='bg-red-200 text-red-800 p-3 rounded mb-4'>❌ Error: Please select a decrease source to fund the increase.</div>";
                exit();
            }

            $stmtDecrease = $pdo->prepare("SELECT allocationID, percentage, Amount FROM costallocation WHERE allocationID = :decrease AND Deptbudget = :dept AND yearlybudget = :year");
            $stmtDecrease->execute([':decrease' => $decreaseID, ':dept' => $dept, ':year' => $year]);
            $decreaseAlloc = $stmtDecrease->fetch(PDO::FETCH_ASSOC);

            if (!$decreaseAlloc) {
                $pdo->rollBack();
                echo "<div class='bg-red-200 text-red-800 p-3 rounded mb-4'>❌ Error: Decrease allocation not found.</div>";
                exit();
            }

            $oldDecreasePercent = $decreaseAlloc['percentage'];
            if ($deltaPercent > $oldDecreasePercent) {
                $pdo->rollBack();
                echo "<div class='bg-red-200 text-red-800 p-3 rounded mb-4'>❌ Error: Not enough budget in the decrease source.</div>";
                exit();
            }

            $newDecreasePercent = $oldDecreasePercent - $deltaPercent;
            $newDecreaseAmount = ($newDecreasePercent / 100) * $totalBudget;

            $stmtAdj = $pdo->prepare("INSERT INTO allocationadjustment (allocationID, oldpercent, oldamount, Reason, ChaneDate) VALUES (:aid, :oldp, :olda, :reason, NOW())");
            $stmtAdj->execute([
                ':aid' => $targetID,
                ':oldp' => $oldTargetPercent,
                ':olda' => $oldTargetAmount,
                ':reason' => $reason
            ]);
            $stmtAdj->execute([
                ':aid' => $decreaseID,
                ':oldp' => $oldDecreasePercent,
                ':olda' => $decreaseAlloc['Amount'],
                ':reason' => $reason
            ]);

            $stmtUpdate = $pdo->prepare("UPDATE costallocation SET percentage = :p, Amount = :a WHERE allocationID = :id AND Deptbudget = :dept AND yearlybudget = :year");
            $stmtUpdate->execute([':p' => $newTargetPercent, ':a' => $newTargetAmount, ':id' => $targetID, ':dept' => $dept, ':year' => $year]);
            $stmtUpdate->execute([':p' => $newDecreasePercent, ':a' => $newDecreaseAmount, ':id' => $decreaseID, ':dept' => $dept, ':year' => $year]);
        } else {
            $stmtAdj = $pdo->prepare("INSERT INTO allocationadjustment (allocationID, oldpercent, oldamount, Reason, ChaneDate) VALUES (:aid, :oldp, :olda, :reason, NOW())");
            $stmtAdj->execute([
                ':aid' => $targetID,
                ':oldp' => $oldTargetPercent,
                ':olda' => $oldTargetAmount,
                ':reason' => $reason
            ]);

            $stmtUpdate = $pdo->prepare("UPDATE costallocation SET percentage = :p, Amount = :a WHERE allocationID = :id AND Deptbudget = :dept AND yearlybudget = :year");
            $stmtUpdate->execute([':p' => $newTargetPercent, ':a' => $newTargetAmount, ':id' => $targetID, ':dept' => $dept, ':year' => $year]);

            if ($deltaPercent < 0) {
                $freedAmount = $oldTargetAmount - $newTargetAmount;
                $stmtUsed = $pdo->prepare("UPDATE departmentbudget SET Usedbudget = Usedbudget - :freed WHERE Deptbudget = :dept");
                $stmtUsed->execute([':freed' => $freedAmount, ':dept' => $dept]);
            }
        }

        $pdo->commit();
        echo "<div class='bg-green-200 text-green-800 p-3 rounded mb-4'>✅ Allocation adjusted successfully!</div>";

    } catch(PDOException $e){
        $pdo->rollBack();
        echo "<div class='bg-red-200 text-red-800 p-3 rounded mb-4'>❌ Database Error: ".$e->getMessage()."</div>";
    }
}

try {
    $sql = "
    SELECT 
        aa.allocateadjustmentID,
        ch.accountName AS Title,
        aa.oldpercent,
        aa.oldamount,
        ca.percentage AS newpercent,
        ca.Amount AS newamount,
        aa.Reason,
        aa.ChaneDate
    FROM allocationadjustment aa
    JOIN costallocation ca ON aa.allocationID = ca.allocationID
    JOIN chartofaccount ch ON ca.accountID = ch.accountID 
    ORDER BY aa.ChaneDate DESC";
    $stmt = $pdo->query($sql);
    $allocationAdjustments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}
?>