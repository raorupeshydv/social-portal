<?php session_start(); require "db.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['identifier'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? OR phone = ?");
    $stmt->bind_param("sss", $identifier, $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if ($user['failed_attempts'] >= 3) {
            $error = "Too many failed attempts. Try again later.";
        } elseif (password_verify($password, $user['password'])) {
            $conn->query("UPDATE users SET failed_attempts = 0 WHERE id = {$user['id']}");
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['username'] = $user['username'];
            header("Location: home.php");
            exit();
        } else {
            $conn->query("UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = {$user['id']}");
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-container">
  <h2>Login</h2>
  <?php if ($error) echo "<p class='error'>$error</p>"; ?>
  <form method="POST">
    <input type="text" name="identifier" placeholder="Username / Email / Phone" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
  <div class="switch-link">
    Don’t have an account? <a href="register.php">Register here</a>
  </div>
</div>
</body>
</html>
