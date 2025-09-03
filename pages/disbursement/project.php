<?php
session_start();

// --- Database Connection ---
  include_once __DIR__ . '/../../utility/connection.php';

// --- Handle Form Submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $projectID = $_POST['update_projectID'];
        $projectName = $_POST['update_projectName'];
        $budgetCode = $_POST['update_budgetCode'];
        $department = $_POST['update_department'];

        $stmt = $pdo['ar']->prepare("UPDATE project SET project_name = :project_name, budget_code = :budget_code, department = :department WHERE project_id = :project_id");
        $stmt->bindParam(':project_name', $projectName);
        $stmt->bindParam(':budget_code', $budgetCode);
        $stmt->bindParam(':department', $department);
        $stmt->bindParam(':project_id', $projectID);

        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Record updated successfully.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error updating record.'];
        }
    } elseif (isset($_POST['delete'])) {
        $projectID = $_POST['delete_projectID'];

        $stmt = $pdo['ar']->prepare("DELETE FROM project WHERE project_id = :project_id");
        $stmt->bindParam(':project_id', $projectID);

        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Record deleted successfully.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error deleting record.'];
        }
    } elseif (isset($_POST['add'])) {
        $projectName = $_POST['add_projectName'];
        $budgetCode = $_POST['add_budgetCode'];
        $department = $_POST['add_department'];

        $stmt = $pdo['ar']->prepare("INSERT INTO project (project_name, budget_code, department) VALUES (:project_name, :budget_code, :department)");
        $stmt->bindParam(':project_name', $projectName);
        $stmt->bindParam(':budget_code', $budgetCode);
        $stmt->bindParam(':department', $department);

        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'New record added successfully.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Error adding new record.'];
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- Fetch Data from Database using PDO ---
try {
    $stmt = $pdo['ar']->prepare("SELECT * FROM project");
    $stmt->execute();
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database operation failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Report</title>
    <script src="https://cdn.tailwindcss.com"></script>
 <link rel="stylesheet" href="/static/css/sidebar.css">
</head>
<body class="bg-gray-100 min-h-screen p-8">

<div class="max-w-6xl mx-auto">
    <?php
    if (isset($_SESSION['message'])) {
        $message_type = $_SESSION['message']['type'];
        $message_text = $_SESSION['message']['text'];
        $color_class = ($message_type === 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
        $icon = ($message_type === 'success') ? '✔️' : '❌';
        echo "<div id='status-alert' class='{$color_class} px-6 py-4 rounded-lg mb-6 flex justify-between items-center transition-opacity duration-300 ease-in-out opacity-100'>
                <p class='font-medium'>{$icon} {$message_text}</p>
                <button onclick=\"document.getElementById('status-alert').style.display='none';\" class='text-lg font-bold'>&times;</button>
              </div>";
        unset($_SESSION['message']);
    }
    ?>

    <div class="table-section">
        <div class="flex justify-between items-center mb-8">
            <h3 class="text-3xl font-bold text-gray-800">Project Report</h3>
            <button onclick="openAddModal()" class="px-6 py-2 bg-green-600 text-white font-semibold rounded-full shadow-lg hover:bg-green-700 transition-colors">
                + Add Project
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($projects)):
                        foreach ($projects as $row): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['project_id']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['project_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['budget_code']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['department']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 action-links space-x-4">
                                    <a href="#" class="view-btn font-medium"
                                        onclick="openViewModal(
                                            '<?php echo htmlspecialchars($row['project_id'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($row['project_name'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($row['budget_code'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($row['department'], ENT_QUOTES); ?>');
                                            return false;">
                                        👁️ View
                                    </a>
                                    <a href="#" class="update-btn font-medium"
                                        onclick="openUpdateModal(
                                            '<?php echo htmlspecialchars($row['project_id'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($row['project_name'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($row['budget_code'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($row['department'], ENT_QUOTES); ?>');
                                            return false;">
                                        ✏️ Update
                                    </a>
                                    <a href="#" class="delete-btn font-medium"
                                        onclick="openDeleteModal('<?php echo htmlspecialchars($row['project_id'], ENT_QUOTES); ?>'); return false;">
                                        🗑️ Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center p-6 text-gray-500">No records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center transition-opacity" onclick="outsideClick(event, 'addModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md relative transform scale-100 transition-transform" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold" onclick="closeModal('addModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Add New Project</h2>
        <form method="post" class="space-y-4">
            <div>
                <label for="addProjectName" class="block text-gray-700 font-medium mb-1">Project Name:</label>
                <input type="text" name="add_projectName" id="addProjectName" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="addBudgetCode" class="block text-gray-700 font-medium mb-1">Budget Code:</label>
                <input type="text" name="add_budgetCode" id="addBudgetCode" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="addDepartment" class="block text-gray-700 font-medium mb-1">Department:</label>
                <input type="text" name="add_department" id="addDepartment" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition" onclick="closeModal('addModal')">Cancel</button>
                <button type="submit" name="add" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Add</button>
            </div>
        </form>
    </div>
</div>

<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center transition-opacity" onclick="outsideClick(event, 'viewModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md relative transform scale-100 transition-transform" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold" onclick="closeModal('viewModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">View Project Record</h2>
        <div class="space-y-4 text-gray-700">
            <p><strong class="font-semibold text-lg">Project ID:</strong> <span id="viewProjectID" class="ml-2 font-mono text-gray-600"></span></p>
            <p><strong class="font-semibold text-lg">Project Name:</strong> <span id="viewProjectName" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Budget Code:</strong> <span id="viewBudgetCode" class="ml-2"></span></p>
            <p><strong class="font-semibold text-lg">Department:</strong> <span id="viewDepartment" class="ml-2"></span></p>
        </div>
    </div>
</div>

<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center transition-opacity" onclick="outsideClick(event, 'updateModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-md relative transform scale-100 transition-transform" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold" onclick="closeModal('updateModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Update Project Record</h2>
        <form method="post" class="space-y-4">
            <input type="hidden" name="update_projectID" id="updateProjectID">
            <div>
                <label for="updateProjectName" class="block text-gray-700 font-medium mb-1">Project Name:</label>
                <input type="text" name="update_projectName" id="updateProjectName" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="updateBudgetCode" class="block text-gray-700 font-medium mb-1">Budget Code:</label>
                <input type="text" name="update_budgetCode" id="updateBudgetCode" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="updateDepartment" class="block text-gray-700 font-medium mb-1">Department:</label>
                <input type="text" name="update_department" id="updateDepartment" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition" onclick="closeModal('updateModal')">Cancel</button>
                <button type="submit" name="update" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center transition-opacity" onclick="outsideClick(event, 'deleteModal')">
    <div class="bg-white p-8 rounded-xl shadow-2xl w-full max-w-sm relative transform scale-100 transition-transform" onclick="event.stopPropagation()">
        <button class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-2xl font-bold" onclick="closeModal('deleteModal')">&times;</button>
        <h2 class="text-3xl font-extrabold mb-6 text-gray-800">Delete Project Record</h2>
        <p class="mb-6 text-gray-700">Are you sure you want to permanently **delete** this project record?</p>
        <form method="post" class="flex justify-end space-x-3">
            <input type="hidden" name="delete_projectID" id="deleteProjectID">
            <button type="button" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition" onclick="closeModal('deleteModal')">Cancel</button>
            <button type="submit" name="delete" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Delete</button>
        </form>
    </div>
</div>

<script>
    function openViewModal(projectID, projectName, budgetCode, department) {
        document.getElementById('viewProjectID').innerText = projectID;
        document.getElementById('viewProjectName').innerText = projectName;
        document.getElementById('viewBudgetCode').innerText = budgetCode;
        document.getElementById('viewDepartment').innerText = department;
        document.getElementById('viewModal').classList.remove('hidden');
    }
    function openUpdateModal(projectID, projectName, budgetCode, department) {
        document.getElementById('updateProjectID').value = projectID;
        document.getElementById('updateProjectName').value = projectName;
        document.getElementById('updateBudgetCode').value = budgetCode;
        document.getElementById('updateDepartment').value = department;
        document.getElementById('updateModal').classList.remove('hidden');
    }
    function openDeleteModal(projectID) {
        document.getElementById('deleteProjectID').value = projectID;
        document.getElementById('deleteModal').classList.remove('hidden');
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