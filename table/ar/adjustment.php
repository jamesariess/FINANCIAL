<div class="table-section" id="invoiceTableSection">
    <h3>Invoice Adjustment</h3>
    <table id="employeesTable">
        <thead>
            <tr>
                <th>Adjustment ID</th>
                <th>Invoice ID</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Reason</th>
                <th>Date of Issue</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="employeesTableBody">
            <?php if (!empty($adjustReports)): ?>
                <?php foreach ($adjustReports as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['adjustment_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['invoice_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td><?php echo htmlspecialchars($row['amount']); ?></td>
                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <a href="#" onclick="openViewModal(
                                '<?php echo htmlspecialchars($row['adjustment_id'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['invoice_id'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['type'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['amount'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['reason'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['created_at'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['status'], ENT_QUOTES); ?>'
                            ); return false;">👁️ View</a>
                            <a href="#" onclick="openUpdateModal(
                                '<?php echo htmlspecialchars($row['adjustment_id'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['invoice_id'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['type'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['amount'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['reason'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['created_at'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['status'], ENT_QUOTES); ?>'
                            ); return false;">✏️ Update</a>
                            <a href="#" onclick="openArchiveModal('<?php echo htmlspecialchars($row['adjustment_id'], ENT_QUOTES); ?>'); return false;">🗄️ Archive</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center p-4">No Records Found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50" onclick="outsideClick(event, 'viewModal')">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">View Adjustment</h2>
        <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('viewModal')">&times;</button>
        <div class="mb-4">
            <p><strong>Adjustment ID:</strong> <span id="viewAdjustmentID"></span></p>
            <p><strong>Invoice ID:</strong> <span id="viewInvoiceID"></span></p>
            <p><strong>Type:</strong> <span id="viewType"></span></p>
            <p><strong>Amount:</strong> <span id="viewAmount"></span></p>
            <p><strong>Reason:</strong> <span id="viewReason"></span></p>
            <p><strong>Date of Issue:</strong> <span id="viewCreatedAt"></span></p>
            <p><strong>Status:</strong> <span id="viewStatus"></span></p>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50" onclick="outsideClick(event, 'updateModal')">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">Update Invoice Adjustment</h2>
        <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('updateModal')">&times;</button>
        <form method="post">
            <input type="hidden" name="adjustment_id" id="updateAdjustID">
            <div class="mb-4">
                <label class="block text-gray-700">Invoice ID:</label>
                <input type="text" name="invoice_id" id="updateInvoiceID" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Type:</label>
                <input type="text" name="type" id="updateType" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Amount:</label>
                <input type="number" step="0.01" name="amount" id="updateAmount" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Reason:</label>
                <input type="text" name="reason" id="updateReason" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Status:</label>
                <select name="status" id="updateStatus" class="w-full border border-gray-300 p-2 rounded" required>
                    <option value="Pending">Pending</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Unresolved">Unresolved</option>
                </select>
            </div>
            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400" onclick="closeModal('updateModal')">Cancel</button>
                <button type="submit" name="update" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- Archive Modal -->
<div id="archiveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50" onclick="outsideClick(event, 'archiveModal')">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">Archive Adjustment</h2>
        <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('archiveModal')">&times;</button>
        <p>Are you sure you want to archive this adjustment?</p>
        <form method="post">
            <input type="hidden" name="archive_collectionID" id="archiveAdjustID">
            <div class="flex justify-end space-x-3 mt-4">
                <button type="submit" name="archive" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Archive</button>
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400" onclick="closeModal('archiveModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Close all modals
    function closeAllModals() {
        document.getElementById('viewModal').classList.add('hidden');
        document.getElementById('updateModal').classList.add('hidden');
        document.getElementById('archiveModal').classList.add('hidden');
    }

    // Open View Modal
    function openViewModal(adjustment_id, invoice_id, type, amount, reason, created_at, status) {
        closeAllModals(); // Close all other modals
        document.getElementById('viewAdjustmentID').innerText = adjustment_id;
        document.getElementById('viewInvoiceID').innerText = invoice_id;
        document.getElementById('viewType').innerText = type;
        document.getElementById('viewAmount').innerText = amount;
        document.getElementById('viewReason').innerText = reason;
        document.getElementById('viewCreatedAt').innerText = created_at;
        document.getElementById('viewStatus').innerText = status;
        document.getElementById('viewModal').classList.remove('hidden');
    }

    // Open Update Modal
    function openUpdateModal(adjustment_id, invoice_id, type, amount, reason, created_at, status) {
        closeAllModals(); // Close all other modals
        document.getElementById('updateAdjustID').value = adjustment_id;
        document.getElementById('updateInvoiceID').value = invoice_id;
        document.getElementById('updateType').value = type;
        document.getElementById('updateAmount').value = amount;
        document.getElementById('updateReason').value = reason;
        document.getElementById('updateStatus').value = status;
        document.getElementById('updateModal').classList.remove('hidden');
    }

    // Open Archive Modal
    function openArchiveModal(adjustment_id) {
        closeAllModals(); // Close all other modals
        document.getElementById('archiveAdjustID').value = adjustment_id;
        document.getElementById('archiveModal').classList.remove('hidden');
    }

    // Close a specific modal
    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    // Handle outside click to close modal
    function outsideClick(event, modalId) {
        if (event.target.id === modalId) {
            closeModal(modalId);
        }
    }
</script>