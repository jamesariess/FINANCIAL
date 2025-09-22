<div class="card">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold flex items-center">
            <i class="fas fa-check-circle text-primary-color mr-2"></i>
            Payment Release Section
        </h2>
    </div>

    <div id="approvalCards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
</div>

<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="form-collection w-full max-w-md p-6 relative">
        <button onclick="closeModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
            <i class="fas fa-times"></i>
        </button>
        <h3 class="text-lg font-semibold mb-4">Request Details</h3>
        <div id="modalContent" class="text-sm space-y-2"></div>
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
            <div class="col-span-full text-center py-10 text-gray-400">
                <i class="fas fa-money-check-alt text-6xl mb-4"></i>
                <h4 class="text-lg font-medium">No Payments to Release</h4>
                <p>All pending payments have been released. Good job!</p>
            </div>
        `;
    } else {
        requests.forEach(req => {
            if (req.status === "Paid") return;

            let cardClasses = "";
            let iconClass = "fa-file-alt";
            let statusClass = req.status === 'Approved' ? 'bg-success-color text-white' : 'bg-info-color text-white';

            switch (req.Name.toLowerCase()) {
                case "hr":
                    cardClasses = "purple";
                    iconClass = "fa-users";
                    break;
                case "maintenance":
                case "operations":
                    cardClasses = "green";
                    iconClass = "fa-truck";
                    break;
                case "finance":
                    cardClasses = "info";
                    iconClass = "fa-briefcase";
                    break;
                case "general services":
                    cardClasses = "red";
                    iconClass = "fa-gas-pump";
                    break;
                default:
                    cardClasses = "primary";
                    iconClass = "fa-layer-group";
            }

            let buttonHTML = "";
            let amountDisplay = req.status === "Approved" ? req.ApprovedAmount : null;

            if (req.status === "Approved") {
                buttonHTML = `<button class="btn btn-primary" onclick="confirmRelease(${req.requestID})">Confirm Release</button>`;
            } else if (req.status === "Pending") {
                buttonHTML = `<button class="btn btn-secondary opacity-70 cursor-not-allowed">Waiting for Approval</button>`;
            }
container.innerHTML += `
    <div class="quick-stat-card ${cardClasses}" data-id="${req.requestID}" role="article" aria-labelledby="card-title-${req.requestID}">
        <div class="flex items-center justify-between mb-4">
            <i class="fas ${iconClass} text-2xl text-${cardClasses}-color"></i>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${req.status === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                            ${req.status}
                        </span>
        </div>
        <h3 class="text-lg font-bold mb-2 capitalize" id="card-title-${req.requestID}">${req.Name} Department</h3>
        <h4 class="text-md font-semibold mb-4 capitalize">${req.accountName}</h4>
        <div class="text-sm space-y-2">
            <p><span class="font-medium">ID:</span> REQ-${req.requestID}</p>
            <p><span class="font-medium">Title:</span> ${req.requestTitle}</p>
            <p><span class="font-medium">Amount:</span> ${amountDisplay != null ? `₱${Number(amountDisplay).toLocaleString()}` : 'Waiting For Approved Amount'}</p>
            <p><span class="font-medium">Requested By:</span> ${req.Requested_by}</p>
        </div>
        <div class="flex mt-6 space-x-3">
            ${buttonHTML}
            <button class="text-primary-color text-sm font-medium underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="viewDetails(${req.requestID})" aria-label="View details for request ${req.requestID}">
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

        let approvedButton = `<button class="btn btn-primary" onclick="confirmRelease(${req.requestID})">Confirm Release</button>`;
        let waitingButton = `<button class="btn btn-secondary opacity-70 cursor-not-allowed">Waiting for Approval</button>`;
        let closeButton = `<button onclick="closeModal()" class="btn btn-secondary">Close</button>`;

        if (req.status === "Approved") {
            modalButtons.innerHTML = `${approvedButton} ${closeButton}`;
        } else {
            modalButtons.innerHTML = `${waitingButton} ${closeButton}`;
        }

        modal.classList.remove("hidden");
    }

    function closeModal() {
        modal.classList.add("hidden");
    }
</script>