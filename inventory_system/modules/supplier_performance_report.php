<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

// Restrict access to Admin (RoleID = 1)
if ($_SESSION['RoleID'] != 1) {
    header("Location: ../index.php");
    exit;
}

// Fetch supplier performance data
$query = "
    SELECT 
        s.Name AS SupplierName, 
        COUNT(sp.ProductID) AS ProductCount, 
        COALESCE(SUM(sa.TotalAmount), 0) AS TotalRevenue
    FROM Suppliers s
    LEFT JOIN SupplierProducts sp ON s.SupplierID = sp.SupplierID
    LEFT JOIN Sales sa ON sp.ProductID = sa.ProductID
    GROUP BY s.SupplierID
    ORDER BY TotalRevenue DESC";
$stmt = $pdo->query($query);
$supplierPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export to CSV if requested
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=supplier_performance_report.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Supplier Name', 'Product Count', 'Total Revenue']);
    foreach ($supplierPerformance as $supplier) {
        fputcsv($output, [
            $supplier['SupplierName'] ?? '',
            $supplier['ProductCount'] ?? '0',
            $supplier['TotalRevenue'] ?? '0'
        ]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Performance Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <a href="../homepage.php" class="btn btn-success mb-4">Back to Home</a>
        <h1 class="text-center mb-4">Supplier Performance Report</h1>

        <a href="supplier_performance_report.php?export=csv" class="btn btn-primary mb-4">Export to CSV</a>

        <table class="table table-striped table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>Supplier Name</th>
                    <th>Product Count</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($supplierPerformance as $supplier): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($supplier['SupplierName'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($supplier['ProductCount'] ?? '0'); ?></td>
                        <td><?php echo htmlspecialchars($supplier['TotalRevenue'] ?? '0'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>