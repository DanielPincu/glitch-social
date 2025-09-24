<h1>Login</h1>
<p><?= $message ?? '' ?></p>

<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit" name="login">Login</button>
</form>

<p><a href="register_page.php">Register</a></p>