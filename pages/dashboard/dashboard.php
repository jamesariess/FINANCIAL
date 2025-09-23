<?php
include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

function formatShortNumber($n) {
    if ($n < 1000) {
        return number_format($n);
    } else if ($n < 1000000) {
        return number_format($n / 1000, 1) . 'K';
    } else if ($n < 1000000000) {
        return number_format($n / 1000000, 1) . 'M';
    } else if ($n < 1000000000000) {
        return number_format($n / 1000000000, 1) . 'B';
    } else if ($n < 1000000000000000) {
        return number_format($n / 1000000000000, 1) . 'T';
    } else {
        return number_format($n / 1000000000000000, 1) . 'Q';
    }
}

function getTotalDisburseAmount($pdo) {
    $sql = "SELECT IFNULL(SUM(ApprovedAmount), 0) as total FROM request WHERE status IN ('Paid') AND Archive = 'NO'";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $result['total'];
        return '₱' . formatShortNumber($total);
    } catch (PDOException $e) {
        error_log("Error in getTotalDisburseAmount: " . $e->getMessage());
        return '₱0'; 
    }
}

function getTotalOutstanding($pdo) {
    $sql = "
        SELECT l.LoanAmount, COALESCE(l.paidAmount, 0) as paidAmount, l.interestRate
        FROM loan l
        WHERE l.Archive = 'NO' AND l.Status != 'Paid' AND Remarks = 'Approved'
    ";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $totalOutstanding = 0;
        while ($row = $stmt->fetch()) {
            $principal = $row['LoanAmount'];
            $interestRate = $row['interestRate'];
            $paid = $row['paidAmount'];
            $totalInterest = $principal * ($interestRate / 100);
            $totalRepayable = $principal + $totalInterest;
            $outstanding = $totalRepayable - $paid;
            $totalOutstanding += $outstanding;
        }
        return '₱' . formatShortNumber($totalOutstanding);
    } catch (PDOException $e) {
        error_log("Error in getTotalOutstanding: " . $e->getMessage());
        return '₱0';
    }
}

function getTotalPayment($pdo) {
    $sql = "SELECT SUM(amount) AS total_amount FROM ar_collections WHERE Archive = 'NO'";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $result['total_amount'];
        return '₱' . formatShortNumber($total);
    } catch (PDOException $e) {
        error_log("Error in getTotalPayment: " . $e->getMessage());
        return '₱0';
    }
}

function getFollowUp($pdo){
    $sql = "SELECT COUNT(*) as total FROM follow WHERE paymentstatus='Not Paid' AND Archive='NO'";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data['total'];
    } catch (PDOException $e) {
        error_log("Error in getFollowUp: " . $e->getMessage());
        return 0;
    }
}

function getUtilization($pdo) {
    $sql = "SELECT SUM(Amount) as total_budget, SUM(UsedBudget) as total_used FROM departmentbudget WHERE DateValid = :year AND status = 'Proceed'";
    $params = [':year' => date('Y')];
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $totalBudget = (int)$data['total_budget'];
        $totalUsed = (int)$data['total_used'];
        $utilization = 0;
        if ($totalBudget > 0) {
            $utilization = min(100, round(($totalUsed / $totalBudget) * 100));
        }
        return $utilization . '%';
    } catch (PDOException $e) {
        error_log("Error in getUtilization: " . $e->getMessage());
        return '0%';
    }
}

function getTotalEntires($pdo) {
    try {
        $sql = "SELECT COUNT(*) as total FROM details WHERE Archive = 'NO'";
        $stmt = $pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error in getTotalEntires: " . $e->getMessage());
        return 0; 
    }
}

