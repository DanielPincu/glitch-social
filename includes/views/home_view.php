<h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
<a href="logout_loader.php">Logout</a><br><br>

<?php if (!empty($blocked_message)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($blocked_message); ?></p>
<?php else: ?>
    <!-- New Post Form -->
    <form method="post" enctype="multipart/form-data">
        <textarea name="content" placeholder="What's on your mind?" required></textarea><br>
        <input type="file" name="imageFile"><br>
        <button type="submit" name="post_submit">Post</button>
    </form>
    <hr>

    <!-- Posts Feed -->
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <div style="margin-bottom:15px; padding:10px; border:1px solid #ccc;">
                <strong><?php echo htmlspecialchars($post['username']); ?></strong>: 
                <?php echo htmlspecialchars($post['content']); ?><br>

                <?php if (!empty($post['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" 
                         alt="Post image" style="max-width:200px;"><br>
                <?php endif; ?>

                <small>Posted on: <?php echo $post['created_at']; ?></small>
                <br>
                <?php if ($postController->hasLikedPost($post['id'], $user_id)): ?>
                    <a href="feed_loader.php?action=unlike&post_id=<?php echo $post['id']; ?>">Unlike</a>
                <?php else: ?>
                    <a href="feed_loader.php?action=like&post_id=<?php echo $post['id']; ?>">Like</a>
                <?php endif; ?>
                <span><?php echo $postController->getPostLikes($post['id']); ?> likes</span>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts yet.</p>
    <?php endif; ?>
<?php endif; ?>