<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php'; // Include the helpers file for logAction()


if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: suppliers.php?error=missing_id");
    exit;
}

$supplierID = $_GET['id'];

// Fetch supplier details
$stmt = $pdo->prepare("SELECT * FROM Suppliers WHERE SupplierID = ?");
$stmt->execute([$supplierID]);
$supplier = $stmt->fetch();

if (!$supplier) {
    header("Location: suppliers.php?error=not_found");
    exit;
}

$errors = [];



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contactInfo = trim($_POST['contact_info'] ?? '');

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Supplier name is required.";
    }
    if (empty($contactInfo)) {
        $errors[] = "Contact information is required.";
    }

    if (empty($errors)) {
        try {
            // Update the supplier in the Suppliers table
            $stmt = $pdo->prepare("UPDATE Suppliers SET Name = ?, ContactInfo = ? WHERE SupplierID = ?");
            $stmt->execute([$name, $contactInfo, $supplierID]);

            logAction($pdo, $_SESSION['UserID'], 'Edited', 'Suppliers', $supplierID);
            // Redirect to the suppliers page after successful update
            header("Location: suppliers.php?success=updated");
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
    <title>Edit Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/edit_form.css">
</head>
<body>
    <div class="container">
        <a href="suppliers.php" class="btn btn-success mb-4">Back to Suppliers</a>
        <h1>Edit Supplier</h1>

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

        <form method="POST" action="edit_supplier.php?id=<?php echo $supplierID; ?>" class="form-container">
            <div class="mb-3">
                <label for="name" class="form-label">Supplier Name:</label>
                <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($supplier['Name']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="contact_info" class="form-label">Contact Info:</label>
                <textarea id="contact_info" name="contact_info" class="form-control" rows="4" required><?php echo htmlspecialchars($supplier['ContactInfo']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update Supplier</button>
        </form>
    </div>
</body>
</html>