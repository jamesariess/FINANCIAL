<?php
include_once __DIR__ . '/../../utility/connection.php';
include_once __DIR__ . '/../../utility/hr.php';
include_once __DIR__ . '/../../utility/cr3.php';

date_default_timezone_set('Asia/Manila');
if (isset($_GET['ajax']) && $_GET['ajax'] == 1 && isset($_GET['query'])) {
    $query = $_GET['query'] . '%';
    $stmt = $pdo2->prepare("SELECT EmployeeID, EmployeeName FROM employee WHERE EmployeeID LIKE ? LIMIT 10");
    $stmt->execute([$query]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($employees);
    exit;
}

if (isset($_POST['submit_employee'])) {
    $empId   = $_POST['empId'] ?? '';
    $empName = $_POST['empName'] ?? '';
    $title   = $_POST['empTitle'] ?? '';
    $amount  = $_POST['empAmount'] ?? '';
    $term    = $_POST['empTerm'] ?? '';
    $purpose = $_POST['empPurpose'] ?? '';

   
    if ($empId && $empName && $title && $amount && $term && $purpose) {
        $stmt = $pdo->prepare("INSERT INTO request (employeeID, Requested_by, requestTiTle, Amount, Due, Purpuse,Modules,status) VALUES (?, ?, ?, ?, ?, ?,'Finance','Verified')");
        $stmt->execute([$empId, $empName, $title, $amount, $term, $purpose]);
        $successMessage = "Employee loan request submitted successfully!";
    } else {
        $errorMessage = "Please fill all fields.";
    }
}




?>