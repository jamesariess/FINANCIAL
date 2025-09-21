<?php include __DIR__ . '/../../crud/ar/collection.php';?>
<?php include __DIR__ . "/../sidebar.html"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AR Payment </title>
    <link rel="stylesheet" href="/static/css/sidebar.css">
     
</head>

<body> 
<div class="content" id="mainContent">
    <div class="header">
        <div class="hamburger" id="hamburger">☰</div>
        <div>
            <h1>AR Payment Collection<span class="system-title">| (Financial)</span></h1>
        </div>
        <div class="theme-toggle-container">
            <span class="theme-label">Dark Mode</span>
            <label class="theme-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>
<?php include __DIR__ . '/../../filtering/ar/collection.html'; ?>
<br>
    <?php include __DIR__ . '/../../modal/ar/collection.html'; ?>
    <?php include __DIR__ . '/../../table/ar/collection.php';?>

</div>
<script src="<?php echo '../../static/js/filter.js';?>"></script>
<script src="<?php echo '../../static/js/modal.js'; ?>"></script>



</body>

</html>