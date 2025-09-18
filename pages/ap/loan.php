<?php include __DIR__ . "/../sidebar.html"; ?>
<?php include __DIR__ . '/../../crud/ap/loan.php'; ?>
<?php include __DIR__ . '/../../crud/ap/loan2.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AR Adjustment</title>
    <link rel="stylesheet" href="/static/css/sidebar.css">
</head>

<body> 
<div class="content" id="mainContent">
    <div class="header">
        <div class="hamburger" id="hamburger">☰</div>
        <div>
            <h1>Loan Request<span class="system-title">| (Financial)</span></h1>
        </div>
        <div class="theme-toggle-container">
            <span class="theme-label">Dark Mode</span>
            <label class="theme-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>
    <?php include __DIR__ . '/../../cards/ap/loan.php'; ?>
    
    
    <?php if ($success): ?>
        <div class="container mx-auto mt-6">
            <div id="notificationContainer" class="mx-auto mt-6"></div>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-6 rounded-lg shadow-md" role="alert">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 mr-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-lg font-semibold"><?php echo htmlspecialchars($message); ?></span>
                    </div>
                    <button type="button" class="text-green-700 hover:text-green-900" onclick="this.parentElement.parentElement.style.display='none';">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>
        </div>
        <script>
        
            setTimeout(() => {
                document.querySelector('.bg-green-100').style.display = 'none';
            }, 5000);
        </script>
    <?php endif; ?>
    <br>
    <?php include __DIR__ . '/../../contents/ap/vendor.php'; ?>    <br>
    <?php include __DIR__ . '/../../modal/ap/loan.html'; ?>    
</div>
<script src="<?php echo '../../static/js/filter.js'; ?>"></script>
<script src="<?php echo '../../static/js/modal.js'; ?>"></script>
</body>
</html>