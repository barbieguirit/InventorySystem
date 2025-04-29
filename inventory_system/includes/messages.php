<!-- filepath: c:\xampp\htdocs\inventory_system\includes\messages.php -->
<?php
function displayMessages() {
    if (isset($_GET['error'])) {
      echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($_GET['error']) . "</div>";
                
    } elseif (isset($_GET['success'])) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                <strong>Success:</strong> " . htmlspecialchars($_GET['success']) . "
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}
?>