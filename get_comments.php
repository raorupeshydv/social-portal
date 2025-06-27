<?php
require "db.php";
session_start();

if (!isset($_GET['post_id'])) exit;

$post_id = (int)$_GET['post_id'];

$stmt = $conn->prepare("
  SELECT c.id, c.comment, c.timestamp, u.username, u.profile_pic, u.id AS user_id
  FROM comments c 
  JOIN users u ON c.user_id = u.id 
  WHERE c.post_id = ?
  ORDER BY c.timestamp DESC
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $pic = (!empty($row['profile_pic'])) ? htmlspecialchars($row['profile_pic']) : 'assets/images/default-profile.png';
    $name = htmlspecialchars($row['username']);
    $uid = (int)$row['user_id'];
    $comment = htmlspecialchars($row['comment']);
    $time = date('M d, H:i', strtotime($row['timestamp']));

    echo "
	<div style='display:flex; align-items:center; margin-bottom:5px;'>
		<a href=\"profile.php?user=$uid\">
			<img src=\"$pic\" style=\"width:25px; height:25px; border-radius:50%; margin-right:5px;\">
		</a>
		<a href=\"profile.php?user=$uid\" style=\"text-decoration:none; color:inherit;\">
			<strong>@$name</strong>
		</a>: $comment
	";
	if ($_SESSION['user_id'] == $uid) {
		echo " <button onclick='deleteComment($post_id, {$row['id']})' style='background:none; border:none; color:red; cursor:pointer;'>❌</button>";
	}
	echo "</div>";
}
?>
