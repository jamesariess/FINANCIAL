<div class="table-section" id="invoiceTableSection">
    <h3>Invoice Adjustment</h3>
    <table id="employeesTable">
        <thead>
            <tr>
                <th>Bill ID </th>
                <th>Vendor ID</th>
                <th>Bill Date</th>
                <th>Due Date</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Reference Number</th>
                <th>Created AT</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="employeesTableBody">
            <?php if (!empty($adjustReports)): ?>
                <?php foreach ($adjustReports as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['bill_id']); ?></td>
                        <td><?= htmlspecialchars($row['vendor_id']); ?></td>
                        <td><?= htmlspecialchars($row['bill_date']); ?></td>
                        <td><?= htmlspecialchars($row['due_date']); ?></td>
                        <td><?= htmlspecialchars($row['description']); ?></td>
                        <td><?= htmlspecialchars($row['amount']); ?></td>
                        <td><?= htmlspecialchars($row['reference_no']); ?></td>
                        <td><?= htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <a href="#" onclick="openViewModal(
                                '<?= $row['bill_id'] ?>',
                                '<?= $row['vendor_id'] ?>',
                                '<?= $row['bill_date'] ?>',
                                '<?= $row['due_date'] ?>',
                                '<?= $row['description'] ?>',
                                '<?= $row['amount'] ?>',
                                '<?= $row['reference_no'] ?>',
                                '<?= $row['created_at'] ?>'
                            ); return false;">👁️ View</a>
                            <a href="#" onclick="openUpdateModal(
                                '<?= $row['bill_id'] ?>',
                                '<?= $row['vendor_id'] ?>',
                                '<?= $row['bill_date'] ?>',
                                '<?= $row['due_date'] ?>',
                                '<?= $row['description'] ?>',
                                '<?= $row['amount'] ?>',
                                '<?= $row['reference_no'] ?>',
                                '<?= $row['created_at'] ?>'
                            ); return false;">✏️ Update</a>
                            <a href="#" onclick="openArchiveModal('<?= $row['bill_id'] ?>'); return false;">🗄️ Archive</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9" class="text-center p-4">No Records Found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50" onclick="outsideClick(event, 'viewModal')">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">View Bill</h2>
        <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('viewModal')">&times;</button>
        <div class="mb-4">
            <p><strong>Bill ID:</strong> <span id="viewBillID"></span></p>
            <p><strong>Vendor ID:</strong> <span id="viewVendorID"></span></p>
            <p><strong>Bill Date:</strong> <span id="viewBillDate"></span></p>
            <p><strong>Due Date:</strong> <span id="viewDueDate"></span></p>
            <p><strong>Description:</strong> <span id="viewDescription"></span></p>
            <p><strong>Amount:</strong> <span id="viewAmount"></span></p>
            <p><strong>Reference Number:</strong> <span id="viewReference"></span></p>
            <p><strong>Created At:</strong> <span id="viewCreated"></span></p>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50" onclick="outsideClick(event, 'updateModal')">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">Update Invoice Adjustment</h2>
        <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('updateModal')">&times;</button>
        <form method="post">
            <input type="hidden" name="bill_id" id="updateBillID">
            <div class="mb-4">
                <label class="block text-gray-700">Vendor ID:</label>
                <input type="text" name="vendor_id" id="updateVendorID" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Bill Date:</label>
                <input type="date" name="bill_date" id="updateBillDate" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Due Date:</label>
                <input type="date" name="due_date" id="updateDueDate" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Description:</label>
                <input type="text" name="description" id="updateDescription" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Amount:</label>
                <input type="number" step="0.01" name="amount" id="updateAmount" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Reference Number:</label>
                <input type="text" name="reference_no" id="updateReference" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Created At:</label>
                <input type="date" name="created_at" id="updateCreated" class="w-full border border-gray-300 p-2 rounded" required>
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
        <h2 class="text-lg font-bold mb-4">Archive Bill</h2>
        <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('archiveModal')">&times;</button>
        <p>Are you sure you want to archive this Bill?</p>
        <form method="post">
            <input type="hidden" name="archive_bill_id" id="archiveBillID">
            <div class="flex justify-end space-x-3 mt-4">
                <button type="submit" name="archive" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Archive</button>
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400" onclick="closeModal('archiveModal')">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeAllModals() {
        ['viewModal','updateModal','archiveModal'].forEach(id => document.getElementById(id).classList.add('hidden'));
    }

    function openViewModal(bill_id, vendor_id, bill_date, due_date, description, amount, reference_no, created_at) {
        closeAllModals();
        document.getElementById('viewBillID').innerText = bill_id;
        document.getElementById('viewVendorID').innerText = vendor_id;
        document.getElementById('viewBillDate').innerText = bill_date;
        document.getElementById('viewDueDate').innerText = due_date;
        document.getElementById('viewDescription').innerText = description;
        document.getElementById('viewAmount').innerText = amount;
        document.getElementById('viewReference').innerText = reference_no;
        document.getElementById('viewCreated').innerText = created_at;
        document.getElementById('viewModal').classList.remove('hidden');
    }

    function openUpdateModal(bill_id, vendor_id, bill_date, due_date, description, amount, reference_no, created_at) {
        closeAllModals();
        document.getElementById('updateBillID').value = bill_id;
        document.getElementById('updateVendorID').value = vendor_id;
        document.getElementById('updateBillDate').value = bill_date;
        document.getElementById('updateDueDate').value = due_date;
        document.getElementById('updateDescription').value = description;
        document.getElementById('updateAmount').value = amount;
        document.getElementById('updateReference').value = reference_no;
        document.getElementById('updateCreated').value = created_at;
        document.getElementById('updateModal').classList.remove('hidden');
    }

    function openArchiveModal(bill_id) {
        closeAllModals();
        document.getElementById('archiveBillID').value = bill_id;
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
