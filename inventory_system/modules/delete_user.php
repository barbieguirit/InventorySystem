<?php
// filepath: c:\xampp\htdocs\inventory_system\modules\delete_user.php

session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

// Restrict access to Admins only
if ($_SESSION['RoleID'] != 1) {
    header("Location: ../index.php");
    exit;
}

// Get user ID from query string
$userID = $_GET['id'] ?? null;
if (!$userID) {
    die('Invalid user ID.');
}

// Delete user
$stmt = $pdo->prepare("DELETE FROM Users WHERE ID = ?");
$stmt->execute([$userID]);

// Redirect back to users.php
header("Location: users.php?success=user_deleted");
exit;
?>