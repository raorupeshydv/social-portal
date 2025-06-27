<?php
session_start();
require "db.php";
if (!isset($_SESSION['user_id'], $_POST['post_id'])) exit;
$post_id = (int)$_POST['post_id'];
$uid = $_SESSION['user_id'];
$post_id = (int)$_POST['post_id'];
$stmt = $conn->prepare("SELECT image FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $image_path = $row['image'];
    if (!empty($image_path) && file_exists($image_path)) {
        unlink($image_path);  // delete the image file
    }
}
$conn->query("DELETE FROM posts WHERE id = $post_id");
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $uid);
$stmt->execute();
header("Location: profile.php");
?>
