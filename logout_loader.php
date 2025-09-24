<?php
session_start();
require_once __DIR__ . '/includes/controllers/UserController.php';

$userController = new UserController();

// Optional: check if user is logged in
if ($userController->isLoggedIn()) {
    session_unset();
    session_destroy();
}

// Redirect to login page
header("Location: login_loader.php");
exit;
