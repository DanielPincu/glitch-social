<?php
require_once __DIR__ . '/includes/controllers/UserController.php';
require_once __DIR__ . '/includes/controllers/PostController.php';
require_once __DIR__ . '/includes/controllers/AdminController.php';
require_once __DIR__ . '/includes/controllers/ProfileController.php';
require_once __DIR__ . '/includes/helpers/Session.php'; 

// Central Routing System
$session = new Session();
$userController = new UserController();
$postController = new PostController();

// Handle AJAX like/unlike functionality
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

$page = $_GET['page'] ?? 'home';
$title = '';

switch ($page) {
    case 'login':
        // If already logged in, redirect to home
        if ($session->isLoggedIn()) {
            header("Location: index.php");
            exit;
        }
        $login_error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            if ($userController->login($username, $password)) {
                header("Location: index.php");
                exit;
            } else {
                $login_error = "Invalid username or password";
            }
        }
        $title = "Login";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/login_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'register':
        $register_error = '';
        $register_success = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            if ($password !== $confirm_password) {
                $register_error = "Passwords do not match";
            } else {
                $result = $userController->register($username, $email, $password);
                if ($result) {
                    $register_success = true;
                } else {
                    $register_error = "Registration failed. Username might already be taken.";
                }
            }
        }
        $title = "Register";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/register_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'logout':
        $session->destroy();
        header("Location: index.php?page=login");
        exit;

    case 'profile':
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }

        $controller = new ProfileController();

        // Determine which profile to show (user’s own or another user’s)
        $user_id = $_GET['id'] ?? $session->getUserId();

        // Handle profile updates (only allowed on own profile)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id == $session->getUserId()) {
            $controller->updateProfile(
                $user_id,
                $_POST['bio'] ?? '',
                $_POST['location'] ?? '',
                $_POST['website'] ?? '',
                $_FILES['avatar'] ?? null
            );
            header("Location: index.php?page=profile&id={$user_id}");
            exit;
        }

        // Fetch profile data and user posts
        $data = $controller->showProfile($user_id);
        $profileData = $data['profile'];
        $posts = $data['posts'];
        $canEditProfile = ($session->getUserId() == $profileData['id']);

        $title = "Profile";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/profile_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'settings':
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }

        $user_id = $session->getUserId();
        $currentUserId = $user_id;
        $isAdmin = $session->isAdmin();

        if ($isAdmin) {
            $adminController = new AdminController();
        }

        // Update post content and image
        if (isset($_POST['update_post'], $_POST['post_id'])) {
            $post_id = (int)$_POST['post_id'];
            $new_content = trim($_POST['new_content'] ?? '');
            $remove_image = isset($_POST['remove_image']);
            $file = $_FILES['new_image'] ?? null;
            $new_image_path = null;

            // Handle new image upload
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
                        $new_image_path = 'uploads/' . $basename;
                    }
                }
            }

            if (!empty($new_content) || $new_image_path || $remove_image) {
                $postController->updatePostContent($post_id, $new_content, $user_id, $new_image_path, $remove_image);
            }

            header("Location: index.php?page=settings");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete own post
            if (isset($_POST['delete_post'], $_POST['post_id'])) {
                $postController->deletePostByUser((int)$_POST['post_id'], $user_id);
                header("Location: index.php?page=settings");
                exit;
            }

            // Admin actions
            if ($isAdmin) {
                if (isset($_POST['block_user'], $_POST['user_id'])) {
                    // Demote user before blocking
                    $adminController->demoteFromAdmin((int)$_POST['user_id']);
                    $adminController->blockUser((int)$_POST['user_id']);
                }
                if (isset($_POST['unblock_user'], $_POST['user_id'])) {
                    $adminController->unblockUser((int)$_POST['user_id']);
                }
                if (isset($_POST['promote_user'], $_POST['user_id'])) {
                    $adminController->promoteToAdmin((int)$_POST['user_id']);
                }
                if (isset($_POST['demote_user'], $_POST['user_id'])) {
                    $adminController->demoteFromAdmin((int)$_POST['user_id']);
                }
                if (isset($_POST['admin_delete_post'], $_POST['post_id'])) {
                    $adminController->deletePost((int)$_POST['post_id']);
                }
                header("Location: index.php?page=settings");
                exit;
            }
        }

        // My own posts
        $posts = $postController->getPostsByUser($user_id);

        // Admin-only
        $allUsers = [];
        $allPosts = [];
        if ($isAdmin) {
            $allUsers = $userController->getAllUsers();
            $allPosts = $postController->getPosts(); // fetch all posts for admin
        }

        $title = "Settings";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/settings_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'home':
    default:
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
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
        $posts = $postController->getPosts();
        $title = "Home";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/home_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;
}