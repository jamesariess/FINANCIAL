<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');


$departments = $pdo->query("
    SELECT Deptbudget, Name, Amount, COALESCE(UsedBudget,0) AS UsedBudget
    FROM departmentbudget
    WHERE status='Proceed'
")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['department'], $_POST['title'])) {
    $dept = $_POST['department'];
    $allocationID = $_POST['title'];
    $increase = floatval($_POST['increase'] ?? 0);
    $decrease = floatval($_POST['decrease'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');


    $stmt = $pdo->prepare("SELECT Amount, Percentage, Deptbudget FROM costallocation WHERE AllocationID = :id");
    $stmt->execute([':id' => $allocationID]);
    $allocation = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($allocation) {
        $oldPercent = floatval($allocation['Percentage']);
        $oldAmount = floatval($allocation['Amount']);
        $newPercent = $oldPercent + $increase - $decrease;
        if($newPercent < 0) $newPercent = 0;
        if($newPercent > 100) $newPercent = 100;


        $stmtDept = $pdo->prepare("SELECT Amount FROM departmentbudget WHERE Deptbudget = :dept");
        $stmtDept->execute([':dept' => $dept]);
        $deptData = $stmtDept->fetch(PDO::FETCH_ASSOC);
        $newAmount = ($newPercent / 100) * $deptData['Amount'];


        $insertAdj = $pdo->prepare("
            INSERT INTO allocationadjustment 
            (allocationID, oldpercent, oldamount, Reason, Archine, ChaneDate) 
            VALUES (:aid, :oldp, :olda, :reason, 'NO', NOW())
        ");
        $insertAdj->execute([
            ':aid' => $allocationID,
            ':oldp' => $oldPercent,
            ':olda' => $oldAmount,
            ':reason' => $reason
        ]);


        $updateAlloc = $pdo->prepare("
            UPDATE costallocation 
            SET Percentage = :percent, Amount = :amount 
            WHERE AllocationID = :id
        ");
        $updateAlloc->execute([
            ':percent' => $newPercent,
            ':amount' => $newAmount,
            ':id' => $allocationID
        ]);


        $stmtSum = $pdo->prepare("SELECT SUM(Amount) AS totalUsed FROM costallocation WHERE Deptbudget = :dept");
        $stmtSum->execute([':dept' => $dept]);
        $sumData = $stmtSum->fetch(PDO::FETCH_ASSOC);
        $usedBudget = floatval($sumData['totalUsed']);

        $updateDept = $pdo->prepare("UPDATE departmentbudget SET UsedBudget = :used WHERE Deptbudget = :dept");
        $updateDept->execute([
            ':used' => $usedBudget,
            ':dept' => $dept
        ]);

        echo "<div class='bg-green-200 text-green-800 p-3 rounded mb-4'>✅ Allocation updated and adjustment logged successfully!</div>";
    }
}
?>