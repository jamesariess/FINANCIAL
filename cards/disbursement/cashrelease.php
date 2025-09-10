<div class="container mx-auto p-4">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

    <!-- Total Request -->
    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col border-l-4 border-purple-500">
      <div class="flex items-center mb-3">
        <i class="fas fa-file-alt text-purple-600 text-xl mr-2"></i>
        <h3 class="text-base font-semibold text-gray-800">Total Request</h3>
      </div>
      <div class="flex items-end justify-between">
        <div id="totalRequest" class="text-3xl font-bold text-purple-600">0</div>
        <span class="text-gray-500 text-sm">Requests</span>
      </div>
    </div>

    <!-- Total Amount Release -->
    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col border-l-4 border-blue-500">
      <div class="flex items-center mb-3">
        <i class="fas fa-wallet text-teal-600 text-xl mr-2"></i>
        <h3 class="text-base font-semibold text-gray-800">Total Release</h3>
      </div>
      <div class="flex items-end justify-between">
        <div id="totalAmountRelease" class="text-3xl font-bold text-teal-600">₱0</div>
        <span class="text-gray-500 text-sm">Released</span>
      </div>
    </div>

    <!-- Rejected Request -->
    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col border-l-4 border-red-500">
      <div class="flex items-center mb-3">
        <i class="fas fa-times-circle text-red-600 text-xl mr-2"></i>
        <h3 class="text-base font-semibold text-gray-800">Rejected</h3>
      </div>
      <div class="flex items-end justify-between">
        <div id="rejectedRequest" class="text-3xl font-bold text-red-600">0</div>
        <span class="text-gray-500 text-sm">Requests</span>
      </div>
    </div>

    <!-- New Request -->
    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col border-l-4 border-indigo-500">
      <div class="flex items-center mb-3">
        <i class="fas fa-plus-circle text-green-600 text-xl mr-2"></i>
        <h3 class="text-base font-semibold text-gray-800">New Request</h3>
      </div>
      <div class="flex items-end justify-between">
        <div id="newRequest" class="text-3xl font-bold text-green-600">0</div>
        <span class="text-gray-500 text-sm">Pending</span>
      </div>
    </div>

  </div>
</div>

<script>
  // Use PHP variables directly
  const data = <?php echo json_encode($data); ?>;

  document.getElementById("totalRequest").textContent = data.totalRequest;
  document.getElementById("totalAmountRelease").textContent = "₱" + Number(data.totalAmountRelease).toLocaleString();
  document.getElementById("rejectedRequest").textContent = data.rejectedRequest;
  document.getElementById("newRequest").textContent = data.newRequest;
</script>
