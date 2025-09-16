<div class="table-section" id="invoiceTableSection">
    <h3>Vendor Details</h3>
    <table id="employeesTable">
        <thead>
            <tr>
                <th>Vendor ID</th>
                <th>Vendor Name</th>
                <th>Contact Info</th>
                <th>Address</th>
                <th>Email</th>
                <th>Contact Person</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="employeesTableBody">
            <?php if (!empty($adjustReports)): ?>
                <?php foreach ($adjustReports as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['vendor_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['vendor_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact_info']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                        <td><?php echo htmlspecialchars($row['Contact_person']); ?></td>
                        <td><?php echo htmlspecialchars($row['Status']); ?></td>
                        <td>
                            <a href="#" onclick="openViewModal(
                                '<?php echo htmlspecialchars($row['vendor_id'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['vendor_name'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['contact_info'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['address'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['Email'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['Contact_person'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['Status'], ENT_QUOTES); ?>'
                            ); return false;">👁️ View</a>
                            <a href="#" onclick="openUpdateModal(
                                '<?php echo htmlspecialchars($row['vendor_id'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['vendor_name'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['contact_info'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['address'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['Email'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['Contact_person'], ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($row['Status'], ENT_QUOTES); ?>'
                            ); return false;">✏️ Update</a>
                            <a href="#" onclick="openArchiveModal('<?php echo htmlspecialchars($row['vendor_id'], ENT_QUOTES); ?>'); return false;">🗄️ Archive</a>
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
        <h2 class="text-lg font-bold mb-4">View Vendor</h2>
        <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('viewModal')">&times;</button>
        <div class="mb-4">
            <p><strong>Vendor ID:</strong> <span id="viewVendorID"></span></p>
            <p><strong>Vendor Name:</strong> <span id="viewName"></span></p>
            <p><strong>Contact Info:</strong> <span id="viewContact"></span></p>
            <p><strong>Address:</strong> <span id="viewAddress"></span></p>
            <p><strong>Email:</strong> <span id="viewEmail"></span></p>
            <p><strong>Contact Person:</strong> <span id="viewPhone"></span></p>
            <p><strong>Status:</strong> <span id="viewStatus"></span></p>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div id="updateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50" onclick="outsideClick(event, 'updateModal')">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3 relative" onclick="event.stopPropagation()">
        <h2 class="text-lg font-bold mb-4">Update Vendor</h2>
        <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('updateModal')">&times;</button>
        <form method="post">
            <input type="hidden" name="vendors_id" id="updateVendorID">
            <div class="mb-4">
                <label class="block text-gray-700">Vendor Name:</label>
                <input type="text" name="name" id="updateName" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Contact Info:</label>
                <input type="text" name="contact" id="updateContact" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Address:</label>
                <input type="text" name="address" id="updateAddress" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Email:</label>
                <input type="email" name="email" id="updateEmail" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Contact Person:</label>
                <input type="text" name="phone" id="updatePhone" class="w-full border border-gray-300 p-2 rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Status:</label>
                <select name="status" id="updateStatus" class="w-full border border-gray-300 p-2 rounded" required>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
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
        <h2 class="text-lg font-bold mb-4">Archive Vendor</h2>
        <button class="absolute top-2 right-2 text-gray-500 hover:text-black" onclick="closeModal('archiveModal')">&times;</button>
        <p>Are you sure you want to archive this Vendor?</p>
        <form method="post">
            <input type="hidden" name="archive_collectionID" id="archiveVendorID">
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
    function openViewModal(vendor_id, vendor_name, contact_info, address, email, contact_person, status) {
        closeAllModals();
        document.getElementById('viewVendorID').innerText = vendor_id;
        document.getElementById('viewName').innerText = vendor_name;
        document.getElementById('viewContact').innerText = contact_info;
        document.getElementById('viewAddress').innerText = address;
        document.getElementById('viewEmail').innerText = email;
        document.getElementById('viewPhone').innerText = contact_person;
        document.getElementById('viewStatus').innerText = status;
        document.getElementById('viewModal').classList.remove('hidden');
    }

    function openUpdateModal(vendor_id, vendor_name, contact_info, address, email, contact_person, status) {
        closeAllModals();
        document.getElementById('updateVendorID').value = vendor_id;
        document.getElementById('updateName').value = vendor_name;
        document.getElementById('updateContact').value = contact_info;
        document.getElementById('updateAddress').value = address;
        document.getElementById('updateEmail').value = email;
        document.getElementById('updatePhone').value = contact_person;
        document.getElementById('updateStatus').value = status;
        document.getElementById('updateModal').classList.remove('hidden');
    }

    function openArchiveModal(vendor_id) {
        closeAllModals();
        document.getElementById('archiveVendorID').value = vendor_id;
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