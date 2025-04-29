<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
  <div class="container">
    <header class="login-title">
      <h1>Inventory Management System</h1>
    </header>

    <form class="login-form" method="POST" action="../includes/auth.php">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
      <div>
        <label for="username">Username</label>
        <input id="username" type="text" name="username" required />
        <small class="error-message" id="username-error"></small>
      </div>

      <div>
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required />
        <small class="error-message" id="password-error"></small>
      </div>                                      

      <button class="btn btn--form" type="submit" value="Log in">Log In</button>
    </form>
  </div>

  <script>
    const form = document.querySelector('.login-form');
    form.addEventListener('submit', (e) => {
      const username = document.getElementById('username');
      const password = document.getElementById('password');
      let valid = true;

      if (username.value.trim() === '') {
        document.getElementById('username-error').textContent = 'Please enter your username.';
        valid = false;
      }

      if (password.value.length < 6) {
        document.getElementById('password-error').textContent = 'Password must be at least 6 characters.';
        valid = false;
      }

      if (!valid) e.preventDefault();
    });
  </script>
</body>
</html>