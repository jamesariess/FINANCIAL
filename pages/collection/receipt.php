<?php include __DIR__ . '/../../crud/collection/receipt.php';?>
<?php include __DIR__ . "/../sidebar.html"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reciept </title>
    <link rel="stylesheet" href="/static/css/sidebar.css">
    
</head>

<body> 
<div class="content" id="mainContent">
    <div class="header">
        <div class="hamburger" id="hamburger">☰</div>
        <div>
            <h1>Receipt<span class="system-title">| (Collection)</span></h1>
        </div>
        <div class="theme-toggle-container">
            <span class="theme-label">Dark Mode</span>
            <label class="theme-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>
   <?php include __DIR__ . '/../../filtering/collection/receipt.html'; ?>
   <br>
    <?php include __DIR__ . '/../../modal/disbursement/disbursement.html'; ?>  
  
    <?php include __DIR__ . '/../../table/collection/receipttable.html';?>

</div>
<script src="<?php echo '../../static/js/filter.js';?>"></script>
<script src="<?php echo '../../static/js/modal.js'; ?>"></script>


</body>

</html>