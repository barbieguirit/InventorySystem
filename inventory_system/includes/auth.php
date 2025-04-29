<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start the session at the very beginning
}

require_once 'db.php'; // Include database connection

function hasRole($requiredRole) {
    return isset($_SESSION['RoleID']) && $_SESSION['RoleID'] == $requiredRole;
}

// Define the Auth class
class Auth {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($username, $password) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Users WHERE Username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['Password'])) {
                $_SESSION['UserID'] = $user['UserID'];
                $_SESSION['RoleID'] = $user['RoleID'];
                return true;
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
        }

        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['UserID']);
    }

    public function hasPermission($roleID, $permissionName) {
        $permissions = [
            1 => ['add_supplier', 'add_product', 'add_stock', 'add_sale'], // Admin
            2 => ['add_sale'], // Staff
        ];

        return in_array($permissionName, $permissions[$roleID] ?? []);
    }
}

// Instantiate the Auth class
$auth = new Auth($pdo);

// Handle login requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($auth->login($username, $password)) {       header("Location: ../index.php");
        exit;
    } else {
        header("Location: ../templates/login.php?error=invalid_credentials");
        exit;
    }
}