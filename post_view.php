<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'], $_GET['post_id'])) {
    header("Location: login.php");
    exit;
}

$my_id = $_SESSION['user_id'];
$post_id = (int)$_GET['post_id'];

// Fetch post + user
$stmt = $conn->prepare("
    SELECT p.*, u.first_name, u.username, u.profile_pic 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    die("Post not found");
}

// Check friend status if not your post
if ($post['user_id'] != $my_id) {
    $friend_stmt = $conn->prepare("
        SELECT * FROM friends 
        WHERE 
        (
            (sender_id = ? AND receiver_id = ?) OR 
            (sender_id = ? AND receiver_id = ?)
        )
        AND status = 'accepted'
    ");
    $friend_stmt->bind_param("iiii", $my_id, $post['user_id'], $post['user_id'], $my_id);
    $friend_stmt->execute();
    if ($friend_stmt->get_result()->num_rows === 0) {
        die("You are not friends with this user.");
    }
}

// Like count
$like_res = $conn->query("SELECT COUNT(*) AS likes FROM likes WHERE post_id = $post_id");
$like_count = $like_res->fetch_assoc()['likes'] ?? 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Post</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #1e1e2f;
            color: #fff;
            padding: 20px;
        }
        .post {
            background: #2b2b3d;
            padding: 15px;
            border-radius: 8px;
            max-width: 500px;
            margin: auto;
        }
        .post img.post-image {
            width: 100%;
            border-radius: 6px;
            margin-top: 10px;
        }
        .post-header {
            display: flex;
            align-items: center;
        }
        .post-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .like-count {
            margin-top: 8px;
            cursor: pointer;
            color: #3a7bd5;
        }
        .post-actions {
            margin-top: 10px;
        }
        .comments {
            margin-top: 15px;
        }
        .comments div {
            margin-bottom: 5px;
        }
        .comment-form {
            display: flex;
            margin-top: 10px;
        }
        .comment-form input {
            flex: 1;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 5px;
        }
        .comment-form button {
            padding: 5px 10px;
            border: none;
            background: #3a7bd5;
            color: #fff;
            border-radius: 4px;
        }
        .likes-list {
            display: none;
            background: #444;
            padding: 10px;
            border-radius: 6px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="post">
    <div class="post-header">
		<a href="profile.php?user=<?= $post['user_id'] ?>" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
			<img src="<?= htmlspecialchars($post['profile_pic'] ?? 'assets/images/default-profile.png') ?>" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;">
			<strong><?= htmlspecialchars($post['username']) ?></strong>
		</a>
	</div>
    <div><?= nl2br(htmlspecialchars($post['content'])) ?></div>
    <?php if (!empty($post['image'])): ?>
        <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post Image">
    <?php endif; ?>
    
    <div class="like-count" onclick="showLikes(<?= $post_id ?>)">
        <?= $like_count ?> Likes
    </div>
    <div class="post-actions">
        <button onclick="likePost(<?= $post_id ?>)">❤️ Like</button>
    </div>
	
	<?php if ($post['user_id'] == $my_id): ?>
		<form method="POST" action="delete_post.php" onsubmit="return confirm('Are you sure you want to delete this post?');">
			<input type="hidden" name="post_id" value="<?= $post['id'] ?>">
			<button type="submit" style="background:#e74c3c; color:#fff; border:none; padding:4px 8px; border-radius:4px; cursor:pointer;">🗑 Delete Post</button>
		</form>
	<?php endif; ?>

    <div class="likes-list" id="likes-list"></div>

    <div class="comments" id="comments"></div>

    <form class="comment-form" onsubmit="return submitComment(<?= $post_id ?>)">
        <input type="text" id="comment-input" placeholder="Add a comment...">
        <button type="submit">Post</button>
    </form>
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

function showLikes(postId) {
    const list = document.getElementById('likes-list');
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

function loadComments() {
    fetch('get_comments.php?post_id=<?= $post_id ?>')
    .then(res => res.text())
    .then(data => {
        document.getElementById('comments').innerHTML = data;
    });
}
loadComments();

function submitComment(postId) {
    const input = document.getElementById('comment-input');
    const comment = input.value.trim();
    if (!comment) return false;
    fetch('add_comment.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'post_id=' + postId + '&comment=' + encodeURIComponent(comment)
    })
    .then(() => {
        loadComments();
        input.value = '';
    });
    return false;
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

function deleteMessage(msgId) {
  if (confirm("Delete this message?")) {
    fetch('delete_message.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: `message_id=${msgId}`
    }).then(() => location.reload());
  }
}
</script>

</body>
</html>
