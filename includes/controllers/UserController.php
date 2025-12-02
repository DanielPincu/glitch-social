<?php
class UserController {
    protected $pdo;
    protected $user;
    protected $profileController;

    public function __construct($pdo, $userModel, $profileController) {
        $this->pdo = $pdo;
        $this->user = $userModel;
        $this->profileController = $profileController;
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
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            return false;
        }
        $user = $this->user->getUserById($user_id);
        return $user && intval($user['is_admin']) === 1;
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
            $profileController = $this->profileController;
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
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';
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
                $password = isset($_POST['password']) ? trim($_POST['password']) : '';
                $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
                if (empty($username)) {
                    $register_error = 'Username cannot be empty.';
                } elseif (strlen($username) < 3) {
                    $register_error = 'Username must be at least 3 characters long.';
                } elseif (strlen($password) < 6) {
                    $register_error = 'Password must be at least 6 characters long.';
                } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[^a-zA-Z0-9]/', $password) || !preg_match('/[0-9]/', $password)) {
                    $register_error = 'Password must contain at least one uppercase letter, one symbol, and one number.';
                } elseif ($password !== $confirm_password) {
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
        // Preserve user input on validation errors (even if early return)
        $old_username = $_POST['username'] ?? '';
        $old_email = $_POST['email'] ?? '';
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/register_view.php';
        require_once __DIR__ . '/../views/footer.php';
    }

    public function searchUsers($query) {
        if (empty($query)) {
            return [];
        }
        return $this->user->searchByUsername($query);
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