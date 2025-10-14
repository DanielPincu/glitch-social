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

    // ----------------------
    // User-to-User Blocking System
    // ----------------------

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
        return $userModel->getBlockedUsersByUser($user_id);
    }
}