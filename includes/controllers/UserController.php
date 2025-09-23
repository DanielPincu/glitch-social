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
    public function register($username, $password, $email) {
        return $this->user->register($username, $password, $email);
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
}
