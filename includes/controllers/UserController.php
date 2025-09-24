<?php
require_once __DIR__ . '/../models/User.php';

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
}