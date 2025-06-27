<?php
session_start();
require "db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.id, u.first_name, u.username FROM users u
    JOIN friends f ON f.receiver_id = u.id
    WHERE f.sender_id = ? AND f.status = 'accepted'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Messages</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main">
  <h2>Your Conversations</h2>
  <ul style="list-style: none; padding-left: 0;">
    <?php while ($row = $res->fetch_assoc()): ?>
      <li style="margin-bottom: 10px;">
        <strong><?= htmlspecialchars($row['first_name']) ?></strong> (@<?= htmlspecialchars($row['username']) ?>)
        <a href="chat.php?user=<?= $row['id'] ?>" style="margin-left: 10px;">💬 Open Chat</a>
      </li>
    <?php endwhile; ?>
  </ul>
</div>
</body>
</html>
