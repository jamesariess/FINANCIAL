<?php include __DIR__ . "/../sidebar.html"; ?>
<?php

// --- Database Connection ---
// This assumes 'connection.php' is now configured to use PDO (PHP Data Objects).
// You MUST update 'connection.php' to use PDO and your actual credentials.
try {
    // Include the PDO database connection file
  include_once __DIR__ . '/../../utility/connection.php';

    // --- Handle Form Submissions ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update'])) {
            $paymentMethodID = $_POST['update_paymentMethodID'];
            $methodName = $_POST['update_methodName'];
            $accountDetails = $_POST['update_accountDetails'];

            $stmt = $pdo['ar']->prepare("UPDATE paymentmethod SET method_name = :method_name, account_details = :account_details WHERE payment_method_id = :payment_method_id");
            $stmt->bindParam(':method_name', $methodName);
            $stmt->bindParam(':account_details', $accountDetails);
            $stmt->bindParam(':payment_method_id', $paymentMethodID);

            if ($stmt->execute()) {
                // Success: Redirect to prevent form resubmission on refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<p class='text-red-500'>Error updating record.</p>";
            }
        } elseif (isset($_POST['archive'])) {
            $paymentMethodID = $_POST['archive_paymentMethodID'];

            $stmt = $pdo['ar']->prepare("DELETE FROM paymentmethod WHERE payment_method_id = :payment_method_id");
            $stmt->bindParam(':payment_method_id', $paymentMethodID);

            if ($stmt->execute()) {
                // Success: Redirect to prevent form resubmission on refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<p class='text-red-500'>Error archiving record.</p>";
            }
        } elseif (isset($_POST['add'])) {
            $methodName = $_POST['add_methodName'];
            $accountDetails = $_POST['add_accountDetails'];

            $stmt = $pdo['ar']->prepare("INSERT INTO paymentmethod (method_name, account_details) VALUES (:method_name, :account_details)");
            $stmt->bindParam(':method_name', $methodName);
            $stmt->bindParam(':account_details', $accountDetails);

            if ($stmt->execute()) {
                // Success: Redirect to prevent form resubmission on refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<p class='text-red-500'>Error adding new record.</p>";
            }
        }
    }

    // --- Fetch Data from Database using PDO ---
    // Prepare and execute a SQL query to select all data from the 'paymentmethod' table
    $stmt = $pdo['ar']->prepare("SELECT * FROM paymentmethod");
    $stmt->execute();
    $paymentMethods = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If the connection or query fails, terminate the script and display an error
    die("Database operation failed: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Method Report</title>
       <link rel="stylesheet" href="/static/css/sidebar.css">
   
</head>
<body>
    
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
    </div>
    .  </div>
 <div class="table-section" id="invoiceTableSection">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-2xl font-bold text-gray-800">Payment Method Report</h3>
        <button onclick="openAddModal()" class="px-4 py-2 bg-green-500 text-white font-semibold rounded-lg shadow-md hover:bg-green-600 transition-colors">
            + Add Payment Method
        </button>
    </div>
    <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Details</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($paymentMethods)):
                foreach ($paymentMethods as $row): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['payment_method_id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['method_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['account_details']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 action-links space-x-2">
                            <a href="#" class="view-btn"
                                onclick="openViewModal(
                                    '<?php echo htmlspecialchars($row['payment_method_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['method_name'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['account_details'], ENT_QUOTES); ?>');
                                    return false;">
                                👁️ View
                            </a>
                            <a href="#" class="update-btn"
                                onclick="openUpdateModal(
                                    '<?php echo htmlspecialchars($row['payment_method_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['method_name'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['account_details'], ENT_QUOTES); ?>');
                                    return false;">
                                ✏️ Update
                            </a>
                            <a href="#" class="archive-btn"
                                onclick="openArchiveModal('<?php echo htmlspecialchars($row['payment_method_id'], ENT_QUOTES); ?>'); return false;">
                                🗄️ Archive
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="4" class="text-center p-4 text-gray-500">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'addModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md relative" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-2xl font-bold" onclick="closeModal('addModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Add New Payment Method</h2>

        <form method="post" class="space-y-6">
            <div>
                <label for="addMethodName" class="block text-gray-700 font-medium mb-1">Method Name:</label>
                <input type="text" name="add_methodName" id="addMethodName" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="addAccountDetails" class="block text-gray-700 font-medium mb-1">Account Details:</label>
                <input type="text" name="add_accountDetails" id="addAccountDetails" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" name="add" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Add</button>
            </div>
        </form>
    </div>
</div>

<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'viewModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md relative transition-all transform scale-95" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-2xl font-bold" onclick="closeModal('viewModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">View Payment Method Record</h2>
        <div class="space-y-4 text-gray-700">
            <p><strong class="font-semibold text-lg">Payment Method ID:</strong> <span id="viewPaymentMethodID" class="ml-2 font-mono text-gray-600"></span></p>
            <p><strong class="font-semibold text-lg">Method Name:</strong> <span id="viewMethodName" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Account Details:</strong> <span id="viewAccountDetails" class="ml-2"></span></p>
        </div>
    </div>
</div>

<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'updateModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md relative" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-2xl font-bold" onclick="closeModal('updateModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Update Payment Method Record</h2>

        <form method="post" class="space-y-6">
            <input type="hidden" name="update_paymentMethodID" id="updatePaymentMethodID">

            <div>
                <label for="updateMethodName" class="block text-gray-700 font-medium mb-1">Method Name:</label>
                <input type="text" name="update_methodName" id="updateMethodName" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="updateAccountDetails" class="block text-gray-700 font-medium mb-1">Account Details:</label>
                <input type="text" name="update_accountDetails" id="updateAccountDetails" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition" onclick="closeModal('updateModal')">Cancel</button>
                <button type="submit" name="update" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="archiveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'archiveModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-sm relative" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-2xl font-bold" onclick="closeModal('archiveModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Archive Payment Method Record</h2>
        <p class="mb-6 text-gray-700">Are you sure you want to archive this payment method record?</p>
        <form method="post" class="flex justify-end space-x-3">
            <input type="hidden" name="archive_paymentMethodID" id="archivePaymentMethodID">
            <button type="button" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition" onclick="closeModal('archiveModal')">Cancel</button>
            <button type="submit" name="archive" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Archive</button>
        </form>
    </div>
</div>


</body>
</html>