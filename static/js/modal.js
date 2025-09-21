   // Theme toggle
    document.getElementById('themeToggle').addEventListener('change', function() {
      document.body.classList.toggle('dark-mode', this.checked);
    });

    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const hamburger = document.getElementById('hamburger');
    const overlay = document.getElementById('overlay');

    // Sidebar toggle
    hamburger.addEventListener('click', function() {
      if (window.innerWidth <= 992) {
        // Mobile: slide in
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
      } else {
        // Desktop: collapse
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
      }
    });

    // Close sidebar when clicking overlay
    overlay.addEventListener('click', function() {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
    });

    // Form toggle
    document.getElementById('toggleFormBtn').addEventListener('click', function() {
      const formSection = document.getElementById('employeeFormSection');
      formSection.style.display = formSection.style.display === 'block' ? 'none' : 'block';
    });

    document.getElementById('cancelBtn').addEventListener('click', function() {
      document.getElementById('employeeFormSection').style.display = 'none';
    });