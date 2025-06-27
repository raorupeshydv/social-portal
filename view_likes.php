<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'], $_GET['post_id'])) exit;

$post_id = (int)$_GET['post_id'];

$stmt = $conn->prepare("
    SELECT u.first_name, u.username, u.profile_pic 
    FROM likes l 
    JOIN users u ON l.user_id = u.id 
    WHERE l.post_id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Likes</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .like-list { max-width: 600px; margin: 50px auto; background: #2b2b3d; padding: 20px; border-radius: 8px; color: #fff; }
        .like-item { display: flex; align-items: center; margin-bottom: 10px; }
        .like-item img { width: 40px; height: 40px; border-radius: 50%; margin-right: 10px; }
    </style>
</head>
<body>
<div class="like-list">
    <h2>People who liked this post</h2>
    <?php if ($res->num_rows > 0): ?>
        <?php while ($row = $res->fetch_assoc()): ?>
            <div class="like-item">
                <img src="<?= htmlspecialchars($row['profile_pic'] ?? 'assets/images/default-profile.png') ?>" alt="Profile">
                <?= htmlspecialchars($row['first_name']) ?> (@<?= htmlspecialchars($row['username']) ?>)
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No likes yet!</p>
    <?php endif; ?>
</div>
</body>
</html>
