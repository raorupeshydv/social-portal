<?php
session_start();
require "db.php";

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT users.id, users.email FROM friends 
    JOIN users ON friends.sender_id = users.id 
    WHERE friends.receiver_id = ? AND friends.status = 'pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$results = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Friend Requests</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main">
  <h2>Friend Requests</h2>
  <?php if ($results->num_rows > 0): ?>
    <?php while ($row = $results->fetch_assoc()): ?>
      <div>
        <?= htmlspecialchars($row['email']) ?>
        <button class="accept-btn" data-id="<?= $row['id'] ?>">Accept</button>
		<button class="block-btn" data-id="<?= $row['id'] ?>">Block</button>
		<span id="req-<?= $row['id'] ?>"></span>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No pending requests</p>
  <?php endif; ?>
</div>
<script>
document.querySelectorAll('.accept-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const userId = this.getAttribute('data-id');
        const status = document.getElementById('req-' + userId);
        fetch('accept_request_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'from=' + userId
        })
        .then(res => res.json())
        .then(data => {
            status.innerHTML = "✅ " + data.message;
            this.disabled = true;
        });
    });
});

document.querySelectorAll('.block-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const userId = this.getAttribute('data-id');
        const status = document.getElementById('req-' + userId);
        fetch('block_user_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'user=' + userId
        })
        .then(res => res.json())
        .then(data => {
            status.innerHTML = "❌ " + data.message;
            this.disabled = true;
        });
    });
});
</script>
</body>
</html>
