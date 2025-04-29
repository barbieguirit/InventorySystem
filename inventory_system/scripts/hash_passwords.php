<?php
require_once '../includes/db.php';

try {
    // Fetch all users from the database
    $stmt = $pdo->query("SELECT UserID, Password FROM Users");
    while ($row = $stmt->fetch()) {
        // Debugging: Output the current password and UserID
        echo "Processing UserID {$row['UserID']} with Password: {$row['Password']}<br>";

        // Check if the password is already hashed
        if (password_get_info($row['Password'])['algo'] === 0) {
            // Hash the plain-text password
            $hashedPassword = password_hash($row['Password'], PASSWORD_DEFAULT);

            // Debugging: Output the hashed password
            echo "Hashed Password for UserID {$row['UserID']}: $hashedPassword<br>";

            // Update the database with the hashed password
            $updateStmt = $pdo->prepare("UPDATE Users SET Password = ? WHERE UserID = ?");
            $updateStmt->execute([$hashedPassword, $row['UserID']]);

            echo "Password for UserID {$row['UserID']} has been hashed and updated.<br>";
        } else {
            echo "Password for UserID {$row['UserID']} is already hashed.<br>";
        }
    }
    echo "Password hashing process completed!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>