<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require "db.php";

if (!isset($_SESSION['user_id'], $_GET['user'])) exit;

$me = $_SESSION['user_id'];
$them = (int)$_GET['user'];

$conn->query("UPDATE messages SET seen=1 WHERE sender_id=$them AND receiver_id=$me");

$res = $conn->query("SELECT * FROM messages WHERE 
  (sender_id=$me AND receiver_id=$them) OR (sender_id=$them AND receiver_id=$me)
  ORDER BY timestamp ASC");

while ($row = $res->fetch_assoc()) {
  $class = $row['sender_id'] == $me ? 'you' : 'friend';
  $time = date("h:i A", strtotime($row['timestamp']));
  echo "<div class='message-bubble $class'>" .
      htmlspecialchars($row['message']) .
      "<div class='timestamp'>$time" . ($row['seen'] ? ' ✅' : '') . "</div>";

	if ($row['sender_id'] == $me) {
		echo "<form method='POST' action='delete_message.php' onsubmit='return confirm(\"Are you sure you want to delete this message?\");' style='margin-top:3px;'>
				<input type='hidden' name='message_id' value='{$row['id']}'>
				<button type='submit' style='background:#e74c3c; color:#fff; border:none; padding:2px 6px; border-radius:4px; cursor:pointer;'>🗑</button>
			  </form>";
	}
  echo "</div>";
}
