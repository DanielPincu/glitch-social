<?php
session_start();
require_once __DIR__ . '/includes/controllers/PostController.php';
require_once __DIR__ . '/includes/controllers/UserController.php';

$postController = new PostController();
$userController = new UserController();

$user_id = $_SESSION['user_id'] ?? null;
$blocked_message = null;

// Redirect blocked users
if ($user_id && $userController->isBlocked($user_id)) {
    $blocked_message = "You are blocked from posting or interacting.";
}

// Handle post submission
if (isset($_POST['post_submit']) && !$blocked_message) {
    $content = $_POST['content'] ?? '';
    $imagePath = null;

    if (!empty($_FILES['imageFile']['tmp_name'])) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['imageFile']['tmp_name']);

        if (in_array($fileType, $allowedTypes) && $_FILES['imageFile']['size'] <= 5*1024*1024) {
            $uploadDir = __DIR__ . '/img/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext = pathinfo($_FILES['imageFile']['name'], PATHINFO_EXTENSION);
            $newFile = 'img_' . uniqid() . '.' . $ext;
            $uploadPath = $uploadDir . $newFile;

            if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $uploadPath)) {
                $imagePath = 'img/' . $newFile; // relative path for DB
            }
        }
    }

    $postController->createPost($user_id, $content, $imagePath);
    header("Location: index.php");
    exit;
}

// Fetch posts for the view
$posts = $postController->getPosts();