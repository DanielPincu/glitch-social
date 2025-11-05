<?php

class ErrorController {
    private $session;

    public function __construct($session = null) {
        $this->session = $session;
    }

    public function show404() {
        $title = "Page Not Found";
        $session = $this->session;

        // Ensure $session is not null to avoid fatal errors in header.php
        if (!$session) {
            $session = new Session();
        }

        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/404_view.php';
        require __DIR__ . '/../views/footer.php';
    }
}