<?php
$custumerID = '1';

include_once __DIR__ . '/../../utility/connection.php';
date_default_timezone_set('Asia/Manila');

$successMessage = '';
$errorMessage = '';

// Function to generate a unique reference number
function generateReferenceNo($pdo) {    
    $prefix = 'INV-' . date('Ymd') . '-';
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < 4; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    $referenceNo = $prefix . $randomString;

    // Optional: Check if reference number already exists
    $sqlCheck = "SELECT COUNT(*) FROM ar_invoices WHERE reference_no = :referenceNo";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->bindParam(':referenceNo', $referenceNo);
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn() > 0) {
        // If exists, generate a new one (recursive call)
        return generateReferenceNo($pdo);
    }
    return $referenceNo;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insert'])) {
   
    $invoiceDate = filter_input(INPUT_POST, 'invoiceDate', FILTER_SANITIZE_STRING);
    $dueDate = filter_input(INPUT_POST, 'dueDate', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);


    if (!$invoiceDate || !$dueDate || !$description || $amount === false) {
        $errorMessage = "All fields are required and amount must be a valid number.";
    } else {
        try {

            $referenceNo = generateReferenceNo($pdo);

            $sql = "INSERT INTO ar_invoices (customer_id, invoice_date, due_date, description, amount, reference_no, created_at)
                    VALUES (:custumerID, :invoiceDate, :dueDate, :description, :amount, :referenceNo, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':custumerID', $custumerID);
            $stmt->bindParam(':invoiceDate', $invoiceDate);
            $stmt->bindParam(':dueDate', $dueDate);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':referenceNo', $referenceNo);
            $stmt->execute();
            $successMessage = "✅ Invoice added successfully with Reference No: $referenceNo.";
        } catch (PDOException $e) {
            $errorMessage = "❌ Error: " . $e->getMessage();
        }
    }
}
?>
<?php include __DIR__ . "/../sidebar.html"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Freight Finance — Invoice Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/static/css/sidebar.css">
</head>
<body >
       <div class="content" id="mainContent">
    <div class="header">
        <div class="hamburger" id="hamburger">☰</div>
        <div>
            <h1>Disbursement Dashboard <span class="system-title">| (NAME OF DEPARTMENT)</span></h1>
        </div>
        <div class="theme-toggle-container">
            <span class="theme-label">Dark Mode</span>
            <label class="theme-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label> 
        </div>
    </div>
    <div class="container mx-auto p-6 max-w-2xl">
        <h1 class="text-3xl font-bold text-center mb-6">Create Invoice</h1>

        <!-- Display success or error messages -->
        <?php if ($successMessage): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p><?php echo $successMessage; ?></p>
            </div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo $errorMessage; ?></p>
            </div>
        <?php endif; ?>

        <!-- Invoice Form -->
        <form method="POST" class="bg-white shadow-md rounded-lg p-6">
            <input type="hidden" name="insert" value="1">
            <div class="mb-4">
                <label for="invoiceDate" class="block text-sm font-medium text-gray-700">Invoice Date</label>
                <input type="date" id="invoiceDate" name="invoiceDate" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="dueDate" class="block text-sm font-medium text-gray-700">Due Date</label>
                <input type="date" id="dueDate" name="dueDate" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea id="description" name="description" required
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                          rows="4"></textarea>
            </div>
            <div class="mb-4">
                <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
                <input type="number" id="amount" name="amount" step="0.01" required
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Add Invoice
                </button>
            </div>
        </form>

        <!-- Display Recent Invoices -->
        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-4">Recent Invoices</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white shadow-md rounded-lg">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference No</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php
                        try {
                            $sql = "SELECT invoice_date, due_date, description, amount, reference_no 
                                    FROM ar_invoices 
                                    WHERE customer_id = :custumerID 
                                    ORDER BY created_at DESC 
                                    LIMIT 5";
                            $stmt = $pdo->prepare($sql);
                            $stmt->bindParam(':custumerID', $custumerID);
                            $stmt->execute();
                            $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            if ($invoices) {
                                foreach ($invoices as $invoice) {
                                    echo '<tr>';
                                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($invoice['invoice_date']) . '</td>';
                                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($invoice['due_date']) . '</td>';
                                    echo '<td class="px-6 py-4 text-sm text-gray-900">' . htmlspecialchars($invoice['description']) . '</td>';
                                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . number_format($invoice['amount'], 2) . '</td>';
                                    echo '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">' . htmlspecialchars($invoice['reference_no']) . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">No invoices found.</td></tr>';
                            }
                        } catch (PDOException $e) {
                            echo '<tr><td colspan="5" class="px-6 py-4 text-sm text-red-500 text-center">Error fetching invoices: ' . $e->getMessage() . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    </div>
    <script src="<?php echo '../../static/js/filter.js';?>"></script>
<script src="<?php echo '../../static/js/modal.js'; ?>"></script>
</body>
</html>