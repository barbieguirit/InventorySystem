<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

if (!hasRole(1)) { // Restrict to Admin
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: suppliers.php?error=missing_id");
    exit;
}

$supplierID = $_GET['id'];

try {
    // Check if the supplier exists
    $stmt = $pdo->prepare("SELECT * FROM Suppliers WHERE SupplierID = ?");
    $stmt->execute([$supplierID]);
    $supplier = $stmt->fetch();

    if (!$supplier) {
        header("Location: suppliers.php?error=not_found");
        exit;
    }

    // Begin transaction
    $pdo->beginTransaction();

    // Delete related records in SupplierProducts and Stock
    $pdo->prepare("DELETE FROM SupplierProducts WHERE SupplierID = ?")->execute([$supplierID]);
    $pdo->prepare("DELETE FROM Stock WHERE SupplierID = ?")->execute([$supplierID]);

    // Delete the supplier
    $pdo->prepare("DELETE FROM Suppliers WHERE SupplierID = ?")->execute([$supplierID]);

    // Commit transaction
    $pdo->commit();

    // Log the action
    logAction($pdo, $_SESSION['UserID'], 'Deleted', 'Suppliers', $supplierID);

    header("Location: suppliers.php?success=deleted");
    exit;
} catch (PDOException $e) {
    $pdo->rollBack();
    header("Location: suppliers.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>