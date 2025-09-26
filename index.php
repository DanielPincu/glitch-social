<?php
session_start();

require_once __DIR__ . '/includes/controllers/UserController.php';
require_once __DIR__ . '/includes/controllers/PostController.php';

$userController = new UserController();
$postController = new PostController();

// Redirect if not logged in
if (!$userController->isLoggedIn()) {
    header("Location: login_loader.php");
    exit;
}

// Check if user is blocked
$blocked_message = '';
if ($userController->isBlocked($_SESSION['user_id'])) {
    $blocked_message = "You are blocked. You cannot access the feed.";
}

// Handle new post submission
if (isset($_POST['post_submit'])) {
    $content = $_POST['content'];
    $file = $_FILES['imageFile'] ?? null;

    // Call PostController to create post with optional image
    $postController->createPost($_SESSION['user_id'], $content, $file);

    // Redirect to refresh page
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