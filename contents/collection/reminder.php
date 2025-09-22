
<div class="container pb-4">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

    <!-- Total Reminders -->
    <div class="quick-stat-card purple border-b-2 border-opacity-50">
      <div class="flex items-center mb-3">
        <i class="fas fa-bell text-purple-600 text-xl mr-2"></i>
        <h3 class="text-base font-semibold text-gray-800">Total Reminders</h3>
      </div>
      <div class="flex items-end justify-between">
        <div id="totalRequest" class="text-3xl font-bold text-purple-600">0</div>
        <span class="text-gray-500 text-sm">All</span>
      </div>
    </div>

    <!-- Paid Reminders -->
    <div class="quick-stat-card green border-b-2 border-opacity-50">
      <div class="flex items-center mb-3">
        <i class="fas fa-check-circle text-green-600 text-xl mr-2"></i>
        <h3 class="text-base font-semibold text-gray-800">Paid</h3>
      </div>
      <div class="flex items-end justify-between">
        <div id="totalAmountRelease" class="text-3xl font-bold text-green-600">0</div>
        <span class="text-gray-500 text-sm">Reminders</span>
      </div>
    </div>

    <!-- Unpaid Reminders -->
    <div class="quick-stat-card red border-b-2 border-opacity-50">
      <div class="flex items-center mb-3">
        <i class="fas fa-times-circle text-red-600 text-xl mr-2"></i>
        <h3 class="text-base font-semibold text-gray-800">Unpaid</h3>
      </div>
      <div class="flex items-end justify-between">
        <div id="rejectedRequest" class="text-3xl font-bold text-red-600">0</div>
        <span class="text-gray-500 text-sm">Reminders</span>
      </div>
    </div>

    <!-- Failed Reminders -->
    <div class="quick-stat-card yellow border-b-2 border-opacity-50">
      <div class="flex items-center mb-3">
        <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-2"></i>
        <h3 class="text-base font-semibold text-gray-800">Failed</h3>
      </div>
      <div class="flex items-end justify-between">
        <div id="newRequest" class="text-3xl font-bold text-yellow-600">0</div>
        <span class="text-gray-500 text-sm">Reminders</span>
      </div>
    </div>

  </div>
</div>


<?php if (!empty($successMessage)): ?>
    <div id="successAlert" class="mt-4 w-full bg-green-100 border-l-4 border-green-500 p-4 rounded-md shadow-md flex justify-between items-center mb-4">
      <p class="text-green-600 font-medium text-lg px-4"><?php echo htmlspecialchars($successMessage); ?></p>
      <button onclick="document.getElementById('successAlert').style.display='none';" class="text-green-600 hover:text-green-800 text-xl p-2">&times;</button>
    </div>
    <script>
      setTimeout(() => document.getElementById('successAlert').style.display = 'none', 5000);
    </script>
  <?php endif; ?>

  <?php if (!empty($errorMessage)): ?>
    <div id="errorAlert" class="mt-4 w-full bg-red-100 border-l-4 border-red-500 p-4 rounded-md shadow-md flex justify-between items-center mb-4">
      <p class="text-red-600 font-medium text-lg px-4"><?php echo htmlspecialchars($errorMessage); ?></p>
      <button onclick="document.getElementById('errorAlert').style.display='none';" class="text-red-600 hover:text-red-800 text-xl p-2">&times;</button>
    </div>
    <script>
      setTimeout(() => document.getElementById('errorAlert').style.display = 'none', 5000);
    </script>
  <?php endif; ?>
  

<div>
    <div class="card flex flex-col md:flex-row md:items-center md:justify-between  shadow-md rounded-2xl p-6 border border-gray-200">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <i class="fas fa-bell text-indigo-500"></i>
                Reminders | Follow Up
            </h2>
          
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

