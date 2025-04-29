<?php
session_start();
if (!isset($_SESSION['UserID']) || !isset($_SESSION['RoleID'])) {
    header("Location: ../templates/login.php?error=session_expired");
    exit;
}
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php'; // Include the helpers file for logAction()


$products = $pdo->query("SELECT ProductID, Name FROM Products")->fetchAll();
$suppliers = $pdo->query("SELECT SupplierID, Name FROM Suppliers")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productID = $_POST['product_id'] ?? '';
    $supplierID = $_POST['supplier_id'] ?? '';
    $quantityAdded = $_POST['quantity_added'] ?? '';
    $dateAdded = $_POST['date_added'] ?? '';

    // Validate inputs
    if (empty($productID)) {
        $errors[] = "Please select a product.";
    }
    if (empty($supplierID)) {
        $errors[] = "Please select a supplier.";
    }
    if (empty($quantityAdded) || !is_numeric($quantityAdded) || $quantityAdded <= 0) {
        $errors[] = "Please enter a valid quantity.";
    }
    if (empty($dateAdded) || !strtotime($dateAdded)) {
        $errors[] = "Please select a valid date.";
    }
    if (empty($errors)) {
        try {
            // Insert the stock entry into the Stock table
            $stmt = $pdo->prepare("INSERT INTO Stock (ProductID, SupplierID, QuantityAdded, DateAdded) VALUES (?, ?, ?, ?)");
            $stmt->execute([$productID, $supplierID, $quantityAdded, $dateAdded]);
    
            // Update the stock level in the Products table
            $stmt = $pdo->prepare("UPDATE Products SET Stock = Stock + ? WHERE ProductID = ?");
            $stmt->execute([$quantityAdded, $productID]);
    
            // Log the action
            logAction($pdo, $_SESSION['UserID'], 'Added', 'Stock', $pdo->lastInsertId());
    
            // Redirect to the stock page after successful insertion
            header("Location: stock.php?success=added");
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
    <title>Add Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/add_form.css">
</head>
<body>
    <div class="container mt-4">
        <a href="stock.php" class="btn btn-success mb-4">Back to Stock</a>
        <h1 class="text-center mb-4">Add New Stock</h1>

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

        <form method="POST" action="add_stock.php" class="form-container">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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
                <label for="supplier_id" class="form-label">Supplier:</label>
                <select id="supplier_id" name="supplier_id" class="form-select" required>
                    <option value="">Select a supplier</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['SupplierID']; ?>"><?php echo htmlspecialchars($supplier['Name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="quantity_added" class="form-label">Quantity Added:</label>
                <input type="number" id="quantity_added" name="quantity_added" class="form-control" min="1" required>
            </div>

            <div class="mb-3">
                <label for="date_added" class="form-label">Date Added:</label>
                <input type="date" id="date_added" name="date_added" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Add Stock</button>
        </form>
    </div>
</body>
</html>