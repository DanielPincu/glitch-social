<?php
require_once __DIR__ . '/includes/controllers/UserController.php';
require_once __DIR__ . '/includes/controllers/PostController.php';
require_once __DIR__ . '/includes/helpers/Session.php'; 

$session = new Session();
$userController = new UserController();
$postController = new PostController();

// Redirect if not logged in
if (!$session->isLoggedIn()) {
    header("Location: login_loader.php");
    exit;
}

// Check if user is blocked
$user_id = $session->getUserId();
$blocked_message = '';
if ($userController->isBlocked($user_id)) {
    $blocked_message = "You are blocked. You cannot access the feed.";
}

// Handle new post submission
if (isset($_POST['post_submit']) && !$blocked_message) {
    $content = $_POST['content'] ?? '';
    $file = $_FILES['imageFile'] ?? null;

    // Create post with optional image
    $postController->createPost($user_id, $content, $file);

    // Refresh page
    header("Location: index.php");
    exit;
}

// Fetch posts for view
$posts = $postController->getPosts();

// Page title for header
$title = "Home";

// Load views
require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/home_view.php';
require __DIR__ . '/includes/views/footer.php';