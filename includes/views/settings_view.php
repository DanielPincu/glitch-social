<div class="min-h-screen flex items-center justify-center p-6">
  <div class="bg-[#008080] rounded-lg shadow-lg w-full max-w-3xl p-8 border border-gray-400">
    <h1 class="text-3xl font-bold text-white mb-6">My Settings</h1>
    <p class="text-gray-200 mb-6">Manage your own posts below.</p>

    <?php if (empty($posts)): ?>
      <p class="text-white italic">You haven’t posted anything yet.</p>
    <?php else: ?>
      <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
        <?php foreach ($posts as $post): ?>
          <div class="bg-black bg-opacity-60 border border-gray-500 rounded-lg p-4">
            <p class="text-green-400 mb-2"><?php echo htmlspecialchars($post['content']); ?></p>
            <?php if (!empty($post['image_path'])): ?>
              <div class="mb-2">
                <img id="previewImg-post-<?php echo $post['id']; ?>" src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image" class="max-w-full h-auto rounded cursor-pointer" />
              </div>
            <?php endif; ?>
            <small class="text-gray-300">Posted on: <?php echo $post['created_at']; ?></small>
            
            <form method="post" class="mt-3">
              <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
              <button type="submit" name="delete_post"
                onclick="return confirm('Delete this post?')"
                class="px-3 py-1 rounded border bg-red-600 border-red-800 text-white font-semibold hover:bg-red-700">
                Delete
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <a href="index.php" class="inline-block mt-6">
      <button type="button" class="bg-gradient-to-b from-blue-500 to-blue-700 text-white font-semibold px-5 py-2 rounded border border-blue-800 shadow-md hover:from-blue-600 hover:to-blue-800 active:translate-y-0.5 active:shadow-none">
        Return to Home
      </button>
    </a>
  </div>
</div>

<script src="js/image-previewer.js"></script>