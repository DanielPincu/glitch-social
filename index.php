<?php
require_once __DIR__ . '/includes/helpers/Session.php';
require_once __DIR__ . '/includes/controllers/UserController.php';

$session = new Session();
$userController = new UserController();

// Redirect if not logged in
if (!$session->isLoggedIn()) {
    header("Location: login_loader.php");
    exit;
}

// Check if user is blocked
$blocked_message = '';
if ($userController->isBlocked($session->getUserId())) {
    $blocked_message = "You are blocked. You cannot access the feed.";
}

// Page title for header
$title = "Home";

// Load views
require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/home_view.php';
require __DIR__ . '/includes/views/footer.php';