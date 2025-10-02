
<?php $baseURL = '../../pages/'; ?>

     <div class="sidebar" id="sidebar">
    <div class="logo">
<img src="../../image/logo.png" alt="SLATE Logo">
    </div>
    <div class="system-name">Financial</div>
    <a href="<?php echo $baseURL; ?>dashboard/dashboard.php" class="sidebar-item active">Dashboard</a>


    <!-- disbursement to bosssing -->
   <div class="dropdown">
  <a href="#" class="sidebar-item dropdown-toggle">
    Disbursement
    <span class="arrow">▶</span>
  </a>
  <div class="dropdown-menu">
    <a href="<?php echo $baseURL; ?>disbursement/approver.php" class="sidebar-item">Payment Release</a>
    <a href="<?php echo $baseURL; ?>disbursement/disbursement.php" class="sidebar-item">Request Reports</a>
    <!-- <a href="<?php echo $baseURL; ?>disbursement/paymentmethod.php" class="sidebar-item">Request Loan </a> -->
    
  </div>
 </div>

     <!--   Collection -->
 <div class="dropdown">
  <a href="#" class="sidebar-item dropdown-toggle">
    Collection
    <span class="arrow">▶</span>
  </a>
  <div class="dropdown-menu">

    
    <a href="<?php echo $baseURL; ?>collection/collectionplan.php" class="sidebar-item">Collection Plans</a>
    
   
    <a href="<?php echo $baseURL; ?>collection/receipt.php" class="sidebar-item">Receipts</a>
    <a href="<?php echo $baseURL; ?>collection/reminders.php" class="sidebar-item">Follow-ups & Reminders</a>
     <a href="<?php echo $baseURL; ?>collection/adjustment.php" class="sidebar-item">Reminders Report</a>
    <!-- <a href=" <?php echo $baseURL; ?>agingreport" class="sidebar-item">Aging Report</a> -->
   
  </div>
 </div>



     <!-- General Ledger to bosssing -->
 <div class="dropdown">
  <a href="#" class="sidebar-item dropdown-toggle">
    General Ledger
    <span class="arrow">▶</span>
  </a>
  <div class="dropdown-menu">
    <a href="<?php echo $baseURL; ?>general ledger/chartofaccout.php" class="sidebar-item">Chart of Accounts</a>
    <a href="<?php echo $baseURL; ?>general ledger/journalDetails.php" class="sidebar-item">Journal Details</a>
    <a href="<?php echo $baseURL; ?>general ledger/journalentries.php" class="sidebar-item">Journal Entries</a>

    <a href="<?php echo $baseURL; ?>general ledger/ledgerbalances.php" class="sidebar-item">Account Ledger</a>
     <a href="<?php echo $baseURL; ?>general ledger/trialbalnce.php" class="sidebar-item">Cash Management</a>
  </div>
 </div>

      <!-- Budgetmanagement to bosssing -->
 <div class="dropdown">
  <a href="#" class="sidebar-item dropdown-toggle">
    Budget Management
    <span class="arrow">▶</span>
  </a>
  <div class="dropdown-menu"> 
    <a href="<?php echo $baseURL; ?>budget/budgetmonitoring.php" class="sidebar-item">Budget Monitoring</a>
    <a href="<?php echo $baseURL; ?>budget/budget.php" class="sidebar-item">Budget</a>
    <a href="<?php echo $baseURL; ?>budget/budgetplanning.php" class="sidebar-item">Budget Approval</a>
    <a href="<?php echo $baseURL; ?>budget/budgetallocation.php" class="sidebar-item">Cost Allocation</a>
    <a href="<?php echo $baseURL; ?>budget/budgetadjustment.php" class="sidebar-item">Allocation Adjustments</a>
   
  </div>
 </div>


       <!-- Budgetmanagement to bosssing -->



      <!-- Ap to bosssing -->
 <div class="dropdown">
  <a href="#" class="sidebar-item dropdown-toggle">
   Accounts Payable
    <span class="arrow">▶</span>
  </a>
  <div class="dropdown-menu">
    <a href="<?php echo $baseURL; ?>ap/vendor.php" class="sidebar-item">Company Management</a>
     <a href="<?php echo $baseURL; ?>ap/loan.php" class="sidebar-item">Loan Management</a>
    <a href="<?php echo $baseURL;?>ap/ap_ment.php" class="sidebar-item">Payment</a>
       <!-- <a href="<?php echo $baseURL;?>ap/ap_adjustment.php" class="sidebar-item">Adjustment</a> -->
    <!-- <a href="#" class="sidebar-item">Approval Workflow</a>
    <a href="#" class="sidebar-item">Vendor Statements / Reconciliation</a>
        <a href="#" class="sidebar-item">Credit/Debit Notes</a> -->
  </div>
 </div>


       <!-- Ar to bosssing -->
 <div class="dropdown">
  <a href="#" class="sidebar-item dropdown-toggle">
   Accounts Receivable
    <span class="arrow">▶</span>
  </a>
  <div class="dropdown-menu">
    <a href="<?php echo $baseURL; ?>ar/custumer.php" class="sidebar-item">Customer Details</a>
    <a href="<?php echo $baseURL; ?>ar/invoice.php" class="sidebar-item">Billing & Invoice </a>
    <a href="<?php echo $baseURL;?>ar/payment.php" class="sidebar-item">Payment Recording</a>
    <!-- <a href="<?php echo $baseURL ;?>ar/adjustment.php" class="sidebar-item">Invoice Adjustments </a>
    <a href="#" class="sidebar-item">AR Aging & Reports</a>
    <a href="#" class="sidebar-item">Customer Statements / Reconciliation</a> -->
  </div>
 </div>
 <div class="dropdown">
  <a href="#" class="sidebar-item dropdown-toggle">
   Dummy Data
    <span class="arrow">▶</span>
  </a>
  <div class="dropdown-menu"> 
    <a href="<?php echo $baseURL; ?>sample/custumer.php" class="sidebar-item">Invoice</a>
    <a href="<?php echo $baseURL; ?>sample/request.php" class="sidebar-item">Request</a>

   
  </div>
 </div>

      <!-- <a href="#" class="sidebar-item admin-feature">System Settings</a>
            <a href="#" class="sidebar-item admin-feature">User Permissions</a>
            <a href="#" class="sidebar-item">Reports</a> -->
            <a href="../login.php" class="sidebar-item">Logout</a>
  </div>
      <div class="overlay" id="overlay"></div>
     <div class="content" id="mainContent">
    <div class="header">
        <div class="hamburger" id="hamburger">☰</div>
        <div>
           
        </div>
        <div class="theme-toggle-container">
            <span class="theme-label">Dark Mode</span>
            <label class="theme-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>