try {
    $chartDataStmt = $pdo->query("
        SELECT p.year, p.month, 
               SUM(CASE WHEN LOWER(c.accounType) IN ('revenue','income') THEN jd.credit - jd.debit ELSE 0 END) as total_revenue,
               SUM(CASE WHEN LOWER(c.accounType) IN ('expense','expenses') THEN jd.debit - jd.credit ELSE 0 END) as total_expenses
        FROM periods p
        LEFT JOIN entries e ON p.period_id = e.periodID
        LEFT JOIN details jd ON e.journalID = jd.journalID
        LEFT JOIN chartofaccount c ON jd.accountID = c.accountID
        GROUP BY p.year, p.month
        ORDER BY p.year, p.month
    ");
    $chartData = $chartDataStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $chartData = [];
    error_log("Error fetching chart data: " . $e->getMessage());
}

$chartLabels = [];
$chartRevenue = [];
$chartExpenses = [];
foreach ($chartData as $row) {
    $chartLabels[] = date('M Y', mktime(0, 0, 0, $row['month'], 1, $row['year']));
    $chartRevenue[] = (float)$row['total_revenue'];
    $chartExpenses[] = (float)$row['total_expenses'];
}

try {
    $sql = "SELECT r.* ,
    ch.accountName,
    d.Name
    FROM request r
    JOIN costallocation c on r.allocationID = c.allocationID
    JOIN chartofaccount ch ON c.accountID = ch.accountID 
    JOIN departmentbudget d on c.Deptbudget = d.Deptbudget
    WHERE r.Archive = 'NO' 
    LIMIT 6";
    $stmt = $pdo->query($sql);
    $disbursementReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "❌ Error fetching plans: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approver Management</title>
   <style>
    /* General styles */
:root {
    --sidebar-width: 250px;
    --primary-color: #4e73df;
    --secondary-color: #f8f9fc;
    --dark-bg: #1a1a2e;
    --dark-card: #16213e;
    --text-light: #f8f9fa;
    --text-dark: #212529;
    --success-color: #1cc88a;
    --info-color: #36b9cc;
    --border-radius: 0.35rem;
    --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    --gradient-blue: linear-gradient(135deg, #4e73df, #7aa2f7);
    --gradient-red: linear-gradient(135deg, #ef4444, #f87171);
    --gradient-yellow: linear-gradient(135deg, #facc15, #fde047);
    --gradient-green: linear-gradient(135deg, #22c55e, #4ade80);
    --gradient-purple: linear-gradient(135deg, #a855f7, #d8b4fe);
    --gradient-orange: linear-gradient(135deg, #f97316, #fdba74);
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    overflow-x: hidden;
    background-color: var(--secondary-color);
    color: var(--text-dark);
    transition: all 0.3s;
}

body.dark-mode {
    background-color: var(--dark-bg);
    color: var(--text-light);
}

/* Sidebar */
.sidebar {
    width: var(--sidebar-width);
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: #2c3e50;
    color: white;
    padding: 0;
    transition: transform 0.3s ease;
    z-index: 1000;
    transform: translateX(0);
     overflow-y: auto; 
}

.sidebar.collapsed,
.sidebar.show {
    transform: translateX(-100%);
}

.sidebar.show {
    transform: translateX(0);
}

.content {
    margin-left: var(--sidebar-width);
    padding: 20px;
    transition: margin-left 0.3s ease;
}

.content.expanded {
    margin-left: 0;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    z-index: 999;
}

.overlay.show {
    display: block;
}

.dropdown.active .dropdown-menu {
    display: block;
}

.sidebar .logo {
    padding: 1.5rem;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar .logo img {
    max-width: 100%;
    height: auto;
}

.system-name {
    padding: 0.5rem 1.5rem;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.8);
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    margin-bottom: 1rem;
}

.sidebar-item {
    display: block;
    color: var(--text-light);
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    border-left: 3px solid transparent;
    transition: all 0.3s;
}

.sidebar-item:hover,
.sidebar-item.active {
    background-color: rgba(255, 255, 255, 0.1);
    color: white;
    border-left: 3px solid white;
}

.sidebar::-webkit-scrollbar {
    width: 10px;
}

.sidebar::-webkit-scrollbar-track {
    background: #2c3e50;
    border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb {
    background-color: var(--primary-color);
    border-radius: 10px;
    border: 2px solid #2c3e50;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background-color: #3a5bc7;
}

.admin-feature {
    background-color: rgba(0, 0, 0, 0.1);
}

.dropdown {
    position: relative;
}

.dropdown-toggle {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dropdown-toggle .arrow {
    font-size: 0.8rem;
    transition: transform 0.3s;
}

.dropdown-toggle.active .arrow {
    transform: rotate(90deg);
}

.dropdown-menu {
    display: none;
    padding-left: 1.5rem;
    background-color: var(--dark-card);
}

.dropdown-menu.show {
    display: block;
}

.dropdown-menu .sidebar-item {
    padding-left: 2.4rem;
}

/* Header */
.header {
    background-color: white;
    padding: 1rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dark-mode .header {
    background-color: var(--dark-card);
    color: var(--text-light);
}

.hamburger {
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
}

.system-title {
    color: var(--primary-color);
    font-size: 1rem;
}

/* Dashboard Cards */
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.card {
    background-color: white;
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    transition: all 0.3s;
    
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.75rem 0 rgba(58, 59, 69, 0.2);
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
}


.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.form-collection {
    background-color: var(--secondary-color);
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

.dark-mode .form-group input,
.dark-mode .form-group select,
.dark-mode .form-group textarea {
    background-color: var(--dark-card);
    border-color: var(--text-light);
    color: var(--text-light);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
}

/* Buttons */
.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: #3a5bc7;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.toggle-form-btn {
    background-color: var(--primary-color);
    color: white;
    margin-bottom: 1.5rem;
}

.toggle-form-btn:hover {
    background-color: #3a5bc7;
}

/* Table Section */
.table-section {
    background-color: white;
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}
th{
    padding: 1rem;
    text-align: left;
    color: var(--text-light); /* was var(--text-light) ❌ */
    border-bottom: 2px solid #ddd;
}
 td {
    padding: 1rem;
    text-align: left;
    color: var(--text-dark); /* was var(--text-light) ❌ */
    border-bottom: 1px solid #ddd;
}


body.dark-mode .table-section {
    background-color: var(--dark-card);
    color: var(--text-light);
}

body.dark-mode table th,
body.dark-mode table td {
    color: var(--text-light);
    border-bottom: 1px solid #3a4b6e;
}
[id$="Modal"] > div {
  background-color: var(--secondary-color); /* Default light mode */
  color: var(--text-dark); /* Default light mode */
  border-radius: var(--border-radius);
  box-shadow: var(--shadow);
  padding: 1.5rem;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
}

body.dark-mode [id$="Modal"] > div {
  background-color: var(--dark-card); /* Dark mode background */
  color: var(--text-light); /* Dark mode text */
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); /* Slightly adjusted shadow for dark mode */
}

[id$="Modal"] h2 {
  color: var(--text-dark); /* Default light mode */
}

body.dark-mode [id$="Modal"] h2 {
  color: var(--text-light); /* Dark mode heading */
}
body.dark-mode [id$="Modal"] .btn-primary {
  background-color: var(--primary-color); /* Retain blue background */
  color: var(--text-light); /* Change text to white for contrast */
}

body.dark-mode [id$="Modal"] .btn-primary:hover {
  background-color: #3a5bc7; /* Darker blue on hover */
  color: var(--text-light); /* Keep text white on hover */
}

body.dark-mode [id$="Modal"] .btn-secondary {
  background-color: #30414e; /* Retain secondary button color */
  color: var(--text-light); /* Change text to white */
}

body.dark-mode [id$="Modal"] .btn-secondary:hover {
  background-color: #30414e; /* Darker secondary on hover */
  color: var(--text-light); /* Keep text white on hover */
}

[id$="Modal"] svg {
  color: #6b7280; /* Default light mode SVG color */
}

body.dark-mode [id$="Modal"] svg {
  color: #9ca3af; /* Neutral gray for dark mode */
}
body.dark-mode [id$="Modal"] .form-group input,
body.dark-mode [id$="Modal"] .form-group select,
body.dark-mode [id$="Modal"] .form-group textarea {
  background-color: #374151; /* Darker input background to match modal */
  border-color: #4b5563; /* Darker border */
  color: var(--text-light); /* White text for readability */
  caret-color: var(--text-light); /* Cursor color */
}

body.dark-mode [id$="Modal"] .form-group input::placeholder,
body.dark-mode [id$="Modal"] .form-group select::placeholder {
  color: #9ca3af; /* Lighter placeholder text */
}
body.dark-mode table tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

body.dark-mode table thead {
    background-color: #243763; /* dark header */
    color: var(--text-light);
}

tr:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

thead {
    background-color: var(--primary-color);
    color: white;
}

/* Theme Toggle */
.theme-toggle-container {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.theme-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.theme-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}
.w-3xl {
    width: 100%;
    max-width: 8906px; /* Equivalent to Tailwind's w-3xl (56rem) */
}
#overdueModal > div {
    max-width: 8960px; /* Match w-3xl */
}
input:checked + .slider {
    background-color: var(--primary-color);
}

input:checked + .slider:before {
    transform: translateX(26px);
}

/* New Enhancements */
.chart-container {
    background: white;
    padding: 1.5rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    max-height: 400px;
    overflow: hidden;
}

.quick-stat-card {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    padding: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.quick-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.quick-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gradient-blue);
}


.quick-stat-card.red::before { background: var(--gradient-red); }
.quick-stat-card.yellow::before { background: var(--gradient-yellow); }
.quick-stat-card.green::before { background: var(--gradient-green); }
.quick-stat-card.purple::before { background: var(--gradient-purple); }
.quick-stat-card.orange::before { background: var(--gradient-orange); }

.icon-circle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.1);
}

/* Dark Mode Specifics */
body.dark-mode .header,
body.dark-mode .card,
body.dark-mode .form-collection,
body.dark-mode .table-section,
body.dark-mode .chart-container,
body.dark-mode .quick-stat-card,
body.dark-mode #detailsModal .bg-white {
    background-color: var(--dark-card);
    color: var(--text-light);
}

.dark-mode th,
.dark-mode td {
    border-bottom-color: #3a4b6e;
}

.dark-mode tr:hover {
    background-color: rgba(255, 255, 255, 0.05);
}

.dark-mode #approvalCards div .text-gray-600,
.dark-mode #detailsModal .text-gray-600 {
    color: #9ca3af;
}

.dark-mode #approvalCards div h3,
.dark-mode #approvalCards div .text-purple-600,
.dark-mode #approvalCards div .text-green-600,
.dark-mode #approvalCards div .text-blue-600,
.dark-mode #approvalCards div .text-red-600,
.dark-mode #approvalCards div .text-indigo-600,
.dark-mode #approvalCards div .text-gray-800,
.dark-mode .quick-stat-card h3,
.dark-mode .quick-stat-card .text-gray-800,
.dark-mode #detailsModal .text-gray-800 {
    color: var(--text-light) !important;
}

.dark-mode #approvalCards div .bg-green-100 {
    background-color: rgba(34, 197, 94, 0.2);
}

.dark-mode #approvalCards div .bg-yellow-100 {
    background-color: rgba(250, 204, 21, 0.2);
}

.dark-mode #detailsModal .border-gray-300 {
    border-color: #4b5563;
}

