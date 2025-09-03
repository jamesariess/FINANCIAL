<?php


try {
    
include_once __DIR__ . '/../../utility/connection.php';

    // --- Handle Form Submissions ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update'])) {
            $approverID = $_POST['update_approverID'];
            $approverName = $_POST['update_approverName'];
            $title = $_POST['update_title'];

            $stmt = $pdo['ar']->prepare("UPDATE approver SET approver_name = :approver_name, title = :title WHERE approver_id = :approver_id");
            $stmt->bindParam(':approver_name', $approverName);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':approver_id', $approverID);

            if ($stmt->execute()) {
                // Success: Redirect to prevent form resubmission on refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<p class='text-red-500'>Error updating record.</p>";
            }
        } elseif (isset($_POST['archive'])) {
            $approverID = $_POST['archive_approverID'];

            $stmt = $pdo['ar']->prepare("DELETE FROM approver WHERE approver_id = :approver_id");
            $stmt->bindParam(':approver_id', $approverID);

            if ($stmt->execute()) {
                // Success: Redirect to prevent form resubmission on refresh
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<p class='text-red-500'>Error archiving record.</p>";
            }
        } elseif (isset($_POST['add'])) {
            $approverName = $_POST['add_approverName'];
            $title = $_POST['add_title'];

            $stmt = $pdo['ar']->prepare("INSERT INTO approver (approver_name, title) VALUES (:approver_name, :title)");
            $stmt->bindParam(':approver_name', $approverName);
            $stmt->bindParam(':title', $title);

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
    // Prepare and execute a SQL query to select all data from the 'approver' table
    $stmt = $pdo['ar']->prepare("SELECT * FROM approver");
    $stmt->execute();
    $approvers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Approver Report</title>
        <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            padding: 2rem;
        }
        .table-section {
            background-color: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        thead th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #4b5563;
        }
        tr:hover {
            background-color: #f1f5f9;
        }
        .action-links a {
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background-color 0.2s;
        }
        .action-links .view-btn { color: #2563eb; }
        .action-links .update-btn { color: #f97316; }
        .action-links .archive-btn { color: #dc2626; }
        .action-links a:hover {
            background-color: #e5e7eb;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-start justify-center min-h-screen">

<div class="table-section max-w-4xl w-full">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-2xl font-bold text-gray-800">Approver Report</h3>
        <button onclick="openAddModal()" class="px-4 py-2 bg-green-500 text-white font-semibold rounded-lg shadow-md hover:bg-green-600 transition-colors">
            + Add Approver
        </button>
    </div>
    <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approver ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approver Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (!empty($approvers)):
                foreach ($approvers as $row): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['approver_id']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['approver_name']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['title']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 action-links space-x-2">
                            <a href="#" class="view-btn"
                                onclick="openViewModal(
                                    '<?php echo htmlspecialchars($row['approver_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['approver_name'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>');
                                    return false;">
                                👁️ View
                            </a>
                            <a href="#" class="update-btn"
                                onclick="openUpdateModal(
                                    '<?php echo htmlspecialchars($row['approver_id'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['approver_name'], ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars($row['title'], ENT_QUOTES); ?>');
                                    return false;">
                                ✏️ Update
                            </a>
                            <a href="#" class="archive-btn"
                                onclick="openArchiveModal('<?php echo htmlspecialchars($row['approver_id'], ENT_QUOTES); ?>'); return false;">
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
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Add New Approver</h2>

        <form method="post" class="space-y-6">
            <div>
                <label for="addApproverName" class="block text-gray-700 font-medium mb-1">Approver Name:</label>
                <input type="text" name="add_approverName" id="addApproverName" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="addTitle" class="block text-gray-700 font-medium mb-1">Title:</label>
                <input type="text" name="add_title" id="addTitle" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
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
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">View Approver Record</h2>
        <div class="space-y-4 text-gray-700">
            <p><strong class="font-semibold text-lg">Approver ID:</strong> <span id="viewApproverID" class="ml-2 font-mono text-gray-600"></span></p>
            <p><strong class="font-semibold text-lg">Approver Name:</strong> <span id="viewApproverName" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Title:</strong> <span id="viewTitle" class="ml-2"></span></p>
        </div>
    </div>
</div>

<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'updateModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md relative" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-900 text-2xl font-bold" onclick="closeModal('updateModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Update Approver Record</h2>

        <form method="post" class="space-y-6">
            <input type="hidden" name="update_approverID" id="updateApproverID">

            <div>
                <label for="updateApproverName" class="block text-gray-700 font-medium mb-1">Approver Name:</label>
                <input type="text" name="update_approverName" id="updateApproverName" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="updateTitle" class="block text-gray-700 font-medium mb-1">Title:</label>
                <input type="text" name="update_title" id="updateTitle" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
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
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Archive Approver Record</h2>
        <p class="mb-6 text-gray-700">Are you sure you want to archive this approver record?</p>
        <form method="post" class="flex justify-end space-x-3">
            <input type="hidden" name="archive_approverID" id="archiveApproverID">
            <button type="button" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition" onclick="closeModal('archiveModal')">Cancel</button>
            <button type="submit" name="archive" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Archive</button>
        </form>
    </div>
</div>

<script>
    // JavaScript functions for handling modals
    function openViewModal(approverID, approverName, title) {
        document.getElementById('viewApproverID').innerText = approverID;
        document.getElementById('viewApproverName').innerText = approverName;
        document.getElementById('viewTitle').innerText = title;
        document.getElementById('viewModal').classList.remove('hidden');
    }

    function openUpdateModal(approverID, approverName, title) {
        document.getElementById('updateApproverID').value = approverID;
        document.getElementById('updateApproverName').value = approverName;
        document.getElementById('updateTitle').value = title;
        document.getElementById('updateModal').classList.remove('hidden');
    }

    function openArchiveModal(approverID) {
        document.getElementById('archiveApproverID').value = approverID;
        document.getElementById('archiveModal').classList.remove('hidden');
    }
    
    // New function for the add modal
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