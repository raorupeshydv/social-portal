<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'], $_POST['from'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$receiver_id = $_SESSION['user_id'];
$sender_id = (int)$_POST['from'];

$stmt = $conn->prepare("UPDATE friends SET status = 'accepted' WHERE sender_id = ? AND receiver_id = ?");
$stmt->bind_param("ii", $sender_id, $receiver_id);
$stmt->execute();
$check = $conn->prepare("SELECT id FROM friends WHERE sender_id = ? AND receiver_id = ?");
$check->bind_param("ii", $receiver_id, $sender_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO friends (sender_id, receiver_id, status) VALUES (?, ?, 'accepted')");
    $insert->bind_param("ii", $receiver_id, $sender_id);
    $insert->execute();
}

echo json_encode(['status' => 'success', 'message' => 'Friend request accepted']);
