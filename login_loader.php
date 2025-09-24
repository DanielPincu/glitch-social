<?php
session_start();
require_once __DIR__ . '/includes/controllers/UserController.php';

$controller = new UserController();
$message = '';
$title = "Login";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $controller->login($username, $password);

    if ($result === true) {
        header($_SESSION['is_admin'] == 1 
            ? "Location: admin_loader.php" 
            : "Location: index.php");
        exit;
    } elseif ($result === 'blocked') {
        $message = "Your account is blocked. Contact admin.";
    } else {
        $message = "Invalid username or password.";
    }
}

// Load header, view, footer
require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/login_view.php';
require __DIR__ . '/includes/views/footer.php';