<?php 
session_start(); 
require "db.php"; 

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit; 
} 

$my_id = $_SESSION['user_id']; 
$profile_id = isset($_GET['user']) ? (int)$_GET['user'] : $my_id; 

$stmt = $conn->prepare("SELECT first_name, last_name, username, email, phone, profile_pic FROM users WHERE id = ?"); 
$stmt->bind_param("i", $profile_id); 
$stmt->execute(); 
$user = $stmt->get_result()->fetch_assoc(); 

$profile_pic = (!empty($user['profile_pic'])) ? $user['profile_pic'] : 'assets/images/default-profile.png'; 

$is_friend = false; 
if ($profile_id != $my_id) { 
    $friend_stmt = $conn->prepare(" 
        SELECT * FROM friends  
        WHERE ( 
            (sender_id = ? AND receiver_id = ?)  
            OR  
            (sender_id = ? AND receiver_id = ?) 
        ) 
        AND status = 'accepted' 
    "); 
    $friend_stmt->bind_param("iiii", $my_id, $profile_id, $profile_id, $my_id); 
    $friend_stmt->execute(); 
    $is_friend = $friend_stmt->get_result()->num_rows > 0; 
} 
?> 

<!DOCTYPE html> 
<html> 
<head> 
    <title>Profile</title> 
    <style> 
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #1e1e2f;
            color: #fff;
            padding: 40px;
        }
        .profile-container {
            max-width: 600px;
            margin: auto;
            background: #2b2b3d;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .profile-container img {
            width: 120px;
            border-radius: 50%;
            margin-bottom: 15px;
        }
        .profile-container h2 {
            margin-bottom: 5px;
        }
        .profile-container p {
            margin: 3px 0;
        }
        .profile-container a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background: #3a7bd5;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }
        .profile-container a:hover {
            background: #2a5fac;
        }
        .post {
            background: #fff;
            color: #000;
            padding: 10px;
            border-radius: 6px;
            margin: 15px auto;
            max-width: 500px;
        }
        .post img {
            width: 100%;
            border-radius: 6px;
        }
        .post .desc {
            padding-top: 6px;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profile Picture">
    <h2><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
    <p>@<?= htmlspecialchars($user['username']) ?></p>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <p>Phone: <?= htmlspecialchars($user['phone']) ?></p>

    <a href="home.php">🏠 Back to Home</a>
</div>

<div class="profile-container" style="margin-top:20px; text-align:left;">
    <h3>Posts</h3>
    <?php 
    $post_stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY timestamp DESC");
    $post_stmt->bind_param("i", $profile_id);
    $post_stmt->execute();
    $post_res = $post_stmt->get_result();

    if ($post_res->num_rows > 0):
        while ($post = $post_res->fetch_assoc()):
    ?>
        <div class="post">
            <?php if (!empty($post['image'])): ?>
                <a href="post_view.php?post_id=<?= $post['id'] ?>">
                    <img src="<?= htmlspecialchars($post['image']) ?>" alt="Post">
                </a>
            <?php endif; ?>
            <?php if (!empty($post['content'])): ?>
                <div class="desc"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
            <?php endif; ?>
        </div>
    <?php 
        endwhile;
    else:
        echo "<p style='text-align:center;'>No posts yet!</p>";
    endif;
    ?>
</div>

</body>
</html>
