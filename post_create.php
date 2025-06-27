<?php
session_start();
require "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);
    $image_path = '';

    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = "uploads/posts/" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $image_name);
        $image_path = $image_name;
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $content, $image_path);
    $stmt->execute();

    header("Location: home.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Post</title>
    <style>
        body { font-family: sans-serif; background: #1e1e2f; color: #fff; padding: 40px; }
        form { max-width: 600px; margin: auto; background: #2b2b3d; padding: 20px; border-radius: 10px; }
        textarea, input[type="file"] { width: 100%; margin-top: 10px; padding: 10px; border-radius: 5px; border: none; }
        button { margin-top: 15px; padding: 10px 20px; border: none; background: #3a7bd5; color: #fff; border-radius: 8px; cursor: pointer; }
        button:hover { background: #2a5fac; }
    </style>
</head>
<body>

<h2>Create a Post</h2>
<form method="POST" enctype="multipart/form-data">
    <label for="content">What's on your mind?</label>
    <textarea name="content" rows="4" required></textarea>
    
    <label for="image">Upload Image (optional):</label>
    <input type="file" name="image" accept="image/*">
    
    <button type="submit">Post</button>
</form>

</body>
</html>