/* Tailwind-like classes for consistency */
.bg-indigo-500 {
    background-color: #4e73df;
}

.hover\:bg-indigo-600:hover {
    background-color: #3a5bc7;
}

.bg-yellow-400 {
    background-color: #facc15;
}

.bg-gradient-to-br {
    background: linear-gradient(to bottom right, var(--dark-card), var(--dark-card)) !important;
}

/* Responsive */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.show {
        transform: translateX(0);
    }

    .content {
        margin-left: 0;
    }
}

@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        pointer-events: none;
    }

    .sidebar.show {
        transform: translateX(0);
        pointer-events: auto;
    }

    .content {
        margin-left: 0 !important;
    }

    .quick-stat-card {
        padding: 1rem;
    }

    th, td {
        padding: 0.75rem;
    }
}

@media (max-width: 576px) {
    .header {
        padding: 0.75rem;
    }

    .hamburger {
        font-size: 1.25rem;
    }

    .card {
        padding: 1rem;
    }

    .btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }

    .form-collection {
        padding: 1rem;
    }
}

   </style>

</head>
<body>
    <?php include "../sidebar.php"; ?>
<div class="overlay" id="overlay"></div>

<div class="content" id="mainContent">
    <div class="header">
        <div class="hamburger" id="hamburger">☰</div>
        <div>
            <h1>Admin Dashboard <span class="system-title">| (NAME OF DEPARTMENT)</span></h1>
        </div>
        <div class="theme-toggle-container">
            <span class="theme-label">Dark Mode</span>
            <label class="theme-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>

    <div class="w-full h-full space-y-8">
        <header>
            <h1 class="text-3xl font-bold text-var(--text-light)">Financial Dashboard</h1>
            <p class="text-sm text-slate-400 mt-2">Welcome back! Here's a brief overview of your financial performance.</p>
        </header>

        <section>
            <h2 class="text-xl font-semibold mb-4 text-var(--text-light)">Quick Stats</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-blue-500">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-slate-400">Disbursement</h3>
                        <div class="p-2 rounded-full bg-blue-500/20 text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down icon"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-var(--text-light) mb-1"><?php echo getTotalDisburseAmount($pdo); ?></p>
                    <p class="text-xs text-slate-500">View Data</p>
                </div>

                <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-red-500">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-slate-400">Accounts Payable</h3>
                        <div class="p-2 rounded-full bg-red-500/20 text-red-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text icon"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="21" y2="21"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-var(--text-light) mb-1"><?php echo getTotalOutstanding($pdo); ?></p>
                    <p class="text-xs text-slate-500">View Data</p>
                </div>

                <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-yellow-500">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-slate-400">Accounts Receivable</h3>
                        <div class="p-2 rounded-full bg-yellow-500/20 text-yellow-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt icon"><path d="M4 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v20l-2-2-2 2-2-2-2 2-2-2-2 2-2-2-2 2-2-2-2 2-2-2-2 2V2z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-var(--text-light) mb-1"><?php echo getTotalPayment($pdo);?></p>
                    <p class="text-xs text-slate-500">View Data</p>
                </div>

                <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-green-500">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-slate-400">Collection</h3>
                        <div class="p-2 rounded-full bg-green-500/20 text-green-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-coins icon"><path d="M9.8 19.95 2.15 15.8a1 1 0 0 1 0-1.6L9.8 10.05a1 1 0 0 1 1.4.15L18.4 14.8a1 1 0 0 1 0 1.6l-7.25 4.05a1 1 0 0 1-1.4-.15z"/><path d="m15 10-8.6 4.86a1 1 0 0 0 0 1.76L15 21l8.6-4.86a1 1 0 0 0 0-1.76L15 10z"/><path d="m7 7 8.6 4.86a1 1 0 0 0 0 1.76L7 18l-8.6-4.86a1 1 0 0 0 0-1.76L7 7z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-var(--text-light) mb-1"><?php echo getFollowUp($pdo);?></p>
                    <p class="text-xs text-slate-500">Need To follow Up</p>
                </div>
                
                <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-purple-500">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-slate-400">Budget Management</h3>
                        <div class="p-2 rounded-full bg-purple-500/20 text-purple-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pie-chart icon"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10Z"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-var(--text-light) mb-1"><?php echo getUtilization($pdo);?></p>
                    <p class="text-xs text-slate-500">View Data</p>
                </div>

                <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-orange-500">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-slate-400">General Ledger</h3>
                        <div class="p-2 rounded-full bg-orange-500/20 text-orange-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-text icon"><path d="M2 11h20M12 2v20M2 15h20M2 19h20M2 7h20"/><path d="M2 3v18M22 3v18"/></svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-var(--text-light) mb-1"><?php echo getTotalEntires($pdo);?> Entries</p>
                    <p class="text-xs text-slate-500">View Data</p>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 h-80">
                    <h2 class="text-xl font-semibold mb-4 text-var(--text-light)">Financial Overview</h2>
                    <canvas id="financialChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <div>
                <div class="bg-slate-900 p-2 rounded-xl shadow-lg border border-slate-800 h-80 overflow-y-auto">
                    <h2 class="text-xl font-semibold mb-4 text-var(--text-light)">New Request Transactions</h2>
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase ">
                            <tr>
                                <th scope="col" class="py-3 px-4">Title</th>
                                <th scope="col" class="py-3 px-4">Account Name</th>
                                <th scope="col" class="py-3 px-4 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($disbursementReports)):
                            foreach($disbursementReports as $row): 
                            $amount = htmlspecialchars($row['Amount']);
                            $hala = formatShortNumber($amount);?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['requestTiTle']);?></td>
                                <td><?php echo htmlspecialchars($row['accountName']);?></td>
                                <td>₱ <?php echo $hala;?></td>
                            </tr>
                            <?php endforeach; else:?>
                            <tr><td colspan="9" class="text-center p-4">NO Records Found.</td></tr>
                            <?php endif;?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    let isDarkMode = false;

