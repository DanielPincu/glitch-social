<?php
require_once __DIR__ . '/includes/controllers/UserController.php';
require_once __DIR__ . '/includes/controllers/PostController.php';
require_once __DIR__ . '/includes/controllers/AdminController.php';
require_once __DIR__ . '/includes/controllers/ProfileController.php';
require_once __DIR__ . '/includes/helpers/Session.php';
require_once __DIR__ . '/includes/helpers/ImageResizer.php';
require_once __DIR__ . '/includes/controllers/AjaxController.php';

$session = new Session();
$userController = new UserController();
$postController = new PostController();

$ajaxController = new AjaxController($session, $userController, $postController);
$ajaxController->handleRequest();

$page = $_GET['page'] ?? 'home';
$title = '';

// Handle follow/unfollow actions (now handled by UserController)
$userController->handleFollowAction($session);

switch ($page) {
    case 'login':
        // If already logged in, redirect to home
        if ($session->isLoggedIn()) {
            header("Location: index.php");
            exit;
        }
        $userController->handleLogin();
        break;

    case 'register':
        $userController->handleRegister();
        break;

    case 'logout':
        $session->logout();
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

        // Handle profile updates and avatar upload via controller
        $controller->handleProfileUpdate($user_id, $session);

        // Handle user block/unblock (user-to-user) - now handled by UserController
        $userController->handleBlockActions($session);

        // Fetch profile data and user posts
        $data = $controller->showProfile($user_id);
        if ($data === false || empty($data['profile'])) {
            header("Location: index.php?page=404");
            exit();
        }
        $profileData = $data['profile'];
        $posts = $data['posts'];
        $canEditProfile = ($session->getUserId() == $profileData['id']);

        $title = "Profile";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/profile_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'post':
        // Redirect to home with id parameter
        header("Location: index.php?page=home&id=" . urlencode($_GET['id'] ?? ''));
        exit();

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

        // Refactored: handle post update and delete via PostController
        $postController->handlePostUpdate($session);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle user-to-user block/unblock (from settings) - now handled by UserController
            $userController->handleBlockActions($session);
            $postController->handlePostDelete($session);
            if ($isAdmin) {
                $adminController->handleAdminActions();
            }
        }

        // My own posts
        $posts = $postController->getPostsByUser($user_id);
        // Fetch user-to-user blocked list
        $blockedUsers = $userController->getBlockedUsers($user_id);

        // Admin-only
        $allUsers = [];
        $allPosts = [];
        if ($isAdmin) {
            $allUsers = $userController->getAllUsers();
            $adminController = new AdminController();
            $allPosts = $adminController->listPosts(); // fetch ALL posts, bypassing visibility
        }

        $title = "Settings";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/settings_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'search':
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        $searchResults = $userController->handleSearch();
        $title = "Search";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/search_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case '404':
        $title = "Page Not Found";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/404_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'home':
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        // Validate post ID if provided
        if (isset($_GET['id'])) {
            $postController->validatePostId($_GET['id']);
        }
        $user_id = $session->getUserId();
        $blocked_message = '';
        if ($userController->isBlocked($user_id)) {
            $blocked_message = "You are blocked. You cannot access the feed.";
        }

        // Refactored: handle comment and post actions via PostController
        $postController->handleCommentActions($session);
        $postController->handleNewPost($session);

        $viewer_id = $session->getUserId();
        $followingPosts = $postController->getPostsFromFollowing($user_id, $viewer_id);
        $profileController = new ProfileController();
        $followingList = $profileController->getFollowingList($user_id);
        $posts = $postController->getPosts($viewer_id);

        // Ensure user sees their own posts (including followers-only and private)
        $ownPosts = $postController->getPostsByUser($viewer_id, $viewer_id);
        $posts = array_merge($ownPosts, $posts);

        // Remove duplicates and sort by newest first
        $unique = [];
        $posts = array_values(array_filter($posts, function ($post) use (&$unique) {
            if (in_array($post['id'], $unique)) return false;
            $unique[] = $post['id'];
            return true;
        }));
        // Filter out posts by blocked users (extra safety layer)
        $blockedUsersList = $userController->getBlockedUsers($viewer_id) ?? [];
        $blockedIds = array_map(function($b) {
            if (isset($b['blocked_id'])) {
                return $b['blocked_id'];
            } elseif (isset($b['id'])) {
                return $b['id'];
            } else {
                return null;
            }
        }, $blockedUsersList);
        $blockedIds = array_filter($blockedIds); // remove nulls
        $posts = array_filter($posts, function($post) use ($blockedIds) {
            return !in_array($post['user_id'], $blockedIds);
        });
        // Also filter out posts by users who are admin-blocked (is_blocked == 1)
        $posts = array_filter($posts, function ($post) use ($userController) {
            $user = $userController->getUserById($post['user_id']);
            return !($user && isset($user['is_blocked']) && $user['is_blocked'] == 1);
        });
        $followingPosts = array_filter($followingPosts, function($post) use ($blockedIds) {
            return !in_array($post['user_id'], $blockedIds);
        });
        // Also filter out posts by users who are admin-blocked (is_blocked == 1)
        $followingPosts = array_filter($followingPosts, function ($post) use ($userController) {
            $user = $userController->getUserById($post['user_id']);
            return !($user && isset($user['is_blocked']) && $user['is_blocked'] == 1);
        });
        usort($posts, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        $title = "Home";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/home_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    default:
        header("Location: index.php?page=404");
        exit;
}
