<?php
require_once __DIR__ . '/UserController.php';

class AdminController extends UserController {

    // Fetch all users (inherited already, but we can alias it for clarity)
    public function listUsers(): array {
        return $this->getAllUsers();
    }

    // Block user and auto-demote if admin
    public function blockUser(int $user_id): bool {
        parent::blockUser($user_id); // call UserController block
        $this->setAdminStatus($user_id, 0); // demote if admin
        return true;
    }

    // Unblock user (inherited, but can keep for clarity)
    public function unblockUser(int $user_id): bool {
        return parent::unblockUser($user_id);
    }

    // Promote user to admin
    public function promoteToAdmin(int $user_id): bool {
        return $this->setAdminStatus($user_id, 1);
    }

    // Demote user from admin
    public function demoteFromAdmin(int $user_id): bool {
        return $this->setAdminStatus($user_id, 0);
    }
}