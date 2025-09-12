<div class="container mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between bg-white shadow-md rounded-2xl p-6 border border-gray-200">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-bell text-indigo-500"></i>
                Reminders | Follow Up
            </h2>
            <h3 class="text-gray-600 mt-2">
                New Invoice Need for Reminder:
                <span id="newInvoiceCount" class="text-xl font-bold text-indigo-600 ml-2"><?= $data["request"] ?></span>
            </h3>
        </div>
        <div class="mt-4 md:mt-0">
            <button id="toggleFormBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl shadow-md flex items-center gap-2 transition">
                <i class="fas fa-plus-circle"></i>
                Create a Reminder
            </button>
        </div>
    </div>
</div>

<div class="container mx-auto p-6 space-y-8">

<div class="bg-white rounded-2xl shadow-lg p-6 border border-red-200">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-red-600 flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i>
            Overdue Reminders (Not Paid)
        </h2>
        <a href="#" onclick="openOverdueModal()" class="text-sm text-red-500 hover:underline">View All</a>
    </div>

    <?php if (empty($overdue)): ?>
        <div class="text-center py-10 bg-gray-50 rounded-lg">
            <p class="text-gray-500 font-medium">No overdue reminders at the moment. 🎉</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($overdue as $row): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-5 shadow">
                    <h3 class="font-semibold text-gray-800">Reference #<?= $row['reference_no'] ?></h3>
                    <p class="text-sm text-gray-600">Due Date: <span class="font-medium"><?= date("F j, Y", strtotime($row['due_date'])) ?></span></p>
                    <p class="text-sm text-gray-600">Contact: <span class="font-medium"><?= $row['Contactinfo'] ?></span></p>
                    <p class="text-sm text-gray-600">Amount: ₱ <span class="font-medium"><?= number_format($row['amount'], 2) ?></span></p>
                    <p class="text-sm text-gray-600">Status: <span class="text-red-500 font-bold"><?= $row['paymentstatus'] ?></span></p>

                    <div class="reminder-action mt-3">
                        <?php
                        $remarks = trim($row['Remarks']);
                        if (stripos($remarks, 'Reminder Sent') !== false || stripos($remarks, 'Emailed Sent') !== false): ?>
                            <p class="text-xs text-gray-500 font-medium">✅ Reminder Already Sent</p>
                        <?php else: ?>
                            <button class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg text-sm font-semibold sendReminderBtn"
                                data-id="<?= $row['reminderID'] ?>">
                                Send Manually
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

---

<div class="bg-white rounded-2xl shadow-lg p-6 border border-green-200">
    <h2 class="text-xl font-bold text-green-600 flex items-center gap-2 mb-4">
        <i class="fas fa-calendar-day"></i>
        Reminders for Today
    </h2>

    <?php if (empty($todayReminders)): ?>
        <div class="text-center py-10 bg-gray-50 rounded-lg">
            <p class="text-gray-500 font-medium">No reminders scheduled for today. 👍</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($todayReminders as $row): ?>
                <div class="bg-green-50 border border-green-200 rounded-xl p-5 shadow">
                    <h3 class="font-semibold text-gray-800">Reference #<?= $row['reference_no'] ?></h3>
                    <p class="text-sm text-gray-600">Follow Up Date: <span class="font-medium"><?= date("F j, Y", strtotime($row['FollowUpDate'])) ?></span></p>
                    <p class="text-sm text-gray-600">Contact: <span class="font-medium"><?= $row['Contactinfo'] ?></span></p>
                    <p class="text-sm text-gray-600">Amount: ₱ <span class="font-medium"><?= number_format($row['amount'], 2) ?></span></p>
                    <p class="text-sm text-gray-600">Status: <span class="text-yellow-600 font-bold"><?= $row['paymentstatus'] ?></span></p>

                    <?php
                    $remarks = trim($row['Remarks']);
                    if (strcasecmp($remarks, 'Automated') === 0): ?>
                        <p class="mt-2 text-xs text-blue-600 font-medium">📩 Automated reminder scheduled</p>
                    <?php elseif (stripos($remarks, 'Reminder Sent') !== false || stripos($remarks, 'Emailed Sent') !== false): ?>
                        <p class="text-xs text-gray-500 font-medium">✅ Reminder Already Sent</p>
                    <?php else: ?>
                        <button class="mt-3 w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg text-sm font-semibold sendReminderBtn"
                                data-id="<?= $row['reminderID'] ?>">
                            Send Manually
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

---

<div class="bg-white rounded-2xl shadow-lg p-6 border border-blue-200">
    <h2 class="text-xl font-bold text-blue-600 flex items-center gap-2 mb-4">
        <i class="fas fa-calendar-plus"></i>
        Reminders for Tomorrow
    </h2>

    <?php if (empty($tomorrowReminders)): ?>
        <div class="text-center py-10 bg-gray-50 rounded-lg">
            <p class="text-gray-500 font-medium">No reminders scheduled for tomorrow. 😊</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($tomorrowReminders as $row): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow">
                    <h3 class="font-semibold text-gray-800">Reference #<?= $row['reference_no'] ?></h3>
                    <p class="text-sm text-gray-600">Follow Up Date: <span class="font-medium"><?= date("F j, Y", strtotime($row['FollowUpDate'])) ?></span></p>
                    <p class="text-sm text-gray-600">Contact: <span class="font-medium"><?= $row['Contactinfo'] ?></span></p>
                    <p class="text-sm text-gray-600">Amount: ₱ <span class="font-medium"><?= number_format($row['amount'], 2) ?></span></p>
                    <p class="text-sm text-gray-600">Status: <span class="text-gray-500 font-bold"><?= $row['paymentstatus'] ?></span></p>
                    <p class="mt-2 text-xs text-blue-600 font-medium">📩 Reminder will be sent tomorrow</p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
    </div>
</div>

<script>
document.querySelectorAll('.sendReminderBtn').forEach(btn => {
    btn.addEventListener('click', function() {
        const reminderID = this.dataset.id;
        const button = this;

        if (!confirm("Are you sure you want to send this reminder?")) return;

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'sendReminder', reminderID })
        })
        .then(res => {
            // Check for non-JSON response from server
            const contentType = res.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return res.json();
            } else {
                // If it's not JSON, assume a server-side error message
                return res.text().then(text => {
                    throw new Error('Server returned non-JSON response: ' + text);
                });
            }
        })
        .then(data => {
            if (data.success) {
                const container = button.parentElement;
                container.innerHTML = '<p class="text-xs text-gray-500 font-medium">✅ Reminder Sent</p>';

                const countEl = document.getElementById('newInvoiceCount');
                if (countEl && !isNaN(countEl.textContent)) {
                    countEl.textContent = parseInt(countEl.textContent) - 1;
                }
                console.log("Email result:", data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => alert('AJAX Error: ' + err));
    });
});
</script>