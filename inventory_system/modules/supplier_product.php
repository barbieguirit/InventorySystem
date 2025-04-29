<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Restrict access to Admins only
if ($_SESSION['RoleID'] != 1) {
    header("Location: ../index.php");
    exit;
}

// Fetch supplier-product relationships
$query = "
    SELECT 
        Suppliers.Name AS SupplierName, 
        Products.Name AS ProductName
    FROM 
        SupplierProducts
    JOIN 
        Suppliers ON SupplierProducts.SupplierID = Suppliers.SupplierID
    JOIN 
        Products ON SupplierProducts.ProductID = Products.ProductID
    ORDER BY 
        Suppliers.Name, Products.Name";
$stmt = $pdo->query($query);
$supplierProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier-Product View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <a href="../index.php" class="btn btn-success mb-4">Back to Dashboard</a>
        <h1 class="text-center mb-4">Supplier-Product View</h1>

        <table class="table table-striped table-bordered text-center">
            <thead class="table-primary">
                <tr>
                    <th>Supplier Name</th>
                    <th>Product Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($supplierProducts as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['SupplierName']); ?></td>
                        <td><?php echo htmlspecialchars($row['ProductName']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>