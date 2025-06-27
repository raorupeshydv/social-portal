<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get first name & profile
$res = $conn->query("SELECT profile_pic, first_name FROM users WHERE id = $user_id");
$user = $res->fetch_assoc();

$pic = $user['profile_pic'] ?? 'assets/images/default-profile.png';
$fname = $user['first_name'] ?? 'User';
?>

<!DOCTYPE html>
<html>
<head>
  <title>Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="sidebar">
    <img src="<?= $pic ?>" 
	alt="Profile Picture" 
    onerror="this.onerror=null;this.src='assets/images/default-profile.png';"
    style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 2px solid #ccc;">
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="search_friends.php">Friends</a></li>
        <li><a href="chat_panel.php">Messages</a></li>
        <li><a href="update_profile.php">Update Profile</a></li>
        <li><a href="manage_requests.php">Friend Requests</a></li>
        <li>Notifications</li>
        <li>Scrap</li>
        <li>Post</li>
        <li>Settings</li>
        <li>Game</li>
        <li>Group Chat</li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
<div class="main">
    <h2>Welcome <?= htmlspecialchars($fname) ?></h2>
    <p>This is your dashboard.</p>
</div>
</body>
</html>
