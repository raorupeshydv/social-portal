<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id']) || !isset($_POST['to'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$from = $_SESSION['user_id'];
$to = (int)$_POST['to'];

$stmt = $conn->prepare("INSERT INTO friends (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $from, $to);

try {
    $stmt->execute();
    echo json_encode(['status' => 'success', 'message' => 'Request sent']);
} catch (mysqli_sql_exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Request already exists']);
}
