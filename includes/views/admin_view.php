<h1>Admin Dashboard</h1>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="logout_loader.php">Logout</a></p>

<?php foreach ($users as $user): ?>
    <div class="mb-2 p-2 border rounded bg-white">
        <strong><?php echo htmlspecialchars($user['username']); ?></strong> -
        <?php echo $user['is_blocked'] ? 'Blocked' : 'Active'; ?> -
        <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>

        <!-- Block / Unblock -->
        <form method="post" style="display:inline;">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            <button type="submit" name="toggle_block">
                <?php echo $user['is_blocked'] ? 'Unblock' : 'Block'; ?>
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