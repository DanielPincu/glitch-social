<?php

class SettingsController {
    private $pdo;
    private $session;
    private $userController;
    private $postController;
    private $adminController;

    public function __construct($pdo, $session, $userController, $postController, $adminController) {
        $this->pdo = $pdo;
        $this->session = $session;
        $this->userController = $userController;
        $this->postController = $postController;
        $this->adminController = $adminController;
    }

    public function show() {
        // Ensure the user is logged in
        if (!$this->session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }

        $user_id = $this->session->getUserId();
        $isAdmin = $this->session->isAdmin();

        // Handle POST requests (profile actions, blocking, posts)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($isAdmin && isset($_POST['update_terms'])) {
                $termsController = new TermsController($this->pdo);
                $content = trim($_POST['terms_content'] ?? '');
                $updateResult = $termsController->updateTerms($user_id, $content);

                if ($updateResult['success']) {
                    $_SESSION['success'] = $updateResult['message'];
                    $_SESSION['last_updated_at'] = $updateResult['updated_at'];
                } else {
                    $_SESSION['error'] = $updateResult['message'];
                }

                header("Location: index.php?page=settings");
                exit;
            }

            $this->userController->handleBlockActions($this->session);
            $this->postController->handlePostUpdate($this->session);
            $this->postController->handlePostDelete($this->session);

            if ($isAdmin) {
                // Ensure admin actions use updated deletion logic
                $this->adminController->handleAdminActions($this->pdo);
            }
        }

        // Load user data
        $posts = $this->postController->getPostsByUser($user_id);
        $blockedUsers = $this->userController->getBlockedUsers($user_id);
        $termsModel = new Terms($this->pdo);
        $termsContent = $termsModel->getCurrent();

        $updaterUsername = null;
        if (!empty($termsContent['updated_by'])) {
            $userModel = new User($this->pdo);
            $updater = $userModel->getUserById($termsContent['updated_by']);
            $updaterUsername = $updater['username'] ?? ('User ID: ' . $termsContent['updated_by']);
        }

        // Admin-only data
        $allUsers = [];
        $allPosts = [];
        if ($isAdmin) {
            $allUsers = $this->userController->getAllUsers();
            $allPosts = $this->adminController->listPosts();
        }

        $title = "Settings";
        $session = $this->session;
        $userController = $this->userController;
        $currentUserId = $user_id;
        $pdo = $this->pdo;
        require __DIR__ . '/../views/header.php';
        $updaterUsername = $updaterUsername; // for clarity of availability
        require __DIR__ . '/../views/settings_view.php';
        require __DIR__ . '/../views/footer.php';
    }
}