
    

 <div class="container mx-auto">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between bg-white shadow-md rounded-2xl p-6 border border-gray-200">
        <div>
  <h1 class="text-3xl font-bold text-gray-900 mb-6">Loan Billing & Payments</h1> 
           
        </div>
        <div class="mt-4 md:mt-0">
            <button id="toggleFormBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl shadow-md flex items-center gap-2 transition">
                <i class="fas fa-plus-circle"></i>
                Request Loan
            </button>
        </div>
    </div>
</div>
   <div class="table-section" id="invoiceTableSection">
  <table id="employeesTable">
    <thead>
      <tr>
        <th class="px-6 py-3">Loan ID</th>
        <th class="px-6 py-3">Lender</th>
        <th class="px-6 py-3">Loan Type</th>
        <th class="px-6 py-3">Principal</th>
        <th class="px-6 py-3">Outstanding</th>
        <th class="px-6 py-3">Interest Rate</th>
        <th class="px-6 py-3">Monthly Due</th>
        <th class="px-6 py-3">Due Date</th>
        <th class="px-6 py-3">Status</th>
        <th class="px-6 py-3 text-center">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y text-sm">
      <?php 
      $loans = getActiveLoans($pdo);
      if (empty($loans)) {
          echo '<tr><td colspan="10" class="px-6 py-4 text-center text-gray-500">No active loans available.</td></tr>';
      } else {
          foreach ($loans as $loan): ?>
          <tr>
            <td class="px-6 py-4 font-semibold"><?php echo htmlspecialchars($loan['id']); ?></td>
            <td class="px-6 py-4"><?php echo htmlspecialchars($loan['lender']); ?></td>
            <td class="px-6 py-4"><?php echo htmlspecialchars($loan['title']); ?></td>
            <td class="px-6 py-4"><?php echo $loan['principal']; ?></td>
            <td class="px-6 py-4"><?php echo $loan['outstanding']; ?></td>
            <td class="px-6 py-4"><?php echo $loan['rate']; ?></td>
            <td class="px-6 py-4"><?php echo $loan['monthlyDue']; ?></td>
            <td class="px-6 py-4"><?php echo $loan['dueDate']; ?></td>
            <td class="px-6 py-4">
              <?php
              $statusClass = '';
              $statusText = $loan['status'];
              switch ($statusText) {
                  case 'Overdue': $statusClass = 'bg-red-100 text-red-700'; break;
                  case 'Pending': $statusClass = 'bg-yellow-100 text-yellow-700'; break;
                  case 'Partially Paid': $statusClass = 'bg-blue-100 text-blue-700'; break;
                  case 'Paid': $statusClass = 'bg-green-100 text-green-700'; break;
              }
              ?>
              <span class="px-2 py-1 text-xs rounded <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
            </td>
            <td class="px-6 py-4 text-center">
              <button onclick="openPaymentForm('<?php echo $loan['rawId']; ?>', <?php echo str_replace(['₱', ','], '', $loan['outstanding']); ?>)" 
                      class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                Manage
              </button>
            </td>
          </tr>
          <?php endforeach;
      } ?>
    </tbody>
  </table>
