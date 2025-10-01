


<script>
        const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('change', function() {
      document.body.classList.toggle('dark-mode', this.checked);
       updateChartColors();
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