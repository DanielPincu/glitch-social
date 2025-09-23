<?php
session_start();
require_once __DIR__ . '/includes/controllers/UserController.php';

$controller = new UserController();
$message = '';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($controller->register($username, $email, $password)) {
        $message = "Registration successful. You can now log in.";
    } else {
        $message = "Username or email already exists.";
    }
}

// Load the view
require __DIR__ . '/includes/views/register_view.php';