<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'], $_POST['post_id'], $_POST['comment'])) exit;

$user_id = $_SESSION['user_id'];
$post_id = (int)$_POST['post_id'];
$comment = trim($_POST['comment']);

if ($comment === '') exit;

$stmt = $conn->prepare("INSERT INTO comments (user_id, post_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $post_id, $comment);
$stmt->execute();

echo json_encode(["status" => "success", "comment" => htmlspecialchars($comment)]);
