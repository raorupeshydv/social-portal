<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'], $_GET['post_id'])) exit;
$post_id = (int)$_GET['post_id'];

$stmt = $conn->prepare("
    SELECT u.id, u.first_name, u.username, u.profile_pic 
    FROM likes l 
    JOIN users u ON l.user_id = u.id 
    WHERE l.post_id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $pic = htmlspecialchars($row['profile_pic'] ?? 'assets/images/default-profile.png');
    $name = htmlspecialchars($row['first_name']);
    $uname = htmlspecialchars($row['username']);
    echo "<div style='display:flex; align-items:center; margin-bottom:5px;'>
        <a href='profile.php?user={$row['id']}'><img src='$pic' style='width:30px;height:30px;border-radius:50%;margin-right:8px;'></a>
        <a href='profile.php?user={$row['id']}' style='color:#3a7bd5;'>$name (@$uname)</a>
    </div>";
}
