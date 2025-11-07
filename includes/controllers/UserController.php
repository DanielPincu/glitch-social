<?php
class UserController {
    protected $pdo;
    protected $user;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->user = new User($this->pdo);
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
        $result = $this->user->setBlocked($user_id, 1);

        // If block successful and current user is admin, unfollow both ways
        if ($result && $this->isAdmin()) {
            $profileController = new ProfileController($this->pdo);
            $admin_id = $_SESSION['user_id'] ?? null;
            if ($admin_id) {
                $profileController->toggleFollow($admin_id, $user_id);
                $profileController->toggleFollow($user_id, $admin_id);
            }
        }

        return $result;
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
        $userModel = new User($this->pdo);
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
        $session = new Session();
        $login_error = '';
        $login_success = '';
        if (isset($_GET['success'])) {
            $login_success = 'Registration successful. You may now log in.';
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                die('CSRF validation failed.');
            }
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
        if (!$session->getCsrfToken()) {
            $session->generateCsrfToken();
        }
        // Render login view with error if any
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/login_view.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    // 2. Handle POST registration requests
    public function handleRegister() {
        $session = new Session();
        $register_success = '';
        $register_error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$session->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                die('CSRF validation failed.');
            }
            if (!isset($_POST['accept_terms']) || !$_POST['accept_terms']) {
                $register_error = 'You must accept the Terms and Regulations before registering.';
            } else {
                $username = isset($_POST['username']) ? trim($_POST['username']) : '';
                $email = isset($_POST['email']) ? trim($_POST['email']) : '';
                $password = isset($_POST['password']) ? $_POST['password'] : '';
                $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
                if ($password !== $confirm_password) {
                    $register_error = 'Passwords do not match.';
                } elseif (!empty($username) && !empty($email) && !empty($password)) {
                    $newUserId = $this->user->register($username, $email, $password);
                    if ($newUserId) {
                        header('Location: index.php?page=login&success=1');
                        exit();
                    } else {
                        $register_error = 'Registration failed. Username or email may already be taken.';
                    }
                } else {
                    $register_error = 'Please fill out all fields.';
                }
            }
        }
        if (!$session->getCsrfToken()) {
            $session->generateCsrfToken();
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
                $profileController = new ProfileController($this->pdo);
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
                    $profileController = new ProfileController($this->pdo);
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
    // 6. Display search page and handle search requests
    public function showSearchPage() {
        $session = new Session();

        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }

        $searchResults = $this->handleSearch();
        $title = "Search";

        
        $pdo = $this->pdo;
        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/search_view.php';
        require __DIR__ . '/../views/footer.php';
    }
}