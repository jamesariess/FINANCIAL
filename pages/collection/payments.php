<?php include __DIR__ ."/../sidebar.html"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>payment </title>

    
</head>

<body> 
<div class="content" id="mainContent">
  <div class="header">
    <div class="hamburger" id="hamburger">☰</div> <h1>Payment <Span class="system-title">'| Collection</Span></h1>
  
 

<div class="theme-toggle-container">
  <span class="theme-label">Dark Mode</span>
  <label class="theme-switch">
    <input type="checkbox"  id="themeToggle">
    <span class="slider"></span>
  </label>
</div>
</div>
<?php include __DIR__. '/../../filtering/collection/paymentfilter.html';?>
<br>
<?php include __DIR__ . '/../../modal/collection/payment.html'; ?>
    <?php include __DIR__ . '/../../table/collection/paymentyable.html';?>
  

</div>
<script src="<?php echo '/financial/static/js/filter.js';?>"></script>
<script src="<?php echo '/financial/static/js/modal.js'; ?>"></script>
</body>

</html>