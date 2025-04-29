<?php
session_start();

require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php'; // Include the helpers file for logAction()

// Check if the user is logged in and has permission
if (!isset($_SESSION['UserID']) || !isset($_SESSION['RoleID'])) {
    header("Location: ../templates/login.php?error=access_denied");
    exit;
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

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
            // Insert the supplier into the Suppliers table
            $stmt = $pdo->prepare("INSERT INTO Suppliers (Name, ContactInfo) VALUES (?, ?)");
            $stmt->execute([$name, $contactInfo]);
            $supplierID = $pdo->lastInsertId(); // Get the last inserted ID

            // Log the action
            logAction($pdo, $_SESSION['UserID'], 'Added', 'Suppliers', $supplierID);

            // Redirect to the suppliers page after successful insertion
            header("Location: suppliers.php?success=added");
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
    <title>Add Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/add_form.css">
</head>
<body>
    <div class="container mt-4">
        <a href="suppliers.php" class="btn btn-success mb-4">Back to Suppliers</a>
        <h1 class="text-center mb-4">Add New Supplier</h1>

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

        <form method="POST" action="add_supplier.php" class="form-container">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Supplier Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="contact_info" class="form-label">Contact Info:</label>
                <textarea id="contact_info" name="contact_info" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Supplier</button>
        </form>
    </div>
</body>
</html>