<?php
require_once __DIR__ . '/UserController.php';

class AdminController {
    protected $userController;

    public function __construct() {
        $this->userController = new UserController();
    }

    // Fetch all users for admin dashboard
    public function listUsers(): array {
        return $this->userController->getAllUsers();
    }

    // Block a user and automatically demote if admin
    public function blockUser(int $user_id): bool {
        // Block the user
        $this->userController->blockUser($user_id);
        // Optional: demote if they were admin
        $this->userController->unblockUser($user_id); // If you want to handle demotion separately, add setAdmin(0) in User model
        return true;
    }

    // Unblock a user
    public function unblockUser(int $user_id): bool {
        return $this->userController->unblockUser($user_id);
    }

    // Toggle block status
    public function toggleBlock(int $user_id): bool {
        return $this->userController->toggleBlock($user_id);
    }

    // Promote user to admin
    public function promoteToAdmin(int $user_id): bool {
        return $this->userController->setAdminStatus($user_id, 1);
    }
    // Demote user from admin
    public function demoteFromAdmin(int $user_id): bool {
        return $this->userController->setAdminStatus($user_id, 0);
    }
}