<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approver Management</title>
     <script src="https://cdn.jsdelivr.net/npm/lucide-react@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/static/css/sidebar.css">

    <style>
        .icon {
            width: 24px;
            height: 24px;
            color: currentColor;
        }
    </style>
</head>
<body class="bg-slate-950 flex min-h-screen text-slate-200">

<?php include __DIR__ . "/../sidebar.html"; ?>
<div class="content" id="mainContent">
    <div class="header">
        <div class="hamburger" id="hamburger">☰</div>
        <div>
            <h1>Approver Dashboard <span class="system-title">| (NAME OF DEPARTMENT)</span></h1>
        </div>
        <div class="theme-toggle-container">
            <span class="theme-label">Dark Mode</span>
            <label class="theme-switch">
                <input type="checkbox" id="themeToggle">
                <span class="slider"></span>
            </label>
        </div>
    </div>
 
        <div class="w-full max-w-7xl space-y-8">
            <header>
                <h1 class="text-3xl font-bold text-var(--text-light)">Financial Dashboard</h1>
                <p class="text-sm text-slate-400 mt-2">Welcome back! Here's a brief overview of your financial performance.</p>
            </header>

            <section>
                <h2 class="text-xl font-semibold mb-4 text-var(--text-light)">Quick Stats</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">

                    <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-blue-500">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-slate-400">Disbursement</h3>
                            <div class="p-2 rounded-full bg-blue-500/20 text-blue-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down icon"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-var(--text-light) mb-1">$45,231</p>
                        <p class="text-xs text-slate-500">View Data</p>
                    </div>

                    <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-red-500">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-slate-400">Accounts Payable</h3>
                            <div class="p-2 rounded-full bg-red-500/20 text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text icon"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><line x1="10" x2="8" y1="21" y2="21"/></svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-var(--text-light) mb-1">$12,450</p>
                        <p class="text-xs text-slate-500">View Data</p>
                    </div>

                    <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-yellow-500">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-slate-400">Accounts Receivable</h3>
                            <div class="p-2 rounded-full bg-yellow-500/20 text-yellow-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-receipt icon"><path d="M4 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v20l-2-2-2 2-2-2-2 2-2-2-2 2-2-2-2 2-2-2-2 2V2z"/></svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-var(--text-light) mb-1">$23,760</p>
                        <p class="text-xs text-slate-500">View Data</p>
                    </div>

                    <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-green-500">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-slate-400">Collection</h3>
                            <div class="p-2 rounded-full bg-green-500/20 text-green-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-coins icon"><path d="M9.8 19.95 2.15 15.8a1 1 0 0 1 0-1.6L9.8 10.05a1 1 0 0 1 1.4.15L18.4 14.8a1 1 0 0 1 0 1.6l-7.25 4.05a1 1 0 0 1-1.4-.15z"/><path d="m15 10-8.6 4.86a1 1 0 0 0 0 1.76L15 21l8.6-4.86a1 1 0 0 0 0-1.76L15 10z"/><path d="m7 7 8.6 4.86a1 1 0 0 0 0 1.76L7 18l-8.6-4.86a1 1 0 0 0 0-1.76L7 7z"/></svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-var(--text-light) mb-1">$50,112</p>
                        <p class="text-xs text-slate-500">View Data</p>
                    </div>
                    
                    <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-purple-500">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-slate-400">Budget Management</h3>
                            <div class="p-2 rounded-full bg-purple-500/20 text-purple-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pie-chart icon"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10Z"/></svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-var(--text-light) mb-1">85%</p>
                        <p class="text-xs text-slate-500">View Data</p>
                    </div>

                    <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 transition-all duration-300 hover:scale-105 hover:border-orange-500">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-slate-400">General Ledger</h3>
                            <div class="p-2 rounded-full bg-orange-500/20 text-orange-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-text icon"><path d="M2 11h20M12 2v20M2 15h20M2 19h20M2 7h20"/><path d="M2 3v18M22 3v18"/></svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-var(--text-light) mb-1">1.2M Entries</p>
                        <p class="text-xs text-slate-500">View Data</p>
                    </div>

                </div>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 h-80">
                        <h2 class="text-xl font-semibold mb-4 text-var(--text-light)">Financial Overview</h2>
                        <canvas id="financialChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                <div>
                    <div class="bg-slate-900 p-6 rounded-xl shadow-lg border border-slate-800 h-80 overflow-y-auto">
                        <h2 class="text-xl font-semibold mb-4 text-var(--text-light)">Recent Transactions</h2>
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-slate-800">
                                <tr>
                                    <th scope="col" class="py-3 px-4">Date</th>
                                    <th scope="col" class="py-3 px-4">Description</th>
                                    <th scope="col" class="py-3 px-4 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-slate-800">
                                    <td class="py-4 px-4 font-medium whitespace-nowrap">2024-09-01</td>
                                    <td class="py-4 px-4">Office Supplies</td>
                                    <td class="py-4 px-4 text-right text-red-400">-$250.00</td>
                                </tr>
                                <tr class="border-b border-slate-800">
                                    <td class="py-4 px-4 font-medium whitespace-nowrap">2024-08-30</td>
                                    <td class="py-4 px-4">Client Payment (Project Alpha)</td>
                                    <td class="py-4 px-4 text-right text-green-400">+$1,500.00</td>
                                </tr>
                                <tr class="border-b border-slate-800">
                                    <td class="py-4 px-4 font-medium whitespace-nowrap">2024-08-28</td>
                                    <td class="py-4 px-4">Software Subscription</td>
                                    <td class="py-4 px-4 text-right text-red-400">-$89.99</td>
                                </tr>
                                <tr class="border-b border-slate-800">
                                    <td class="py-4 px-4 font-medium whitespace-nowrap">2024-08-25</td>
                                    <td class="py-4 px-4">Consulting Fee</td>
                                    <td class="py-4 px-4 text-right text-green-400">+$2,000.00</td>
                                </tr>
                                <tr class="border-b border-slate-800">
                                    <td class="py-4 px-4 font-medium whitespace-nowrap">2024-08-22</td>
                                    <td class="py-4 px-4">Utility Bill</td>
                                    <td class="py-4 px-4 text-right text-red-400">-$150.75</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('financialChart').getContext('2d');
        const financialChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000, 19000, 13000, 17000, 15000, 22000],
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.3,
                    fill: false,
                }, {
                    label: 'Expenses',
                    data: [8000, 10000, 9000, 11000, 10500, 14000],
                    borderColor: 'rgb(255, 99, 132)',
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
                            color: '#e2e8f0' // Light text color for labels
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#94a3b8' // Slate 400 for x-axis labels
                        },
                        grid: {
                            color: '#334155' // Slate 700 for grid lines
                        }
                    },
                    y: {
                        ticks: {
                            color: '#94a3b8' // Slate 400 for y-axis labels
                        },
                        grid: {
                            color: '#334155' // Slate 700 for grid lines
                        }
                    }
                }
            }
        });
    </script>
    <script src="<?php echo '/financial/static/js/filter.js';?>"></script>
<script src="<?php echo '/financial/static/js/modal.js'; ?>"></script>
</body>
</html>