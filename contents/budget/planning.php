<div  class="mb-4">
    <div class=" rounded-2xl shadow-xl p-6  table-section">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold  flex items-center">
                <i class="fas fa-check-circle text-indigo-500 mr-2"></i>
                Approval Section
            </h2>
        </div>
        
        
        <div id="approvalCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
    </div>
</div>

<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button onclick="closeModals()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
        </button>
        <h3 class="text-lg font-semibold  mb-4">Request Details</h3>
        <div id="modalContent" class="text-sm text-gray-600 space-y-2"></div>
        <div class="mt-4 flex justify-end space-x-2" id="modalButtons"></div>
    </div>
</div>