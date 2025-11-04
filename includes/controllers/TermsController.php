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
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_terms'])) {
            $content = trim($_POST['content'] ?? '');
            if (!empty($content)) {
                $this->termsModel->update($content, $admin_id);
                header('Location: index.php?page=terms');
                exit;
            }
        }

        $terms = $this->termsModel->getCurrent();
        require __DIR__ . '/../views/admin/terms_edit.php';
    }
}