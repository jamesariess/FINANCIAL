<div class="container mx-auto p-6">
    <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-check-circle text-indigo-500 mr-2"></i>
                Payment Release Section
            </h2>
        </div>

        <div id="approvalCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
    </div>
</div>

<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
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

    // Render cards
    if (requests.length === 0) {
        container.innerHTML = `
            <div class="col-span-full text-center py-10">
                <i class="fas fa-money-check-alt text-6xl text-gray-300 mb-4"></i>
                <h4 class="text-lg font-medium text-gray-500">No Payments to Release</h4>
                <p class="text-gray-400">All pending payments have been released. Good job!</p>
            </div>
        `;
    } else {
        requests.forEach(req => {
            if (req.status === "Paid") return;

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
            let amountDisplay = req.status === "Approved" ? req.ApprovedAmount : null;

            if (req.status === "Approved") {
                buttonHTML = `<button class="bg-indigo-500 text-white text-sm font-semibold py-2 px-4 rounded-md hover:bg-indigo-600"
                                     onclick="confirmRelease(${req.requestID})">Confirm Release</button>`;
            } else if (req.status === "Verified") {
                buttonHTML = `<button class="bg-yellow-400 text-white text-sm font-semibold py-2 px-4 rounded-md opacity-70 cursor-not-allowed">
                                     Waiting for Approval
                                </button>`;
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
                    <h4 class="text-lg font-semibold text-gray-800 mb-4 capitalize">${req.accountName}</h4>
                    <div class="text-sm text-gray-600 space-y-2">
                        <p><span class="font-medium text-gray-700">ID:</span> REQ-${req.requestID}</p>
                        <p><span class="font-medium text-gray-700">Title:</span> ${req.requestTitle}</p>
                        <p><span class="font-medium text-gray-700">Amount:</span> ${amountDisplay != null ? `₱${Number(amountDisplay).toLocaleString()}` : 'Waiting For Approved Amount'}</p>
                        <p><span class="font-medium text-gray-700">Requested By:</span> ${req.Requested_by}</p>
                    </div>
                    <div class="flex mt-6 space-x-3">
                        ${buttonHTML.replace(
                            'py-2 px-4 rounded-md',
                            'py-2 px-5 rounded-lg text-sm font-semibold tracking-wide transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500'
                        )}
                        <button class="text-indigo-600 text-sm font-medium hover:text-indigo-800 underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="viewDetails(${req.requestID})" aria-label="View details for request ${req.requestID}">
                            Details
                        </button>
                    </div>
                </div>
            `;
        });
    }

    function confirmRelease(id) {
        if (!confirm("Are you sure you want to confirm the payment release for this request?")) return;

        fetch("", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ requestID: id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Payment successfully released!");
                window.location.reload();
            } else {
                alert(data.error || "Failed to confirm payment release.");
            }
        })
        .catch(err => {
            console.error("Error:", err);
            alert("An error occurred. Please try again.");
        });
    }

    function viewDetails(id) {
        const req = requests.find(r => Number(r.requestID) === Number(id));
        if (!req) return;

        let amountToShow = req.status === "Approved" ? req.ApprovedAmount : null;

        modalContent.innerHTML = `
            <p><span class="font-medium">ID:</span> REQ-${req.requestID}</p>
            <p><span class="font-medium">Title:</span> ${req.requestTitle}</p>
            <p><span class="font-medium">Cost Allocation:</span> ${req.accountName}</p>
            <p><span class="font-medium">Department:</span> ${req.Name}</p>
            <p><span class="font-medium">Amount:</span> ${amountToShow != null ? `₱${Number(amountToShow).toLocaleString()}` : 'Waiting For Approved Amount'}</p>
            <p><span class="font-medium">Requested By:</span> ${req.Requested_by}</p>
            <p><span class="font-medium">Due:</span> ${req.Due}</p>
            <p><span class="font-medium">Status:</span> ${req.status}</p>
            <p><span class="font-medium">Date:</span> ${req.date}</p>
        `;

        if (req.status === "Approved") {
            modalButtons.innerHTML = `
                <button class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600" onclick="confirmRelease(${req.requestID})">Confirm Release</button>
                <button onclick="closeModal()" class="px-4 py-2 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">Close</button>
            `;
        } else {
            modalButtons.innerHTML = `
                <button class="bg-yellow-400 text-white px-4 py-2 rounded opacity-70 cursor-not-allowed">Waiting for Approval</button>
                <button onclick="closeModal()" class="px-4 py-2 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">Close</button>
            `;
        }

        modal.classList.remove("hidden");
    }

    function closeModal() {
        modal.classList.add("hidden");
    }
</script>