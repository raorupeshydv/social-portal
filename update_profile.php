<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile'])) {
    $file = $_FILES['profile'];
    
    if ($file['error'] === 0 && $file['size'] < 2 * 1024 * 1024) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = "Invalid file type. Allowed: JPG, PNG, GIF, WEBP";
        } else {
            $new_filename = "uploads/profile_pics/user_" . $user_id . "." . $ext;

            // Fetch old pic
            $res = $conn->query("SELECT profile_pic FROM users WHERE id = $user_id");
            $user = $res->fetch_assoc();
            $old_pic = $user['profile_pic'];

            // Delete old pic if it's not the default
            if ($old_pic && $old_pic !== 'assets/images/default-profile.png' && file_exists($old_pic)) {
                unlink($old_pic);
            }

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $new_filename)) {
                $conn->query("UPDATE users SET profile_pic = '$new_filename' WHERE id = $user_id");
                $success = "Profile picture updated successfully!";
            } else {
                $error = "Failed to move uploaded file.";
            }
        }
    } else {
        $error = "File too large or not uploaded correctly. Max size: 2MB";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Update Profile Picture</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main">
  <h2>Update Profile Picture</h2>
  <?php if ($error) echo "<p class='error'>$error</p>"; ?>
  <?php if ($success) echo "<p class='success'>$success</p>"; ?>
  <form method="POST" enctype="multipart/form-data">
    <input type="file" name="profile" accept="image/*" required><br><br>
    <button type="submit">Upload</button>
  </form>
  <p><a href="dashboard.php">← Back to Dashboard</a></p>
</div>
</body>
</html>
