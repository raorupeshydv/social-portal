<?php
session_start();
require "db.php";
if (!isset($_SESSION['user_id'], $_POST['comment_id'])) exit;
$comment_id = (int)$_POST['comment_id'];
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $comment_id, $user_id);
$stmt->execute();
?>
