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

    <?php if (!empty($isAdmin) && $isAdmin): ?>
      <!-- Admin Dashboard -->
      <div class="mt-12">
        <h2 class="text-2xl font-bold text-yellow-300 mb-4">Admin Dashboard</h2>
        <!-- User Management Table -->
        <div class="bg-black bg-opacity-60 border border-yellow-500 rounded-lg p-6 mb-10">
          <h3 class="text-lg font-semibold text-yellow-200 mb-3">User Management</h3>
          <?php if (!empty($allUsers)): ?>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm text-left text-white border border-yellow-400">
                <thead class="bg-yellow-900 text-yellow-200">
                  <tr>
                    <th class="px-2 py-2 border-b border-yellow-400">Username</th>
                    <th class="px-2 py-2 border-b border-yellow-400">Role</th>
                    <th class="px-2 py-2 border-b border-yellow-400">Status</th>
                    <th class="px-2 py-2 border-b border-yellow-400">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($allUsers as $user): ?>
                    <tr class="odd:bg-yellow-950 even:bg-yellow-800/40">
                      <td class="px-2 py-2 border-b border-yellow-700"><?php echo htmlspecialchars($user['username']); ?></td>
                      <td class="px-2 py-2 border-b border-yellow-700">
                        <?php echo $user['is_admin'] ? '<span class="font-bold text-yellow-400">Admin</span>' : 'User'; ?>
                      </td>
                      <td class="px-2 py-2 border-b border-yellow-700">
                        <?php echo $user['is_blocked'] ? '<span class="text-red-400 font-semibold">Blocked</span>' : '<span class="text-green-400 font-semibold">Active</span>'; ?>
                      </td>
                      <td class="px-2 py-2 border-b border-yellow-700">
                        <div class="flex flex-wrap gap-1">
                          <?php if (!$user['is_admin']): ?>
                            <form method="post" class="inline">
                              <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                              <button type="submit" name="<?php echo $user['is_blocked'] ? 'unblock_user' : 'block_user'; ?>"
                                class="px-2 py-1 rounded border <?php echo $user['is_blocked'] ? 'bg-green-600 border-green-800 hover:bg-green-700' : 'bg-red-600 border-red-800 hover:bg-red-700'; ?> text-white font-semibold"
                                onclick="return confirm('<?php echo $user['is_blocked'] ? 'Unblock' : 'Block'; ?> this user?')">
                                <?php echo $user['is_blocked'] ? 'Unblock' : 'Block'; ?>
                              </button>
                            </form>
                          <?php endif; ?>
                          <form method="post" class="inline">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <?php if ($user['is_admin']): ?>
                              <?php if ($user['id'] != $currentUserId): ?>
                                <button type="submit" name="demote_user"
                                  class="px-2 py-1 rounded border bg-blue-700 border-blue-900 hover:bg-blue-800 text-white font-semibold"
                                  onclick="return confirm('Demote this admin to user?')">
                                  Demote
                                </button>
                              <?php else: ?>
                                <span class="text-xs text-gray-400 ml-2">You</span>
                              <?php endif; ?>
                            <?php else: ?>
                              <button type="submit" name="promote_user"
                                class="px-2 py-1 rounded border bg-yellow-700 border-yellow-900 hover:bg-yellow-800 text-white font-semibold"
                                onclick="return confirm('Promote this user to admin?')">
                                Promote
                              </button>
                            <?php endif; ?>
                          </form>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-yellow-200 italic">No users found.</p>
          <?php endif; ?>
        </div>

        <!-- Posts Management Section -->
        <div class="bg-black bg-opacity-60 border border-cyan-400 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-cyan-200 mb-3">All Posts</h3>
          <?php if (!empty($allPosts)): ?>
            <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
              <?php foreach ($allPosts as $post): ?>
                <div class="bg-cyan-900 bg-opacity-60 border border-cyan-600 rounded-lg p-4">
                  <div class="flex items-center mb-2">
                    <span class="text-cyan-300 font-bold mr-3">@<?php echo htmlspecialchars($post['username']); ?></span>
                    <small class="text-gray-300">Posted on: <?php echo $post['created_at']; ?></small>
                  </div>
                  <p class="text-cyan-100 mb-2"><?php echo htmlspecialchars($post['content']); ?></p>
                  <?php if (!empty($post['image_path'])): ?>
                    <div class="mb-2">
                      <img id="previewImg-admin-post-<?php echo $post['id']; ?>" src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image" class="max-w-full h-auto rounded cursor-pointer" />
                    </div>
                  <?php endif; ?>
                  <form method="post" class="mt-2">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" name="admin_delete_post"
                      onclick="return confirm('Delete this post?')"
                      class="px-3 py-1 rounded border bg-red-600 border-red-800 text-white font-semibold hover:bg-red-700">
                      Delete
                    </button>
                  </form>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-cyan-200 italic">No posts found.</p>
          <?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="js/image-previewer.js"></script>