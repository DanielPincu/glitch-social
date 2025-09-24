<?php
session_start();
require_once __DIR__ . '/includes/controllers/UserController.php';

$controller = new UserController();
$message = '';
$title = "Register";

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email    = $_POST['email'];

    if ($controller->register($username, $password, $email)) {
        $message = "Registration successful. You can now log in.";
    } else {
        $message = "Username or email already exists.";
    }
}

// Load header, view, footer
require __DIR__ . '/includes/views/header.php';
require __DIR__ . '/includes/views/register_view.php';
require __DIR__ . '/includes/views/footer.php';