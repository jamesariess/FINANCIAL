<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

$id = $_GET['id'] ?? null;
$sql = "SELECT * FROM details WHERE accountID = :id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id', $id);
$stmt->execute();

try {
    $sql = "SELECT 
        jd.entriesID,
        jd.journalID,
        j.Description AS journalDescription,
        c.accountName,
        jd.debit,
        jd.credit
    FROM details jd
    JOIN entries j ON jd.journalID = j.journalID
    JOIN chartofaccount c ON jd.accountID = c.accountID 
    WHERE jd.Archive = 'NO'";
    if ($id) {
        $sql .= " AND jd.accountID = :id";
    }
    $stmt = $pdo->prepare($sql);
    if ($id) {
        $stmt->bindParam(':id', $id);
    }
    $stmt->execute();
    $journalDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    
    echo json_encode(['success' => false, 'message' => 'Error fetching plans: ' . $e->getMessage()]);
    exit;
}


function getTotalEntires($pdo) {
    try {
        $sql = "SELECT COUNT(*) as total FROM details WHERE Archive = 'NO'";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        return 0; 
    }
}


function getTotalCredits($pdo) {
    try {
        $sql = "SELECT COALESCE(SUM(credit), 0) as total FROM details WHERE Archive = 'NO'";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return number_format($result['total'], 2);
    } catch (PDOException $e) {
        return '0.00'; 
    }
}


function getTotalDebits($pdo) {
    try {
        $sql = "SELECT COALESCE(SUM(debit), 0) as total FROM details WHERE Archive = 'NO'";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return number_format($result['total'], 2);
    } catch (PDOException $e) {
        return '0.00'; 
    }
}
?>