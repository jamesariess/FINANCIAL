<div class="container mx-auto p-6">
    <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
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
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Request Details</h3>
        <div id="modalContent" class="text-sm text-gray-600 space-y-2"></div>
        <div class="mt-4 flex justify-end space-x-2" id="modalButtons"></div>
    </div>
</div>

<script>
    const requests = <?php echo json_encode($requests); ?>;
    const container = document.getElementById("approvalCards");
    const modal = document.getElementById("detailsModal");
    const modalContent = document.getElementById("modalContent");
    const modalButtons = document.getElementById("modalButtons");


    if (requests.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-10">
                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                <h4 class="text-lg font-medium text-gray-500">All caught up!</h4>
                <p class="text-gray-400">There are no new requests for your approval at the moment. Good job!</p>
            </div>
        `;
    } else {
        requests.forEach(req => {
            let cardColor = "from-gray-50 to-white border-gray-200 text-gray-600";
            let icon = "fa-file-alt";

            switch (req.Name.toLowerCase()) {
                case "hr":
                    cardColor = "from-purple-50 to-white border-purple-200 text-purple-600";
                    icon = "fa-users";
                    break;
                case "maintenance":
                case "operations":
                    cardColor = "from-green-50 to-white border-green-200 text-green-600";
                    icon = "fa-truck";
                    break;
                case "finance":
                    cardColor = "from-blue-50 to-white border-blue-200 text-blue-600";
                    icon = "fa-briefcase";
                    break;
                case "general services":
                    cardColor = "from-red-50 to-white border-red-200 text-red-600";
                    icon = "fa-gas-pump";
                    break;
                default:
                    cardColor = "from-indigo-50 to-white border-indigo-200 text-indigo-600";
                    icon = "fa-layer-group";
            }

            let buttonHTML = "";
            if (req.status === "Verified") {
                buttonHTML = `
                <div class="mt-4 space-y-3">
                    <label class="block text-sm text-gray-700">Change Amount</label>
                    <input id="approvedAmount-${req.requestID}" 
                           type="number" 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                           placeholder="Enter approved amount"
                           value="${req.Amount}">

                    <div class="flex gap-2">
                        <button class="flex-1 bg-indigo-500 text-white text-sm font-semibold py-2 rounded-md hover:bg-indigo-600 transition"
                                onclick="approve(${req.requestID})">Approve</button>
                        <button class="flex-1 bg-red-500 text-white text-sm font-semibold py-2 rounded-md hover:bg-red-600 transition"
                                onclick="reject(${req.requestID})">Reject</button>
                    </div>
                </div>
                `;
            }

            container.innerHTML += `
                      <div class="bg-gradient-to-br ${cardColor} rounded-xl shadow-lg p-6 hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300 border border-opacity-50" data-id="${req.requestID}" role="article" aria-labelledby="card-title-${req.requestID}">
                    <div class="flex items-center justify-between mb-4">
                        <i class="fas ${icon} text-3xl ${cardColor.split(' ')[2].replace('border-', 'text-')}"></i>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${req.status === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                            ${req.status}
                        </span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2 capitalize" id="card-title-${req.requestID}">${req.Name} Department</h3>
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 capitalize">${req.Title}</h4>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p><span class="font-medium text-gray-700">ID:</span> REQ-${req.requestID}</p>
                        <p><span class="font-medium text-gray-700">Title:</span> ${req.requestTitle}</p>
                            <p><span class="font-medium">Requested Amount:</span> ₱${Number(req.Amount).toLocaleString()}</p>
                            <p><span class="font-medium">Requested By:</span> ${req.Requested_by}</p>
                 
                        </div>
                
                    <div class="mt-4">
                        ${buttonHTML}
                        <button class="text-indigo-600 text-sm mt-2 hover:underline" onclick="viewDetails(${req.requestID})">View Details</button>
                    </div>
                </div>
            `;
        });
    }

    function approve(id) {
    const input = document.getElementById(`approvedAmount-${id}`);
    if (!input || !input.value) {
        alert("Please enter the approved amount.");
        return;
    }

    const approvedAmount = parseFloat(input.value);
    if (isNaN(approvedAmount) || approvedAmount <= 0) {
        alert("Please enter a valid amount.");
        return;
    }

    if (!confirm(`Approve request with amount ₱${approvedAmount.toLocaleString()}?`)) return;

    fetch(window.location.href, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ requestID: id, status: "Approved", approvedAmount })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Request Approved!");
            window.location.reload();
        } else {
            alert("Failed to update request.");
        }
    })
    .catch(err => {
   window.location.reload();
    });
}

    
    function reject(id) {
        const remarks = prompt("Enter rejection reason (optional):");
        if (!confirm("Are you sure you want to reject this request?")) return;

        fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ requestID: id, status: "Rejected", remarks })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Request Rejected!");
                window.location.reload(); 
            } else {
                alert("Failed to update request.");
            }
        })
        .catch(err => {
          window.location.reload();
        });
    }

 
    function viewDetails(id) {
        const req = requests.find(r => Number(r.requestID) === Number(id));
        if (!req) return;

        modalContent.innerHTML = `
          <p><span class="font-medium">ID:</span> REQ-${req.requestID}</p>
            <p><span class="font-medium">Title:</span> ${req.requestTitle}</p>
            <p><span class="font-medium">Cost Allocation:</span> ${req.Title}</p>
            <p><span class="font-medium">Department:</span> ${req.Name}</p>
            <p><span class="font-medium">Requested Amount:</span> ₱${Number(req.Amount).toLocaleString()}</p>
            <p><span class="font-medium">Requested By:</span> ${req.Requested_by}</p>
            <p><span class="font-medium">Due:</span> ${req.Due}</p>
            <p><span class="font-medium">Status:</span> ${req.status}</p>
            <p><span class="font-medium">Date:</span> ${req.date}</p>
        `;

        modalButtons.innerHTML = `
            <button onclick="closeModals()" 
                    class="px-4 py-2 rounded-md border border-gray-300 text-gray-600 hover:bg-gray-100">Close</button>
        `;

        modal.classList.remove("hidden");
    }

    function closeModals() {
        modal.classList.add("hidden");
    }
</script>
