<?php
session_start();
require_once __DIR__ . '/includes/controllers/UserController.php';

$userController = new UserController();

// Redirect if not logged in
if (!$userController->isLoggedIn()) {
    header("Location: login_loader.php");
    exit;
}

// Check if user is blocked
$blocked_message = '';
if ($userController->isBlocked($_SESSION['user_id'])) {
    $blocked_message = "You are blocked. You cannot access the feed.";
}

// Page title for header
$title = "Home";

// Load views
require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/home_view.php';
require __DIR__ . '/includes/views/footer.php';