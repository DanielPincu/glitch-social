<h1>Register</h1>
<p><?= $message ?? '' ?></p>
<form method="post">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit" name="register">Register</button>
</form>
<p><a href="login_page.php">Login</a></p>
