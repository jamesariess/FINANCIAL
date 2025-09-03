<?php

// --- Database Connection ---
try {
include_once __DIR__ . '/../../utility/connection.php';

    // --- Handle Form Submissions ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update'])) {
            $disbursementID = $_POST['update_disbursementID'];
            $amount = $_POST['update_amount'];
            $disbursement_date = $_POST['update_disbursement_date'];
            $status = $_POST['update_status'];
            $description = $_POST['update_description'];
            $approver_id = $_POST['update_approver_id'];
            $vendor_id = $_POST['update_vendor_id'];
            $payment_method_id = $_POST['update_payment_method_id'];
            $project_id = $_POST['update_project_id'];

            $stmt = $pdo['ar']->prepare("UPDATE disbursement SET amount = :amount, disbursement_date = :disbursement_date, status = :status, description = :description, approver_id = :approver_id, vendor_id = :vendor_id, payment_method_id = :payment_method_id, project_id = :project_id WHERE disbursement_id = :disbursement_id");
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':disbursement_date', $disbursement_date);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':approver_id', $approver_id);
            $stmt->bindParam(':vendor_id', $vendor_id);
            $stmt->bindParam(':payment_method_id', $payment_method_id);
            $stmt->bindParam(':project_id', $project_id);
            $stmt->bindParam(':disbursement_id', $disbursementID);

            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<p class='text-red-500'>Error updating record.</p>";
            }
        } elseif (isset($_POST['archive'])) {
            $disbursementID = $_POST['archive_disbursementID'];

            $stmt =$pdo['ar']->prepare("DELETE FROM disbursement WHERE disbursement_id = :disbursement_id");
            $stmt->bindParam(':disbursement_id', $disbursementID);

            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<p class='text-red-500'>Error archiving record.</p>";
            }
        } elseif (isset($_POST['add'])) {
            $amount = $_POST['add_amount'];
            $disbursement_date = $_POST['add_disbursement_date'];
            $status = $_POST['add_status'];
            $description = $_POST['add_description'];
            $approver_id = $_POST['add_approver_id'];
            $vendor_id = $_POST['add_vendor_id'];
            $payment_method_id = $_POST['add_payment_method_id'];
            $project_id = $_POST['add_project_id'];

            $stmt = $pdo['ar']->prepare("INSERT INTO disbursement (amount, disbursement_date, status, description, approver_id, vendor_id, payment_method_id, project_id) VALUES (:amount, :disbursement_date, :status, :description, :approver_id, :vendor_id, :payment_method_id, :project_id)");
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':disbursement_date', $disbursement_date);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':approver_id', $approver_id);
            $stmt->bindParam(':vendor_id', $vendor_id);
            $stmt->bindParam(':payment_method_id', $payment_method_id);
            $stmt->bindParam(':project_id', $project_id);

            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<p class='text-red-500'>Error adding new record.</p>";
            }
        }
    }

    // --- Fetch Data from Database using PDO ---
    $stmt = $pdo['ar']->prepare("SELECT * FROM disbursement");
    $stmt->execute();
    $disbursements = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database operation failed: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disbursement Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; padding: 2rem; }
        .table-section { background-color: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
        thead th { background-color: #f9fafb; font-weight: 600; color: #4b5563; }
        tr:hover { background-color: #f1f5f9; }
        .action-links a { padding: 0.5rem; border-radius: 0.5rem; transition: background-color 0.2s; }
        .action-links .view-btn { color: #2563eb; }
        .action-links .update-btn { color: #f97316; }
        .action-links .archive-btn { color: #dc2626; }
        .action-links a:hover { background-color: #e5e7eb; }
    </style>
</head>
<body class="bg-gray-100 flex items-start justify-center min-h-screen">

<div class="table-section max-w-6xl w-full">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-2xl font-bold text-gray-800">Disbursement Report</h3>
        <button onclick="openAddModal()" class="px-4 py-2 bg-green-500 text-white font-semibold rounded-lg shadow-md hover:bg-green-600 transition-colors">
            + Add Disbursement
        </button>
    </div>
    <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approver ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($disbursements)):
                foreach ($disbursements as $row): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['disbursement_id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['amount']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['disbursement_date']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['status']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['description']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['approver_id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['vendor_id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['payment_method_id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['project_id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 action-links space-x-2">
                            <a href="#" class="view-btn"
                                onclick="openViewModal(
                                    '<?php echo htmlspecialchars($row['disbursement_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['amount'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['disbursement_date'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['status'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['description'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['approver_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['vendor_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['payment_method_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['project_id'], ENT_QUOTES); ?>');
                                    return false;">
                                👁️ View
                            </a>
                            <a href="#" class="update-btn"
                                onclick="openUpdateModal(
                                    '<?php echo htmlspecialchars($row['disbursement_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['amount'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['disbursement_date'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['status'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['description'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['approver_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['vendor_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['payment_method_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['project_id'], ENT_QUOTES); ?>');
                                    return false;">
                                ✏️ Update
                            </a>
                            <a href="#" class="archive-btn"
                                onclick="openArchiveModal('<?php echo htmlspecialchars($row['disbursement_id'], ENT_QUOTES); ?>'); return false;">
                                🗄️ Archive
                            </a>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="10" class="text-center p-4 text-gray-500">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'addModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg relative" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-2xl font-bold" onclick="closeModal('addModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Add New Disbursement</h2>

        <form method="post" class="space-y-4">
            <div>
                <label for="addAmount" class="block text-gray-700 font-medium mb-1">Amount:</label>
                <input type="number" step="0.01" name="add_amount" id="addAmount" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="addDisbursementDate" class="block text-gray-700 font-medium mb-1">Disbursement Date:</label>
                <input type="date" name="add_disbursement_date" id="addDisbursementDate" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="addStatus" class="block text-gray-700 font-medium mb-1">Status:</label>
                <input type="text" name="add_status" id="addStatus" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="addDescription" class="block text-gray-700 font-medium mb-1">Description:</label>
                <textarea name="add_description" id="addDescription" rows="3" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
            </div>
            <div>
                <label for="addApproverID" class="block text-gray-700 font-medium mb-1">Approver ID:</label>
                <input type="number" name="add_approver_id" id="addApproverID" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="addVendorID" class="block text-gray-700 font-medium mb-1">Vendor ID:</label>
                <input type="number" name="add_vendor_id" id="addVendorID" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="addPaymentMethodID" class="block text-gray-700 font-medium mb-1">Payment Method ID:</label>
                <input type="number" name="add_payment_method_id" id="addPaymentMethodID" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="addProjectID" class="block text-gray-700 font-medium mb-1">Project ID:</label>
                <input type="number" name="add_project_id" id="addProjectID" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" name="add" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Add</button>
            </div>
        </form>
    </div>
</div>

<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'viewModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg relative transition-all transform scale-95" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-2xl font-bold" onclick="closeModal('viewModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">View Disbursement Record</h2>
        <div class="space-y-4 text-gray-700">
            <p><strong class="font-semibold text-lg">Disbursement ID:</strong> <span id="viewDisbursementID" class="ml-2 font-mono text-gray-600"></span></p>
            <p><strong class="font-semibold text-lg">Amount:</strong> <span id="viewAmount" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Date:</strong> <span id="viewDisbursementDate" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Status:</strong> <span id="viewStatus" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Description:</strong> <span id="viewDescription" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Approver ID:</strong> <span id="viewApproverID" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Vendor ID:</strong> <span id="viewVendorID" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Payment Method ID:</strong> <span id="viewPaymentMethodID" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Project ID:</strong> <span id="viewProjectID" class="ml-2"></span></p>
        </div>
    </div>
</div>

<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'updateModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-lg relative" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-2xl font-bold" onclick="closeModal('updateModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Update Disbursement Record</h2>

        <form method="post" class="space-y-4">
            <input type="hidden" name="update_disbursementID" id="updateDisbursementID">

            <div>
                <label for="updateAmount" class="block text-gray-700 font-medium mb-1">Amount:</label>
                <input type="number" step="0.01" name="update_amount" id="updateAmount" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="updateDisbursementDate" class="block text-gray-700 font-medium mb-1">Disbursement Date:</label>
                <input type="date" name="update_disbursement_date" id="updateDisbursementDate" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="updateStatus" class="block text-gray-700 font-medium mb-1">Status:</label>
                <input type="text" name="update_status" id="updateStatus" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="updateDescription" class="block text-gray-700 font-medium mb-1">Description:</label>
                <textarea name="update_description" id="updateDescription" rows="3" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
            </div>
            <div>
                <label for="updateApproverID" class="block text-gray-700 font-medium mb-1">Approver ID:</label>
                <input type="number" name="update_approver_id" id="updateApproverID" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="updateVendorID" class="block text-gray-700 font-medium mb-1">Vendor ID:</label>
                <input type="number" name="update_vendor_id" id="updateVendorID" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="updatePaymentMethodID" class="block text-gray-700 font-medium mb-1">Payment Method ID:</label>
                <input type="number" name="update_payment_method_id" id="updatePaymentMethodID" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="updateProjectID" class="block text-gray-700 font-medium mb-1">Project ID:</label>
                <input type="number" name="update_project_id" id="updateProjectID" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
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
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Archive Disbursement Record</h2>
        <p class="mb-6 text-gray-700">Are you sure you want to archive this disbursement record?</p>
        <form method="post" class="flex justify-end space-x-3">
            <input type="hidden" name="archive_disbursementID" id="archiveDisbursementID">
            <button type="button" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition" onclick="closeModal('archiveModal')">Cancel</button>
            <button type="submit" name="archive" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Archive</button>
        </form>
    </div>
</div>

<script>
    function openViewModal(id, amount, date, status, description, approver, vendor, payment, project) {
        document.getElementById('viewDisbursementID').innerText = id;
        document.getElementById('viewAmount').innerText = amount;
        document.getElementById('viewDisbursementDate').innerText = date;
        document.getElementById('viewStatus').innerText = status;
        document.getElementById('viewDescription').innerText = description;
        document.getElementById('viewApproverID').innerText = approver;
        document.getElementById('viewVendorID').innerText = vendor;
        document.getElementById('viewPaymentMethodID').innerText = payment;
        document.getElementById('viewProjectID').innerText = project;
        document.getElementById('viewModal').classList.remove('hidden');
    }

    function openUpdateModal(id, amount, date, status, description, approver, vendor, payment, project) {
        document.getElementById('updateDisbursementID').value = id;
        document.getElementById('updateAmount').value = amount;
        document.getElementById('updateDisbursementDate').value = date;
        document.getElementById('updateStatus').value = status;
        document.getElementById('updateDescription').value = description;
        document.getElementById('updateApproverID').value = approver;
        document.getElementById('updateVendorID').value = vendor;
        document.getElementById('updatePaymentMethodID').value = payment;
        document.getElementById('updateProjectID').value = project;
        document.getElementById('updateModal').classList.remove('hidden');
    }

    function openArchiveModal(id) {
        document.getElementById('archiveDisbursementID').value = id;
        document.getElementById('archiveModal').classList.remove('hidden');
    }
    
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function outsideClick(event, modalId) {
        if (event.target.id === modalId) {
            closeModal(modalId);
        }
    }
</script>
</body>
</html>