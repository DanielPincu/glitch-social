<?php
require_once __DIR__ . '/includes/controllers/UserController.php';
require_once __DIR__ . '/includes/controllers/PostController.php';
require_once __DIR__ . '/includes/helpers/Session.php'; 

$session = new Session();
$userController = new UserController();
$postController = new PostController();

// Handle AJAX like/unlikeell functionality
if (isset($_POST['ajax']) && $_POST['ajax'] === 'like') {
    if (!$session->isLoggedIn()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    $user_id = $session->getUserId();
    if ($userController->isBlocked($user_id)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User is blocked']);
        exit;
    }
    header('Content-Type: application/json');
    $post_id = (int)($_POST['post_id'] ?? 0);
    $action = $_POST['action'] ?? null;

    if ($post_id && $action) {
        if ($action === 'like') {
            $postController->likePost($post_id, $user_id);
        } elseif ($action === 'unlike') {
            $postController->unlikePost($post_id, $user_id);
        }
        $likeCount = $postController->getLikeCount($post_id);
        echo json_encode(['success' => true, 'likes' => $likeCount]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
    exit;
}

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

// Handle new post submission (with image upload validation)
if (isset($_POST['post_submit']) && !$blocked_message) {
    $content = $_POST['content'] ?? '';
    $file = $_FILES['imageFile'] ?? null;
    $imagePath = null;
    if ($file && $file['error'] === UPLOAD_ERR_OK && $file['size'] > 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (in_array($mimeType, $allowedTypes) && $file['size'] <= $maxSize) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $targetDir = __DIR__ . '/uploads/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $basename = uniqid('img_', true) . '.' . $ext;
            $targetPath = $targetDir . $basename;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $imagePath = 'uploads/' . $basename;
            }
        }
    }
    // Create post with optional image path
    $postController->createPost($user_id, $content, $imagePath);
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