<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'], $_POST['post_id'])) exit;

$user_id = $_SESSION['user_id'];
$post_id = (int)$_POST['post_id'];

// Check if already liked
$check = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$check->bind_param("ii", $user_id, $post_id);
$check->execute();
$check_res = $check->get_result();

if ($check_res->num_rows > 0) {
    // Already liked, remove like
    $del = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $del->bind_param("ii", $user_id, $post_id);
    $del->execute();
    echo json_encode(["status" => "removed"]);
} else {
    // Add like
    $ins = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $ins->bind_param("ii", $user_id, $post_id);
    $ins->execute();
    echo json_encode(["status" => "liked"]);
}
