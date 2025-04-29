<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/messages.php';
require_once '../includes/helpers.php';
require_once '../includes/auth.php';

// Allow access to Admin (RoleID = 1) and Staff (RoleID = 2)
if ($_SESSION['RoleID'] != 1 && $_SESSION['RoleID'] != 2) {
    header("Location: ../index.php");
    exit;
}

// Pagination and search setup
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch paginated records
$result = fetchPaginatedRecords($pdo, 'sales', 'ProductID', $search, $limit, $offset);
$sales = $result['records'];
$totalRecords = $result['totalRecords'];
$totalPages = ceil($totalRecords / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>
    <div class="d-flex">
        <!-- Left-Side Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark flex-column vh-100">
            <a href="../index.php" class="navbar-brand text-center py-3">Dashboard</a>
            <ul class="navbar-nav flex-column">
                <li class="nav-item">
                    <a href="products.php" class="nav-link">Manage Products</a>
                </li>
                <li class="nav-item">
                    <a href="suppliers.php" class="nav-link">Manage Suppliers</a>
                </li>
                <li class="nav-item">
                    <a href="stock.php" class="nav-link">Manage Stock</a>
                </li>
                <li class="nav-item">
                    <a href="sales.php" class="nav-link active">View Sales</a>
                </li>
                <li class="nav-item">
                    <a href="../analytics.php" class="nav-link">Analytics Dashboard</a>
                </li>
                <li class="nav-item">
        <a href="supplier_product.php" class="nav-link">Supplier-Product View</a>
    </li>
                <li class="nav-item">
                    <a href="register.php" class="nav-link">Register User</a>
                </li>
                <li class="nav-item">
                    <a href="../templates/logout.php" class="nav-link">Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid p-4">
            <h1 class="text-center mb-4">Sales</h1>

            <!-- Display Messages -->
            <?php displayMessages(); ?>

            <!-- Search Form -->
            <form method="GET" class="d-flex mb-4" action="sales.php">
                <input type="text" class="form-control me-2" name="search" placeholder="Search by Product ID" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>

            <a href="add_sale.php" class="btn btn-success mb-3">Add New Sale</a>

            <table class="table table-striped table-hover table-bordered text-center">
                <thead class="table-primary">
                    <tr>
                        <th>Sale ID</th>
                        <th>Product ID</th>
                        <th>Quantity Sold</th>
                        <th>Sale Date</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['SaleID']); ?></td>
                            <td><?php echo htmlspecialchars($row['ProductID']); ?></td>
                            <td><?php echo htmlspecialchars($row['QuantitySold']); ?></td>
                            <td><?php echo htmlspecialchars($row['SaleDate']); ?></td>
                            <td><?php echo htmlspecialchars($row['TotalAmount']); ?></td>
                            <td>
                                <a href="edit_sale.php?id=<?php echo $row['SaleID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_sale.php?id=<?php echo $row['SaleID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this sale?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Pagination Links -->
            <?php echo generatePaginationLinks($page, $totalPages, 'sales.php', $search); ?>
        </div>
    </div>
</body>
</html>