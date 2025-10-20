<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/ProfileController.php';

class UserController {
    protected $user;

    public function __construct() {
        $this->user = new User();
    }

    // Login user
    public function login($username, $password) {
        return $this->user->login($username, $password);
    }

    // Register user
    public function register($username, $email, $password) {
        return $this->user->register($username, $email, $password);
    }

    // Check if a user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Check if user is admin
    public function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    }

    // Check if user is blocked
    public function isBlocked($user_id) {
        return $this->user->isBlocked($user_id);
    }

    // Get user by ID
    public function getUserById($user_id) {
        return $this->user->getUserById($user_id);
    }

    // Fetch all users
    public function getAllUsers() {
        return $this->user->getAllUsers();
    }

    // Block user
    public function blockUser($user_id) {
        return $this->user->setBlocked($user_id, 1);
    }

    // Unblock user
    public function unblockUser($user_id) {
        return $this->user->setBlocked($user_id, 0);
    }

    // Toggle block status for user
    public function toggleBlock($user_id) {
        if ($this->isBlocked($user_id)) {
            return $this->unblockUser($user_id);
        } else {
            return $this->blockUser($user_id);
        }
    }

    // Set admin status for user (safe access for other controllers)
    public function setAdminStatus($user_id, $is_admin) {
        return $this->user->setAdmin($user_id, $is_admin);
    }

   
    // User-to-User Blocking System
   

    // Block another user (user-to-user block)
    public function blockUserByUser($blocker_id, $blocked_id) {
        return $this->user->blockUser($blocker_id, $blocked_id);
    }

    // Unblock a user
    public function unblockUserByUser($blocker_id, $blocked_id) {
        return $this->user->unblockUser($blocker_id, $blocked_id);
    }

    // Check if a user is blocked by another user
    public function isUserBlockedByUser($blocker_id, $blocked_id) {
        return $this->user->isUserBlocked($blocker_id, $blocked_id);
    }

    // Get all users blocked by this user
    public function getBlockedUsersByUser($blocker_id) {
        if (empty($blocker_id)) {
            return [];
        }
        return $this->user->getBlockedUsers($blocker_id);
    }

    // Check if the current user has blocked another user
    public function hasUserBlocked($blocker_id, $blocked_id) {
        if (empty($blocker_id) || empty($blocked_id)) {
            return false;
        }
        return $this->user->isUserBlocked($blocker_id, $blocked_id);
    }
    // New method to get all users blocked by a given user ID
    public function getBlockedUsers($user_id) {
        $userModel = new User();
        return $userModel->getBlockedUsers($user_id);
    }
    // Search users by username
    public function searchUsers($query) {
        if (empty($query)) {
            return [];
        }
        return $this->user->searchByUsername($query);
    }
   
    // Custom Handlers
   

    // 1. Handle POST login requests
    public function handleLogin() {
        $login_error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            if (!empty($username) && !empty($password)) {
                if ($this->login($username, $password)) {
                    header('Location: index.php');
                    exit();
                } else {
                    $login_error = 'Invalid username or password.';
                }
            } else {
                $login_error = 'Please enter both username and password.';
            }
        }
        // Render login view with error if any
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/login_view.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    // 2. Handle POST registration requests
    public function handleRegister() {
        $register_success = '';
        $register_error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
            if ($password !== $confirm_password) {
                $register_error = 'Passwords do not match.';
            } elseif (!empty($username) && !empty($email) && !empty($password)) {
                if ($this->register($username, $email, $password)) {
                    $register_success = 'Registration successful. You may now log in.';
                } else {
                    $register_error = 'Registration failed. Username or email may already be taken.';
                }
            } else {
                $register_error = 'Please fill out all fields.';
            }
        }
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/register_view.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    // 3. Handle follow/unfollow POST actions
    public function handleFollowAction($session) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['follow_action']) && isset($_POST['followed_id'])) {
            $follower_id = $session->getUserId();
            $followed_id = $_POST['followed_id'];
            if ($follower_id && $followed_id) {
                $profileController = new ProfileController();
                $profileController->toggleFollow($follower_id, $followed_id);
            }
            header('Location: index.php?page=profile&id=' . urlencode($followed_id));
            exit();
        }
    }

    // 4. Handle block and unblock POST actions
    public function handleBlockActions($session) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $blocker_id = $session->getUserId();
            // Block user
            if (isset($_POST['block_user']) && isset($_POST['blocked_id'])) {
                $blocked_id = $_POST['blocked_id'];
                if ($blocker_id && $blocked_id) {
                    $profileController = new ProfileController();
                    $profileController->blockUserAndUnfollow($blocker_id, $blocked_id);
                }
                // Determine redirect: if in settings, go to settings, else profile
                $redirect = isset($_POST['from_settings'])
                    ? 'index.php?page=settings'
                    : ('index.php?page=profile&id=' . urlencode($blocked_id));
                header('Location: ' . $redirect);
                exit();
            }
            // Unblock user
            if (isset($_POST['unblock_user']) && isset($_POST['blocked_id'])) {
                $blocked_id = $_POST['blocked_id'];
                if ($blocker_id && $blocked_id) {
                    $this->unblockUserByUser($blocker_id, $blocked_id);
                }
                $redirect = isset($_POST['from_settings'])
                    ? 'index.php?page=settings'
                    : ('index.php?page=profile&id=' . urlencode($blocked_id));
                header('Location: ' . $redirect);
                exit();
            }
        }
    }

    // 5. Handle user search
    public function handleSearch() {
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';
        if (!empty($query)) {
            return $this->searchUsers($query);
        }
        return [];
    }
}