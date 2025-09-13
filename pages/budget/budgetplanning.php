<?php include __DIR__ . "/../sidebar.html"; ?>
<?php include __DIR__ . '/../../crud/budget/planning.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Planning </title>
    <link rel="stylesheet" href="/static/css/sidebar.css">
    
</head>

<body> 
<div class="content" id="mainContent">
    <div class="header">
        <div class="hamburger" id="hamburger">☰</div>
        <div>
            <h1>Disbursement Dashboard <span class="system-title">| (NAME OF DEPARTMENT)</span></h1>
        </div>
        <div class="theme-toggle-container">
            <span class="theme-label">Dark Mode</span>
            <label class="theme-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>

     <?php include __DIR__ . '/../../contents/budget/planning.php'; ?>
     <?php include __DIR__ . '/../../filtering/budget/planning.html'; ?>
      <?php include __DIR__ . '/../../modal/disbursement/disbursement.html'; ?>
   
<br>
    <?php include __DIR__ . '/../../modal/budgetmanage/planningmodal.html'; ?>
    <?php include __DIR__ . '/../../table/budgetmanagement.html/planningtable.html';?>

</div>
<script src="<?php echo '/financial/static/js/filter.js';?>"></script>
<script src="<?php echo '/financial/static/js/modal.js'; ?>"></script>


</body>

</html>