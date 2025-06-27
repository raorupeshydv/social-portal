<?php
session_start();
require "db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$my_id = $_SESSION['user_id'];
$chat_user_id = isset($_GET['user']) ? (int)$_GET['user'] : null;

$friends = $conn->prepare("
    SELECT u.id, u.first_name, u.username FROM users u
    JOIN friends f ON f.receiver_id = u.id
    WHERE f.sender_id = ? AND f.status = 'accepted'
");
$friends->bind_param("i", $my_id);
$friends->execute();
$friend_res = $friends->get_result();

$chat_name = "Select a friend to chat";
if ($chat_user_id) {
    $stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $chat_user_id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    $chat_name = $r ? $r['first_name'] : "Unknown";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Messages</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { margin: 0; font-family: 'Segoe UI', sans-serif; display: flex; height: 100vh; background: #f1f1f1; }
    .left-panel { width: 250px; background: #343a40; color: white; padding: 20px; overflow-y: auto; }
    .left-panel h3 { margin-top: 0; }
    .left-panel a { display: block; color: #ccc; margin: 10px 0; text-decoration: none; }
    .left-panel a:hover { color: #fff; }
    .chat-box { flex: 1; display: flex; flex-direction: column; padding: 20px; }
    #chat-window { flex: 1; background: #fff; padding: 15px; border-radius: 8px; overflow-y: scroll; margin-bottom: 10px; }
    .message-bubble { max-width: 60%; padding: 10px 15px; margin: 8px 0; border-radius: 20px; display: inline-block; word-wrap: break-word; }
    .you { background: #3a7bd5; color: #fff; float: right; clear: both; }
    .friend { background: #4bd5a0; color: #fff; float: left; clear: both; }
    .timestamp { font-size: 10px; color: #999; margin-top: 4px; }
    #message-form { display: flex; gap: 10px; }
    #message-form input { flex: 1; padding: 10px; border-radius: 20px; border: 1px solid #ccc; }
    #message-form button { padding: 10px 20px; border-radius: 20px; background: #3a7bd5; color: #fff; border: none; }
	.unseen-count {
		background: #ff4757;
		color: #fff;
		padding: 2px 6px;
		border-radius: 12px;
		font-size: 11px;
		margin-left: 4px;
	}
  </style>
</head>
<body>

<div class="left-panel">
  <h3>Friends</h3>
  <?php
  while ($f = $friend_res->fetch_assoc()):
	$unseen_stmt = $conn->prepare("
		SELECT COUNT(*) AS cnt FROM messages
		WHERE sender_id = ? AND receiver_id = ? AND seen = 0
	");
	$unseen_stmt->bind_param("ii", $f['id'], $my_id);
	$unseen_stmt->execute();
	$unseen = $unseen_stmt->get_result()->fetch_assoc();
	$friend_unseen = $unseen ? $unseen['cnt'] : 0;
  ?>
  <a href="chat_panel.php?user=<?= $f['id'] ?>">
    <?= htmlspecialchars($f['first_name']) ?> (@<?= htmlspecialchars($f['username']) ?>)
    <?php if ($friend_unseen > 0): ?>
        <span class="unseen-count"><?= $friend_unseen ?></span>
    <?php endif; ?>
	</a>
  <?php endwhile; ?>
</div>

<div class="chat-box">
  <h2><?= htmlspecialchars($chat_name) ?></h2>
  <div id="typing-status"></div>
  <div id="chat-window"></div>
  <div id="typing-indicator" style="font-size: 12px; color: #ccc; margin-top: 5px;"></div>

  <?php if ($chat_user_id): ?>
  <form id="message-form">
    <input type="hidden" name="receiver" value="<?= $chat_user_id ?>">
    <input type="text" name="message" id="message" placeholder="Type your message..." required>
    <button type="submit">Send</button>
  </form>
  <?php endif; ?>
</div>

<script>
function loadMessages() {
    <?php if ($chat_user_id): ?>
    fetch("get_messages.php?user=<?= $chat_user_id ?>")
        .then(res => res.text())
        .then(html => {
            const box = document.getElementById('chat-window');
            box.innerHTML = html;
            box.scrollTop = box.scrollHeight;
        });
    <?php endif; ?>
}

function checkTyping() {
    <?php if ($chat_user_id): ?>
    fetch(`typing_status.php?check=1&user=<?= $chat_user_id ?>`)
        .then(res => res.text())
        .then(txt => document.getElementById('typing-status').innerText = txt);
    <?php endif; ?>
}

const form = document.getElementById('message-form');
if (form) {
    form.addEventListener('submit', e => {
        e.preventDefault();
        const msg = document.getElementById('message').value;
        fetch('send_message.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `receiver=<?= $chat_user_id ?>&message=${encodeURIComponent(msg)}`
        }).then(() => {
            document.getElementById('message').value = '';
            loadMessages();
        });
    });

    const input = document.getElementById('message');
    input.addEventListener('input', () => {
        fetch('typing_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `receiver=<?= $chat_user_id ?>&typing=1`
        });
        clearTimeout(window.typingTimeout);
        window.typingTimeout = setTimeout(() => {
            fetch('typing_status.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `receiver=<?= $chat_user_id ?>&typing=0`
            });
        }, 1500);
    });
}

loadMessages();
setInterval(loadMessages, 2000);
setInterval(checkTyping, 1500);
</script>
</body>
</html>
