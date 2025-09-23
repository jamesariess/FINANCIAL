<div class="table-section" id="invoiceTableSection">
    <h3>Collection Report</h3>
    <table id="employeesTable">
        <thead>
            <tr>
    <th>Collection</th>
    <th>Invoice ID</th>
    <th>Payment Date</th>
    <th>Amount</th>
    <th>Payment Method</th>
    <th>Remarks</th>
    <th>Credit At</th>
  
    <th>Action</th>
            </tr>
        </thead>
        <tbody id="employeesTableBody">
            <?php if(!empty($collectionReports)):
            foreach($collectionReports as $row): ?>
            <tr>
    <td><?php echo htmlspecialchars($row['collection_id']);?></td>
    <td><?php echo htmlspecialchars($row['invoice_id']);?></td>
    <td><?php echo htmlspecialchars($row['payment_date']);?></td>
    <td><?php echo htmlspecialchars($row['amount']);?></td>
    <td><?php echo htmlspecialchars($row['method']);?></td>
    <td><?php echo htmlspecialchars($row['remarks']);?></td>
    <td><?php echo htmlspecialchars($row['created_at']);?></td>
     <td>
<a href=""
onclick="openViewModal(
    '<?php echo $row['collection_id'];?>',
    '<?php echo htmlspecialchars($row['invoice_id'],ENT_QUOTES);?>',
    '<?php echo htmlspecialchars($row['payment_date'],ENT_QUOTES);?>',
    '<?php echo htmlspecialchars($row['amount'],ENT_QUOTES);?>',
    '<?php echo htmlspecialchars($row['method'],ENT_QUOTES);?>',
    '<?php echo htmlspecialchars($row['remarks'],ENT_QUOTES);?>'
); return false;"> 👁️ View</a>

<a href=""
onclick="openUpdateModal(
    '<?php echo $row['collection_id'];?>',
    '<?php echo htmlspecialchars($row['invoice_id'],ENT_QUOTES);?>',
    '<?php echo htmlspecialchars($row['payment_date'],ENT_QUOTES);?>',
    '<?php echo htmlspecialchars($row['amount'],ENT_QUOTES);?>',
    '<?php echo htmlspecialchars($row['method'],ENT_QUOTES);?>',
    '<?php echo htmlspecialchars($row['remarks'],ENT_QUOTES);?>'
); return false;"> ✏️ Update</a>

        <a href=""
        onclick="openArchiveModal('<?php echo $row['collection_id']?>'); return false;"> 🗄️ Archive</a>
     </td>
         
            </tr>
            <?php endforeach; else:?>
            <tr><Td colspan="8" class="text-center p-4">NO Records Found.</Td></tr>
            <?php endif;?>
        </tbody>
    </table>
</div>

<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'viewModal')">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">View</h2>
      <div class="space-y-2">
        <p><strong>Collection ID:</strong> <span id="viewAdjustID"></span></p>
        <p><strong>Invoice ID:</strong> <span id="viewBudgetID"></span></p>
        <p><strong>Payment Date:</strong> <span id="viewAdjustedBy"></span></p>
        <p><strong>Amount:</strong> <span id="viewAdjustmentDate"></span></p>
        <p><strong>Payment Method:</strong> <span id="viewReason"></span></p>
        <p><strong>Remarks:</strong> <span id="viewNewAmount"></span></p>
        <p><strong>Status:</strong> <span id="viewStatus"></span></p>
      </div>
        <button class="mt-4 px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" onclick="closeModal('viewModal')">Close</button>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center" onclick="outsideClick(event, 'updateModal')">
  <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
    <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('updateModal')">&times;</button>
    <h2 class="text-lg font-bold mb-4">Update Payment Collection</h2>
    <form method="post">
        <input type="hidden" name="update_collectionID" id="updateAdjustID">
      <div class="space-y-4">
        <div class="form-group">
          <label for="updateInvoiceID" class="block text-sm font-medium text-gray-700">Invoice ID</label>
          <input type="text" name="update_invoiceID" id="updateInvoiceID" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required>
        </div>  
        <div class="form-group">
          <label for="updatePaymentDate" class="block text-sm font-medium text-gray-700">Payment Date</label>
          <input type="date" name="update_paymentDate" id="updatePaymentDate" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required> 
        </div>
        <div class="form-group">
          <label for="updateAmount" class="block text-sm font-medium text-gray-700">Amount</label>
          <input type="number" step="0.01" name="update_amount" id="updateAmount" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required> 
        </div>
        <div class="form-group">
          <label for="updatePaymentMethod" class="block text-sm font-medium text-gray-700">Payment Method</label>
          <input type="text" name="update_paymentMethod" id="updatePaymentMethod" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required> 
        </div>
        <div class="form-group">
          <label for="updateRemarks" class="block text-sm font-medium text-gray-700">Remarks</label>
          <textarea name="update_remarks" id="updateRemarks" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" required></textarea> 
        </div>
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
   
        <h2 class="text-lg font-bold mb-4 text-center">Archive Collection</h2>
        <p>Are you sure you want to archive this collection?</p>
        <form method="post">
          
          <div class="flex justify-end space-x-3">
            <input type="hidden" name="archive_collectionID" id="archiveAdjustID">
              <button type="button" onclick="closeModal('archiveModal')" class="px-4 py-2 bg-blue-200 rounded-lg hover:bg-blue-300">Cancel</button>
                 <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700" name="archive">Yes, Archive</button>
             </div>
        </form>
  </div>
</div>

<script>
function openViewModal(collectionID, invoiceID, paymentDate, amount, method, remarks) {
    document.getElementById('viewAdjustID').innerText = collectionID;
    document.getElementById('viewBudgetID').innerText = invoiceID;
    document.getElementById('viewAdjustedBy').innerText = paymentDate;  
    document.getElementById('viewAdjustmentDate').innerText = amount;
    document.getElementById('viewReason').innerText = method;
    document.getElementById('viewNewAmount').innerText = remarks;
    document.getElementById('viewModal').classList.remove('hidden');
}
function openUpdateModal(collectionID, invoiceID, paymentDate, amount, method, remarks) {
    document.getElementById('updateAdjustID').value = collectionID;
    document.getElementById('updateInvoiceID').value = invoiceID;
    document.getElementById('updatePaymentDate').value = paymentDate;  
    document.getElementById('updateAmount').value = amount;
    document.getElementById('updatePaymentMethod').value = method;
    document.getElementById('updateRemarks').value = remarks;
    document.getElementById('updateModal').classList.remove('hidden');
}
function openArchiveModal(collectionID) {
    document.getElementById('archiveAdjustID').value = collectionID;
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