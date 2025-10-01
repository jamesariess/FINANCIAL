
<?php 
include "../../crud/collection/reminder.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <?php include "../../static/head/header.php" ?>
</head>
<body>
    <?php include "../sidebar.php"; ?> 
   
        <!-- Confirmation Modal -->
<div id="confirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 shadow-lg w-full max-w-md">
        <div class="flex justify-between items-center border-b pb-3">
            <h3 class="text-lg font-semibold">Confirm Action</h3>
            <button id="closeConfirmationModal" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <div class="mt-4 ">
            <p>Are you sure you want to send this reminder?</p>
        </div>
        <div class="mt-6 flex justify-end gap-4">
            <button id="cancelConfirmation" class="bg-red-300 hover:bg-red-400 px-4 py-2 rounded-lg">Cancel</button>
            <button id="confirmSend" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">Confirm</button>
        </div>
    </div>
</div>
     

     
        
        <?php include __DIR__ . '/../../contents/collection/reminder.php'; ?> 
        <?php include __DIR__ . '/../../modal/collection/remindersmodal.html'; ?>
        
    </div>
</div>

<script src="<?php echo '../../static/js/filter.js'; ?>"></script>
<?php include "../../static/js/modal.php" ?>
<script>
     const data = <?php 
     $data = [
         "totalRequest" => 0,
         "totalAmountRelease" => 0,
         "rejectedRequest" => 0,
         "newRequest" => 0,
     ];

     $sql = "SELECT COUNT(*) as total FROM follow WHERE Archive='NO'";
     $stmt = $pdo->query($sql);
     $data["totalRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

     $sql = "SELECT COUNT(*) as total FROM follow WHERE paymentstatus='Paid' AND Archive='NO'";
     $stmt = $pdo->query($sql);
     $data["totalAmountRelease"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

     $sql = "SELECT COUNT(*) as total FROM follow WHERE paymentstatus='Not Paid' AND Archive='NO'";
     $stmt = $pdo->query($sql);
     $data["rejectedRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

     $sql = "SELECT COUNT(*) as total FROM follow WHERE Remarks='Failed To Sent' AND Archive='NO'";
     $stmt = $pdo->query($sql);
     $data["newRequest"] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

     echo json_encode($data); 
     ?>;
  document.getElementById("totalRequest").textContent = data.totalRequest;
  document.getElementById("totalAmountRelease").textContent = data.totalAmountRelease;
  document.getElementById("rejectedRequest").textContent = data.rejectedRequest;
  document.getElementById("newRequest").textContent = data.newRequest;
</script>

<script>document.addEventListener('DOMContentLoaded', function() {
    // Function to update dashboard cards
    function updateDashboardCards(data) {
        document.getElementById('totalRequest').textContent = data.totalRequest;
        document.getElementById('totalAmountRelease').textContent = data.totalAmountRelease;
        document.getElementById('rejectedRequest').textContent = data.rejectedRequest;
        document.getElementById('newRequest').textContent = data.newRequest;
    }

    // Function to update reminder section
    function updateReminderSection(containerId, reminders, sectionType) {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (reminders.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10 bg-gray-50 rounded-lg">
                    <p class="text-gray-500 font-medium">No ${sectionType} reminders at the moment. 🎉</p>
                </div>`;
            return;
        }

        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">';
        reminders.forEach(row => {
            const remarks = row.Remarks.trim();
            const isSent = remarks.includes('Reminder Sent') || remarks.includes('Emailed Sent');
            const isAutomated = remarks.toLowerCase() === 'automated';
            let actionHtml = '';

            if (sectionType === 'overdue' || sectionType === 'today') {
                if (isSent) {
                    actionHtml = '<p class="text-xs text-gray-500 font-medium">✅ Reminder Already Sent</p>';
                } else if (sectionType === 'today' && isAutomated) {
                    actionHtml = '<p class="mt-2 text-xs text-blue-600 font-medium">📩 Automated reminder scheduled</p>';
                } else {
                    actionHtml = `
                        <button class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg text-sm font-semibold sendReminderBtn"
                                data-id="${row.reminderID}">
                            Send Manually
                        </button>`;
                }
            } else if (sectionType === 'tomorrow') {
                actionHtml = '<p class="mt-2 text-xs text-blue-600 font-medium">📩 Reminder will be sent tomorrow</p>';
            }

            const dateField = sectionType === 'overdue' ? 'due_date' : 'FollowUpDate';
            const label = sectionType === 'overdue' ? 'Due Date' : 'Follow Up Date';
            const statusColor = row.paymentstatus === 'Not Paid' ? 'text-red-500' : 'text-yellow-600';

            html += `
                <div class="bg-${sectionType === 'overdue' ? 'red' : sectionType === 'today' ? 'green' : 'blue'}-50 border border-${sectionType === 'overdue' ? 'red' : sectionType === 'today' ? 'green' : 'blue'}-200 rounded-xl p-5 shadow">
                    <h3 class="font-semibold text-gray-800">Reference #${row.reference_no}</h3>
                    <p class="text-sm text-gray-600">${label}: <span class="font-medium">${new Date(row[dateField]).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}</span></p>
                    <p class="text-sm text-gray-600">Contact: <span class="font-medium">${row.Contactinfo}</span></p>
                    <p class="text-sm text-gray-600">Amount: ₱ <span class="font-medium">${parseFloat(row.amount).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span></p>
                    <p class="text-sm text-gray-600">Status: <span class="font-bold ${statusColor}">${row.paymentstatus}</span></p>
                    <div class="reminder-action mt-3">${actionHtml}</div>
                </div>`;
        });
        html += '</div>';
        container.innerHTML = html;

        // Re-attach event listeners for send buttons
        attachSendReminderListeners();
    }

    // Function to attach event listeners to send reminder buttons
function attachSendReminderListeners() {
    document.querySelectorAll('.sendReminderBtn').forEach(btn => {
        btn.replaceWith(btn.cloneNode(true));
        const newBtn = document.querySelector(`[data-id="${btn.dataset.id}"]`);
        newBtn.addEventListener('click', function() {
            const reminderID = this.dataset.id;
            const button = this;

            // Show confirmation modal
            const confirmationModal = document.getElementById('confirmationModal');
            const closeConfirmationModal = document.getElementById('closeConfirmationModal');
            const cancelConfirmation = document.getElementById('cancelConfirmation');
            const confirmSend = document.getElementById('confirmSend');

            confirmationModal.classList.remove('hidden');

            // Handle confirmation
            confirmSend.addEventListener('click', function handleConfirm() {
                // Remove event listeners to prevent multiple triggers
                confirmSend.removeEventListener('click', handleConfirm);
                closeConfirmationModal.removeEventListener('click', handleClose);
                cancelConfirmation.removeEventListener('click', handleCancel);

                fetch('../../crud/collection/reminder.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'sendReminder', reminderID })
                })
                .then(res => {
                    const contentType = res.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        return res.json();
                    } else {
                        return res.text().then(text => {
                            throw new Error('Server returned non-JSON response: ' + text);
                        });
                    }
                })
                .then(data => {
                    confirmationModal.classList.add('hidden');
                    if (data.success) {
                        const container = button.closest('.reminder-action');
                        container.innerHTML = '<p class="text-xs text-gray-500 font-medium">✅ Reminder Sent</p>';
                        showModal(data.message); // Show success message in modal
                        fetchData(); // Refresh data
                    } else {
                        showModal(data.message); // Show error message in modal
                    }
                })
                .catch(err => {
                    confirmationModal.classList.add('hidden');
                    showModal('AJAX Error: ' + err.message); // Show error in modal
                });

                // Clean up
                confirmationModal.classList.add('hidden');
            });

            // Handle cancel
            function handleCancel() {
                confirmationModal.classList.add('hidden');
                confirmSend.removeEventListener('click', handleConfirm);
                closeConfirmationModal.removeEventListener('click', handleClose);
                cancelConfirmation.removeEventListener('click', handleCancel);
            }

            // Handle close
            function handleClose() {
                confirmationModal.classList.add('hidden');
                confirmSend.removeEventListener('click', handleConfirm);
                closeConfirmationModal.removeEventListener('click', handleClose);
                cancelConfirmation.removeEventListener('click', handleCancel);
            }

            closeConfirmationModal.addEventListener('click', handleClose);
            cancelConfirmation.addEventListener('click', handleCancel);

            // Close modal if clicked outside
            confirmationModal.addEventListener('click', (e) => {
                if (e.target === confirmationModal) handleClose();
            }, { once: true });
        });
    });
}

    // Function to fetch data from the server
  function fetchData() {
            fetch('../../crud/collection/reminder.php?ajax=1')
                .then(res => {
                    const contentType = res.headers.get("content-type");
                    if (res.ok && contentType && contentType.indexOf("application/json") !== -1) {
                        return res.json();
                    }
                    return res.text().then(text => { throw new Error('Server returned non-JSON response: ' + text); });
                })
                .then(data => {
                    if (data.success) {
                        updateDashboardCards(data.data);
                        updateReminderSection('overdueReminders', data.data.overdue, 'overdue');
                        updateReminderSection('todayReminders', data.data.todayReminders, 'today');
                        updateReminderSection('tomorrowReminders', data.data.tomorrowReminders, 'tomorrow');
                    } else {
                        console.error('Error fetching data:', data.message);
                    }
                })
                .catch(err => console.error('Fetch error:', err));
        }

    // Initial data fetch
    fetchData();

    // Poll every 10 seconds
    setInterval(fetchData, 5000);

    // Attach initial listeners
    attachSendReminderListeners();

 

});
</script>
</body>
</html>