function getChartColors(isDark) {
    if (isDark) {
        return {
            textColor: '#f8fafc',   // white text
            gridColor: '#475569',   // slate-600
            revenueColor: '#38bdf8', // cyan-400
            expensesColor: '#f87171' // red-400
        };
    } else {
        return {
            textColor: '#334155',   // slate-800
            gridColor: '#cbd5e1',   // slate-300
            revenueColor: '#0ea5e9', // sky-500
            expensesColor: '#ef4444' // red-500
        };
    }
}

let colors = getChartColors(isDarkMode);

   const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('change', function() {
      document.body.classList.toggle('dark-mode', this.checked);
      // Update chart colors when theme changes
      updateChartColors();
    });

    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const hamburger = document.getElementById('hamburger');
    const overlay = document.getElementById('overlay');

    // Sidebar toggle logic
    hamburger.addEventListener('click', function() {
      if (window.innerWidth <= 992) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
      } else {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
      }
    });

    // Close sidebar on overlay click
    overlay.addEventListener('click', function() {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });

    // Dropdown toggle logic
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(event) {
            event.preventDefault();
            const parentDropdown = this.closest('.dropdown');
            parentDropdown.classList.toggle('active');
        });
    });

    // Function to get CSS variable value
    function getCssVariable(name) {
        return getComputedStyle(document.body).getPropertyValue(name).trim();
    }

