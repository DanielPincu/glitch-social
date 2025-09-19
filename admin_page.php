<?php
session_start();
require_once __DIR__ . '/includes/controllers/UserController.php';

$userController = new UserController();

// Check if user is logged in and is admin
if (!$userController->isLoggedIn() || $_SESSION['is_admin'] != 1) {
    header("Location: login_page.php");
    exit;
}

// Load the admin view
require __DIR__ . '/includes/views/admin_view.php';
