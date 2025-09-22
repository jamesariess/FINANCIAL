// Theme toggle logic
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('change', function() {
      document.body.classList.toggle('dark-mode', this.checked);
    });

    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const hamburger = document.getElementById('hamburger');
    const overlay = document.getElementById('overlay');


    hamburger.addEventListener('click', function() {
      if (window.innerWidth <= 992) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
      } else {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
      }
    });

  
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
