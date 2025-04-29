<?php
// filepath: c:\xampp\htdocs\inventory_system\modules\users.php

session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

// Restrict access to Admins only
if (!isset($_SESSION['RoleID']) || $_SESSION['RoleID'] != 1) {
    header("Location: ../index.php");
    exit;
}

// Fetch all users from the database
$stmt = $pdo->query("SELECT UserID, Username, RoleID FROM Users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <style>
        .table-container {
            margin-top: 50px;
        }
        .table-container h1 {
            text-align: center;
            margin-bottom: 50px;
        }
        .btn-primary {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
                <a href="sales.php" class="nav-link">View Sales</a>
            </li>
            <li class="nav-item">
                <a href="../analytics.php" class="nav-link">Analytics Dashboard</a>
            </li>
            <li class="nav-item">
        <a href="modules/supplier_product.php" class="nav-link">Supplier-Product View</a>
    </li>
            <li class="nav-item">
                <a href="register.php" class="nav-link active">Register User</a>
            </li>
            <li class="nav-item">
                <a href="../templates/logout.php" class="nav-link">Logout</a>
            </li>
        </ul>
    </nav>



    <!-- Users Table -->
    <div class="container table-container">
        <h1>Manage Users</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['UserID']); ?></td>
                        <td><?php echo htmlspecialchars($user['Username']); ?></td>
                        <td><?php echo $user['RoleID'] == 1 ? 'Admin' : 'Staff'; ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['UserID']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_user.php?id=<?php echo $user['UserID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="register.php" class="btn btn-primary">Add New User</a>
    </div>
</body>
</html>