const ctx = document.getElementById('financialChart').getContext('2d');
const financialChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode($chartRevenue); ?>,
            borderColor: colors.revenueColor,
            tension: 0.3,
            fill: false,
        }, {
            label: 'Expenses',
            data: <?php echo json_encode($chartExpenses); ?>,
            borderColor: colors.expensesColor,
            tension: 0.3,
            fill: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: colors.textColor
                }
            }
        },
        scales: {
            x: {
                ticks: { color: colors.textColor },
                grid: { color: colors.gridColor }
            },
            y: {
                ticks: { color: colors.textColor },
                grid: { color: colors.gridColor }
            }
        }
    }
});

    
    // Function to update chart colors
themeToggle.addEventListener('change', function() {
    document.body.classList.toggle('dark-mode', this.checked);

    isDarkMode = this.checked;
    colors = getChartColors(isDarkMode);

    // Update chart colors
    financialChart.options.plugins.legend.labels.color = colors.textColor;
    financialChart.options.scales.x.ticks.color = colors.textColor;
    financialChart.options.scales.x.grid.color = colors.gridColor;
    financialChart.options.scales.y.ticks.color = colors.textColor;
    financialChart.options.scales.y.grid.color = colors.gridColor;

    financialChart.data.datasets[0].borderColor = colors.revenueColor;
    financialChart.data.datasets[1].borderColor = colors.expensesColor;

    financialChart.update();
});


    window.onload = createChart;
</script>

</body>
</html>