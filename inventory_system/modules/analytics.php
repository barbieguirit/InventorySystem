<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';


// Restrict access to Admin (RoleID = 1)
if ($_SESSION['RoleID'] != 1) {
    header("HTTP/1.1 403 Forbidden");
    echo json_encode(['error' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

try {
    if (!isset($_GET['action'])) {
        echo json_encode(['error' => 'No action specified']);
        exit;
    }

    $action = $_GET['action'];

    if ($action === 'all_analytics') {
        // Query for supplier product count
        $productCountStmt = $pdo->query("
            SELECT s.Name AS SupplierName, COUNT(sp.ProductID) AS ProductCount
            FROM Suppliers s
            LEFT JOIN SupplierProducts sp ON s.SupplierID = sp.SupplierID
            GROUP BY s.SupplierID
            ORDER BY ProductCount DESC
        ");
        $supplierProductCount = $productCountStmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Query for supplier revenue
        $revenueStmt = $pdo->query("
            SELECT s.Name AS SupplierName, SUM(sa.TotalAmount) AS TotalRevenue
            FROM Suppliers s
            LEFT JOIN SupplierProducts sp ON s.SupplierID = sp.SupplierID
            LEFT JOIN Sales sa ON sp.ProductID = sa.ProductID
            GROUP BY s.SupplierID
            ORDER BY TotalRevenue DESC
        ");
        $supplierRevenue = $revenueStmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Handle cases where no data is available
        if (empty($supplierProductCount)) {
            $supplierProductCount = [['SupplierName' => 'No Data', 'ProductCount' => 0]];
        }
        if (empty($supplierRevenue)) {
            $supplierRevenue = [['SupplierName' => 'No Data', 'TotalRevenue' => 0]];
        }
    
        // Combine the data into one response
        $data = [
            'supplier_product_count' => $supplierProductCount,
            'supplier_revenue' => $supplierRevenue,
        ];
    
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>