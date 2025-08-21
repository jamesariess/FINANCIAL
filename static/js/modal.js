 document.getElementById('themeToggle').addEventListener('change', function() {
            document.body.classList.toggle('dark-mode', this.checked);
        });

        document.getElementById('hamburger').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('mainContent').classList.toggle('expanded');
        });



     
        document.querySelectorAll('.dropdown-toggle').forEach(item => {
            item.addEventListener('click', event => {
                event.preventDefault();
                event.stopPropagation();
                
                const parentDropdown = item.closest('.dropdown');
                const dropdownMenu = parentDropdown.querySelector('.dropdown-menu');
                
         
                dropdownMenu.classList.toggle('show');
                item.classList.toggle('active');

    
                const siblings = Array.from(parentDropdown.parentElement.children).filter(child => child !== parentDropdown);
                siblings.forEach(sibling => {
                    const siblingMenu = sibling.querySelector('.dropdown-menu');
                    const siblingToggle = sibling.querySelector('.dropdown-toggle');
                    if (siblingMenu) siblingMenu.classList.remove('show');
                    if (siblingToggle) siblingToggle.classList.remove('active');
                });
            });
        });