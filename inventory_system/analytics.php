<?php
session_start();
require_once 'includes/auth.php'; // Include the auth file
require_once 'includes/helpers.php';

// Restrict access to Admins only
if (!isset($_SESSION['RoleID']) || $_SESSION['RoleID'] != 1) {
    header("Location: modules/sales.php"); // Redirect non-admins to the sales page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/analytics.css">
    <link rel="stylesheet" href="assets/css/index.css"> <!-- Add this for consistent text styles -->
    <link rel="stylesheet" href="assets/css/navbar.css">
   
   
</head>
<body>
    <div class="d-flex">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark flex-column vh-100">
            <a href="index.php" class="navbar-brand text-center py-3">Dashboard</a>
            <ul class="navbar-nav flex-column">
                <li class="nav-item">
                    <a href="modules/products.php" class="nav-link ">Manage Products</a>
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
                    <a href="analytics.html" class="nav-link active">Analytics Dashboard</a>
                </li>
                <li class="nav-item">
        <a href="modules/supplier_product.php" class="nav-link">Supplier-Product View</a>
    </li>
                <li class="nav-item">
                    <a href="modules/register.php" class="nav-link">Register User</a>
                </li>
                <li class="nav-item">
                    <a href="templates/logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid p-4">
            <h1 class="text-center mb-4">Analytics Dashboard</h1>

            <h2>Supplier Product Count</h2>
            <table id="productCountTable" class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Supplier Name</th>
                        <th>Product Count</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated here -->
                </tbody>
            </table>

            <h2>Supplier Revenue</h2>
            <table id="revenueTable" class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Supplier Name</th>
                        <th>Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be populated here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Fetch analytics data from the backend
        fetch('modules/analytics.php?action=all_analytics')
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            console.error('Error:', data.error);
            alert('Failed to load analytics data.');
            return;
        }

        // Populate Supplier Product Count Table
        const productCountTable = document.getElementById('productCountTable').querySelector('tbody');
        if (data.supplier_product_count.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="2">No data available</td>`;
            productCountTable.appendChild(tr);
        } else {
            data.supplier_product_count.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${row.SupplierName}</td><td>${row.ProductCount}</td>`;
                productCountTable.appendChild(tr);
            });
        }

        // Populate Supplier Revenue Table
        const revenueTable = document.getElementById('revenueTable').querySelector('tbody');
        if (data.supplier_revenue.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="2">No data available</td>`;
            revenueTable.appendChild(tr);
        } else {
            data.supplier_revenue.forEach(row => {
                const tr = document.createElement('tr');
                const totalRevenue = row.TotalRevenue !== null ? row.TotalRevenue : '0.00';
                tr.innerHTML = `<td>${row.SupplierName}</td><td>${totalRevenue}</td>`;
                revenueTable.appendChild(tr);
            });
        }
    })
    .catch(error => {
        console.error('Error fetching analytics:', error);
        alert('Failed to load analytics data.');
    });
    </script>
</body>
</html>