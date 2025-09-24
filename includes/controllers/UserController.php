<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    protected $user;

    public function __construct() {
        $this->user = new User();
    }

    // Login user
    public function login(string $username, string $password): bool {
        return $this->user->login($username, $password);
    }

    // Register user
    public function register(string $username, string $email, string $password): bool {
        return $this->user->register($username, $email, $password);
    }

    // Check if a user is logged in
    public function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    // Check if user is admin
    public function isAdmin(): bool {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    }

    // Check if user is blocked
    public function isBlocked(int $user_id): bool {
        return $this->user->isBlocked($user_id);
    }

    // Fetch all users
    public function getAllUsers(): array {
        return $this->user->getAllUsers();
    }

    // Block user
    public function blockUser(int $user_id): bool {
        return $this->user->setBlocked($user_id, 1);
    }

    // Unblock user
    public function unblockUser(int $user_id): bool {
        return $this->user->setBlocked($user_id, 0);
    }

    // Toggle block status for user
    public function toggleBlock(int $user_id): bool {
        if ($this->isBlocked($user_id)) {
            return $this->unblockUser($user_id);
        } else {
            return $this->blockUser($user_id);
        }
    }

    // Set admin status for user (safe access for other controllers)
    public function setAdminStatus(int $user_id, int $is_admin): bool {
        return $this->user->setAdmin($user_id, $is_admin);
    }
}