// Theme toggle logic
   let isDarkMode = false;

function getChartColors(isDark) {
    if (isDark) {
        return {
            textColor: '#f8fafc',   // white text
            gridColor: '#475569',   // slate-600
            revenueColor: '#38bdf8', // cyan-400
            expensesColor: '#f87171' // red-400
        };
    } else {
        return {
            textColor: '#334155',   // slate-800
            gridColor: '#cbd5e1',   // slate-300
            revenueColor: '#0ea5e9', // sky-500
            expensesColor: '#ef4444' // red-500
        };
    }
}

let colors = getChartColors(isDarkMode);

   const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('change', function() {
      document.body.classList.toggle('dark-mode', this.checked);
      // Update chart colors when theme changes
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

    // Function to get CSS variable value
    function getCssVariable(name) {
        return getComputedStyle(document.body).getPropertyValue(name).trim();
    }

const ctx = document.getElementById('financialChart').getContext('2d');
const financialChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: 'Revenue',
            data: <?php echo json_encode($chartRevenue); ?>,
            borderColor: colors.revenueColor,
            tension: 0.3,
            fill: false,
        }, {
            label: 'Expenses',
            data: <?php echo json_encode($chartExpenses); ?>,
            borderColor: colors.expensesColor,
            tension: 0.3,
            fill: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: colors.textColor
                }
            }
        },
        scales: {
            x: {
                ticks: { color: colors.textColor },
                grid: { color: colors.gridColor }
            },
            y: {
                ticks: { color: colors.textColor },
                grid: { color: colors.gridColor }
            }
        }
    }
});

    
    // Function to update chart colors
themeToggle.addEventListener('change', function() {
    document.body.classList.toggle('dark-mode', this.checked);

    isDarkMode = this.checked;
    colors = getChartColors(isDarkMode);

    // Update chart colors
    financialChart.options.plugins.legend.labels.color = colors.textColor;
    financialChart.options.scales.x.ticks.color = colors.textColor;
    financialChart.options.scales.x.grid.color = colors.gridColor;
    financialChart.options.scales.y.ticks.color = colors.textColor;
    financialChart.options.scales.y.grid.color = colors.gridColor;

    financialChart.data.datasets[0].borderColor = colors.revenueColor;
    financialChart.data.datasets[1].borderColor = colors.expensesColor;

    financialChart.update();
});


    window.onload = createChart;



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

