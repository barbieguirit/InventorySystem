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

// Fetch stock details
$stmt = $pdo->prepare("SELECT * FROM Stock WHERE StockID = ?");
$stmt->execute([$stockID]);
$stock = $stmt->fetch();

if (!$stock) {
    header("Location: stock.php?error=not_found");
    exit;
}

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
            // Update the stock entry in the Stock table
            $stmt = $pdo->prepare("UPDATE Stock SET ProductID = ?, SupplierID = ?, QuantityAdded = ?, DateAdded = ? WHERE StockID = ?");
            $stmt->execute([$productID, $supplierID, $quantityAdded, $dateAdded, $stockID]);

            logAction($pdo, $_SESSION['UserID'], 'Edited', 'Stock', $stockID);
            // Redirect to the stock page after successful update
            header("Location: stock.php?success=updated");
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
    <title>Edit Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/edit_form.css">
</head>
<body>
    <div class="container">
    <a href="stock.php" class="btn btn-success mb-4">Back to Stock</a>
        <h1>Edit Stock</h1>

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

        <form method="POST" action="edit_stock.php?id=<?php echo $stockID; ?>" class="form-container">
            

            <div class="mb-3">
                <label for="product_id" class="form-label">Product:</label>
                <select id="product_id" name="product_id" class="form-control" required>
                    <option value="">Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['ProductID']; ?>" <?php if ($product['ProductID'] == $stock['ProductID']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($product['Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="supplier_id" class="form-label">Supplier:</label>
                <select id="supplier_id" name="supplier_id" class="form-control" required>
                    <option value="">Select a supplier</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['SupplierID']; ?>" <?php if ($supplier['SupplierID'] == $stock['SupplierID']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($supplier['Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="quantity_added" class="form-label">Quantity Added:</label>
                <input type="number" id="quantity_added" name="quantity_added" class="form-control" min="1" value="<?php echo htmlspecialchars($stock['QuantityAdded']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="date_added" class="form-label">Date Added:</label>
                <input type="date" id="date_added" name="date_added" class="form-control" value="<?php echo htmlspecialchars($stock['DateAdded']); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Stock</button>
        </form>
    </div>
</body>
</html>