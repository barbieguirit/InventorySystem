<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/messages.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$auth = new Auth($pdo);

if (!$auth->hasPermission($_SESSION['RoleID'], 'add_user')) {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../assets/css/access_denied.css">
    </head>
    <body>
        <div class="access-denied">
            <h1>Access Denied</h1>
            <p>You do not have permission to add users.</p>
            <a href="../index.php">Go Back to Dashboard</a>
        </div>
    </body>
    </html>';
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleID = $_POST['role_id'] ?? '';

    // Validate inputs
    if (empty($username)) $errors[] = "Username is required.";
    if (empty($password) || strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) || 
        !preg_match('/[a-z]/', $password) || 
        !preg_match('/[0-9]/', $password) || 
        !preg_match('/[\W]/', $password)) {
        $errors[] = "Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, a number, and a special character.";
    }
    if (empty($roleID)) $errors[] = "Role is required.";

    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO Users (Username, Password, RoleID) VALUES (?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $roleID]);
            header("Location: users.php?success=added");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Error: " . htmlspecialchars($e->getMessage());
        }
    }

    // Regenerate CSRF token after form submission
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/add_form.css">
</head>
<body>
    <div class="d-flex">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark flex-column vh-100">
            <a href="../index.php" class="navbar-brand text-center py-3">Dashboard</a>
            <ul class="navbar-nav flex-column">
                <li class="nav-item"><a href="products.php" class="nav-link">Manage Products</a></li>
                <li class="nav-item"><a href="suppliers.php" class="nav-link">Manage Suppliers</a></li>
                <li class="nav-item"><a href="stock.php" class="nav-link">Manage Stock</a></li>
                <li class="nav-item"><a href="sales.php" class="nav-link">View Sales</a></li>
                <li class="nav-item"><a href="../analytics.php" class="nav-link">Analytics Dashboard</a></li>
                <li class="nav-item"><a href="modules/supplier_product.php" class="nav-link">Supplier-Product View</a></li>
                <li class="nav-item"><a href="register.php" class="nav-link active">Register User</a></li>
                <li class="nav-item"><a href="../templates/logout.php" class="nav-link">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid mt-4">
            <h1 class="text-center mb-4">Register New User</h1>

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

            <form method="POST" action="register.php" class="form-container">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="mb-3">
                    <label for="username" class="form-label">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="role_id" class="form-label">Role:</label>
                    <select id="role_id" name="role_id" class="form-select" required>
                        <option value="">Select a role</option>
                        <option value="1">Admin</option>
                        <option value="2">Staff</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
        </div>
    </div>
</body>
</html>