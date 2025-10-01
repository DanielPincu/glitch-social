<?php
require_once __DIR__ . '/includes/helpers/Session.php';
require_once __DIR__ . '/includes/controllers/PostController.php';
require_once __DIR__ . '/includes/controllers/UserController.php';

$session = new Session();
$postController = new PostController();
$userController = new UserController();

if (!$session->isLoggedIn()) {
    header("Location: login_loader.php");
    exit;
}

$user_id = $session->getUserId();
$username = $_SESSION['username'] ?? 'User';

// Handle post delete (only their own posts)
if (isset($_POST['delete_post'])) {
    $post_id = (int)$_POST['post_id'];
    $postController->deletePost($post_id, $user_id); 
    header("Location: settings_loader.php");
    exit;
}

// Fetch only this user’s posts
$posts = $postController->getPostsByUser($user_id);

// Page title
$title = "Settings";

// Load views
require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/settings_view.php';
require __DIR__ . '/includes/views/footer.php';