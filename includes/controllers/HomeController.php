<?php

class HomeController {
    private $pdo;
    private $session;
    private $userController;
    private $postController;
    private $profileController;

    public function __construct($pdo, $session, $userController, $postController, $profileController) {
        $this->pdo = $pdo;
        $this->session = $session;
        $this->userController = $userController;
        $this->postController = $postController;
        $this->profileController = $profileController;
    }

    public function showHome() {
        // Ensure user is logged in
        if (!$this->session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }

        // Validate post ID if provided
        if (isset($_GET['id'])) {
            $this->postController->validatePostId($_GET['id']);
        }

        $user_id = $this->session->getUserId();
        $viewer_id = $this->session->getUserId();

        // Check if user is blocked by admin
        $blocked_message = '';
        if ($this->userController->isBlocked($user_id)) {
            $blocked_message = "You are blocked. You cannot access the feed.";
        }

        // Handle comment and post actions
        $this->postController->handleCommentActions($this->session);
        $this->postController->handleNewPost($this->session);

        // Get posts and relationships
        $followingPosts = $this->postController->getPostsFromFollowing($user_id, $viewer_id);
        $followingList = $this->profileController->getFollowingList($user_id);
        $posts = $this->postController->getPosts($viewer_id);

        // Include own posts
        $ownPosts = $this->postController->getPostsByUser($viewer_id, $viewer_id);
        $posts = array_merge($ownPosts, $posts);

        // Remove duplicates
        $unique = [];
        $posts = array_values(array_filter($posts, function ($post) use (&$unique) {
            if (in_array($post['id'], $unique)) return false;
            $unique[] = $post['id'];
            return true;
        }));

        // Filter out posts from blocked users
        $blockedUsersList = $this->profileController->getBlockedUsers($viewer_id) ?? [];
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

        // Filter out posts by admin-blocked users
        $posts = array_filter($posts, function ($post) {
            $userController = $this->userController;
            $user = $userController->getUserById($post['user_id']);
            return !($user && isset($user['is_blocked']) && $user['is_blocked'] == 1);
        });

        $followingPosts = array_filter($followingPosts, function($post) use ($blockedIds) {
            return !in_array($post['user_id'], $blockedIds);
        });

        $followingPosts = array_filter($followingPosts, function ($post) {
            $userController = $this->userController;
            $user = $userController->getUserById($post['user_id']);
            return !($user && isset($user['is_blocked']) && $user['is_blocked'] == 1);
        });

        // Sort posts by newest first
        usort($posts, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        // Pass variables to view
        $title = "Home";
        $session = $this->session;
        $userController = $this->userController;
        $profileController = $this->profileController;
        $postController = $this->postController;
        $user_id = $this->session->getUserId();
        $blocked_message = $blocked_message ?? null;
        $followingList = $followingList ?? [];
        $followingPosts = $followingPosts ?? [];
        $posts = $posts ?? [];
        $isAdmin = $this->userController->isAdmin();

        // Render views
        $pdo = $this->pdo;
        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/home_view.php';
        require __DIR__ . '/../views/footer.php';
    }
}