<?php include __DIR__ . "/../sidebar.html"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Plan </title>
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="../../static/css/sidebar.css">
    <link rel="stylesheet" href="../../static/css/filter.css">
</head>
<body>
  <?php include __DIR__ . "/../sidebar.html"; ?>  
<div class="overlay" id="overlay"></div>
<div class="content" id="mainContent">
    <div class="header">
        <div class="hamburger" id="hamburger">☰</div>
        <div>
            <h1>Collection  <span class="system-title">| (Collection Planning)</span></h1>
        </div>
        <div class="theme-toggle-container">
            <span class="theme-label">Dark Mode</span>
            <label class="theme-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>
  
    <?php include __DIR__ . '/../../filtering/collection/planfilter.html';?>
      <?php include __DIR__ . '/../../modal/disbursement/disbursement.html'; ?>
    <br>
    <?php include __DIR__ . '/../../modal/collection/collectionplanmodal.html'; ?>
    <?php include __DIR__ . '/../../table/collection/collectiontable.html';?>

</div>
<script src="<?php echo '../../static/js/filter.js';?>"></script>
<script src="<?php echo '../../static/js/filter.js';?>"></script>
<script>
      const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('change', function() {
      document.body.classList.toggle('dark-mode', this.checked);
    });
const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const hamburger = document.getElementById('hamburger');
const overlay = document.getElementById('overlay');

// Sidebar toggle logic
hamburger.addEventListener('click', function() {
  if (window.innerWidth <= 992) {
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
  } else {
    // This is the key change for desktop
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded'); 
  }
});

// Close sidebar on overlay click
overlay.addEventListener('click', function() {
  sidebar.classList.remove('show');
  overlay.classList.remove('show');
});


    // Dropdown toggle logic
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(event) {
            event.preventDefault();
            const parentDropdown = this.closest('.dropdown');
            parentDropdown.classList.toggle('active');
        });
    });

</script>

</body>

</html>