<div class="table-section" id="invoiceTableSection">
    <h3>Custumer Report</h3>
    <table id="employeesTable">
        <thead>
            <tr>
    <th>Invoice ID</th>
    <th>Custumer ID</th>
    <th>Invoice Date</th>
    <th>Due Date</th>
    <th>Description</th>
    <th>Amount</th>
    <th>Reference</th>
    <th>Created AT</th>
    <th>Status</th>
    <th>Action</th>
            </tr>
        </thead>
        <tbody id="employeesTableBody">
            <?php if(!empty($invoiceReports)):
            foreach($invoiceReports as $row): ?>
            <tr>
    <td><?php echo htmlspecialchars($row['invoice_id']);?></td>
    <td><?php echo htmlspecialchars($row['customer_id']);?></td>
    <td><?php echo htmlspecialchars($row['invoice_date']);?></td>
    <td><?php echo htmlspecialchars($row['due_date']);?></td>
    <td><?php echo htmlspecialchars($row['description']);?></td>
    <td><?php echo htmlspecialchars($row['amount']);?></td>
    <td><?php echo htmlspecialchars($row['reference_no']);?></td>
    <td><?php echo htmlspecialchars($row['created_at']);?></td>
    <td><?php echo htmlspecialchars($row['stat']);?></td>
     <td>
<a href=""
    onclick="openViewModal(
        '<?php echo $row['invoice_id'];?>',
        '<?php echo htmlspecialchars($row['customer_id'],ENT_QUOTES);?>',
        '<?php echo htmlspecialchars($row['invoice_date'],ENT_QUOTES);?>',
        '<?php echo htmlspecialchars($row['due_date'],ENT_QUOTES);?>',
        '<?php echo htmlspecialchars($row['description'],ENT_QUOTES);?>',
        '<?php echo htmlspecialchars($row['amount'],ENT_QUOTES);?>',
        '<?php echo htmlspecialchars($row['reference_no'],ENT_QUOTES);?>',
        '<?php echo htmlspecialchars($row['created_at'],ENT_QUOTES);?>',
        '<?php echo htmlspecialchars($row['stat'],ENT_QUOTES);?>'
    ); return false;"> 👁️ View</a>
        <a href=""
        onclick="openUpdateModal(
            '<?php echo $row['invoice_id'];?>',
            '<?php echo htmlspecialchars($row['customer_id'],ENT_QUOTES);?>',
            '<?php echo htmlspecialchars($row['invoice_date'],ENT_QUOTES);?>',
            '<?php echo htmlspecialchars($row['due_date'],ENT_QUOTES);?>',
            '<?php echo htmlspecialchars($row['description'],ENT_QUOTES);?>',
            '<?php echo htmlspecialchars($row['amount'],ENT_QUOTES);?>',
            '<?php echo htmlspecialchars($row['reference_no'],ENT_QUOTES);?>',
            '<?php echo htmlspecialchars($row['stat'],ENT_QUOTES);?>',
            '<?php echo htmlspecialchars($row['created_at'],ENT_QUOTES);?>');  
        return false;"> ✏️ Update</a>
        <a href=""
        onclick="openArchiveModal('<?php echo $row['invoice_id']?>'); return false;"> 🗄️ Archive</a>
     </td>
         
            </tr>
            <?php endforeach; else:?>
            <tr><Td colspan="8" class="text-center p-4">NO Records Found.</Td></tr>
            <?php endif;?>
        </tbody>
    </table>
