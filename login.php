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
        // Redirect admin to dashboard
        if ($_SESSION['is_admin'] == 1) {
            header("Location: admin/dashboard.php");
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
?>

<h1>Login</h1>
<p><?= $message ?></p>
<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit" name="login">Login</button>
</form>
<p><a href="register.php">Register</a></p>
