<script>
    const accounts = <?php echo json_encode($accounts); ?>;
</script>

<div id="allocationError" class="mb-4 hidden bg-red-200 text-red-800 p-3 rounded"></div>

<div class=" p-6 rounded-2xl shadow-md form-group">
  <h1 class="text-2xl font-bold mb-4 text-indigo-700">Department Cost Allocation</h1>

  <form method="POST" id="allocationForm">
    <div class="grid grid-cols-2 gap-4 mb-6 ">
      <div >
        <label class="block  mb-1">Select Department</label>
        <select id="department" name="department" class="w-full p-2 border rounded">
          <option value="">-- Choose Department --</option>
          <?php foreach ($departments as $d): ?>
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
        <label class="block  mb-1">Year</label>
        <select id="year" name="year" class="w-full p-2 border rounded">
          <option value="">-- Choose Year --</option>
        </select>
      </div>
    </div>

    <div id="budgetInfo" class="mb-6 hidden  p-4 rounded border">
      <p class="">Yearly Budget: <span id="yearlyBudget" class="font-bold text-indigo-600"></span></p>
      <p class="">Remaining Budget: <span id="remainingBudget" class="font-bold text-green-600"></span></p>
    </div>

    <div class="mt-6">
      <div class="flex justify-between text-sm text-gray-600 mb-1">
        <span>Total Percentage</span>
        <span id="totalPercent">0%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
        <div id="progressBar" class="bg-indigo-500 h-3 rounded-full w-0"></div>
      </div>

      <div id="allocationRows" class="space-y-4"></div>
  
      <button type="button" id="addAllocationBtn" onclick="addRow()" 
        class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600" disabled>+ Add Allocation</button>
    </div>

    <button type="submit" 
      class="mt-6 px-6 py-3 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700">Submit Allocation</button>
  </form>
</div>

<script>
let yearlyBudget = 0;
let remainingBudget = 0;
let rowIndex = 0;
let excludedAccountsFromDB = [];
let restrictedAccountsFromDB = [];

function formatPeso(value) {
  return "₱" + Number(value).toLocaleString();
}

function areDepartmentAndYearSelected() {
  const deptSelect = document.getElementById("department");
  const yearSelect = document.getElementById("year");
  return deptSelect.value && yearSelect.value;
}

function updateFormControls() {
  const addBtn = document.getElementById("addAllocationBtn");
  const accountSelects = document.querySelectorAll('.account-select');
  const isValid = areDepartmentAndYearSelected();
  
  addBtn.disabled = !isValid;
  accountSelects.forEach(select => {
    select.disabled = !isValid;
    if (!isValid) {
      select.value = "";
      const percentInput = select.closest('div').querySelector('.percentage');
      if (percentInput) {
        percentInput.value = "";
        recalculate(percentInput);
      }
    }
  }); 
}

