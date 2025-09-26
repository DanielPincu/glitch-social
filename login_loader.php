<?php
require_once __DIR__ . '/includes/helpers/Session.php';
require_once __DIR__ . '/includes/controllers/UserController.php';


$session = new Session();
$controller = new UserController();
$message = '';
$title = "Login";

// If already logged in, redirect accordingly
if ($session->isLoggedIn()) {
    if ($session->isAdmin()) {
        header("Location: admin_loader.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = $controller->login($username, $password);

    if ($result === true) {
        if ($session->isAdmin()) {
            header("Location: admin_loader.php");
        } else {
            header("Location: index.php");
        }
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