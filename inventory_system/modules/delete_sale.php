<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php'; // Include the helpers file for logAction()


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: sales.php?error=missing_id");
    exit;
}

$saleID = $_GET['id'];

try {
    // Check if the SaleID exists
    $stmt = $pdo->prepare("SELECT * FROM Sales WHERE SaleID = ?");
    $stmt->execute([$saleID]);
    $sale = $stmt->fetch();

    if (!$sale) {
        header("Location: sales.php?error=not_found");
        exit;
    }

    // Delete the sale
    $stmt = $pdo->prepare("DELETE FROM Sales WHERE SaleID = ?");
    $stmt->execute([$saleID]);

    // Log the action
logAction($pdo, $_SESSION['UserID'], 'Deleted', 'Sales', $saleID);

    // Redirect to the sales page with a success message
    header("Location: sales.php?success=deleted");
    exit;
} catch (PDOException $e) {
    header("Location: sales.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>