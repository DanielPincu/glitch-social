<?php

class AboutController {
    private $aboutModel;
    private $session;

    public function __construct($aboutModel, $session) {
        $this->aboutModel = $aboutModel;
        $this->session = $session;
    }

    public function getCurrent() {
        return $this->aboutModel->getCurrent();
    }

    public function show() {
        $title = "About";
        $session = $this->session;
        $skipUI = true;
        $aboutContent = $this->aboutModel->getCurrent();

        require __DIR__ . '/../views/header.php';
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
                'message' => "About updated successfully by " . ($_SESSION['username']) . " on " . date('Y-m-d H:i:s'),
                'updated_at' => $latestAbout['updated_at'] ?? null,
            ];
        }

        return [
            'success' => false,
            'message' => 'Database update failed.'
        ];
    }
}