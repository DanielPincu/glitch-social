<?php
session_start();
require_once __DIR__ . '/../controllers/AdminController.php';

// Create the admin controller
$adminController = new AdminController();

// Handle form submissions
if (isset($_POST['toggle_block'], $_POST['user_id'])) {
    $adminController->toggleBlock((int)$_POST['user_id']);
    header("Location: admin_view.php");
    exit;
}

if (isset($_POST['promote'], $_POST['user_id'])) {
    $adminController->promoteToAdmin((int)$_POST['user_id']);
    header("Location: admin_view.php");
    exit;
}

if (isset($_POST['demote'], $_POST['user_id'])) {
    $adminController->demoteFromAdmin((int)$_POST['user_id']);
    header("Location: admin_view.php");
    exit;
}

// Fetch all users
$users = $adminController->listUsers();
?>

<h1>Admin Dashboard</h1>

<?php foreach ($users as $user): ?>
    <p>
        <?= htmlspecialchars($user['username']) ?> -
        <?= $user['is_blocked'] ? 'Blocked' : 'Active' ?> -
        <?= $user['is_admin'] ? 'Admin' : 'User' ?>
    </p>

    <form method="post" style="display:inline;">
        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
        <button type="submit" name="toggle_block">
            <?= $user['is_blocked'] ? 'Unblock' : 'Block' ?>
        </button>
    </form>

    <?php if (!$user['is_blocked']): ?>
        <form method="post" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <?php if (!$user['is_admin']): ?>
                <button type="submit" name="promote">Promote to Admin</button>
            <?php else: ?>
                <button type="submit" name="demote">Demote from Admin</button>
            <?php endif; ?>
        </form>
    <?php endif; ?>

<?php endforeach; ?>