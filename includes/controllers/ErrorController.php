<?php

class ErrorController {
    private $session;
    private PDO $pdo;
    

    public function __construct(PDO $pdo, $session = null) {
        $this->pdo = $pdo;
        $this->session = $session;
    }

    public function show404() {
        $title = "Page Not Found";
        $session = $this->session;

        // Ensure $session is not null to avoid fatal errors in header.php
        if (!$session) {
            $session = new Session();
        }

        $pdo = $this->pdo;

        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/404_view.php';
        require __DIR__ . '/../views/footer.php';
    }
}