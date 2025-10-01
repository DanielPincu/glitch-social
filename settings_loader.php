<?php
require_once __DIR__ . '/includes/helpers/Session.php';
require_once __DIR__ . '/includes/controllers/PostController.php';
require_once __DIR__ . '/includes/controllers/UserController.php';
require_once __DIR__ . '/includes/controllers/AdminController.php';

$session = new Session();
$postController = new PostController();
$userController = new UserController();
$adminController = new AdminController();

if (!$session->isLoggedIn()) {
    header("Location: login_loader.php");
    exit;
}

$user_id = $session->getUserId();
$username = $_SESSION['username'] ?? 'User';

// Check if user is admin
$isAdmin = $userController->isAdmin($user_id);

// Handle delete_post for non-admin users
if (isset($_POST['delete_post']) && !$isAdmin) {
    $post_id = (int)$_POST['post_id'];
    $postController->deletePostByUser($post_id, $user_id);
    header("Location: settings_loader.php");
    exit;
}

// Admin actions: block/unblock, promote/demote, delete posts for any user
if ($isAdmin) {
    // Admin delete post
    if (isset($_POST['admin_delete_post'])) {
        $post_id = (int)$_POST['post_id'];
        $postController->deletePost($post_id);
        header("Location: settings_loader.php");
        exit;
    }
    // Block/unblock user
    if (isset($_POST['block_user'])) {
        $target_user_id = (int)$_POST['user_id'];
        $adminController->blockUser($target_user_id);
        header("Location: settings_loader.php");
        exit;
    }
    if (isset($_POST['unblock_user'])) {
        $target_user_id = (int)$_POST['user_id'];
        $adminController->unblockUser($target_user_id);
        header("Location: settings_loader.php");
        exit;
    }
    // Promote/demote user
    if (isset($_POST['promote_user'])) {
        $target_user_id = (int)$_POST['user_id'];
        $adminController->promoteToAdmin($target_user_id);
        header("Location: settings_loader.php");
        exit;
    }
    if (isset($_POST['demote_user'])) {
        $target_user_id = (int)$_POST['user_id'];
        $adminController->demoteFromAdmin($target_user_id);
        header("Location: settings_loader.php");
        exit;
    }
    // Fetch all users and all posts
    $allUsers = $userController->getAllUsers();
    $allPosts = $postController->getAllPosts();
} else {
    $allUsers = null;
    $allPosts = null;
}

// Fetch only this user’s posts
$posts = $postController->getPostsByUser($user_id);
$currentUserId = $user_id;

// Page title
$title = "Settings";

// Load views
require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/settings_view.php';
require __DIR__ . '/includes/views/footer.php';