<?php if (!empty($_SESSION['error'])): ?>
  <div class="bg-red-600 text-white text-center font-bold rounded-lg p-3 mb-5 shadow-lg shadow-red-500/60">
    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
  </div>
<?php endif; ?>
<div class="min-h-screen flex items-center justify-center p-6 w-full h-full z-10 relative">
  <div class="bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] rounded-lg shadow-lg w-full p-8 border border-gray-400 space-y-12">

    <div class="-mx-8 -my-8 border-b-2 border-blue-700 bg-gradient-to-b from-blue-600 via-blue-500 to-blue-600 h-9 flex items-center justify-between px-3 shadow-lg z-50">
      <div class="text-white font-semibold text-sm tracking-wide select-none">
        Settings.exe
      </div>
      <div class="flex items-center space-x-1 -mx-2">
        <span class="pointer-events-none opacity-80 w-7 h-7 flex items-center justify-center rounded-sm border border-white bg-gradient-to-b from-blue-800 via-blue-600 to-blue-700 text-white text-sm transition-all duration-200 hover:from-blue-700 hover:via-blue-500 hover:to-blue-600">
          —
        </span>
        <span class="pointer-events-none opacity-80 w-7 h-7 flex items-center justify-center rounded-sm border border-white bg-gradient-to-b from-blue-800 via-blue-600 to-blue-700 text-white text-sm transition-all duration-200 hover:from-blue-700 hover:via-blue-500 hover:to-blue-600">
          ▣
        </span>
        <a href="index.php" 
           class="w-7 h-7 flex items-center justify-center rounded-sm border border-white bg-gradient-to-b from-red-700 via-red-500 to-red-700 text-white font-bold text-sm hover:scale-110 hover:shadow-[0_0_8px_rgba(255,0,0,0.8)] transition-all duration-150 cursor-pointer">
          ╳
        </a>
      </div>
    </div>

    <?php if (!empty($isAdmin) && $isAdmin): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full">

      <!-- My Posts Section -->
      <section class="border border-teal-400 rounded-lg p-6 bg-black bg-opacity-60">
        <h1 class="text-2xl font-bold text-gray-300 mb-4 border-b border-teal-400 pb-2">My Posts</h1>
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
                
                <div class="flex gap-2 mt-3">
                  <form method="post" action="index.php?page=settings" class="inline">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="button"
                      onclick="document.getElementById('edit-form-<?php echo $post['id']; ?>').classList.toggle('hidden')"
                      class="px-3 py-1 rounded border bg-yellow-500 border-yellow-700 text-white font-semibold hover:bg-yellow-600">
                      Edit
                    </button>
                  </form>

                  <form method="post" action="index.php?page=settings" class="inline">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" name="delete_post"
                      onclick="return confirm('Delete this post?')"
                      class="px-3 py-1 rounded border bg-red-600 border-red-800 text-white font-semibold hover:bg-red-700">
                      Delete
                    </button>
                  </form>
                </div>

                <!-- Hidden edit form -->
                <form id="edit-form-<?php echo $post['id']; ?>" method="post" action="index.php?page=settings" enctype="multipart/form-data" class="hidden mt-3 space-y-2">
                  <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                  <textarea name="new_content" rows="3" class="w-full rounded border border-teal-400 bg-black bg-opacity-50 text-green-400 p-2"><?php echo htmlspecialchars($post['content']); ?></textarea>

                  <?php if (!empty($post['image_path'])): ?>
                    <div class="mt-2">
                      <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image" class="rounded w-32 h-32 object-cover mb-2">
                      <label class="inline-flex items-center space-x-2">
                        <input type="checkbox" name="remove_image" value="1" class="text-red-600">
                        <span class="text-sm text-gray-400">Remove image</span>
                      </label>
                    </div>
                  <?php endif; ?>

                  <div>
                    <label class="text-sm text-gray-400 block mb-1">Replace image:</label>
                    <input type="file" name="new_image" accept="image/*" class="text-gray-200 text-sm">
                  </div>

                  <div>
                    <label class="text-sm text-gray-400 block mb-1">Visibility:</label>
                    <select name="visibility" class="w-full rounded border border-teal-400 bg-black bg-opacity-50 text-green-400 p-2">
                      <option value="public" <?php echo $post['visibility'] === 'public' ? 'selected' : ''; ?>>Public</option>
                      <option value="followers" <?php echo $post['visibility'] === 'followers' ? 'selected' : ''; ?>>Followers</option>
                      <option value="private" <?php echo $post['visibility'] === 'private' ? 'selected' : ''; ?>>Private</option>
                    </select>
                    <input type="hidden" name="visibility_fallback" value="<?php echo $post['visibility']; ?>">
                  </div>

                  <button type="submit" name="update_post"
                    class="px-3 py-1 rounded border bg-green-600 border-green-800 text-white font-semibold hover:bg-green-700">
                    Save Changes
                  </button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
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
                    <td class="px-2 py-2 border-b border-yellow-700">
                      <a href="index.php?page=profile&id=<?php echo $user['id']; ?>" class="text-yellow-300 hover:underline">
                        <?php echo htmlspecialchars($user['username']); ?>
                      </a>
                    </td>
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
                      <form method="post" action="index.php?page=settings" class="inline">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="from_settings" value="1">
                        <button type="submit" name="unblock_user"
                          class="px-2 w-20 py-1 rounded border bg-green-600 border-green-800 hover:bg-green-700 text-white font-semibold">
                          Unblock
                        </button>
                      </form>
                        <?php else: ?>
                          <!-- Active users -->
                          <?php if ($user['is_admin']): ?>
                            <?php if ($user['id'] != $currentUserId): ?>
                              <form method="post" action="index.php?page=settings" class="inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="demote_user"
                                  class="px-2 w-20 py-1 rounded border bg-blue-700 border-blue-900 hover:bg-blue-800 text-white font-semibold">
                                  Demote
                                </button>
                              </form>
                              <form method="post" action="index.php?page=settings" class="inline">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="from_settings" value="1">
                                <button type="submit" name="block_user"
                                  class="px-2 w-20 py-1 rounded border bg-red-600 border-red-800 hover:bg-red-700 text-white font-semibold">
                                  Block
                                </button>
                              </form>
                            <?php else: ?>
                              <span class="text-xs text-gray-400 ml-2">You</span>
                            <?php endif; ?>
                          <?php else: ?>
                            <form method="post" action="index.php?page=settings" class="inline">
                              <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                              <button type="submit" name="promote_user"
                                class="px-2 w-20 py-1 rounded border bg-yellow-700 border-yellow-900 hover:bg-yellow-800 text-white font-semibold">
                                Promote
                              </button>
                            </form>
                            <form method="post" action="index.php?page=settings" class="inline">
                              <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                              <input type="hidden" name="from_settings" value="1">
                              <button type="submit" name="block_user"
                                class="px-2 w-20 py-1 rounded border bg-red-600 border-red-800 hover:bg-red-700 text-white font-semibold">
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
                  <a href="index.php?page=profile&id=<?php echo $post['user_id']; ?>" class="text-cyan-300 font-bold mr-3">@<?php echo htmlspecialchars($post['username']); ?></a>
                  <small class="text-gray-300">Posted on: <?php echo $post['created_at']; ?></small>
                </div>
                <p class="text-cyan-100 mb-2"><?php echo htmlspecialchars($post['content']); ?></p>
                <?php if (!empty($post['image_path'])): ?>
                  <div class="mb-2">
                    <img id="previewImg-admin-post-<?php echo $post['id']; ?>" src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image" class="max-w-full h-auto rounded cursor-pointer" />
                  </div>
                <?php endif; ?>
                <form method="post" action="index.php?page=settings" class="mt-2">
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
        <h1 class="text-2xl font-bold text-gray-300 mb-4 border-b border-teal-400 pb-2">My Posts</h1>
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
                
                <div class="flex gap-2 mt-3">
                  <form method="post" action="index.php?page=settings" class="inline">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="button"
                      onclick="document.getElementById('edit-form-<?php echo $post['id']; ?>').classList.toggle('hidden')"
                      class="px-3 py-1 rounded border bg-yellow-500 border-yellow-700 text-white font-semibold hover:bg-yellow-600">
                      Edit
                    </button>
                  </form>

                  <form method="post" action="index.php?page=settings" class="inline">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" name="delete_post"
                      onclick="return confirm('Delete this post?')"
                      class="px-3 py-1 rounded border bg-red-600 border-red-800 text-white font-semibold hover:bg-red-700">
                      Delete
                    </button>
                  </form>
                </div>

                <!-- Hidden edit form -->
                <form id="edit-form-<?php echo $post['id']; ?>" method="post" action="index.php?page=settings" enctype="multipart/form-data" class="hidden mt-3 space-y-2">
                  <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                  <textarea name="new_content" rows="3" class="w-full rounded border border-teal-400 bg-black bg-opacity-50 text-green-400 p-2"><?php echo htmlspecialchars($post['content']); ?></textarea>

                  <?php if (!empty($post['image_path'])): ?>
                    <div class="mt-2">
                      <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image" class="rounded w-32 h-32 object-cover mb-2">
                      <label class="inline-flex items-center space-x-2">
                        <input type="checkbox" name="remove_image" value="1" class="text-red-600">
                        <span class="text-sm text-gray-400">Remove image</span>
                      </label>
                    </div>
                  <?php endif; ?>

                  <div>
                    <label class="text-sm text-gray-400 block mb-1">Replace image:</label>
                    <input type="file" name="new_image" accept="image/*" class="text-gray-200 text-sm">
                  </div>

                  <div>
                    <label class="text-sm text-gray-400 block mb-1">Visibility:</label>
                    <select name="visibility" class="w-full rounded border border-teal-400 bg-black bg-opacity-50 text-green-400 p-2">
                      <option value="public" <?php echo $post['visibility'] === 'public' ? 'selected' : ''; ?>>Public</option>
                      <option value="followers" <?php echo $post['visibility'] === 'followers' ? 'selected' : ''; ?>>Followers</option>
                      <option value="private" <?php echo $post['visibility'] === 'private' ? 'selected' : ''; ?>>Private</option>
                    </select>
                    <input type="hidden" name="visibility_fallback" value="<?php echo $post['visibility']; ?>">
                  </div>

                  <button type="submit" name="update_post"
                    class="px-3 py-1 rounded border bg-green-600 border-green-800 text-white font-semibold hover:bg-green-700">
                    Save Changes
                  </button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <a href="index.php" class="inline-block mt-6">
          <button type="button" class="bg-gradient-to-b from-blue-500 to-blue-700 text-white font-semibold px-5 py-2 rounded border border-blue-800 shadow-md hover:from-blue-600 hover:to-blue-800 active:translate-y-0.5 active:shadow-none">
            Go Back Home
          </button>
        </a>
      </section>
    </div>
    <?php endif; ?>

    <!-- Blocked Users Section (shown to all users) -->
    <section class="border border-red-400 rounded-lg p-6 bg-black bg-opacity-60 mt-8">
      <h2 class="text-2xl font-bold text-red-400 mb-4 border-b border-red-400 pb-2">Blocked Users</h2>
      <?php 
        $blockedUsers = $userController->getBlockedUsersByUser($session->getUserId());
      ?>
      <?php if (!empty($blockedUsers)): ?>
        <div class="space-y-3">
          <?php foreach ($blockedUsers as $blocked): ?>
            <div class="flex justify-between items-center bg-black bg-opacity-50 border border-red-500 rounded p-3">
              <div class="flex items-center gap-3">
                <a href="index.php?page=profile&id=<?php echo $blocked['id']; ?>" class="text-white hover:underline">
                  @<?php echo htmlspecialchars($blocked['username']); ?>
                </a>
              </div>
              <form method="POST" action="index.php?page=settings">
                <input type="hidden" name="blocked_id" value="<?php echo $blocked['id']; ?>">
                <input type="hidden" name="from_settings" value="1">
                <button type="submit" name="unblock_user"
                  class="px-3 py-1 bg-green-600 text-white font-semibold rounded hover:bg-green-700 border border-green-800">
                  Unblock
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-gray-300 italic">You haven't blocked anyone yet.</p>
      <?php endif; ?>
    </section>

    <!-- Edit Terms & Conditions Section (admin only) -->
    <?php if (!empty($isAdmin) && $isAdmin): ?>
    <section class="border border-green-400 rounded-lg p-6 bg-black bg-opacity-60 mt-8">
      <h2 class="text-2xl font-bold text-green-400 mb-4 border-b border-green-400 pb-2 flex justify-between items-center">
        Edit Terms & Conditions
        <button type="button" onclick="document.getElementById('terms-editor').classList.toggle('hidden')" class="text-sm bg-green-700 px-3 py-1 rounded hover:bg-green-800 text-white border border-green-900">
          Show / Hide
        </button>
      </h2>
      <div id="terms-editor" class="hidden">
        <form method="POST" action="index.php?page=settings">
          <textarea name="terms_content" rows="10" class="w-full p-3 bg-gray-800 border border-green-400 text-white rounded-md mb-4"><?php echo htmlspecialchars($termsContent['content'] ?? '', ENT_QUOTES); ?></textarea>
          <input type="hidden" name="update_terms" value="1">
          <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded border border-green-800">
            Update Terms
          </button>
        </form>
      </div>
    </section>
    <?php endif; ?>


  </div>
</div>