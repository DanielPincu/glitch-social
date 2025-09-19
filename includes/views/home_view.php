<?php if(isset($blocked_message)): ?>
    <h1><?= $blocked_message ?></h1>
<?php else: ?>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
    <a href='logout_page.php'>Logout</a><br><br>

    <?php if($_SESSION['is_admin'] == 1): ?>
        <a href='admin/dashboard.php'>Go to Admin Dashboard</a><br><br>
    <?php endif; ?>
<?php endif; ?>
