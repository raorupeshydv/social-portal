<?php require "db.php"; 
$error = $success = '';

function is_valid_password($password) {
    return preg_match('/^(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (!is_valid_password($password)) {
        $error = "Password must be 8+ characters, 1 capital letter, and 1 number.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $first, $last, $username, $email, $phone, $hashed);
        try {
            $stmt->execute();
            $success = "Registered! <a href='login.php'>Login</a>";
        } catch (mysqli_sql_exception $e) {
            $error = "Username/email/phone already taken.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-container">
  <h2>Register</h2>
  <?php if ($error) echo "<p class='error'>$error</p>"; ?>
  <?php if ($success) echo "<p class='success'>$success</p>"; ?>
  <form method="POST">
    <input type="text" name="first_name" placeholder="First Name" required>
    <input type="text" name="last_name" placeholder="Last Name" required>
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="text" name="phone" placeholder="Phone" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="confirm" placeholder="Confirm Password" required>
    <button type="submit">Register</button>
  </form>
  <div class="switch-link">
    Already have an account? <a href="login.php">Login here</a>
  </div>
</div>
</body>
</html>
