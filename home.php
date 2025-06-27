<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$my_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT first_name, profile_pic FROM users WHERE id = ?");
$user_stmt->bind_param("i", $my_id);
$user_stmt->execute();
$user_res = $user_stmt->get_result()->fetch_assoc();
$first_name = $user_res ? $user_res['first_name'] : 'User';
$profile_pic = (!empty($user_res['profile_pic'])) ? $user_res['profile_pic'] : 'assets/images/default-profile.png';
?>
<!DOCTYPE html>
<html>
<head>
  <title>Home</title>
  <style>
    body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #f5f6fa; display: flex; }
    .sidebar { width: 220px; background: #2b2b3d; color: #fff; padding: 20px; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; height: 100vh; }
    .sidebar img { width: 100px; border-radius: 50%; margin-bottom: 10px; }
    .sidebar a { color: #ccc; text-decoration: none; margin: 8px 0; display: block; padding: 8px; border-radius: 6px; text-align: center; }
    .sidebar a:hover { background: #3a7bd5; color: #fff; }
    .unseen-count { background: #ff4757; color: #fff; padding: 2px 6px; border-radius: 12px; font-size: 11px; margin-left: 4px; }
    .content { flex: 1; padding: 30px; overflow-y: auto; }
    .post-card { background: #fff; border: 1px solid #ddd; border-radius: 10px; margin-bottom: 20px; max-width: 500px; margin-left: auto; margin-right: auto; }
    .post-header { display: flex; align-items: center; padding: 10px; }
    .post-header img { width: 40px; height: 40px; border-radius: 50%; margin-right: 10px; }
    .post-image { width: 100%; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd; }
    .post-description { padding: 10px; }
    .post-actions { display: flex; justify-content: space-between; padding: 10px; border-top: 1px solid #ddd; }
    .like-count { padding: 0 10px; font-size: 14px; color: #3a7bd5; cursor: pointer; }
    .comments { padding: 0 10px; }
    .comments div { margin-bottom: 4px; font-size: 13px; }
    .comment-form { display: flex; padding: 10px; }
    .comment-form input { flex: 1; padding: 5px; border: 1px solid #ccc; border-radius: 4px; margin-right: 5px; }
    .comment-form button { padding: 5px 10px; background: #3a7bd5; border: none; color: #fff; border-radius: 4px; cursor: pointer; }
    .likes-list { display: none; padding: 10px; background: #eee; border-radius: 8px; margin: 0 10px 10px 10px; }
  </style>
</head>
<body>

<div class="sidebar">
  <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profile Picture">
  <h2><?= htmlspecialchars($first_name) ?></h2>
  <a href="home.php">🏠 Home</a>
  <?php
    $count_stmt = $conn->prepare("SELECT COUNT(DISTINCT sender_id) AS cnt FROM messages WHERE receiver_id = ? AND seen = 0");
    $count_stmt->bind_param("i", $my_id);
    $count_stmt->execute();
    $count_res = $count_stmt->get_result()->fetch_assoc();
    $msg_count = $count_res ? $count_res['cnt'] : 0;
  ?>
  <a href="chat_panel.php">Messages
    <?php if ($msg_count > 0): ?>
      <span class="unseen-count"><?= $msg_count ?></span>
    <?php endif; ?>
  </a>
  <a href="search_friends.php">🔍 Search Friends</a>
  <a href="friend_requests.php">👥 Friend Requests</a>
  <a href="post_create.php">📝 Create Post</a>
  <a href="profile.php">👤 View Profile</a>
  <a href="settings.php">⚙️ Settings</a>
  <a href="logout.php">🚪 Logout</a>
</div>

<div class="content">
  <h1>Recent Posts</h1>
  <?php
  $res = $conn->query("
	SELECT p.*, u.first_name, u.username, u.profile_pic 
	FROM posts p 
	JOIN users u ON p.user_id = u.id 
	WHERE 
		p.user_id = $my_id 
		OR p.user_id IN (
			SELECT 
				CASE 
					WHEN sender_id = $my_id THEN receiver_id 
					WHEN receiver_id = $my_id THEN sender_id 
				END AS friend_id
			FROM friends 
			WHERE 
				(sender_id = $my_id OR receiver_id = $my_id)
				AND status = 'accepted'
		)
	ORDER BY p.timestamp DESC
  ");
  if ($res && $res->num_rows > 0):
    while ($post = $res->fetch_assoc()):
      $post_id = $post['id'];
      $like_count_res = $conn->query("SELECT COUNT(*) AS likes FROM likes WHERE post_id = $post_id");
      $like_count = $like_count_res->fetch_assoc()['likes'] ?? 0;
  ?>
  <div class="post-card">
    <div class="post-header">
      <a href="profile.php?user=<?= $post['user_id'] ?>">
		<img src="<?= htmlspecialchars($post['profile_pic'] ?? 'assets/images/default-profile.png') ?>" alt="Profile">
	  </a>
      <div>
        <a href="profile.php?user=<?= $post['user_id'] ?>" style="text-decoration:none; color:inherit;">
			<strong>@<?= htmlspecialchars($post['username']) ?></strong>
		</a><br>
        <small><?= date('M d, Y h:i A', strtotime($post['timestamp'])) ?></small>
      </div>
    </div>
    <?php if (!empty($post['image'])): ?>
      <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post">
    <?php endif; ?>
    <?php if (!empty($post['content'])): ?>
      <div class="post-description"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
    <?php endif; ?>
    <div class="like-count" onclick="showLikes(<?= $post_id ?>)"><?= $like_count ?> Likes</div>
    <div class="post-actions">
      <button onclick="likePost(<?= $post_id ?>)">❤️ Like</button>
    </div>
	<?php if ($post['user_id'] == $my_id): ?>
		<form method="POST" action="delete_post.php" onsubmit="return confirm('Are you sure you want to delete this post?');">
			<input type="hidden" name="post_id" value="<?= $post['id'] ?>">
			<button type="submit" style="background:#e74c3c;color:white;padding:4px 8px;border:none;border-radius:4px;cursor:pointer;">🗑 Delete Post</button>
		</form>
	<?php endif; ?>
    <div class="likes-list" id="likes-list-<?= $post_id ?>"></div>
    <div class="comments" id="comments-<?= $post_id ?>"></div>
	<script>window.addEventListener('DOMContentLoaded', () => loadComments(<?= $post_id ?>));</script>
    <form class="comment-form" onsubmit="return submitComment(<?= $post_id ?>)">
      <input type="text" id="comment-input-<?= $post_id ?>" placeholder="Add a comment...">
      <button type="submit">Post</button>
    </form>
  </div>
  <?php
    endwhile;
  else:
  ?>
  <div style="text-align:center; margin-top:20px;">
	<p>No posts yet. Be the first to post something!</p>
	<a href="post_create.php" style="display:inline-block; margin:10px; padding:10px 15px; background:#3a7bd5; color:#fff; border-radius:6px; text-decoration:none;">📝 Create Post</a>
	<a href="search_friends.php" style="display:inline-block; margin:10px; padding:10px 15px; background:#3a7bd5; color:#fff; border-radius:6px; text-decoration:none;">🔍 Search Friends</a>
	<p>Tip: Start by connecting with friends or sharing your thoughts!</p>
  </div>
  <?php
  endif;
  ?>
</div>

<script>
function likePost(postId) {
  fetch('like_post.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'post_id=' + postId
  })
  .then(res => res.json())
  .then(data => {
    alert(data.status);
    location.reload();
  });
}

function submitComment(postId) {
  const input = document.getElementById('comment-input-' + postId);
  const comment = input.value.trim();
  if (!comment) return false;
  fetch('add_comment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'post_id=' + postId + '&comment=' + encodeURIComponent(comment)
  })
  .then(() => {
    loadComments(postId);
    input.value = '';
  });
  return false;
}

function loadComments(postId) {
  fetch('get_comments.php?post_id=' + postId)
  .then(res => res.text())
  .then(data => {
    document.getElementById('comments-' + postId).innerHTML = data;
  });
}

function showLikes(postId) {
  const list = document.getElementById('likes-list-' + postId);
  if (list.style.display === 'none' || list.style.display === '') {
    fetch('get_likes.php?post_id=' + postId)
    .then(res => res.text())
    .then(data => {
      list.innerHTML = data;
      list.style.display = 'block';
    });
  } else {
    list.style.display = 'none';
  }
}

function deleteComment(postId, commentId) {
    if (confirm('Delete this comment?')) {
        fetch('delete_comment.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `post_id=${postId}&comment_id=${commentId}`
        }).then(() => loadComments(postId));
    }
}
</script>
</body>
</html>
