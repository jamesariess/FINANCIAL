
      <?php include __DIR__ . '/../../crud/budget/budget.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Allocation </title>
 <style>

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 999;
        }

        .overlay.show {
            display: block;
        }
     
        .dropdown.active .dropdown-menu {
            display: block;
        }
    </style>
</head>
<body>
  <?php include __DIR__ . "/../sidebar.html"; ?>  
<div class="overlay" id="overlay"></div>
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

    <?php include __DIR__ . '/../../cards/budget/budget.php'; ?>
    <?php include __DIR__ . '/../../ai/budget/budget.php'; ?>
  <?php include __DIR__ . '/../../contents/budget/budget.php'; ?>
<br>

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