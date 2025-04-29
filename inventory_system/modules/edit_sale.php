<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php'; // Include the helpers file for logAction()


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: sales.php?error=missing_id");
    exit;
}

$saleID = $_GET['id'];

// Fetch sale details
$stmt = $pdo->prepare("SELECT * FROM Sales WHERE SaleID = ?");
$stmt->execute([$saleID]);
$sale = $stmt->fetch();

if (!$sale) {
    header("Location: sales.php?error=not_found");
    exit;
}

$products = $pdo->query("SELECT ProductID, Name FROM Products")->fetchAll();

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
            // Update the sale in the Sales table
            $stmt = $pdo->prepare("UPDATE Sales SET ProductID = ?, QuantitySold = ?, SaleDate = ?, TotalAmount = ? WHERE SaleID = ?");
            $stmt->execute([$productID, $quantitySold, $saleDate, $totalAmount, $saleID]);

            logAction($pdo, $_SESSION['UserID'], 'Edited', 'Sales', $saleID);
            // Redirect to the sales page after successful update
            header("Location: sales.php?success=updated");
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
    <title>Edit Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/edit_form.css">
</head>
<body>
    <div class="container">
    <a href="sales.php" class="btn btn-success mb-4">Back to Sales</a>
        <h1>Edit Sale</h1>

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

        <form method="POST" action="edit_sale.php?id=<?php echo $saleID; ?>" class="form-container">
            <div class="mb-3">
                <label for="product_id" class="form-label">Product:</label>
                <select name="product_id" id="product_id" class="form-control" required>
                    <option value="">Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['ProductID']; ?>" <?php if ($product['ProductID'] == $sale['ProductID']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($product['Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="quantity_sold" class="form-label">Quantity Sold:</label>
                <input type="number" name="quantity_sold" id="quantity_sold" class="form-control" min="1" value="<?php echo htmlspecialchars($sale['QuantitySold']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="sale_date" class="form-label">Sale Date:</label>
                <input type="date" name="sale_date" id="sale_date" class="form-control" value="<?php echo htmlspecialchars($sale['SaleDate']); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Sale</button>
        </form>
    </div>
</body>
</html>