    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
      <div class="bg-white shadow rounded-xl p-6 border-l-4 border-indigo-600">
        <p class="text-gray-500">Total Active Loans</p>
        <h2 class="text-2xl font-semibold mt-1"><?php echo getTotalActiveLoans($pdo); ?></h2>
      </div>
      <div class="bg-white shadow rounded-xl p-6 border-l-4 border-green-600">
        <p class="text-gray-500">Outstanding Balance</p>
        <h2 class="text-2xl font-semibold mt-1"><?php echo getTotalOutstanding($pdo); ?></h2>
      </div>
      <div class="bg-white shadow rounded-xl p-6 border-l-4 border-red-600">
        <p class="text-gray-500">Next Due</p>
        <h2 class="text-2xl font-semibold mt-1"><?php echo getNextDueDate($pdo); ?></h2>
      </div>
    </div>