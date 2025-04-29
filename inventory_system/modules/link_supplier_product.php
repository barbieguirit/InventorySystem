<!-- filepath: c:\xampp\htdocs\inventory_system\modules\link_supplier_product.php -->
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

// Fetch products and suppliers
$products = $pdo->query("SELECT ProductID, Name FROM Products")->fetchAll();
$suppliers = $pdo->query("SELECT SupplierID, Name FROM Suppliers")->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productID = $_POST['product_id'] ?? '';
    $supplierIDs = $_POST['supplier_ids'] ?? [];

    // Validate inputs
    if (empty($productID)) {
        $errors[] = "Please select a product.";
    }
    if (empty($supplierIDs)) {
        $errors[] = "Please select at least one supplier.";
    }

    if (empty($errors)) {
        try {
            // Delete existing links for the selected product
            $stmt = $pdo->prepare("DELETE FROM SupplierProducts WHERE ProductID = ?");
            $stmt->execute([$productID]);

            // Normalization: SupplierProducts table resolves many-to-many relationships (2NF).
            $stmt = $pdo->prepare("INSERT INTO SupplierProducts (SupplierID, ProductID) VALUES (?, ?)");
            foreach ($supplierIDs as $supplierID) {
                $stmt->execute([$supplierID, $productID]);
            }

            // Redirect with success message
            header("Location: link_supplier_product.php?success=linked");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Suppliers to Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <a href="../homepage.php" class="btn btn-success mb-4">Back to Home</a>
        <h1 class="text-center mb-4">Link Suppliers to Product</h1>

        <!-- Display Errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif (isset($_GET['success']) && $_GET['success'] == 'linked'): ?>
            <div class="alert alert-success">
                Suppliers successfully linked to the product.
            </div>
        <?php endif; ?>

        <form method="POST" action="link_supplier_product.php">
            <div class="mb-3">
                <label for="product_id" class="form-label">Product:</label>
                <select id="product_id" name="product_id" class="form-select" required>
                    <option value="">Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['ProductID']; ?>"><?php echo htmlspecialchars($product['Name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="supplier_ids" class="form-label">Suppliers:</label>
                <select id="supplier_ids" name="supplier_ids[]" class="form-select" multiple required>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['SupplierID']; ?>"><?php echo htmlspecialchars($supplier['Name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Link Suppliers</button>
        </form>
    </div>
</body>
</html>