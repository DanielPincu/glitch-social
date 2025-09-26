<?php
session_start();
require_once __DIR__ . '/includes/controllers/AdminController.php';

// Redirect non-admins
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit;
}

$adminController = new AdminController();

// Handle form submissions
if (isset($_POST['toggle_block'], $_POST['user_id'])) {
    $adminController->toggleBlock($_POST['user_id']);
    header("Location: admin_loader.php");
    exit;
}

if (isset($_POST['promote'], $_POST['user_id'])) {
    $adminController->promoteToAdmin($_POST['user_id']);
    header("Location: admin_loader.php");
    exit;
}

if (isset($_POST['demote'], $_POST['user_id'])) {
    $adminController->demoteFromAdmin($_POST['user_id']);
    header("Location: admin_loader.php");
    exit;
}

// Fetch all users for the view
$users = $adminController->listUsers();

// Page title
$title = "Admin Dashboard";

// Load header, view, footer
require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/admin_view.php';
require __DIR__ . '/includes/views/footer.php';