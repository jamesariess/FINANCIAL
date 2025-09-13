<div id="allocationError" class="mb-4 hidden bg-red-200 text-red-800 p-3 rounded"></div>

<div class=" bg-white p-6 rounded-2xl shadow-md">
  <h1 class="text-2xl font-bold mb-4 text-indigo-700">Department Cost Allocation</h1>

  <form method="POST" id="allocationForm">
    <!-- Department & Year -->
    <div class="grid grid-cols-2 gap-4 mb-6">
      <div>
        <label class="block text-gray-700 mb-1">Select Department</label>
        <select id="department" name="department" class="w-full p-2 border rounded">
          <option value="">-- Choose Department --</option>
          <?php foreach($departments as $d): ?>
            <option value="<?= $d['Deptbudget'] ?>" 
                    data-amount="<?= $d['Amount'] ?>" 
                    data-used="<?= $d['UsedBudget'] ?>" 
                    data-year="<?= $d['DateValid'] ?>">
              <?= $d['Name'] ?> (₱<?= number_format($d['Amount'] - $d['UsedBudget']) ?> remaining)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-gray-700 mb-1">Year</label>
        <select id="year" name="year" class="w-full p-2 border rounded">
          <option value="">-- Choose Year --</option>
        </select>
      </div>
    </div>

 
    <div id="budgetInfo" class="mb-6 hidden bg-gray-50 p-4 rounded border">
      <p class="text-gray-700">Yearly Budget: <span id="yearlyBudget" class="font-bold text-indigo-600"></span></p>
      <p class="text-gray-700">Remaining Budget: <span id="remainingBudget" class="font-bold text-green-600"></span></p>
    </div>

    <div class="mt-6">
      <div class="flex justify-between text-sm text-gray-600 mb-1">
        <span>Total Percentage</span>
        <span id="totalPercent">0%</span>
      </div>
    <div class="w-full bg-gray-200 rounded-full h-3">
        <div id="progressBar" class="bg-indigo-500 h-3 rounded-full w-0"></div>
      </div>
    

    <div id="allocationRows" class="space-y-4"></div>
  
    <button type="button" onclick="addRow()" 
      class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">+ Add Allocation</button>
</div>


    <button type="submit" 
      class="mt-6 px-6 py-3 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700">Submit Allocation</button>
  </form>
</div>

<script>
let yearlyBudget = 0;
let remainingBudget = 0;
let rowIndex = 0;


function formatPeso(value) {
  return "₱" + Number(value).toLocaleString();
}

document.getElementById("department").addEventListener("change", function() {
  const option = this.options[this.selectedIndex];
  yearlyBudget = parseFloat(option.dataset.amount || 0);
  remainingBudget = yearlyBudget - parseFloat(option.dataset.used || 0);


  const yearSelect = document.getElementById("year");
  yearSelect.innerHTML = "";
  if(option.value && option.dataset.year) {
    const opt = document.createElement("option");
    opt.value = option.dataset.year;
    opt.text = option.dataset.year;
    yearSelect.appendChild(opt);
  }


  const budgetInfo = document.getElementById("budgetInfo");
  if(remainingBudget > 0) {
    budgetInfo.classList.remove("hidden");
    document.getElementById("yearlyBudget").innerText = formatPeso(yearlyBudget);
    document.getElementById("remainingBudget").innerText = formatPeso(remainingBudget);
  } else {
    budgetInfo.classList.add("hidden");
  }


  document.getElementById("allocationRows").innerHTML = "";
  rowIndex = 0;
  updateTotal();
});


function addRow() {
  const container = document.getElementById("allocationRows");
  const row = document.createElement("div");
  row.className = "grid grid-cols-12 gap-2 items-center bg-gray-50 p-3 rounded border";
  row.innerHTML = `
    <input type="text" name="allocations[${rowIndex}][title]" placeholder="Title" class="col-span-3 p-2 border rounded">
    <input type="number" name="allocations[${rowIndex}][percentage]" placeholder="%" min="0" max="100" 
           class="col-span-2 p-2 border rounded percentage" oninput="recalculate(this)">
    <input type="text" name="allocations[${rowIndex}][amount]" readonly class="col-span-3 p-2 border rounded bg-gray-100 amount" placeholder="Yearly">
    <div class="col-span-3 text-sm text-gray-600">
      <p>Monthly: <span class="monthly font-bold">₱0</span></p>
      <p>Daily: <span class="daily font-bold">₱0</span></p>
    </div>
    <button type="button" onclick="removeRow(this)" class="col-span-1 text-red-500 font-bold">-</button>
  `;
  container.appendChild(row);
  rowIndex++;
}


function removeRow(btn) {
  btn.parentElement.remove();
  updateTotal();
}


function recalculate(input) {
  let percent = parseFloat(input.value) || 0;


  const totalPercent = Array.from(document.querySelectorAll('.percentage'))
                        .reduce((sum, p) => sum + (parseFloat(p.value)||0), 0);

const errorDiv = document.getElementById("allocationError");

if(totalPercent > 100) {
    errorDiv.innerText = "⚠ Total allocation cannot exceed 100%!";
    errorDiv.classList.remove("hidden");
    input.value = "";
    updateTotal();
    return;
} else {
    errorDiv.classList.add("hidden"); 
}

  const parent = input.parentElement;
  const amountField = parent.querySelector(".amount");
  const monthlyField = parent.querySelector(".monthly");
  const dailyField = parent.querySelector(".daily");

  const yearly = (percent / 100) * remainingBudget;
  const monthly = yearly / 12;
  const daily = yearly / 365;

  amountField.value = yearly.toLocaleString();
  monthlyField.innerText = formatPeso(monthly);
  dailyField.innerText = formatPeso(daily);

  updateTotal();
}


function updateTotal() {
  const percents = document.querySelectorAll(".percentage");
  const amounts = document.querySelectorAll(".amount");

  let totalPercent = 0;
  let totalAmount = 0;

  percents.forEach(p => totalPercent += parseFloat(p.value) || 0);
  amounts.forEach(a => totalAmount += parseFloat(a.value.replace(/,/g,"")) || 0);

  document.getElementById("totalPercent").innerText = totalPercent.toFixed(2) + "%";
  document.getElementById("progressBar").style.width = totalPercent + "%";
  document.getElementById("progressBar").classList.toggle("bg-red-500", totalPercent > 100);
  document.getElementById("progressBar").classList.toggle("bg-indigo-500", totalPercent <= 100);

  const remaining = remainingBudget - totalAmount;
  document.getElementById("remainingBudget").innerText = formatPeso(remaining);
}
</script>
