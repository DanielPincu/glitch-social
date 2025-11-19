<?php if (!empty($_SESSION['error'])): ?>
  <div class="bg-red-600 text-white text-center font-bold rounded-lg p-3 mb-5 shadow-lg shadow-red-500/60">
    <?php echo htmlspecialchars($_SESSION['error']);
    unset($_SESSION['error']); ?>
  </div>
<?php endif; ?>
<main class="container mx-auto px-4 pb-16 pt-8 relative z-10">

  <div class="grid grid-cols-1 md:grid-cols-4 gap-6">




    <!-- Left Sidebar -->
    <div class="md:col-span-1 md:sticky md:top-8 md:self-start md:h-[calc(100vh-6rem)] md:overflow-y-auto space-y-4 flex flex-col rounded-lg overflow-hidden shadow-inner border-2 border-[#7AA0E0]">
      <div class="flex flex-col h-full justify-between">
        <!--  Following  -->
        <div class="bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4 h-full">
          <h3 class="font-bold mb-3">FOLLOWING</h3>
          <div class="space-y-2">
            <?php
            if (!empty($followingList)) {
              foreach ($followingList as $followedUser) {
                $avatar = !empty($followedUser['avatar_url'])
                  ? htmlspecialchars($followedUser['avatar_url'])
                  : null;
            ?>
                <div class="flex items-center space-x-2">
                  <a href="index.php?page=profile&id=<?php echo $followedUser['id']; ?>">
                    <div class="w-8 h-8 bg-black border-2 border-green-500 flex items-center justify-center overflow-hidden">
                      <?php if ($avatar): ?>
                        <img src="<?php echo $avatar; ?>" alt="<?php echo htmlspecialchars($followedUser['username']); ?>'s avatar" class="object-cover w-full h-full">
                      <?php else: ?>
                        <i data-feather="user" class="text-green-500 text-xs"></i>
                      <?php endif; ?>
                    </div>
                  </a>
                  <div>
                    <p class="text-sm font-bold text-green-200 hover:underline">
                      <a href="index.php?page=profile&id=<?php echo $followedUser['id']; ?>">
                        <?php echo htmlspecialchars($followedUser['username']); ?>
                      </a>
                    </p>
                  </div>
                </div>
            <?php
              }
            } else {
              echo '<p class="text-sm text-gray-200">You are not following anyone yet.</p>';
            }
            ?>
          </div>
        </div>
        <!-- System Alerts -->
         <!-- Just for fun -->
        <div class="bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4 ">
          <h3 class="font-bold mb-3">SYSTEM STATUS</h3>
          <div class="space-y-2">
            <div class="bg-black bg-opacity-20 p-2">
              <p class="text-xs matrix-text">
                Connected as <?php echo htmlspecialchars($_SESSION['username']); ?> ⟁ Role: <?php echo !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] ? 'Admin' : 'User'; ?>
              </p>
            </div>
            <div class="bg-black bg-opacity-20 p-2">
              <p class="text-xs matrix-text">CPU: Cyrix 6x86 @ 133MHz</p>
              <p class="text-xs matrix-text">Memory: 64MB EDO RAM</p>
              <p class="text-xs matrix-text">GPU: S3 Trio64V+ 2MB VRAM</p>
              <p class="text-xs matrix-text">OS: ZionOS Build 1999</p>
            </div>
            <div class="bg-black bg-opacity-20 p-2">
              <p class="text-xs matrix-text">Storage: Quantum Fireball 2.1GB IDE</p>
              <p class="text-xs matrix-text">Network: Z-Link 56K Dial-up Modem</p>
            </div>
           <div class="bg-black bg-opacity-20 p-2">
              <?php
                $startDate = new DateTime('1999-12-31');
                $now = new DateTime();
                $interval = $startDate->diff($now);
              ?>
              <p class="text-xs matrix-text">
                System Uptime: <?php echo $interval->y; ?> years, <?php echo $interval->m; ?> months (approx.)
              </p>
       <?php
         $daemons = [
           'matrix_core.dll, illusion_handler.vxd',
           'zion_uplink.exe, sentinel_watchdog.sys',
           'neural_sync.sys, dreamnet.daemon',
           'reality_bridge.dll, matrix_core.vxd',
           'construct_loader.sys, oracle_node.dll',
           'ghost_trace.vxd, sentinel_link.exe',
           'architect_thread.dll, anomaly_detector.sys',
           'zion_channel.exe, mainframe_relay.vxd',
           'glitch_purifier.sys, source_portal.dll',
           'source_decoder.sys, morph_relay.vxd',
           'simulacra_engine.dll, perception_filter.sys',
           'winxp_shell32.dll, zion_recovery.sys',
           'bluepill_bootmgr.exe, 404_handler.vxd',
           'msmatrix32.dll, code_red_patch.sys',
           'agent_smith.exe, xp_restore_point.vxd',
           'trinity_service.exe, PPOE_encrypter.dll',
           'nt_matrix_bridge.sys, illusion_handler.vxd',
           'zion_repair_tool.exe, matrix_dxdiag.dll',
           'matrix_taskmgr.exe, holo_reboot.vxd'
         ];
         $randomDaemon = $daemons[array_rand($daemons)];
       ?>
       <p class="text-xs matrix-text">Daemons: <?php echo $randomDaemon; ?></p>
              <?php
                $entropySymbols = ['⟴', '⟊', '⟠', '⟢', '⟡', '⟆', '∞', '⟁', '⧈', '⌬', '⧋', '⧉'];
                shuffle($entropySymbols);
                $randomEntropy = implode('', array_slice($entropySymbols, 0, rand(7, 10)));
              ?>
              <p class="text-xs matrix-text">Glitch Entropy: <?php echo $randomEntropy; ?></p>

              <?php
                $syncLevels = ['Strong', 'Nominal', 'Unstable', 'Critical', 'Degraded'];
                $randomSync = $syncLevels[array_rand($syncLevels)];
              ?>
              <p class="text-xs matrix-text">Reality Sync: <?php echo $randomSync; ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Feed -->
    <div class="md:col-span-2 space-y-4">
      <?php if (!empty($_SESSION['is_blocked']) && $_SESSION['is_blocked'] == 1): ?>
        <div class="xp-window bg-red-800 text-white p-4 rounded-lg shadow-lg border border-red-400 text-center">
          🚫 You are blocked and cannot create posts or interact with the feed.
        </div>
      <?php elseif (!empty($blocked_message) || !empty($chat_blocked_message)): ?>
        <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4 mb-4">
          <?php if (!empty($blocked_message)): ?>
            <p class="text-red-400 font-bold text-center"><?php echo htmlspecialchars($blocked_message); ?></p>
          <?php endif; ?>
          <?php if (!empty($chat_blocked_message)): ?>
            <p class="text-red-400 font-bold text-center mt-2">
              <?php echo htmlspecialchars($chat_blocked_message); ?>
            </p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <!-- Center Search Field -->
        <div class="flex justify-center mb-6">
          <form method="get" action="index.php" class="flex gap-2 w-full max-w-lg">
            <input type="hidden" name="page" value="search">
            <input
              type="text"
              name="q"
              placeholder="Search for The One..."
              value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
              class="flex-1 bg-white text-gray-800 text-sm px-4 py-2 rounded border border-[#7AA0E0] focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-gray-400">
            <button
              type="submit"
              class="bg-gradient-to-t from-[#5A8DEE] to-[#7AA0E0] text-white px-4 py-2 rounded font-semibold text-sm shadow hover:brightness-110 active:translate-y-0.5 transition">
              Search
            </button>
          </form>
        </div>
        <!-- Create Post -->
        <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] rounded-lg shadow-lg p-6 border border-[#b0b0b0] relative">
          <div class="flex items-center mb-4 space-x-2">
            <i data-feather="edit-2" class="text-green-400 drop-shadow-[0_0_3px_rgba(0,255,0,0.7)]"></i>
            <h3 class="text-lg font-semibold matrix-text drop-shadow-[0_0_5px_rgba(0,255,0,0.4)]">Create Post</h3>
          </div>
          <form id="create-post-form" method="post" enctype="multipart/form-data" action="index.php?page=home">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($session->getCsrfToken(), ENT_QUOTES); ?>">
            <div id="quill-editor" class="w-full bg-slate-300 text-black px-5 py-4 border border-[#b0b0b0] focus:outline-none"></div>
            <input type="hidden" name="content" id="post-content">
            <div class="flex justify-between items-center mt-4">
              <div class="flex items-center gap-2">
                <label for="imageFile" class="cursor-pointer inline-flex items-center px-3 py-1 rounded border border-[#b0b0b0] bg-gradient-to-t from-[#3A6EA5] to-[#5CACEE] text-blue-900 text-xs font-semibold shadow-inner hover:brightness-110 active:brightness-90 transition select-none">
                  <i data-feather="image" class="w-4 h-4 mr-1 drop-shadow-[0_0_3px_rgba(0,255,0,0.7)]"></i> Image
                  <input type="file" name="imageFile" id="imageFile" onchange="previewImage(event)" class="hidden">
                </label>
                <select name="visibility" id="visibility"
                  class="px-3 py-1 rounded border border-[#b0b0b0] bg-gradient-to-t from-[#e8e8e8] to-[#d0e4fa] text-blue-900 text-xs font-semibold shadow-inner focus:outline-none focus:ring-2 focus:ring-green-500 transition"
                  style="min-width: 100px;">
                  <option value="public">Public</option>
                  <option value="followers">Followers</option>
                  <option value="private">Private</option>
                </select>
              </div>
              <button type="submit" name="post_submit"
                class="px-6 py-2 rounded border border-[#b0b0b0] bg-gradient-to-t from-[#1E90FF] to-[#5CACEE] text-blue-900 font-bold hover:drop-shadow-[0_0_10px_rgba(30,144,255,0.5)] transition select-none">
                Post
              </button>
            </div>
            <div id="imagePreview" class="mt-3 hidden border border-gray-300 rounded-lg overflow-hidden max-h-40">
              <p class="text-xs text-green-400 mb-1 px-2 pt-1">Image attached: <span id="fileName"></span></p>
              <img id="previewImg" src="" alt="Preview" class="w-full object-contain max-h-36 drop-shadow-[0_0_6px_rgba(0,255,0,0.5)] rounded" style="box-shadow: 0 0 8px 2px rgba(0,255,0,0.4);">
            </div>
          </form>
        </div>
        <!-- Tab Switcher -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <button id="hotTabBtn" class="w-full px-6 py-2 rounded border border-[#b0b0b0] bg-gradient-to-t from-[#1E90FF] to-[#5CACEE] text-blue-900 font-bold hover:drop-shadow-[0_0_10px_rgba(30,144,255,0.5)] transition select-none" type="button">🔥 Hot</button>
          <button id="followingTabBtn" class="w-full px-6 py-2 rounded border border-[#b0b0b0] bg-gradient-to-t from-[#1E90FF] to-[#5CACEE] text-blue-900 font-bold hover:drop-shadow-[0_0_10px_rgba(30,144,255,0.5)] transition select-none" type="button">👥 Following</button>
        </div>
        <!-- Posts Feed -->
        <div id="hotFeed">
          <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
              <div id="post-<?php echo $post['id']; ?>" class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4 mb-3">
                <div class="flex justify-between items-center mb-3">
                  <div class="flex items-center space-x-3">
                    <?php if (!empty($post['avatar_url'])): ?>
                      <div class="w-20 h-20 border-2 border-white overflow-hidden bg-black">
                        <a href="index.php?page=profile&id=<?php echo $post['user_id']; ?>">
                          <img
                            src="<?php echo htmlspecialchars($post['avatar_url']); ?>"
                            alt="<?php echo htmlspecialchars($post['username']); ?>'s avatar"
                            class="w-full h-full object-cover">
                        </a>
                      </div>
                    <?php else: ?>
                      <div class="w-20 h-20 border-2 border-white overflow-hidden flex items-center justify-center bg-black">
                        <a href="index.php?page=profile&id=<?php echo $post['user_id']; ?>">
                          <i data-feather="user" class="text-green-400 w-5 h-5"></i>
                        </a>
                      </div>
                    <?php endif; ?>
                    <div>
                      <h4 class="font-bold">
                        <a href="index.php?page=profile&id=<?php echo $post['user_id']; ?>" class="text-green-200 hover:underline">
                          <?php echo htmlspecialchars($post['username']); ?>
                        </a>
                      </h4>
                      <p class="text-xs">
                        <?php if (!empty($post['created_at'])): ?>
                          <?php echo htmlspecialchars($post['created_at']); ?>
                        <?php endif; ?>
                      </p>
                    </div>
                  </div>
                </div>
                <div class="mb-3"><?php echo $post['content']; ?></div>
                <?php if (!empty($post['image_path'])): ?>
                  <div class="mb-3 border-2 border-white bg-black flex justify-center">
                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image" class="w-auto max-h-[600px] object-contain">
                  </div>
                <?php endif; ?>
                <!-- Post actions -->
                <div class="flex justify-between text-sm border-t border-gray-400 pt-2">
                  <div class="flex items-center gap-2">
                    <button
                      class="like-btn flex items-center gap-1 hover:scale-110 <?php echo $postController->hasLikedPost($post['id'], $user_id) ? 'text-pink-300' : ''; ?>"
                      data-post-id="<?php echo $post['id']; ?>"
                      data-liked="<?php echo $postController->hasLikedPost($post['id'], $user_id) ? 'true' : 'false'; ?>"
                      type="button">
                      <?php echo $postController->hasLikedPost($post['id'], $user_id) ? '❤️' : '🤍'; ?>
                      <span><?php echo $postController->getLikeCount($post['id']); ?> likes</span>
                    </button>
                  </div>
                  <!-- Placeholder for comments/share -->
                  <div class="flex items-center gap-3">
                    <button
                      type="button"
                      onclick="toggleCommentForm('hot', <?php echo $post['id']; ?>)"
                      class="flex items-center gap-1 text-gray-200 hover:text-blue-300 transition">
                      <i data-feather="message-square" class="w-4 h-4"></i>
                      Comment
                    </button>
                  </div>
                </div>
                <!-- Comments Section -->
                <div class="mt-4 border-t border-gray-400 pt-2">
                  <div class="comment-form hidden" id="hot-comment-form-<?php echo $post['id']; ?>">
                    <form method="POST" action="index.php?page=home" class="flex items-center space-x-2 mb-2">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($session->getCsrfToken(), ENT_QUOTES); ?>">
                      <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                      <input type="text" name="comment_content" placeholder="Add a comment..."
                        class="w-full bg-gray-800 text-white text-sm px-3 py-2 rounded border border-gray-600 focus:outline-none">
                      <button type="submit" name="add_comment"
                        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                        Post
                      </button>
                    </form>
                  </div>
                  <?php
                  $comments = $postController->getComments($post['id']);
                  if (!empty($comments)):
                    foreach ($comments as $comment): ?>
                      <div class="flex items-start space-x-2 mb-1" data-comment-id="<?php echo $comment['id']; ?>">
                        <div class="w-6 h-6 rounded-full overflow-hidden border border-gray-500 flex items-center justify-center bg-black">
                          <?php if (!empty($comment['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($comment['avatar_url']); ?>" class="w-full h-full object-cover">
                          <?php else: ?>
                            <i data-feather="user" class="text-green-400 w-4 h-4"></i>
                          <?php endif; ?>
                        </div>
                        <div class="text-sm flex flex-col w-full">
                          <div class="flex justify-between items-center">
                            <a href="index.php?page=profile&id=<?php echo $comment['user_id']; ?>" class="font-semibold text-green-200 hover:underline">
                              <?php echo htmlspecialchars($comment['username']); ?>
                            </a>
                            <?php
                            $canDelete = false;
                            if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                              $canDelete = true; // admin
                            } elseif ($comment['user_id'] == $user_id) {
                              $canDelete = true; // comment owner
                            } elseif ($post['user_id'] == $user_id) {
                              $canDelete = true; // post owner
                            }
                            ?>
                            <?php if ($canDelete): ?>
                              <div class="flex gap-2 text-xs">
                                <?php if ($comment['user_id'] == $user_id): ?>
                                  <button
                                    type="button"
                                    class="edit-comment-btn bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition"
                                    data-comment-id="<?php echo $comment['id']; ?>">
                                    Edit
                                  </button>
                                <?php endif; ?>
                                <form method="POST" action="index.php?page=home" class="inline">
                                  <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                  <button
                                    type="submit"
                                    name="delete_comment"
                                    class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 transition">
                                    Delete
                                  </button>
                                </form>
                              </div>
                            <?php endif; ?>
                          </div>
                          <p id="hot-comment-text-<?php echo $comment['id']; ?>" class="text-gray-300" data-comment-text><?php echo htmlspecialchars($comment['content']); ?></p>
                        </div>
                      </div>
                  <?php endforeach;
                  endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4">
              <p class="text-center text-gray-100">No posts yet.</p>
            </div>
          <?php endif; ?>
        </div>
        <div id="followingFeed" class="hidden">
          <?php if (!empty($followingPosts)): ?>
            <?php foreach ($followingPosts as $post): ?>
              <div id="post-<?php echo $post['id']; ?>" class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4">
                <div class="flex justify-between items-center mb-3">
                  <div class="flex items-center space-x-3">
                    <?php if (!empty($post['avatar_url'])): ?>
                      <div class="w-20 h-20 border-2 border-white overflow-hidden bg-black">
                        <a href="index.php?page=profile&id=<?php echo $post['user_id']; ?>">
                          <img
                            src="<?php echo htmlspecialchars($post['avatar_url']); ?>"
                            alt="<?php echo htmlspecialchars($post['username']); ?>'s avatar"
                            class="w-full h-full object-cover">
                        </a>
                      </div>
                    <?php else: ?>
                      <div class="w-20 h-20 border-2 border-white overflow-hidden flex items-center justify-center bg-black">
                        <a href="index.php?page=profile&id=<?php echo $post['user_id']; ?>">
                          <i data-feather="user" class="text-green-400 w-5 h-5"></i>
                        </a>
                      </div>
                    <?php endif; ?>
                    <div>
                      <h4 class="font-bold">
                        <a href="index.php?page=profile&id=<?php echo $post['user_id']; ?>" class="text-green-200 hover:underline">
                          <?php echo htmlspecialchars($post['username']); ?>
                        </a>
                      </h4>
                      <p class="text-xs">
                        <?php if (!empty($post['created_at'])): ?>
                          <?php echo htmlspecialchars($post['created_at']); ?>
                        <?php endif; ?>
                      </p>
                    </div>
                  </div>
                </div>
                <div class="mb-3"><?php echo $post['content']; ?></div>
                <?php if (!empty($post['image_path'])): ?>
                  <div class="mb-3 border-2 border-white bg-black flex justify-center">
                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image" class="w-auto max-h-[600px] object-contain">
                  </div>
                <?php endif; ?>
                <!-- Post actions -->
                <div class="flex justify-between text-sm border-t border-gray-400 pt-2">
                  <div class="flex items-center gap-2">
                    <button
                      class="like-btn flex items-center gap-1 hover:scale-110 <?php echo $postController->hasLikedPost($post['id'], $user_id) ? 'text-pink-300' : ''; ?>"
                      data-post-id="<?php echo $post['id']; ?>"
                      data-liked="<?php echo $postController->hasLikedPost($post['id'], $user_id) ? 'true' : 'false'; ?>"
                      type="button">
                      <?php echo $postController->hasLikedPost($post['id'], $user_id) ? '❤️' : '🤍'; ?>
                      <span><?php echo $postController->getLikeCount($post['id']); ?> likes</span>
                    </button>
                  </div>
                  <div class="flex items-center gap-3">
                    <button
                      type="button"
                      onclick="toggleCommentForm('following', <?php echo $post['id']; ?>)"
                      class="flex items-center gap-1 text-gray-200 hover:text-blue-300 transition">
                      <i data-feather="message-square" class="w-4 h-4"></i>
                      Comment
                    </button>
                  </div>
                </div>
                <!-- Comments Section -->
                <div class="mt-4 border-t border-gray-400 pt-2">
                  <div class="comment-form hidden" id="following-comment-form-<?php echo $post['id']; ?>">
                    <form method="POST" action="index.php?page=home" class="flex items-center space-x-2 mb-2">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($session->getCsrfToken(), ENT_QUOTES); ?>">
                      <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                      <input type="text" name="comment_content" placeholder="Add a comment..."
                        class="w-full bg-gray-800 text-white text-sm px-3 py-2 rounded border border-gray-600 focus:outline-none">
                      <button type="submit" name="add_comment"
                        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                        Post
                      </button>
                    </form>
                  </div>
                  <?php
                  $comments = $postController->getComments($post['id']);
                  if (!empty($comments)):
                    foreach ($comments as $comment): ?>
                      <div class="flex items-start space-x-2 mb-1" data-comment-id="<?php echo $comment['id']; ?>">
                        <div class="w-6 h-6 rounded-full overflow-hidden border border-gray-500 flex items-center justify-center bg-black">
                          <?php if (!empty($comment['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($comment['avatar_url']); ?>" class="w-full h-full object-cover">
                          <?php else: ?>
                            <i data-feather="user" class="text-green-400 w-4 h-4"></i>
                          <?php endif; ?>
                        </div>
                        <div class="text-sm flex flex-col w-full">
                          <div class="flex justify-between items-center">
                            <a href="index.php?page=profile&id=<?php echo $comment['user_id']; ?>" class="font-semibold text-green-200 hover:underline">
                              <?php echo htmlspecialchars($comment['username']); ?>
                            </a>
                            <?php
                            $canDelete = false;
                            if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                              $canDelete = true; // admin
                            } elseif ($comment['user_id'] == $user_id) {
                              $canDelete = true; // comment owner
                            } elseif ($post['user_id'] == $user_id) {
                              $canDelete = true; // post owner
                            }
                            ?>
                            <?php if ($canDelete): ?>
                              <div class="flex gap-2 text-xs">
                                <?php if ($comment['user_id'] == $user_id): ?>
                                  <button
                                    type="button"
                                    class="edit-comment-btn bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition"
                                    data-comment-id="<?php echo $comment['id']; ?>">
                                    Edit
                                  </button>
                                <?php endif; ?>
                                <form method="POST" action="index.php?page=home" class="inline">
                                  <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                  <button
                                    type="submit"
                                    name="delete_comment"
                                    class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 transition">
                                    Delete
                                  </button>
                                </form>
                              </div>
                            <?php endif; ?>
                          </div>
                          <p id="following-comment-text-<?php echo $comment['id']; ?>" class="text-gray-300" data-comment-text><?php echo htmlspecialchars($comment['content']); ?></p>
                        </div>
                      </div>
                  <?php endforeach;
                  endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4">
              <p class="text-center text-gray-100">No posts from followed users yet.</p>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>


    <!-- Right Sidebar -->
    <div class="md:col-span-1 md:sticky md:top-8 md:self-start md:h-[calc(100vh-6rem)] md:overflow-y-auto flex flex-col space-y-4">

      <!-- Messenger Header -->
      <div class="bg-gradient-to-b from-blue-600 via-blue-500 to-blue-600 border-2 border-[#7AA0E0] rounded-lg shadow-inner flex flex-col h-full">
        <div class="flex flex-col items-start bg-gradient-to-b from-blue-600 via-blue-500 to-blue-600 text-white px-3 py-2 rounded-t-md">
          <h3 class="font-bold text-sm flex items-center gap-2 matrix-text drop-shadow-[0_0_5px_rgba(0,255,0,0.4)]">
            <img src="./icons/z.webp" alt="Zahoo! Messenger Icon" class="w-20">
            <span class="text-xl -mr-1">Zahoo<span class="text-4xl p-0 m-0 italic">!</span></span><span class="xl:block hidden">Messenger</span>
            <span class="2xl:text-6xl hidden 2xl:block ml-2">📡</span>
          </h3>
          
        </div>

        <!-- Zion Chat -->
        <div class="p-3 border-t border-[#7AA0E0] bg-blue-100 flex flex-col flex-1 min-h-0">
          <?php if (!empty($_SESSION['is_blocked']) && $_SESSION['is_blocked'] == 1): ?>
            <div class="flex-1 flex items-center justify-center text-center">
              <div class="bg-red-200 text-red-800 p-3 rounded border border-red-400 shadow-md animate-pulse w-full max-w-xs mx-auto">
                <p class="font-semibold text-center">🚫 You are blocked and cannot access Zion Messenger.</p>
                <p class="text-xs text-center mt-1 italic">Your chat privileges have been revoked by the system administrator.</p>
              </div>
            </div>
          <?php else: ?>
            <div id="chatMessages"
              class="flex-1 overflow-y-auto bg-blue-200 text-gray-800 rounded border border-[#7AA0E0] p-3 text-sm font-sans shadow-inner mb-2"
              style="word-break: break-word; overflow-wrap: break-word; white-space: normal;">
              <p class="text-gray-400 italic">Connecting to Zion Messenger...</p>
            </div>

            <form id="chatForm" class="flex gap-2 items-center mt-auto bg-blue-100 p-2 border-t border-[#7AA0E0] rounded-b-md">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($session->getCsrfToken(), ENT_QUOTES); ?>">
              <input type="text" id="chatInput" name="message" placeholder="Input data payload..."
                class="flex-1 bg-white text-gray-900 px-3 py-2 rounded border border-[#7AA0E0] focus:outline-none focus:ring-2 focus:ring-blue-400 text-sm placeholder-gray-400">
              <button type="submit"
                class="bg-gradient-to-t from-[#5A8DEE] to-[#7AA0E0] text-white px-4 py-2 rounded font-semibold text-sm shadow hover:brightness-110 active:translate-y-0.5 transition">
                Send
              </button>
            </form>
          <?php endif; ?>
        </div>

        <div class="flex flex-col justify-between items-center text-xs text-gray-600 bg-gradient-to-b from-blue-600 via-blue-500 to-blue-600 px-3 py-2 rounded-b-md">
          <span class="text-gray-100">💫 Feeling nostalgic?</span>
          <span class="italic text-gray-100">Was 1999 the peak of our civilization?</span>
          <span class="text-gray-100">Let's talk about it!</span>
        </div>
      </div>
    </div>

  </div>
</main>

<script src="scripts/quill.js"></script>
<script src="scripts/tab-switcher.js"></script>
<script src="scripts/image-previewer.js"></script>
<script src="scripts/like.js"></script>
<script src="scripts/comment.js"></script>

<script>
  function toggleCommentForm(tab, postId) {
    const form = document.getElementById(`${tab}-comment-form-${postId}`);
    if (form) form.classList.toggle("hidden");
  }
</script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const postId = params.get('id');
    if (!postId) return;

    const scrollToPost = () => {
      const post = document.getElementById(`post-${postId}`);
      if (post) {
        post.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
        post.classList.add('ring', 'ring-blue-900', 'ring-offset-4');
        setTimeout(() => post.classList.remove('ring', 'ring-blue-900', 'ring-offset-4'), 4000);
      } else {
        setTimeout(scrollToPost, 100);
      }
    };

    scrollToPost();
  });
</script>
<script>
  const currentUserId = <?php echo (int)$_SESSION['user_id']; ?>;
  const isAdmin = <?php echo (int)$_SESSION['is_admin']; ?>;
</script>
<script src="scripts/zionchat.js"></script>