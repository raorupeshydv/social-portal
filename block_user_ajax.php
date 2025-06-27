<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'], $_POST['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$blocker = $_SESSION['user_id'];
$blocked = (int)$_POST['user'];

$conn->query("DELETE FROM friends WHERE (sender_id = $blocker AND receiver_id = $blocked) OR (sender_id = $blocked AND receiver_id = $blocker)");

$stmt = $conn->prepare("INSERT INTO friends (sender_id, receiver_id, status) VALUES (?, ?, 'blocked')");
$stmt->bind_param("ii", $blocker, $blocked);
$stmt->execute();

echo json_encode(['status' => 'success', 'message' => 'User blocked']);
