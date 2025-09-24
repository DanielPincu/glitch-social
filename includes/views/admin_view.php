<h1>Admin Dashboard</h1>
<p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?> | <a href="logout_loader.php">Logout</a></p>

<?php foreach ($users as $user): ?>
    <div class="mb-2 p-2 border rounded bg-white">
        <strong><?= htmlspecialchars($user['username']) ?></strong> -
        <?= $user['is_blocked'] ? 'Blocked' : 'Active' ?> -
        <?= $user['is_admin'] ? 'Admin' : 'User' ?>

        <!-- Block / Unblock -->
        <form method="post" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <button type="submit" name="toggle_block">
                <?= $user['is_blocked'] ? 'Unblock' : 'Block' ?>
            </button>
        </form>

        <!-- Promote / Demote -->
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
    </div>
<?php endforeach; ?>