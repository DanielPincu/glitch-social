<?php

class AdminController extends UserController {

    public function __construct($pdo, $userModel, $profileController, $session) {
        parent::__construct($pdo, $userModel, $profileController, $session);
    }

    

    // Fetch all users (inherited already, but we can alias it for clarity)
    public function listUsers() {
        return $this->getAllUsers();
    }

    // Block user and auto-demote if admin
    public function blockUser($user_id) {
        parent::blockUser($user_id); // call UserController block
        $this->setAdminStatus($user_id, 0); // demote if admin
        return true;
    }

    // Unblock user (inherited, but can keep for clarity)
    public function unblockUser($user_id) {
        return parent::unblockUser($user_id);
    }

    // Promote user to admin
    public function promoteToAdmin($user_id) {
        return $this->setAdminStatus($user_id, 1);
    }

    // Demote user from admin
    public function demoteFromAdmin($user_id) {
        return $this->setAdminStatus($user_id, 0);
    }
    // Fetch all posts
    public function listPosts() {
        $post = new Post($this->pdo);
        return $post->fetchAll(null, true);
    }

    // Delete a post by ID (with image cleanup)
    public function deletePost($post_id) {
        $post = new Post($this->pdo);
        $postData = $post->getPostById($post_id);

        if ($postData && !empty($postData['image_path'])) {
            $this->deleteImageFile($postData['image_path']);
        }

        return $post->deletePostById($post_id);
    }

    // Delete an image file safely
    private function deleteImageFile($relativePath) {
        $fullPath = __DIR__ . '/../../' . ltrim($relativePath, '/');
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    public function handleAdminActions() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        if (isset($_POST['block_user'], $_POST['user_id'])) {
            $this->demoteFromAdmin($_POST['user_id']);
            $this->blockUser($_POST['user_id']);
        } elseif (isset($_POST['unblock_user'], $_POST['user_id'])) {
            $this->unblockUser($_POST['user_id']);
        } elseif (isset($_POST['promote_user'], $_POST['user_id'])) {
            $this->promoteToAdmin($_POST['user_id']);
        } elseif (isset($_POST['demote_user'], $_POST['user_id'])) {
            $this->demoteFromAdmin($_POST['user_id']);
        } elseif (isset($_POST['admin_delete_post'], $_POST['post_id'])) {
            $this->deletePost($_POST['post_id']);
        }

        header('Location: index.php?page=settings');
        exit;
    }
}