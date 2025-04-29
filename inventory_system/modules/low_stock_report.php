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

// Fetch products with low stock
$lowStockThreshold = 10; // Define the threshold for low stock
$query = "SELECT ProductID, Name, Stock FROM Products WHERE Stock <= ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$lowStockThreshold]);
$lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export to CSV if requested
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=low_stock_report.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Product ID', 'Name', 'Stock']);
    foreach ($lowStockProducts as $product) {
        fputcsv($output, $product);
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
    <title>Low Stock Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <a href="../homepage.php" class="btn btn-success mb-4">Back to Home</a>
        <h1 class="text-center mb-4">Low Stock Report</h1>

        <a href="low_stock_report.php?export=csv" class="btn btn-primary mb-4">Export to CSV</a>

        <table class="table table-striped table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>Product ID</th>
                    <th>Name</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStockProducts as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['ProductID']); ?></td>
                        <td><?php echo htmlspecialchars($product['Name']); ?></td>
                        <td><?php echo htmlspecialchars($product['Stock']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>