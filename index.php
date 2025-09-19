<?php
session_start();
require_once  __DIR__ . '/includes/controllers/UserController.php';
$userController = new UserController();

// Redirect if not logged in
if (!$userController->isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Show admin dashboard link
if ($userController->isAdmin()) {
    echo "<a href='admin/dashboard.php'>Go to Admin Dashboard</a><br><br>";
}

// Check if user is blocked
if ($userController->isBlocked($_SESSION['user_id'])) {
    echo "<h1>You are blocked. You cannot access the feed.</h1>";
    exit;
}

$user_id = $_SESSION['user_id'];


echo "<h1>Welcome, " . htmlspecialchars($_SESSION['username']) . "</h1>";
echo "<a href='logout.php'>Logout</a><br><br>";


