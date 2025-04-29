<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php'; // Include the helpers file for logAction()


// Restrict access to Admin (RoleID = 1)
if ($_SESSION['RoleID'] != 1) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: products.php?error=missing_id");
    exit;
}

$productID = $_GET['id'];

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM Products WHERE ProductID = ?");
$stmt->execute([$productID]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php?error=not_found");
    exit;
}

// Fetch all suppliers for the dropdown
$suppliers = $pdo->query("SELECT SupplierID, Name FROM Suppliers")->fetchAll();

// Fetch currently linked suppliers for the product
$stmt = $pdo->prepare("SELECT SupplierID FROM SupplierProducts WHERE ProductID = ?");
$stmt->execute([$productID]);
$currentSuppliers = $stmt->fetchAll(PDO::FETCH_COLUMN);

$errors = [];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? '';
    $supplierIDs = $_POST['supplier_ids'] ?? []; // Array of selected suppliers

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Product name is required.";
    }
    if (empty($price) || !is_numeric($price) || $price <= 0) {
        $errors[] = "Please enter a valid price.";
    }
    if (empty($supplierIDs)) {
        $errors[] = "Please select at least one supplier.";
    }

    if (empty($errors)) {
        try {
            // Update the product details
            $stmt = $pdo->prepare("UPDATE Products SET Name = ?, Price = ? WHERE ProductID = ?");
            $stmt->execute([$name, $price, $productID]);

            // Update supplier links
            // First, delete existing links
            $stmt = $pdo->prepare("DELETE FROM SupplierProducts WHERE ProductID = ?");
            $stmt->execute([$productID]);

            // Then, insert new links
            $stmt = $pdo->prepare("INSERT INTO SupplierProducts (SupplierID, ProductID) VALUES (?, ?)");
            foreach ($supplierIDs as $supplierID) {
                $stmt->execute([$supplierID, $productID]);
            }
            
            // After successfully editing the product
            logAction($pdo, $_SESSION['UserID'], 'Edited', 'Products', $productID);

            // Redirect to the products page after successful update
            header("Location: products.php?success=updated");
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
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/edit_form.css">
</head>
<body>
    <div class="container mt-4">
        <a href="products.php" class="btn btn-success mb-4">Back to Products</a>
        <h1 class="text-center mb-4">Edit Product</h1>

        <!-- Display Errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_product.php?id=<?php echo $productID; ?>" class="form-container">
            <div class="mb-3">
                <label for="name" class="form-label">Product Name:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['Name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price:</label>
                <input type="number" step="0.01" id="price" name="price" class="form-control" value="<?php echo htmlspecialchars($product['Price']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="supplier_ids" class="form-label">Suppliers:</label>
                <select id="supplier_ids" name="supplier_ids[]" class="form-select" multiple required>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['SupplierID']; ?>" <?php echo in_array($supplier['SupplierID'], $currentSuppliers) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($supplier['Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
    </div>
</body>
</html>