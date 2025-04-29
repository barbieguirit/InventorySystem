<?php
session_start();
if (!isset($_SESSION['UserID']) || !isset($_SESSION['RoleID'])) {
    header("Location: ../templates/login.php?error=session_expired");
    exit;
}
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php'; // Include the helpers file for logAction()

$products = $pdo->query("SELECT ProductID, Name, Stock FROM Products")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productID = $_POST['product_id'] ?? '';
    $quantitySold = $_POST['quantity_sold'] ?? '';
    $saleDate = $_POST['sale_date'] ?? '';
    $totalAmount = $_POST['total_amount'] ?? '';

    // Validate inputs
    if (empty($productID)) {
        $errors[] = "Please select a product.";
    }
    if (empty($quantitySold) || !is_numeric($quantitySold) || $quantitySold <= 0) {
        $errors[] = "Please enter a valid quantity.";
    }
    if (empty($saleDate) || !strtotime($saleDate)) {
        $errors[] = "Please select a valid date.";
    }
    if (empty($totalAmount) || !is_numeric($totalAmount) || $totalAmount <= 0) {
        $errors[] = "Please enter a valid total amount.";
    }

    if (empty($errors)) {
        try {
            // Check if the stock is sufficient
            $stmt = $pdo->prepare("SELECT Stock FROM Products WHERE ProductID = ?");
            $stmt->execute([$productID]);
            $product = $stmt->fetch();

            if (!$product) {
                $errors[] = "Product not found.";
            } elseif ($product['Stock'] < $quantitySold) {
                $errors[] = "Insufficient stock for the selected product.";
            } else {
                // Deduct the stock
                $newStock = $product['Stock'] - $quantitySold;
                $stmt = $pdo->prepare("UPDATE Products SET Stock = ? WHERE ProductID = ?");
                $stmt->execute([$newStock, $productID]);
            
                // Insert the sale into the Sales table
                $stmt = $pdo->prepare("INSERT INTO Sales (ProductID, QuantitySold, SaleDate, TotalAmount) VALUES (?, ?, ?, ?)");
                $stmt->execute([$productID, $quantitySold, $saleDate, $totalAmount]);
            
                // Log the action
                logAction($pdo, $_SESSION['UserID'], 'Added', 'Sales', $pdo->lastInsertId());
            
                // Redirect to the sales page after successful insertion
                header("Location: sales.php?success=added");
                exit;
            }
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
    <title>Add Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/add_form.css">
</head>
<body>
    <div class="container mt-4">
        <a href="sales.php" class="btn btn-success mb-4">Back to Sales</a>
        <h1 class="text-center mb-4">Add New Sale</h1>

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

        <form method="POST" action="add_sale.php" class="form-container">
            <div class="mb-3">
                <label for="product_id" class="form-label">Product:</label>
                <select name="product_id" id="product_id" class="form-select" required>
                    <option value="">Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['ProductID']; ?>">
                            <?php echo htmlspecialchars($product['Name']) . " (Stock: " . $product['Stock'] . ")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="quantity_sold" class="form-label">Quantity Sold:</label>
                <input type="number" name="quantity_sold" id="quantity_sold" class="form-control" min="1" required>
            </div>

            <div class="mb-3">
                <label for="sale_date" class="form-label">Sale Date:</label>
                <input type="date" name="sale_date" id="sale_date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="total_amount" class="form-label">Total Amount:</label>
                <input type="number" step="0.01" name="total_amount" id="total_amount" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Add Sale</button>
        </form>
    </div>
</body>
</html>