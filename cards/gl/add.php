<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
  <!-- Bank Balance -->
  <div class="bg-white rounded-2xl shadow p-6 border-t-4 border-purple-500">
    <p class="text-gray-500 text-sm">Bank Balance</p>
    <h2 class="text-3xl font-bold mt-2 text-gray-800">₱<?php echo number_format(getBankBalance($pdo), 2); ?></h2>
    <div class="flex gap-3 mt-4">
      <button type="button" class="open-deposit px-4 py-2 bg-purple-500 text-white rounded-lg" data-type="Bank">Deposit</button>
      <button type="button" id="open-withdraw" class="px-4 py-2 bg-gray-200 rounded-lg">Withdraw</button>
    </div>
  </div>

  <!-- Owner Deposit -->
  <div class="bg-white rounded-2xl shadow p-6 border-t-4 border-green-500">
    <p class="text-gray-500 text-sm">Owner Deposit</p>
    <h2 class="text-3xl font-bold mt-2 text-gray-800">₱<?php echo number_format(getOwnerDeposit($pdo), 2); ?></h2>
    <div class="flex gap-3 mt-4">
      <button type="button" class="open-deposit px-4 py-2 bg-green-500 text-white rounded-lg" data-type="Owner">Add Capital</button>
    </div>
  </div>

  <!-- Cash On Hand -->
  <div class="bg-white rounded-2xl shadow p-6 border-t-4 border-red-500">
    <p class="text-gray-500 text-sm">Cash On Hand</p>
    <h2 class="text-3xl font-bold mt-2 text-gray-800">₱<?php echo number_format(getCashOnHand($pdo), 2); ?></h2>
    <div class="mt-3">
      <button type="button" class="open-deposit px-4 py-2 bg-red-500 text-white rounded-lg" data-type="Petty Cash">Add Capital</button>
    </div>
  </div>
</div>

