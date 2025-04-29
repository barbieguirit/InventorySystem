<?php
// filepath: c:\xampp\htdocs\inventory_system\modules\edit_user.php

session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

// Restrict access to Admins only
if ($_SESSION['RoleID'] != 1) {
    header("Location: ../index.php");
    exit;
}

// Get user ID from query string
$userID = $_GET['id'] ?? null;
if (!$userID) {
    die('Invalid user ID.');
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM Users WHERE ID = ?");
$stmt->execute([$userID]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('User not found.');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $roleID = $_POST['role_id'] ?? '';

    if (!empty($username) && !empty($roleID)) {
        $stmt = $pdo->prepare("UPDATE Users SET Username = ?, RoleID = ? WHERE ID = ?");
        $stmt->execute([$username, $roleID, $userID]);
        header("Location: users.php?success=user_updated");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Edit User</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="role_id" class="form-label">Role:</label>
                <select id="role_id" name="role_id" class="form-select" required>
                    <option value="1" <?php echo $user['RoleID'] == 1 ? 'selected' : ''; ?>>Admin</option>
                    <option value="2" <?php echo $user['RoleID'] == 2 ? 'selected' : ''; ?>>Staff</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Save Changes</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>