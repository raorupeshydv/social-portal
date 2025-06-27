<?php
session_start();
require "db.php";

if (isset($_POST['message_id'], $_SESSION['user_id'])) {
    $msg_id = (int)$_POST['message_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND sender_id = ?");
    $stmt->bind_param("ii", $msg_id, $user_id);
    $stmt->execute();
}
header("Location: chat_panel.php");
exit;
?>
