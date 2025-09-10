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
  requests.forEach(req => {
    if (req.status === "Paid") return; // Skip Paid

    let cardColor = "from-gray-50 to-white border-gray-200 text-gray-600";
    let icon = "fa-file-alt";

    switch (req.Modules.toLowerCase()) {
      case "HR":
        cardColor = "from-purple-50 to-white border-purple-200 text-purple-600";
        icon = "fa-users";
        break;
      case "Maintenance":
        case "Operations":
      
        cardColor = "from-green-50 to-white border-green-200 text-green-600";
        icon = "fa-truck";
        break;
      case "Finance":
        cardColor = "from-blue-50 to-white border-blue-200 text-blue-600";
        icon = "fa-briefcase";
        break;
     
      case "General Services":
        cardColor = "from-red-50 to-white border-red-200 text-red-600";
        icon = "fa-gas-pump";
        break;
      default:
        cardColor = "from-indigo-50 to-white border-indigo-200 text-indigo-600";
        icon = "fa-layer-group";
    }


    let buttonHTML = "";
    if (req.status === "Approved") {
      buttonHTML = `<button class="bg-indigo-500 text-white text-sm font-semibold py-2 px-4 rounded-md hover:bg-indigo-600"
                      onclick="approve(${req.requestID})">Confirm Release</button>`;
    } else if (req.status === "Verified") {
      buttonHTML = `<button class="bg-yellow-400 text-white text-sm font-semibold py-2 px-4 rounded-md opacity-70 cursor-not-allowed">
                      Waiting for Approval
                    </button>`;
    }

    container.innerHTML += `
      <div class="bg-gradient-to-br ${cardColor} rounded-lg shadow-md p-6 hover:shadow-lg transition duration-200 border" data-id="${req.requestID}">
        <i class="fas ${icon} text-2xl mb-3"></i>
        <h3 class="text-lg font-semibold text-gray-800 mb-3 capitalize">${req.Modules} Payment</h3>
        <div class="text-sm text-gray-600 space-y-1">
          <p><span class="font-medium">ID:</span> REQ-${req.requestID}</p>
          <p><span class="font-medium">Title:</span> ${req.requestTitle}</p>
          <p><span class="font-medium">Amount:</span> ₱${Number(req.Amount).toLocaleString()}</p>
          <p><span class="font-medium">Requested By:</span> ${req.Requested_by}</p>
          <p><span class="font-medium">Status:</span> ${req.status}</p>
        </div>
        <div class="flex mt-4 space-x-2">
          ${buttonHTML}
          <button class="text-indigo-500 text-sm hover:underline" onclick="viewDetails(${req.requestID})">Details</button>
        </div>
      </div>
    `;
  });


  function approve(id) {
    if (!confirm("Are you sure you want to confirm release?")) return;

    fetch("", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ requestID: id })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("Request marked as Paid!");
          const card = document.querySelector(`[data-id='${id}']`);
          if (card) card.remove();
          closeModal();
        } else {
          alert("Failed to update status.");
        }
      })
      .catch(err => console.error("Error:", err));
  }


  function viewDetails(id) {
    const req = requests.find(r => Number(r.requestID) === Number(id));
    if (!req) return;

    modalContent.innerHTML = `
      <p><span class="font-medium">ID:</span> REQ-${req.requestID}</p>
      <p><span class="font-medium">Title:</span> ${req.requestTitle}</p>
      <p><span class="font-medium">Module:</span> ${req.Modules}</p>
      <p><span class="font-medium">Amount:</span> ₱${Number(req.Amount).toLocaleString()}</p>
      <p><span class="font-medium">Requested By:</span> ${req.Requested_by}</p>
      <p><span class="font-medium">Due:</span> ${req.Due}</p>
      <p><span class="font-medium">Status:</span> ${req.status}</p>
      <p><span class="font-medium">Date:</span> ${req.date}</p>
    `;

    if (req.status === "Approved") {
      modalButtons.innerHTML = `
        <button class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600" onclick="approve(${req.requestID})">Confirm Release</button>
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