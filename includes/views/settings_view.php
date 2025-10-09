<div class="min-h-screen flex items-center justify-center p-6 w-full h-full z-10 relative">
  <div class="bg-[#008080] rounded-lg shadow-lg w-full p-8 border border-gray-400 space-y-12">

    <?php if (!empty($isAdmin) && $isAdmin): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">

      <!-- My Posts Section -->
      <section class="border border-teal-400 rounded-lg p-6 bg-black bg-opacity-60">
        <h1 class="text-3xl font-bold text-white mb-4 border-b border-teal-400 pb-2">My Posts</h1>
        <p class="text-gray-200 mb-6">Manage your own posts below.</p>

        <?php if (empty($posts)): ?>
          <p class="text-white italic">You haven’t posted anything yet.</p>
        <?php else: ?>
          <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
            <?php foreach ($posts as $post): ?>
              <div class="bg-black bg-opacity-70 border border-teal-500 rounded-lg p-4">
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
      </section>

      <!-- User Management Section -->
      <section class="border border-yellow-500 rounded-lg p-6 bg-black bg-opacity-60">
        <h2 class="text-2xl font-bold text-yellow-300 mb-4 border-b border-yellow-400 pb-2">User Management</h2>
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
                        <?php if ($user['is_blocked']): ?>
                          <!-- Blocked users: only show Unblock button -->
                          <form method="post" class="inline">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" name="unblock_user"
                              class="px-2 w-20 py-1 rounded border bg-green-600 border-green-800 hover:bg-green-700 text-white font-semibold"
                              onclick="return confirm('Unblock this user?')">
                              Unblock
                            </button>
                          </form>
                        <?php else: ?>
                          <!-- Active users -->
                          <?php if ($user['is_admin']): ?>
                            <?php if ($user['id'] != $currentUserId): ?>
                              <form method="post" class="inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="demote_user"
                                  class="px-2 w-20 py-1 rounded border bg-blue-700 border-blue-900 hover:bg-blue-800 text-white font-semibold"
                                  onclick="return confirm('Demote this admin to user?')">
                                  Demote
                                </button>
                              </form>
                              <form method="post" class="inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="block_user"
                                  class="px-2 w-20 py-1 rounded border bg-red-600 border-red-800 hover:bg-red-700 text-white font-semibold"
                                  onclick="return confirm('Block this user? This will remove admin privileges.')">
                                  Block
                                </button>
                              </form>
                            <?php else: ?>
                              <span class="text-xs text-gray-400 ml-2">You</span>
                            <?php endif; ?>
                          <?php else: ?>
                            <form method="post" class="inline">
                              <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                              <button type="submit" name="promote_user"
                                class="px-2 w-20 py-1 rounded border bg-yellow-700 border-yellow-900 hover:bg-yellow-800 text-white font-semibold"
                                onclick="return confirm('Promote this user to admin?')">
                                Promote
                              </button>
                            </form>
                            <form method="post" class="inline">
                              <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                              <button type="submit" name="block_user"
                                class="px-2 w-20 py-1 rounded border bg-red-600 border-red-800 hover:bg-red-700 text-white font-semibold"
                                onclick="return confirm('Block this user?')">
                                Block
                              </button>
                            </form>
                          <?php endif; ?>
                        <?php endif; ?>
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
      </section>

      <!-- All Posts Section -->
      <section class="border border-cyan-400 rounded-lg p-6 bg-black bg-opacity-60">
        <h2 class="text-2xl font-bold text-cyan-300 mb-4 border-b border-cyan-400 pb-2">All Posts</h2>
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
      </section>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 w-full max-w-3xl mx-auto">

      <!-- My Posts Section -->
      <section class="border border-teal-400 rounded-lg p-6 bg-black bg-opacity-60">
        <h1 class="text-3xl font-bold text-white mb-4 border-b border-teal-400 pb-2">My Posts</h1>
        <p class="text-gray-200 mb-6">Manage your own posts below.</p>

        <?php if (empty($posts)): ?>
          <p class="text-white italic">You haven’t posted anything yet.</p>
        <?php else: ?>
          <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
            <?php foreach ($posts as $post): ?>
              <div class="bg-black bg-opacity-70 border border-teal-500 rounded-lg p-4">
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
      </section>
    </div>
    <?php endif; ?>

  </div>
</div>

<script src="js/image-previewer.js"></script>