</div>
  </div>

  <!-- Payment Modal -->
  <div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-2xl p-6">
      <h2 class="text-xl font-bold mb-4">Loan Management</h2>
      
      <!-- Tabs -->
      <div class="flex border-b mb-4">
        <button id="tabPayment" onclick="switchTab('payment')" class="px-4 py-2 font-medium border-b-2 border-indigo-600 text-indigo-600">Make Payment</button>
        <button id="tabHistory" onclick="switchTab('history')" class="px-4 py-2 font-medium text-gray-600 hover:text-indigo-600">Billing History</button>
      </div>

      <!-- Payment Form -->
      <form id="paymentForm" class="space-y-4" data-tab="payment">
        <input type="hidden" id="loanId" name="loanId">
        <input type="hidden" name="action" value="submit_payment">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm text-gray-600 mb-1">Loan ID</label>
            <input type="text" id="loanIdDisplay" class="w-full border rounded p-2 bg-gray-100" readonly>
          </div>
          <div>
            <label class="block text-sm text-gray-600 mb-1">Outstanding Balance</label>
            <input type="text" id="balanceDisplay" class="w-full border rounded p-2 bg-gray-100" readonly>
          </div>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Payment Amount</label>
          <input type="number" id="amount" name="amount" step="0.01" class="w-full border rounded p-2" required>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Payment Method</label>
          <select id="method" name="method" class="w-full border rounded p-2" required>
            <option value="">-- Select Method --</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Check">Check</option>
            <option value="Cash">Cash</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1">Remarks</label>
          <textarea id="remarks" name="remarks" rows="2" class="w-full border rounded p-2"></textarea>
        </div>
        <div class="flex justify-end gap-3">
          <button type="button" onclick="closePaymentForm()" class="px-4 py-2 border rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Submit</button>
        </div>
      </form>

      <!-- Billing History -->
      <div id="billingHistory" class="hidden" data-tab="history">
        <table class="w-full text-sm border-collapse">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2">Date</th>
              <th class="px-4 py-2">Amount</th>
              <th class="px-4 py-2">Method</th>
              <th class="px-4 py-2">Remarks</th>
              <th class="px-4 py-2">Status</th>
            </tr>
          </thead>
          <tbody id="historyBody">
            <!-- Populated via JS -->
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    const modal = document.getElementById('paymentModal');
    const form = document.getElementById('paymentForm');
    const tabPayment = document.getElementById('tabPayment');
    const tabHistory = document.getElementById('tabHistory');
    const billingHistory = document.getElementById('billingHistory');
    const historyBody = document.getElementById('historyBody');

    function openPaymentForm(loanId, balance) {
      document.getElementById('loanId').value = loanId.replace('LN-', ''); // Strip prefix for DB ID
      document.getElementById('loanIdDisplay').value = loanId;
      document.getElementById('balanceDisplay').value = "₱" + balance.toLocaleString();
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      switchTab('payment');
    }

    function closePaymentForm() {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
      form.reset();
    }

    function switchTab(tab) {
      if (tab === 'payment') {
        form.classList.remove('hidden');
        billingHistory.classList.add('hidden');
        tabPayment.classList.add('border-b-2', 'border-indigo-600', 'text-indigo-600');
        tabHistory.classList.remove('border-b-2', 'border-indigo-600', 'text-indigo-600');
      } else {
        form.classList.add('hidden');
        billingHistory.classList.remove('hidden');
        tabHistory.classList.add('border-b-2', 'border-indigo-600', 'text-indigo-600');
        tabPayment.classList.remove('border-b-2', 'border-indigo-600', 'text-indigo-600');
        loadHistory();
      }
    }

    async function loadHistory() {
      const loanId = document.getElementById('loanId').value;
      const response = await fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=get_history&loanId=${loanId}`
      });
      const history = await response.json();
      historyBody.innerHTML = '';
      history.forEach(entry => {
        const row = `
          <tr class="border-t">
            <td class="px-4 py-2">${entry.payment_date}</td>
            <td class="px-4 py-2">₱${parseFloat(entry.amount).toLocaleString()}</td>
            <td class="px-4 py-2">${entry.method}</td>
            <td class="px-4 py-2">${entry.remarks || ''}</td>
            <td class="px-4 py-2"><span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Completed</span></td>
          </tr>
        `;
        historyBody.insertAdjacentHTML('beforeend', row);
      });
    }

    form.addEventListener('submit', async function(e) {
      e.preventDefault();
      const formData = new FormData(form);
      const response = await fetch('', {
        method: 'POST',
        body: formData
      });
      const result = await response.json();
      if (result.success) {
        alert(result.message);
        closePaymentForm();
        location.reload();
      } else {
        alert('Error: ' + result.message);
      }
    });
  </script>
