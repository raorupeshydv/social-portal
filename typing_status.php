<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) exit;

$my_id = $_SESSION['user_id'];

if (isset($_POST['receiver'], $_POST['typing'])) {
    $friend_id = (int)$_POST['receiver'];
    $typing = (int)$_POST['typing'];

    $stmt = $conn->prepare("UPDATE friends SET typing=? WHERE sender_id=? AND receiver_id=?");
    $stmt->bind_param("iii", $typing, $my_id, $friend_id);
    $stmt->execute();
    exit;
}

if (isset($_GET['check'], $_GET['user'])) {
    $friend_id = (int)$_GET['user'];
    $stmt = $conn->prepare("SELECT typing FROM friends WHERE sender_id=? AND receiver_id=?");
    $stmt->bind_param("ii", $friend_id, $my_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && $result['typing']) {
        echo "💬 Typing...";
    } else {
        echo "";
    }
    exit;
}
?>
