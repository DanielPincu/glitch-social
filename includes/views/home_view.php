<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
<a href="logout_loader.php">Logout</a><br><br>

<?php if (!empty($blocked_message)): ?>
    <p style="color:red;"><?= htmlspecialchars($blocked_message) ?></p>
<?php else: ?>

    <!-- New post form -->
    <form method="post" action="feed_loader.php">
        <textarea name="content" placeholder="What's on your mind?" required></textarea><br>
        <button type="submit" name="post_submit">Post</button>
    </form>

    <hr>

    <!-- Posts feed -->
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div style="margin-bottom:15px; padding:10px; border:1px solid #ccc;">
                <strong><?= htmlspecialchars($post['username']) ?></strong>: 
                <?= htmlspecialchars($post['content']) ?><br>
                <small>Posted on: <?= $post['created_at'] ?></small><br>
                Likes: <?= $post['like_count'] ?> 

                <!-- Like / Unlike -->
                <?php if ($postController->userLiked($_SESSION['user_id'], $post['id'])): ?>
                    <a href="feed_loader.php?post_id=<?= $post['id'] ?>&action=unlike">Unlike</a>
                <?php else: ?>
                    <a href="feed_loader.php?post_id=<?= $post['id'] ?>">Like</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts yet.</p>
    <?php endif; ?>

<?php endif; ?>