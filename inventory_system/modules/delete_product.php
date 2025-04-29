<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

if (!$auth->hasPermission($_SESSION['RoleID'], 'delete_product')) {
    header("Location: ../templates/error.php?error=access_denied");
    exit;
}


if (!hasRole(1)) { // Restrict to Admin
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php?error=missing_id");
    exit;
}

$productID = $_GET['id'];

try {
    // Check if the product exists
    $stmt = $pdo->prepare("SELECT * FROM Products WHERE ProductID = ?");
    $stmt->execute([$productID]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: products.php?error=not_found");
        exit;
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Delete related records in SupplierProducts, Stock, and Sales
    $pdo->prepare("DELETE FROM SupplierProducts WHERE ProductID = ?")->execute([$productID]);
    $pdo->prepare("DELETE FROM Stock WHERE ProductID = ?")->execute([$productID]);
    $pdo->prepare("DELETE FROM Sales WHERE ProductID = ?")->execute([$productID]);

    // Delete the product
    $pdo->prepare("DELETE FROM Products WHERE ProductID = ?")->execute([$productID]);

    // Commit transaction
    $pdo->commit();

    // Log the action
    logAction($pdo, $_SESSION['UserID'], 'Deleted', 'Products', $productID);

    header("Location: products.php?success=deleted");
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    header("Location: products.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>