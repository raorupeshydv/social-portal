<?php
session_start();
require "db.php";
if (!isset($_SESSION['user_id'])) header("Location: login.php");

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $first = trim($_POST['first_name']);
  $last = trim($_POST['last_name']);
  $username = trim($_POST['username']);
  $email = trim($_POST['email']);
  $phone = trim($_POST['phone']);
  
  $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, username=?, email=?, phone=? WHERE id=?");
  $stmt->bind_param("sssssi", $first, $last, $username, $email, $phone, $user_id);
  if ($stmt->execute()) {
    $success = "Profile updated!";
  } else {
    $error = "Failed to update.";
  }

  if (!empty($_POST['new_password'])) {
    if ($_POST['new_password'] === $_POST['confirm_password']) {
      $pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
      $conn->query("UPDATE users SET password='$pass' WHERE id=$user_id");
      $success .= " Password changed!";
    } else {
      $error .= " Passwords do not match.";
    }
  }

  if (!empty($_FILES['profile_pic']['name'])) {
    $path = 'uploads/' . basename($_FILES['profile_pic']['name']);
    move_uploaded_file($_FILES['profile_pic']['tmp_name'], $path);
    $conn->query("UPDATE users SET profile_pic='$path' WHERE id=$user_id");
    $success .= " Profile picture updated!";
  }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Settings</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #1e1e2f;
      color: #fff;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .settings-container {
      background: #2b2b3d;
      padding: 30px;
      border-radius: 10px;
      width: 350px;
      box-shadow: 0 0 10px rgba(0,0,0,0.5);
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="file"] {
      width: 100%;
      padding: 10px;
      margin: 8px 0;
      border: none;
      border-radius: 6px;
      background: #404055;
      color: #fff;
    }
    button {
      width: 100%;
      padding: 10px;
      background: #3a7bd5;
      border: none;
      border-radius: 6px;
      color: #fff;
      font-size: 16px;
      cursor: pointer;
      margin-top: 10px;
    }
    button:hover {
      background: #2a5fac;
    }
    .back-link {
      display: block;
      margin-top: 15px;
      text-align: center;
      color: #3a7bd5;
      text-decoration: none;
    }
    .back-link:hover {
      text-decoration: underline;
    }
    .msg {
      text-align: center;
      margin-bottom: 10px;
      font-size: 14px;
    }
    .success {
      color: #2ecc71;
    }
    .error {
      color: #e74c3c;
    }
  </style>
</head>
<body>

<div class="settings-container">
  <h2>Settings</h2>
  <?php if($success) echo "<p class='msg success'>$success</p>"; ?>
  <?php if($error) echo "<p class='msg error'>$error</p>"; ?>
  
  <form method="POST" enctype="multipart/form-data">
    <input name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" style="background:#3a3a4f; border:none; padding:8px; border-radius:4px; color:#fff; width:100%; margin-bottom:8px;">
	<input name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" style="background:#3a3a4f; border:none; padding:8px; border-radius:4px; color:#fff; width:100%; margin-bottom:8px;">
	<input name="username" value="<?= htmlspecialchars($user['username']) ?>" style="background:#3a3a4f; border:none; padding:8px; border-radius:4px; color:#fff; width:100%; margin-bottom:8px;">
    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($user['email']) ?>">
    <input name="phone" value="<?= htmlspecialchars($user['phone']) ?>" style="background:#3a3a4f; border:none; padding:8px; border-radius:4px; color:#fff; width:100%; margin-bottom:8px;">
    
    <label style="font-size: 13px; color: #bbb; display: block; margin-top: 8px;">Profile Pic:</label>
    <input type="file" name="profile_pic">
    
    <input type="password" name="new_password" placeholder="New Password">
    <input type="password" name="confirm_password" placeholder="Confirm Password">
    
    <button type="submit">Save Changes</button>
  </form>
  
  <a href="home.php" class="back-link">🏠 Back to Home</a>
</div>

</body>
</html>
