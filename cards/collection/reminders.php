<div class="container mx-auto p-4">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

    <!-- Total Reminders -->
    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col border-l-4 border-purple-500">
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
    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col border-l-4 border-green-500">
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
    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col border-l-4 border-red-500">
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
    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col border-l-4 border-yellow-500">
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

<script>
  const data = <?php echo json_encode($data); ?>;
  document.getElementById("totalRequest").textContent = data.totalRequest;
  document.getElementById("totalAmountRelease").textContent = data.totalAmountRelease;
  document.getElementById("rejectedRequest").textContent = data.rejectedRequest;
  document.getElementById("newRequest").textContent = data.newRequest;
</script>