</div>

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'viewModal')">
  <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
    <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('viewModal')">&times;</button>
    <h2 class="text-lg font-bold mb-4">View Invoice</h2>

    <div class="mb-2"><strong>Invoice ID:</strong> <span id="viewInvoiceID"></span></div>
    <div class="mb-2"><strong>Customer ID:</strong> <span id="viewCustomerID"></span></div>
    <div class="mb-2"><strong>Invoice Date:</strong> <span id="viewInvoiceDate"></span></div>
    <div class="mb-2"><strong>Due Date:</strong> <span id="viewDueDate"></span></div>
    <div class="mb-2"><strong>Description:</strong> <span id="viewDescription"></span></div>
    <div class="mb-2"><strong>Amount:</strong> <span id="viewAmount"></span></div>
    <div class="mb-2"><strong>Reference No:</strong> <span id="viewReferenceNo"></span></div>
    <div class="mb-2"><strong>Created At:</strong> <span id="viewCreatedAt"></span></div>
    <div class="mb-2"><strong>Status:</strong> <span id="viewStatus"></span></div> 

    <div class="flex justify-end mt-4">
      <button class="mt-4 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" onclick="closeModal('viewModal')">Close</button>
    </div>
  </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'updateModal')">
  <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
    <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('updateModal')">&times;</button>
    <h2 class="text-lg font-bold mb-4">Adjust Invoice</h2>
    <form method="post">
      <input type="hidden" name="update_InvoiceID" id="updateAdjustID">
      <div class="mb-2 form-group">
        <label for="updateCustumerName" class="block text-sm font-medium text-gray-700">Custumer ID:</label>
        <input type="text" name="update_CustumerName" id="updateCustumerName" class="w-full border border-gray-300 p-2 rounded" required>
      </div>
      <div class="mb-2 form-group">
        <label for="updateContactPerson" class="block text-sm font-medium text-gray-700">Invoice Date:</label>
        <input type="date" name="update_ContactPerson" id="updateContactPerson" class="w-full border border-gray-300 p-2 rounded" required>
      </div>
      <div class="mb-2 form-group">
        <label for="updateContactNumber" class="block text-sm font-medium text-gray-700">Due Date:</label>
        <input type="date" name="update_ContactNumber" id="updateContactNumber" class="w-full border border-gray-300 p-2 rounded" required>
      </div>
      <div class="mb-2 form-group">
        <label for="updateEmail" class="block text-sm font-medium text-gray-700">Description:</label>
        <input type="text" name="update_Email" id="updateEmail" class="w-full border border-gray-300 p-2 rounded" required>
      </div>
      <div class="mb-2 form-group">
        <label for="updateAddress" class="block text-sm font-medium text-gray-700">Amount:</label>
        <input type="number" step="0.01" name="update_Address" id="updateAddress" class="w-full border border-gray-300 p-2 rounded" required>
      </div>
      <div class="mb-2 form-group">
        <label for="updatePaymentTerms" class="block text-sm font-medium text-gray-700">Reference No:</label>
        <input type="text" name="update_PaymentTerms" id="updatePaymentTerms" class="w-full border border-gray-300 p-2 rounded" required>
      </div>
      <div class="mb-2 form-group">
        <label for="updateStatus" class="block text-sm font-medium text-gray-700">Status:</label>
        <select name="update_Status" id="updateStatus" class="w-full border border-gray-300 p-2 rounded" required>
          <option value="Paid">Paid</option>
          <option value="UnPaid">UnPaid</option>
          <option value="Overdue">Overdue</option>
          <option value="Partially Paid">Partially Paid</option>
        </select>
      </div>



      <div class="flex justify-end space-x-3 mt-4">
          <button type="button" class="text-white px-4 py-2 bg-red-500 rounded-lg hover:bg-red-600" onclick="closeModal('updateModal')">Cancel</button>
        <button type="submit" name="update" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
      </div>
    </form>
  </div>
</div>



<div id="archiveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'archiveModal')">
  <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
    <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('archiveModal')">&times;</button>
        <svg class="mx-auto mb-4 text-gray-400 w-12 h-12 dark:text-gray-200" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
      <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
      </svg>
    
        <h2 class="text-lg font-bold mb-4">Archive Invoice</h2>
        <p>Are you sure you want to archive this custumer?</p>
        <form method="post">
          
          <div class="flex justify-end space-x-3">
            <input type="hidden" name="archive_InvoiceID" id="archiveAdjustID">
              <button type="button" onclick="closeModal('archiveModal')" class="px-4 py-2 bg-blue-200 rounded-lg hover:bg-blue-300">Cancel</button>
                 <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700" name="archive">Yes, Archive</button>
            </div>
        </form>
  </div>
</div>

<script>
function openViewModal(invoiceId, customerId, invoiceDate, dueDate, description, amount, referenceNo, createdAt, status) {
    document.getElementById('viewInvoiceID').innerText = invoiceId;
    document.getElementById('viewCustomerID').innerText = customerId;

    document.getElementById('viewInvoiceDate').innerText = new Date(invoiceDate).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
    document.getElementById('viewDueDate').innerText = new Date(dueDate).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric'
    });

    document.getElementById('viewDescription').innerText = description;
    document.getElementById('viewAmount').innerText = "₱ " + parseFloat(amount).toLocaleString();

    document.getElementById('viewReferenceNo').innerText = referenceNo;
    document.getElementById('viewCreatedAt').innerText = new Date(createdAt).toLocaleString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });

    document.getElementById('viewStatus').innerText = status;
    document.getElementById('viewModal').classList.remove('hidden');
}

function openUpdateModal(invoiceId, customerId, invoiceDate, dueDate, description, amount, referenceNo, status, createdAt) {
    document.getElementById('updateAdjustID').value = invoiceId;
    document.getElementById('updateCustumerName').value = customerId;
    document.getElementById('updateContactPerson').value = invoiceDate;
    document.getElementById('updateContactNumber').value = dueDate;
    document.getElementById('updateEmail').value = description;
    document.getElementById('updateAddress').value = amount;
    document.getElementById('updatePaymentTerms').value = referenceNo;
    document.getElementById('updateStatus').value = status;
    document.getElementById('updateModal').classList.remove('hidden');
}
function openArchiveModal(invoiceId) {
    document.getElementById('archiveAdjustID').value = invoiceId;
    document.getElementById('archiveModal').classList.remove('hidden');
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