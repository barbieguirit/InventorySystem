<?php
session_start();

require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php'; // Include the helpers file for logAction()


// Generate CSRF token if it doesn't exist  
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// Fetch suppliers for the dropdown
$suppliers = $pdo->query("SELECT SupplierID, Name FROM Suppliers")->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token.";
    }

    // Initialize variables from POST data
    $name = trim($_POST['name'] ?? '');
    $price = $_POST['price'] ?? '';
    $supplierIDs = $_POST['supplier_ids'] ?? [];

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
            // Insert the product into the Products table
            $stmt = $pdo->prepare("INSERT INTO Products (Name, Price) VALUES (?, ?)");
            $stmt->execute([$name, $price]);
            $productID = $pdo->lastInsertId();

            // Link the product to the selected suppliers
            $stmt = $pdo->prepare("INSERT INTO SupplierProducts (SupplierID, ProductID) VALUES (?, ?)");
            foreach ($supplierIDs as $supplierID) {
                $stmt->execute([$supplierID, $productID]);
            }

            // Log the action
            logAction($pdo, $_SESSION['UserID'], 'Added', 'Products', $productID);

            // Redirect to the products page after successful insertion
            header("Location: products.php?success=added");
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
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/add_form.css">
</head>
<body>

    <div class="container mt-4">
        <a href="products.php" class="btn btn-success mb-4">Back to Products</a>
        <h1 class="text-center mb-4">Add New Product</h1>

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

        <form method="POST" action="add_product.php" class="form-container">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">      
        
            <div class="mb-3">
                <label for="name" class="form-label">Product Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price:</label>
                <input type="number" step="0.01" id="price" name="price" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="supplier_ids" class="form-label">Suppliers:</label>
                <select id="supplier_ids" name="supplier_ids[]" class="form-select" multiple required>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['SupplierID']; ?>"><?php echo htmlspecialchars($supplier['Name']); ?></option>
                    <?php endforeach; ?>


                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
        </form>
    </div>
</body>
</html>