<?php if (!empty($successMessage)): ?>
    <div id="successAlert" class="mt-4 w-full bg-green-100 border-l-4 border-green-500 p-4 rounded-md shadow-md flex justify-between items-center mb-4">
      <p class="text-green-600 font-medium text-lg px-4"><?php echo htmlspecialchars($successMessage); ?></p>
      <button onclick="document.getElementById('successAlert').style.display='none';" class="text-green-600 hover:text-green-800 text-xl p-2">&times;</button>
    </div>
    <script>
      setTimeout(() => document.getElementById('successAlert').style.display = 'none', 5000);
    </script>
  <?php endif; ?>

  <?php if (!empty($errorMessage)): ?>
    <div id="errorAlert" class="mt-4 w-full bg-red-100 border-l-4 border-red-500 p-4 rounded-md shadow-md flex justify-between items-center mb-4">
      <p class="text-red-600 font-medium text-lg px-4"><?php echo htmlspecialchars($errorMessage); ?></p>
      <button onclick="document.getElementById('errorAlert').style.display='none';" class="text-red-600 hover:text-red-800 text-xl p-2">&times;</button>
    </div>
    <script>
      setTimeout(() => document.getElementById('errorAlert').style.display = 'none', 5000);
    </script>
  <?php endif; ?>
    <br>