document.getElementById("department").addEventListener("change", function() {
  const option = this.options[this.selectedIndex];
  yearlyBudget = parseFloat(option.dataset.amount || 0);
  remainingBudget = yearlyBudget - parseFloat(option.dataset.used || 0);

  const yearSelect = document.getElementById("year");
  yearSelect.innerHTML = "<option value=''>-- Choose Year --</option>";

  const deptbudget = option.value;
  const deptname = option.textContent.split(' (')[0].trim();
  console.log("Selected at " + new Date().toLocaleString() + ": Deptbudget:", deptbudget, "Deptname:", deptname);

  if (deptname) {
    fetch(`../../crud/budget/allocation.php?deptname=${encodeURIComponent(deptname)}`)
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status} - ${res.statusText}`);
        }
        return res.json();
      })
      .then(years => {
        console.log("Fetched years at " + new Date().toLocaleString() + ": ", years);
        yearSelect.innerHTML = "<option value=''>-- Choose Year --</option>";
        if (years.length > 0) {
          years.forEach(y => {
            const opt = document.createElement("option");
            opt.value = y;
            opt.text = y;
            yearSelect.appendChild(opt);
          });
        } else {
          console.log("No years found for this department at " + new Date().toLocaleString());
          const opt = document.createElement("option");
          opt.value = "";
          opt.text = "No years available";
          opt.disabled = true;
          yearSelect.appendChild(opt);
        }
        updateFormControls();
      })
      .catch(error => {
        console.error('Error fetching years at ' + new Date().toLocaleString() + ':', error);
        alert('Failed to load years for the selected department. Check console for details at ' + new Date().toLocaleString());
        updateFormControls();
      });
  } else {
    console.log("No department name found at " + new Date().toLocaleString());
    updateFormControls();
  }

  updateBudgetInfo();
});

document.getElementById("year").addEventListener("change", function() {
  const deptSelect = document.getElementById("department");
  const deptname = deptSelect.options[deptSelect.selectedIndex].text.split(' (')[0].trim();
  const year = this.value;

  if (deptname && year) {
    fetch(`../../crud/budget/allocation.php?deptname=${encodeURIComponent(deptname)}&year=${year}`)
      .then(res => {
        if (!res.ok) {
          throw new Error(`HTTP error! status: ${res.status} - ${res.statusText}`);
        }
        return res.json();
      })
      .then(data => {
        console.log("Fetched budget data at " + new Date().toLocaleString() + ": ", data);
        yearlyBudget = data.yearlyBudget;
        remainingBudget = data.remainingBudget;
        excludedAccountsFromDB = data.existing_accounts || [];
        restrictedAccountsFromDB = data.restricted_accounts || [];
        updateBudgetInfo();
        repopulateAllSelects();
        updateFormControls();
      })
      .catch(error => {
        console.error('Error fetching budget data at ' + new Date().toLocaleString() + ':', error);
        alert('Failed to load budget data for the selected year. Check console for details at ' + new Date().toLocaleString());
        yearlyBudget = 0;
        remainingBudget = 0;
        excludedAccountsFromDB = [];
        restrictedAccountsFromDB = [];
        updateBudgetInfo();
        repopulateAllSelects();
        updateFormControls();
      });
  } else {
    yearlyBudget = 0;
    remainingBudget = 0;
    excludedAccountsFromDB = [];
    restrictedAccountsFromDB = [];
    updateBudgetInfo();
    repopulateAllSelects();
    updateFormControls();
  }
});

function updateBudgetInfo() {
  const budgetInfo = document.getElementById("budgetInfo");
  if (remainingBudget > 0) {
    budgetInfo.classList.remove("hidden");
    document.getElementById("yearlyBudget").innerText = formatPeso(yearlyBudget);
    document.getElementById("remainingBudget").innerText = formatPeso(remainingBudget);
  } else {
    budgetInfo.classList.add("hidden");
  }
  updateTotal();
  updateFormControls();
}

function addRow() {
  if (!areDepartmentAndYearSelected()) {
    return;
  }
  const container = document.getElementById("allocationRows");
  const row = document.createElement("div");
  row.className = "grid grid-cols-12 gap-2 items-center  p-3 rounded border";
  row.innerHTML = `
    <select name="allocations[${rowIndex}][accountID]" class="col-span-3 p-2 border rounded account-select" onchange="updateAccountSelections(this)">
      <option value="">-- Select Account --</option>
    </select>
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
  const newSelect = row.querySelector('.account-select');
  populateAccounts(newSelect);
  updateAccountSelections(newSelect);
  updateFormControls();
  rowIndex++;
}

function isAccountAllocatedToDept(accountID, deptname) {
  if (!deptname) return Promise.resolve(false);
  
  return fetch(`../../crud/budget/allocation.php?check_allocation=true&accountID=${accountID}&deptname=${encodeURIComponent(deptname)}`)
    .then(res => {
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status} - ${res.statusText}`);
      }
      return res.json();
    })
    .then(data => data.isAllocated)
    .catch(error => {
      console.error('Error checking account allocation:', error);
      return false;
    });
}

function populateAccounts(selectElement) {
  selectElement.innerHTML = '<option value="">-- Select Account --</option>';
  const deptSelect = document.getElementById("department");
  const deptname = deptSelect.options[deptSelect.selectedIndex]?.text.split(' (')[0].trim();
  
  Promise.all(accounts.map(account => {
    return isAccountAllocatedToDept(account.accountID, deptname).then(isAllocated => ({
      account,
      isAllocated
    }));
  })).then(results => {
    results.forEach(({ account, isAllocated }) => {
      const isRestricted = restrictedAccountsFromDB.includes(account.accountID);
      const isExcluded = excludedAccountsFromDB.includes(account.accountID);
      const isValidType = account.accounType === 'Expenses' || account.accounType === 'Assets';
      
      if (isValidType && !isExcluded && (!isRestricted || isAllocated)) {
        const opt = document.createElement("option");
        opt.value = account.accountID;
        opt.text = account.accountName;
        selectElement.appendChild(opt);
      }
    });
  }).catch(error => {
    console.error('Error populating accounts:', error);
  });
}

function repopulateAllSelects() {
  const allSelects = document.querySelectorAll('.account-select');
  allSelects.forEach(select => {
    const currentValue = select.value;
    populateAccounts(select);
    if (currentValue) {
      const option = Array.from(select.options).find(opt => opt.value === currentValue);
      if (option) {
        select.value = currentValue;
      } else {
        select.value = "";
        console.log('Previously selected account is now excluded or restricted.');
        const percentInput = select.closest('div').querySelector('.percentage');
        if (percentInput) {
          percentInput.value = "";
          recalculate(percentInput);
        }
      }
    }
  });
  updateAccountSelections();
  updateFormControls();
}

function updateAccountSelections(changedSelect = null) {
  const allSelects = document.querySelectorAll('.account-select');
  const deptSelect = document.getElementById("department");
  const deptname = deptSelect.options[deptSelect.selectedIndex]?.text.split(' (')[0].trim();

  allSelects.forEach(select => {
    if (select.options.length <= 1) {
      populateAccounts(select);
    }
  });

  Promise.all(Array.from(allSelects).map(select => {
    const currentValue = select.value;
    const excludedValues = new Set();

    allSelects.forEach(otherSelect => {
      if (otherSelect !== select && otherSelect.value) {
        excludedValues.add(otherSelect.value);
      }
    });

    return Promise.all(Array.from(select.options).map(option => {
      if (!option.value) return Promise.resolve({ option, disabled: !areDepartmentAndYearSelected() });
      
      const isRestricted = restrictedAccountsFromDB.includes(option.value);
      const isExcluded = excludedAccountsFromDB.includes(option.value);
      
      if (isRestricted && !isExcluded) {
        return isAccountAllocatedToDept(option.value, deptname).then(isAllocated => ({
          option,
          disabled: excludedValues.has(option.value) || isExcluded || (isRestricted && !isAllocated)
        }));
      }
      return Promise.resolve({
        option,
        disabled: excludedValues.has(option.value) || isExcluded
      });
    })).then(options => {
      options.forEach(({ option, disabled }) => {
        option.disabled = disabled;
      });

      if (changedSelect === select && currentValue && Array.from(select.options).find(opt => opt.value === currentValue)?.disabled) {
        select.value = "";
        const percentInput = select.closest('div').querySelector('.percentage');
        if (percentInput) {
          recalculate(percentInput);
        }
        alert('This account is already selected or restricted. Please choose a different one.');
      }
    });
  })).then(() => {
    updateFormControls();
  });
}

function removeRow(btn) {
  const rowToRemove = btn.parentElement;
  const selectToRemove = rowToRemove.querySelector('.account-select');
  const removedValue = selectToRemove.value;
  rowToRemove.remove();
  updateAccountSelections();
  updateTotal();
  updateFormControls();
  rowIndex--;
}

function recalculate(input) {
  let percent = parseFloat(input.value) || 0;

  const totalPercent = Array.from(document.querySelectorAll('.percentage'))
    .reduce((sum, p) => sum + (parseFloat(p.value) || 0), 0);

  const errorDiv = document.getElementById("allocationError");
  if (totalPercent > 100) {
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
  amounts.forEach(a => totalAmount += parseFloat(a.value.replace(/,/g, "")) || 0);

  document.getElementById("totalPercent").innerText = totalPercent.toFixed(2) + "%";
  document.getElementById("progressBar").style.width = totalPercent + "%";
  document.getElementById("progressBar").classList.toggle("bg-red-500", totalPercent > 100);
  document.getElementById("progressBar").classList.toggle("bg-indigo-500", totalPercent <= 100);

  const remaining = remainingBudget - totalAmount;
  document.getElementById("remainingBudget").innerText = formatPeso(remaining < 0 ? 0 : remaining);
}
</script>