<?php
include_once __DIR__ . '/../../utility/connection.php';

date_default_timezone_set('Asia/Manila');

// Function to fetch active loans with vendor info
function getActiveLoans($pdo) {
    $sql = "
        SELECT 
            l.LoanID,
            l.LoanTitle,
            l.LoanAmount,
            l.interestRate,
            l.paidAmount,
            l.startDate,
            l.EndDate,
            l.Status,
            v.vendor_name as lender
        FROM loan l
        LEFT JOIN vendor v ON l.VendorID = v.vendor_id
        WHERE l.Archive = 'NO' AND l.Status != 'Paid'
        ORDER BY l.LoanID
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $loans = [];
    while ($row = $stmt->fetch()) {
        $principal = $row['LoanAmount'];
        $rate = $row['interestRate'];
        $paid = $row['paidAmount'] ?? 0;
        $interest = $principal * ($rate / 100); 
        $outstanding = ($principal + $interest) - $paid;
        $monthlyDue = $outstanding / 12; 
        $dueDate = date('Y-m-d', strtotime($row['EndDate'])); 

        $loans[] = [
            'id' => 'LN-' . date('Y') . '-' . str_pad($row['LoanID'], 3, '0', STR_PAD_LEFT),
            'rawId' => $row['LoanID'], 
            'lender' => $row['lender'],
            'title' => $row['LoanTitle'],
            'principal' => '₱' . number_format($principal),
            'outstanding' => '₱' . number_format($outstanding),
            'rate' => $rate . '%',
            'monthlyDue' => '₱' . number_format($monthlyDue, 0),
            'dueDate' => $dueDate,
            'status' => $row['Status']
        ];
    }
    return $loans;
}

// [Rest of the functions remain unchanged...]
function getTotalActiveLoans($pdo) {
    $sql = "SELECT COUNT(*) as count FROM loan WHERE Archive = 'NO' AND Status != 'Paid'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row['count'];
}

function getTotalOutstanding($pdo) {
    $sql = "
        SELECT SUM(l.LoanAmount + (l.LoanAmount * l.interestRate / 100) - COALESCE(l.paidAmount, 0)) as total
        FROM loan l
        WHERE l.Archive = 'NO' AND l.Status != 'Paid'
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    return '₱' . number_format($row['total'] ?? 0);
}

function getNextDueDate($pdo) {
    $sql = "
        SELECT MIN(EndDate) as nextDue
        FROM loan
        WHERE Archive = 'NO' AND Status != 'Paid'
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $row = $stmt->fetch();
    return date('M d, Y', strtotime($row['nextDue'] ?? '2025-09-30'));
}

function getPaymentHistory($pdo, $loanId) {
    $sql = "
        SELECT payment_date, amount, method, remarks
        FROM ap_payments
        WHERE LoanID = :loanId AND Archive = 'NO'
        ORDER BY payment_date DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loanId', $loanId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getOutstandingForLoan($pdo, $loanId) {
    $sql = "
        SELECT LoanAmount, interestRate, paidAmount
        FROM loan
        WHERE LoanID = :loanId
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':loanId', $loanId, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();
    if ($row) {
        $principal = $row['LoanAmount'];
        $rate = $row['interestRate'];
        $paid = $row['paidAmount'] ?? 0;
        $interest = $principal * ($rate / 100);
        return ($principal + $interest) - $paid;
    }
    return 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_payment') {
    try {
        $loanId = (int)$_POST['loanId'];
        $amount = (float)$_POST['amount'];
        $method = $_POST['method'];
        $remarks = $_POST['remarks'] ?? '';
        $paymentDate = date('Y-m-d');

        $validMethods = ['Bank Transfer', 'Check', 'Cash'];
        if (!in_array($method, $validMethods)) {
            echo json_encode(['success' => false, 'message' => 'Invalid payment method']);
            exit;
        }

        $pdo->beginTransaction();

        $sql = "INSERT INTO ap_payments (LoanID, payment_date, amount, method, remarks, created_at, Archive) VALUES (:loanId, :paymentDate, :amount, :method, :remarks, NOW(), 'NO')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':loanId' => $loanId,
            ':paymentDate' => $paymentDate,
            ':amount' => $amount,
            ':method' => $method,
            ':remarks' => $remarks
        ]);

        $updateSql = "UPDATE loan SET paidAmount = paidAmount + :amount WHERE LoanID = :loanId";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([':amount' => $amount, ':loanId' => $loanId]);

        $outstanding = getOutstandingForLoan($pdo, $loanId);
        $newStatus = ($outstanding <= 0) ? 'Paid' : 'Partially Paid';
        $statusSql = "UPDATE loan SET Status = :status WHERE LoanID = :loanId";
        $statusStmt = $pdo->prepare($statusSql);
        $statusStmt->execute([':status' => $newStatus, ':loanId' => $loanId]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Payment submitted successfully']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error submitting payment: ' . $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_history') {
    $loanId = (int)$_POST['loanId'];
    $history = getPaymentHistory($pdo, $loanId);
    echo json_encode($history);
    exit;
}
?>