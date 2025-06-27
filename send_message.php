<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'], $_POST['receiver'], $_POST['message'])) {
    exit('Missing data');
}

$sender = $_SESSION['user_id'];
$receiver = (int)$_POST['receiver'];
$message = trim($_POST['message']);

if ($message === '') exit('Empty message');

if ($stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)")) {
    $stmt->bind_param("iis", $sender, $receiver, $message);
    if ($stmt->execute()) {
        echo "Inserted!";
    } else {
        echo "Execute error: " . $stmt->error;
    }
} else {
    echo "Prepare error: " . $conn->error;
}
?>
