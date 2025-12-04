<?php

class AboutController {
    private $aboutModel;
    private $session;
    private $userModel;

    public function __construct($aboutModel, $session, $userModel) {
        $this->aboutModel = $aboutModel;
        $this->session = $session;
        $this->userModel = $userModel;
    }

    public function getCurrent() {
        return $this->aboutModel->getCurrent();
    }

    public function show() {
        $title = "About";
        $session = $this->session;
        $skipUI = true;
        $aboutContent = $this->aboutModel->getCurrent();
        $aboutUpdaterName = null;
        if (!empty($aboutContent['updated_by'])) {
            $user = $this->userModel->getUserById($aboutContent['updated_by']);
            if (!empty($user) && !empty($user['username'])) {
                $aboutUpdaterName = $user['username'];
            }
        }

        require __DIR__ . '/../views/header.php';
        $aboutUpdaterName = $aboutUpdaterName;
        require __DIR__ . '/../views/about_view.php';
        require __DIR__ . '/../views/footer.php';
    }

    public function updateAbout($content, $admin_id) {
        if (empty($content)) {
            return [
                'success' => false,
                'message' => 'Content cannot be empty.'
            ];
        }

        $result = $this->aboutModel->updateAbout($content, $admin_id);

        if ($result) {
            $latestAbout = $this->aboutModel->getCurrent();
            return [
                'success' => true,
                'message' => "About updated successfully by " . htmlspecialchars($this->session->username ?? '') . " on " . date('Y-m-d H:i:s'),
                'updated_at' => $latestAbout['updated_at'] ?? null,
            ];
        }

        return [
            'success' => false,
            'message' => 'Database update failed.'
        ];
    }
}