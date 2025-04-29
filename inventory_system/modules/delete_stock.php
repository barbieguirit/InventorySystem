<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php'; // Include the helpers file for logAction()


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: stock.php?error=missing_id");
    exit;
}

$stockID = $_GET['id'];

try {
    // Check if the StockID exists
    $stmt = $pdo->prepare("SELECT * FROM Stock WHERE StockID = ?");
    $stmt->execute([$stockID]);
    $stock = $stmt->fetch();

    if (!$stock) {
        header("Location: stock.php?error=not_found");
        exit;
    }

    // Delete the stock entry
    $stmt = $pdo->prepare("DELETE FROM Stock WHERE StockID = ?");
    $stmt->execute([$stockID]);

    // Log the action
logAction($pdo, $_SESSION['UserID'], 'Deleted', 'Stock', $stockID);
    // Redirect to the stock page with a success message
    header("Location: stock.php?success=deleted");
    exit;
} catch (PDOException $e) {
    header("Location: stock.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>