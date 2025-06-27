<?php session_start(); require "db.php"; 

$user_id = $_SESSION['user_id']; 
$results = []; 
$query = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = trim($_POST['search']); 
    $stmt = $conn->prepare("
        SELECT id, username, first_name, last_name, profile_pic FROM users 
        WHERE (username LIKE ? OR first_name LIKE ? OR last_name LIKE ?)
        AND id != ?
        AND id NOT IN (
            SELECT receiver_id FROM friends WHERE sender_id = ? AND status = 'blocked'
        )
    ");
    $searchTerm = "%$query%";
    $stmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $user_id, $user_id);
    $stmt->execute();
    $results = $stmt->get_result();
}

function getFriendStatus($conn, $user_id, $target_id) {
    $check = $conn->prepare("SELECT status FROM friends WHERE sender_id = ? AND receiver_id = ?");
    $check->bind_param("ii", $user_id, $target_id);
    $check->execute();
    return $check->get_result()->fetch_assoc()['status'] ?? null;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Search Friends</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="main">
  <h2>Search for Friends</h2>
  <form method="POST" style="text-align:center;">
    <input type="text" name="search" placeholder="Enter name or username" required style="padding:10px; width:300px;">
    <button type="submit" style="padding:10px;">Search</button>
  </form>

  <?php if ($results): ?>
    <h3>Results:</h3>
    <div>
    <?php while ($row = $results->fetch_assoc()): 
        $status = getFriendStatus($conn, $user_id, $row['id']);
    ?>
      <div class="user-inline">
        <img src="<?= htmlspecialchars($row['profile_pic'] ?? 'assets/images/default-profile.png') ?>" class="user-pic-small" alt="Profile">
        <div class="user-info">
          <div class="user-name"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></div>
          <div class="user-username">@<?= htmlspecialchars($row['username']) ?></div>
        </div>
        <div class="user-actions">
          <?php if ($status === 'pending'): ?>
            Pending
          <?php elseif ($status === 'accepted'): ?>
            Friends
          <?php elseif ($status === 'blocked'): ?>
            Blocked
          <?php elseif ($status === null): ?>
            <button class="add-friend-btn" data-id="<?= $row['id'] ?>">Add Friend</button>
            <span id="status-<?= $row['id'] ?>"></span>
          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
    </div>
  <?php endif; ?>
</div>
<script>
document.querySelectorAll('.add-friend-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    const userId = this.getAttribute('data-id');
    const statusBox = document.getElementById('status-' + userId);
    this.disabled = true;
    fetch('send_request.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'to=' + userId
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        statusBox.innerHTML = "✅ Request sent";
      } else {
        statusBox.innerHTML = "⚠️ " + data.message;
        this.disabled = false;
      }
    })
    .catch(() => {
      statusBox.innerHTML = "❌ Error sending request";
      this.disabled = false;
    });
  });
});
</script>
</body>
</html>
