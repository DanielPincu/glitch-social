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

    public function updateTerms($admin_id, $content) {
        if (empty($content)) {
            return ['success' => false, 'message' => 'Content cannot be empty.'];
        }

        $result = $this->termsModel->updateTerms($content, $admin_id);

        if ($result) {
            $latestTerms = $this->termsModel->getCurrent();
            return [
                'success' => true,
                'message' => "Terms updated successfully by {$_SESSION['username']} on " . date('Y-m-d H:i:s'),
                'updated_at' => $latestTerms['updated_at'] ?? null,
            ];
        } else {
            return ['success' => false, 'message' => 'Database update failed.'];
        }
    }
}