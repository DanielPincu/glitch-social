<?php

class SettingsController {
    private $session;
    private $userController;
    private $postController;
    private $adminController;

    public function __construct() {
        $this->session = new Session();
        $this->userController = new UserController();
        $this->postController = new PostController();
        $this->adminController = new AdminController();
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
                $termsController = new TermsController();
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
                $this->adminController->handleAdminActions();
            }
        }

        // Load user data
        $posts = $this->postController->getPostsByUser($user_id);
        $blockedUsers = $this->userController->getBlockedUsers($user_id);
        $termsModel = new Terms();
        $termsContent = $termsModel->getCurrent();

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
        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/settings_view.php';
        require __DIR__ . '/../views/footer.php';
    }
}