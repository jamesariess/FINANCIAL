
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

  
  </div>
  

