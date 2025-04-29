<!-- filepath: c:\xampp\htdocs\inventory_system\index.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

// Restrict access to Admin (RoleID = 1)
if ($_SESSION['RoleID'] != 1) {
    header("Location: modules/sales.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Analytics Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <!-- Add Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Left-Side Navbar -->
        <nav class="navbar navbar-expand-lg navbar-green bg-dark flex-column vh-100">
            <a href="index.php" class="navbar-brand text-center py-3">Dashboard</a>
            <ul class="navbar-nav flex-column">
                <?php if ($_SESSION['RoleID'] == 1): // Admin ?>
                    <li class="nav-item">
                        <a href="modules/products.php" class="nav-link">Manage Products</a>
                    </li>
                    <li class="nav-item">
                        <a href="modules/suppliers.php" class="nav-link">Manage Suppliers</a>
                    </li>
                    <li class="nav-item">
                        <a href="modules/stock.php" class="nav-link">Manage Stock</a>
                    </li>
                    <li class="nav-item">
                        <a href="modules/sales.php" class="nav-link">View Sales</a>
                    </li>
                    <li class="nav-item">
                        <a href="analytics.php" class="nav-link">Analytics Dashboard</a>
                    </li>
                    <li class="nav-item">
                    <a href="modules/supplier_product.php" class="nav-link">Supplier-Product View</a>
                </li>                    
                    <li class="nav-item">
                        <a href="modules/register.php" class="nav-link">Register User</a>
                    </li>
                <?php endif; ?>
                <?php if ($_SESSION['RoleID'] == 2): // Staff ?>
                    <li class="nav-item">
                        <a href="modules/sales.php" class="nav-link">View Sales</a>
                    </li>
                    <li class="nav-item">
                        <a href="modules/stock.php" class="nav-link">View Stock</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="templates/logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid p-4">
            <h1 class="text-center mb-4">Real-Time Analytics Dashboard</h1>


            <!-- Loading Indicator -->
            <div id="loading" class="text-center">Loading analytics data...</div>

            <!-- Analytics Charts -->
            <div id="analytics" style="display: none;">
                <div class="chart-container">
                    <canvas id="totalStockChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="salesRevenueChart"></canvas>
                </div>
                <div class="chart-container">
                    <canvas id="supplierPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Show loading indicator
            $('#loading').show();
            $('#analytics').hide();

            // Fetch all analytics data
            $.get('modules/analytics.php', { action: 'all_analytics' })
                .done(function (data) {
                    try {
                        const analytics = data;

                        // Hide loading indicator and show analytics
                        $('#loading').hide();
                        $('#analytics').show();

                        // Total Stock Levels Chart
                        const totalStock = analytics.total_stock || 0;
                        const ctx1 = document.getElementById('totalStockChart').getContext('2d');
                        new Chart(ctx1, {
                            type: 'bar',
                            data: {
                                labels: ['Total Stock'],
                                datasets: [{
                                    label: 'Stock Levels',
                                    data: [totalStock],
                                    backgroundColor: ['rgba(75, 192, 192, 0.2)'],
                                    borderColor: ['rgba(75, 192, 192, 1)'],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: {
                                    y: { beginAtZero: true }
                                }
                            }
                        });

                        // Sales Revenue Per Product Chart
                        const salesRevenue = analytics.sales_revenue || [];
                        const revenueLabels = salesRevenue.map(row => row.ProductName);
                        const revenueData = salesRevenue.map(row => parseFloat(row.TotalRevenue));
                        const ctx2 = document.getElementById('salesRevenueChart').getContext('2d');
                        new Chart(ctx2, {
                            type: 'pie',
                            data: {
                                labels: revenueLabels,
                                datasets: [{
                                    label: 'Sales Revenue',
                                    data: revenueData,
                                    backgroundColor: [
                                        'rgba(255, 99, 132, 0.2)',
                                        'rgba(54, 162, 235, 0.2)',
                                        'rgba(255, 206, 86, 0.2)',
                                        'rgba(75, 192, 192, 0.2)'
                                    ],
                                    borderColor: [
                                        'rgba(255, 99, 132, 1)',
                                        'rgba(54, 162, 235, 1)',
                                        'rgba(255, 206, 86, 1)',
                                        'rgba(75, 192, 192, 1)'
                                    ],
                                    borderWidth: 1
                                }]
                            }
                        });

                        // Supplier Performance Chart
                        const supplierPerformance = analytics.supplier_performance || [];
                        const supplierLabels = supplierPerformance.map(row => row.SupplierName);
                        const performanceData = supplierPerformance.map(row => row.Performance);
                        const ctx3 = document.getElementById('supplierPerformanceChart').getContext('2d');
                        new Chart(ctx3, {
                            type: 'line',
                            data: {
                                labels: supplierLabels,
                                datasets: [{
                                    label: 'Supplier Performance',
                                    data: performanceData,
                                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                                    borderColor: 'rgba(153, 102, 255, 1)',
                                    borderWidth: 1
                                }]
                            }
                        });
                    } catch (error) {
                        console.error("Invalid JSON response:", data);
                        alert("Failed to load analytics data. Please try again.");
                    }
                })
                .fail(function () {
                    $('#loading').text('Failed to load analytics data.');
                });
        });
    </script>
</body>
</html>