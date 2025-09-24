<?php
session_start();
require_once __DIR__ . '/includes/controllers/UserController.php';

$controller = new UserController();
$message = '';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $controller->login($username, $password);

    if ($result === true) {
        // Redirect admin or regular user
        header($_SESSION['is_admin'] == 1 ? "Location: includes/views/admin_view.php" : "Location: index.php");
        exit;
    } elseif ($result === 'blocked') {
        $message = "Your account is blocked. Contact admin.";
    } else {
        $message = "Invalid username or password.";
    }
}

// Load the view
require __DIR__ . '/includes/views/login_view.php';
