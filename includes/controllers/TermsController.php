<?php

class TermsController {
    private $termsModel;

    public function __construct() {
        $this->termsModel = new Terms();
    }

    public function showTerms() {
        $terms = $this->termsModel->getCurrent();
        if (!$terms || empty($terms['content'])) {
            $message = "No terms and regulations available at the moment.";
        } else {
            $message = null;
        }
        require __DIR__ . '/../views/terms_view.php';
    }

    public function acceptTerms($user_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_terms'])) {
            $this->termsModel->recordAcceptance($user_id);
            header('Location: index.php?page=home');
            exit;
        }

        $this->showTerms();
    }

    public function updateTerms($admin_id) {
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
            header("Location: index.php?page=403");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_terms'])) {
            $content = trim($_POST['terms_content'] ?? '');
            if (!empty($content)) {
                if ($this->termsModel->updateTerms($content, $admin_id)) {
                    $latestTerms = $this->termsModel->getCurrent();
                    $_SESSION['success'] = "Terms and Conditions updated successfully by {$_SESSION['username']} on " . date('Y-m-d H:i:s') . ".";
                    $_SESSION['last_updated_by'] = $_SESSION['username'];
                    $_SESSION['last_updated_at'] = $latestTerms['updated_at'] ?? null;
                } else {
                    $_SESSION['error'] = "Database update failed.";
                }
            } else {
                $_SESSION['error'] = "Content cannot be empty.";
            }

            header("Location: index.php?page=settings");
            exit;
        }
    }
}