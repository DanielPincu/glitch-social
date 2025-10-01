<div class="min-h-screen flex items-center justify-center p-6">
  <div class="bg-[#008080] rounded-lg shadow-lg w-full max-w-5xl p-8 border border-gray-400">
    <h1 class="text-4xl font-bold text-gray-800 mb-6 select-none">Admin Dashboard</h1>

    <div class="flex items-center justify-between bg-gray-200 border border-gray-400 rounded px-4 py-2 mb-8 select-none">
      <div class="text-sm text-gray-700 font-mono">Session ID: <?php echo session_id(); ?></div>
      <div class="text-sm text-gray-700">
        Welcome, <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></span> |
        <a href="logout_loader.php" class="text-blue-700 hover:underline font-semibold">Logout</a>
      </div>
    </div>

    <a href="index.php" class="inline-block mb-8">
      <button type="button" class="bg-gradient-to-b from-blue-500 to-blue-700 text-white font-semibold px-5 py-2 rounded border border-blue-800 shadow-md hover:from-blue-600 hover:to-blue-800 active:translate-y-0.5 active:shadow-none select-none transition duration-150">
        Return to Home
      </button>
    </a>

    <section class="mb-12">
      <h2 class="text-2xl font-semibold text-gray-800 mb-4 select-none">Manage Users</h2>
      <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
        <?php foreach ($users as $user): ?>
          <div class="bg-white border border-gray-400 rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between hover:shadow-xl transition-shadow duration-200 select-none">
            <div class="mb-3 md:mb-0">
              <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['username']); ?></span> -
              <span class="<?php echo $user['is_blocked'] ? 'text-red-600' : 'text-green-700'; ?> font-semibold">
                <?php echo $user['is_blocked'] ? 'Blocked' : 'Active'; ?>
              </span> -
              <span class="<?php echo $user['is_admin'] ? 'text-blue-700' : 'text-gray-700'; ?> font-semibold">
                <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
              </span>
            </div>
            <div class="flex flex-wrap gap-2">
              <!-- Block / Unblock -->
              <form method="post" style="display:inline;">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <button type="submit" name="toggle_block"
                  class="px-4 py-1 rounded border font-semibold transition duration-150 select-none
                  <?php echo $user['is_blocked'] ? 'bg-green-600 border-green-800 text-white hover:bg-green-700 active:translate-y-0.5 active:shadow-none' : 'bg-red-600 border-red-800 text-white hover:bg-red-700 active:translate-y-0.5 active:shadow-none'; ?>">
                  <?php echo $user['is_blocked'] ? 'Unblock' : 'Block'; ?>
                </button>
              </form>

              <!-- Promote / Demote -->
              <?php if (!$user['is_blocked']): ?>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <?php if (!$user['is_admin']): ?>
                    <button type="submit" name="promote"
                      class="px-4 w-64 py-1 rounded border bg-blue-600 border-blue-800 text-white font-semibold hover:bg-blue-700 active:translate-y-0.5 active:shadow-none select-none">
                      Promote to Admin
                    </button>
                  <?php else: ?>
                    <button type="submit" name="demote"
                      class="px-4 py-1 w-64 rounded border bg-yellow-600 border-yellow-800 text-white font-semibold hover:bg-yellow-700 active:translate-y-0.5 active:shadow-none select-none">
                      Demote from Admin
                    </button>
                  <?php endif; ?>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section>
      <h2 class="text-2xl font-semibold text-gray-800 mb-4 select-none">Manage Posts</h2>
      <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
        <?php foreach ($posts as $post): ?>
          <div class="bg-white border border-gray-400 rounded-lg p-4 hover:shadow-xl transition-shadow duration-200 select-none">
            <div class="mb-2">
              <strong class="text-gray-900"><?php echo htmlspecialchars($post['username']); ?></strong>:
              <span class="text-gray-800"><?php echo htmlspecialchars($post['content']); ?></span>
            </div>
            <?php if (!empty($post['image_path'])): ?>
              <div class="mb-2">
                <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image" class="max-h-48 rounded border">
              </div>
            <?php endif; ?>
            <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
              <small>Posted on: <?php echo $post['created_at']; ?></small>
              <form method="post" action="admin_loader.php" style="display:inline;">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <button type="submit" name="delete_post"
                  onclick="return confirm('Delete this post?')"
                  class="px-3 py-1 rounded border bg-red-600 border-red-800 text-white font-semibold hover:bg-red-700 active:translate-y-0.5 active:shadow-none select-none transition duration-150">
                  Delete
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </div>
</div>
<script src="scripts/image-previewer.js"></script>