<div class="card rounded-2xl shadow-lg p-6 border border-red-200">
    <div class=" flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold text-red-600 flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i>
            Overdue Reminders (Not Paid)
        </h2>
        <a href="#" onclick="openOverdueModal()" class="text-sm text-red-500 hover:underline">View All</a>
    </div>

    <div id="overdueReminders card">
    <?php if (empty($overdue)): ?>
        <div class="text-center py-10 bg-gray-50 rounded-lg card ">
            <p class="text-gray-500 font-medium">No overdue reminders at the moment. 🎉</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6  ">
            <?php foreach ($overdue as $row): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-5 shadow card ">
                    <h3 class="font-semibold ">Reference #<?= $row['reference_no'] ?></h3>
                    <p class="text-sm ">Due Date: <span class="font-medium"><?= date("F j, Y", strtotime($row['due_date'])) ?></span></p>
                    <p class="text-sm ">Contact: <span class="font-medium"><?= $row['Contactinfo'] ?></span></p>
                    <p class="text-sm ">Amount: ₱ <span class="font-medium"><?= number_format($row['amount'], 2) ?></span></p>
                    <p class="text-sm ">Status: <span class="text-red-500 font-bold"><?= $row['paymentstatus'] ?></span></p>

                    <div class="reminder-action mt-3 ">
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
</div>


<div class="card rounded-2xl shadow-lg p-6 border border-green-200">
    <h2 class="text-xl font-bold text-green-600 flex items-center gap-2 mb-4">
        <i class="fas fa-calendar-day"></i>
        Reminders for Today
    </h2>

    <div id="todayReminders card">
    <?php if (empty($todayReminders)): ?>
        <div class="text-center py-10 bg-gray-50 rounded-lg card">
            <p class="text-gray-500 font-medium">No reminders scheduled for today. 👍</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 card">
            <?php foreach ($todayReminders as $row): ?>
                <div class="bg-green-50 border border-green-600 rounded-xl p-5 shadow card">
                    <h3 class="font-semibold">Reference #<?= $row['reference_no'] ?></h3>
                    <p class="text-sm ">Follow Up Date: <span class="font-medium"><?= date("F j, Y", strtotime($row['FollowUpDate'])) ?></span></p>
                    <p class="text-sm ">Contact: <span class="font-medium"><?= $row['Contactinfo'] ?></span></p>
                    <p class="text-sm ">Amount: ₱ <span class="font-medium"><?= number_format($row['amount'], 2) ?></span></p>
                    <p class="text-sm ">Status: <span class="text-yellow-600 font-bold"><?= $row['paymentstatus'] ?></span></p>

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
</div>

<div class="card rounded-2xl shadow-lg p-6 border border-blue-200">
    <h2 class="text-xl font-bold text-blue-600 flex items-center gap-2 mb-4">
        <i class="fas fa-calendar-plus"></i>
        Reminders for Tomorrow
    </h2>

    <div id="tomorrowReminders card">
    <?php if (empty($tomorrowReminders)): ?>
        <div class="text-center py-10 bg-gray-50 rounded-lg card">
            <p class="text-gray-500 font-medium">No reminders scheduled for tomorrow. 😊</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 ">
            <?php foreach ($tomorrowReminders as $row): ?>
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 shadow card">
                    <h3 class="font-semibold ">Reference #<?= $row['reference_no'] ?></h3>
                    <p class="text-sm ">Follow Up Date: <span class="font-medium"><?= date("F j, Y", strtotime($row['FollowUpDate'])) ?></span></p>
                    <p class="text-sm ">Contact: <span class="font-medium"><?= $row['Contactinfo'] ?></span></p>
                    <p class="text-sm ">Amount: ₱ <span class="font-medium"><?= number_format($row['amount'], 2) ?></span></p>
                    <p class="text-sm ">Status: <span class="text-gray-500 font-bold"><?= $row['paymentstatus'] ?></span></p>
                    <p class="mt-2 text-xs text-blue-600 font-medium">📩 Reminder will be sent tomorrow</p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </div>
</div>
    </div>
</div>
