<?php
session_start();
require "db.php";
if (!isset($_SESSION['user_id'], $_GET['user'])) die("Unauthorized access");

$my_id = $_SESSION['user_id'];
$friend_id = (int)$_GET['user'];

$name_stmt = $conn->prepare("SELECT first_name, username FROM users WHERE id = ?");
$name_stmt->bind_param("i", $friend_id);
$name_stmt->execute();
$name_res = $name_stmt->get_result()->fetch_assoc();
$friend_name = $name_res ? $name_res['first_name'] : 'Friend';

// Check friendship
$check = $conn->prepare("SELECT * FROM friends WHERE sender_id = ? AND receiver_id = ? AND status = 'accepted'");
$check->bind_param("ii", $my_id, $friend_id);
$check->execute();
if ($check->get_result()->num_rows === 0) die("You're not friends with this user.");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat with <?= htmlspecialchars($friend_name) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="chat-container">
    <h2>Chat with <?= htmlspecialchars($friend_name) ?></h2>
    <div id="typing-status"></div>
    <div id="chat-box"></div>
    <form id="chat-form">
        <input type="hidden" name="receiver" value="<?= $friend_id ?>">
        <input type="text" name="message" id="message" placeholder="Type your message..." required>
        <button type="submit">Send</button>
    </form>
</div>

<script>
function loadMessages() {
    fetch('get_messages.php?user=<?= $friend_id ?>')
        .then(res => res.text())
        .then(html => {
            const box = document.getElementById('chat-box');
            box.innerHTML = html;
            box.scrollTop = box.scrollHeight;
        });
}

function checkTyping() {
    fetch(`typing_status.php?check=1&user=<?= $friend_id ?>`)
        .then(res => res.text())
        .then(txt => document.getElementById('typing-status').innerText = txt);
}

document.getElementById('chat-form').addEventListener('submit', e => {
    e.preventDefault();
    const msg = document.getElementById('message').value;
    fetch('send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `receiver=<?= $friend_id ?>&message=${encodeURIComponent(msg)}`
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
        body: `receiver=<?= $friend_id ?>&typing=1`
    });
    clearTimeout(window.typingTimeout);
    window.typingTimeout = setTimeout(() => {
        fetch('typing_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `receiver=<?= $friend_id ?>&typing=0`
        });
    }, 1500);
});

loadMessages();
setInterval(loadMessages, 2000);
setInterval(checkTyping, 1500);
</script>
</body>
</html>
