<?php
require_once __DIR__ . '/includes/controllers/PostController.php';
require_once __DIR__ . '/includes/controllers/UserController.php';
require_once __DIR__ . '/includes/helpers/Session.php';

$session = new Session();
$postController = new PostController();
$userController = new UserController();

$user_id = $session->getUserId();
$blocked_message = null;

// Redirect if not logged in
if (!$session->isLoggedIn()) {
    header("Location: login_loader.php");
    exit;
}

// Redirect blocked users
if ($userController->isBlocked($user_id)) {
    $blocked_message = "You are blocked from posting or interacting.";
}

// Handle likes/unlikes
if (isset($_GET['post_id'])) {
    $post_id = (int)$_GET['post_id'];
    if (isset($_GET['action']) && $_GET['action'] === 'unlike') {
        $postController->unlikePost($post_id, $user_id);
    } else {
        $postController->likePost($post_id, $user_id);
    }
    header("Location: index.php");
    exit;
}

// Handle new post submission
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
                // Save relative path to DB
                $imagePath = 'img/' . $newFile;
            }
        }
    }

    $postController->createPost($user_id, $content, $imagePath);
    header("Location: index.php");
    exit;
}

// Fetch posts for view
$posts = $postController->getPosts();