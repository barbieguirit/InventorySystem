<?php
session_start();

// Destroy the session
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/logout.css">
</head>
<body>
    <!-- filepath: c:\xampp\htdocs\inventory_system\templates\logout.php -->
<a href="logout.php" class="nav-link" onclick="return confirm('Are you sure you want to log out?');">Logout</a>
    <div class="container text-center mt-5">
        <h1 class="text-success">You have been logged out successfully!</h1>
        <p class="mt-3">Thank you for using the Inventory Management System.</p>
        <a href="../templates/login.php" class="btn btn-primary mt-4">Log In Again</a>
    </div>
</body>
</html>