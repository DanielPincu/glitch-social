<main class="container mx-auto px-4 pb-16 pt-8 relative z-10">
  <?php if (!empty($_SESSION['upload_error'])): ?>
    <div class="bg-red-600 text-white text-center font-bold rounded-lg p-3 mb-5 shadow-lg shadow-red-500/60">
      <?php echo $_SESSION['upload_error']; unset($_SESSION['upload_error']); ?>
    </div>
  <?php endif; ?>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

    

    <!-- Left Sidebar -->
    <div class="md:col-span-1 md:sticky md:top-8 md:self-start md:h-[calc(100vh-6rem)] md:overflow-y-auto space-y-1 flex flex-col">
      <!-- System Status -->
      <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4">
        <h3 class="font-bold mb-3">SYSTEM STATUS</h3>
        <div class="space-y-1 text-xs font-mono matrix-text">
          <p>CPU Usage: 37%</p>
          <p>RAM: 1.2GB / 4GB</p>
          <p>Uptime: 12h 42m</p>
          <p>Latency: 42ms</p>
        </div>
      </div>
      <!-- Weather Forecast -->
      <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4">
        <h3 class="font-bold mb-0">WEATHER STATUS</h3>
        <div class="space-y-1 text-xs font-mono matrix-text">
          <p>Location: Zion Underground</p>
          <p>Condition: Acid Rain ☂</p>
          <p>Temp: 21°C</p>
          <p>Clouds: Digital Haze</p>
          <p>Forecast: System glitch expected...</p>
        </div>
      </div>
      <!-- Daily Quote -->
      <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4 h-96">
        <h3 class="font-bold mb-0 matrix-text">DAILY QUOTE</h3>
        <blockquote class="italic text-sm matrix-text">“There is no spoon.”</blockquote>
        <p class="text-xs text-right mt-2 matrix-text">- The Matrix</p>
      </div>
      <!-- Glitches / Errors -->
      <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] matrix-text p-4">
        <h3 class="font-bold mb-3 matrix-text">GLITCHES / ERRORS</h3>
        <div class="space-y-1 text-xs font-mono  matrix-text">
          <p>[ERROR] Memory leak detected</p>
          <p>[WARN] Connection unstable</p>
          <p>[FAIL] Render pipeline crash</p>
          <p>[INFO] Retrying...</p>
        </div>
      </div>
      <!-- Terminal Console -->
      <div class="xp-window bg-[#000] p-4">
        <div class="space-y-1 text-xs font-mono matrix-text">
          <p class="console-line">&gt; boot sequence initiated</p>
          <p>&gt; decrypting Zion keys...</p>
          <p>&gt; access granted</p>
        </div>
      </div>
    </div>

    <!-- Main Feed -->
    <div class="md:col-span-2 space-y-4">
      <?php if (!empty($blocked_message)): ?>
        <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4 mb-4">
          <p class="text-red-400 font-bold text-center"><?php echo htmlspecialchars($blocked_message); ?></p>
        </div>
      <?php else: ?>
        <!-- Create Post -->
        <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] rounded-lg shadow-lg p-6 border border-[#b0b0b0] relative">
          <div class="flex items-center mb-4 space-x-2">
            <i data-feather="edit-2" class="text-green-400 drop-shadow-[0_0_3px_rgba(0,255,0,0.7)]"></i>
            <h3 class="text-lg font-semibold text-green-400 drop-shadow-[0_0_5px_rgba(0,255,0,0.8)]">Create Post</h3>
          </div>
          <form method="post" enctype="multipart/form-data">
            <textarea name="content" placeholder="What's the truth, Neo?" required
              class="w-full bg-[#e8e8e8] text-black px-5 py-4 rounded border border-[#b0b0b0] resize-y focus:outline-none focus:ring-2 focus:ring-green-500 focus:shadow-[0_0_8px_rgba(0,255,0,0.7)] transition-shadow"></textarea>
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
            <!-- Preview Section -->
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
              <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4">
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
                <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <?php if (!empty($post['image_path'])): ?>
                  <div class="mb-3 border-2 border-white">
                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image" class="w-full max-h-96 object-cover object-center">
                  </div>
                <?php endif; ?>
                <!-- Post actions -->
                <div class="flex justify-between text-sm border-t border-gray-400 pt-2">
                  <div class="flex items-center gap-2">
                    <button 
                      class="like-btn flex items-center gap-1 hover:scale-110 <?php echo $postController->hasLikedPost($post['id'], $user_id) ? 'text-pink-300' : ''; ?>" 
                      data-post-id="<?php echo $post['id']; ?>" 
                      data-liked="<?php echo $postController->hasLikedPost($post['id'], $user_id) ? 'true' : 'false'; ?>"
                      type="button"
                    >
                      <?php echo $postController->hasLikedPost($post['id'], $user_id) ? '❤️' : '🤍'; ?>
                      <span><?php echo $postController->getLikeCount($post['id']); ?> likes</span>
                    </button>
                  </div>
                  <!-- Placeholder for comments/share -->
                  <div class="flex items-center gap-3">
                    <button 
                      type="button" 
                      onclick="toggleCommentForm('hot', <?php echo $post['id']; ?>)" 
                      class="flex items-center gap-1 text-gray-200 hover:text-blue-300 transition"
                    >
                      <i data-feather="message-square" class="w-4 h-4"></i>
                      Comment
                    </button>
                  </div>
                </div>
                <!-- Comments Section -->
                <div class="mt-4 border-t border-gray-400 pt-2">
                  <div class="comment-form hidden" id="hot-comment-form-<?php echo $post['id']; ?>">
                    <form method="POST" class="flex items-center space-x-2 mb-2">
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
                          <div class="w-6 h-6 rounded-full overflow-hidden border border-gray-500">
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
                              <?php if ($comment['username'] === $_SESSION['username']): ?>
                                <div class="flex gap-2 text-xs">
                                  <button 
                                    type="button" 
                                    class="edit-comment-btn bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition"
                                    data-comment-id="<?php echo $comment['id']; ?>"
                                  >
                                    Edit
                                  </button>
                                  <form method="POST" class="inline">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <button 
                                      type="submit" 
                                      name="delete_comment" 
                                      class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 transition"
                                    >
                                      Delete
                                    </button>
                                  </form>
                                </div>
                              <?php endif; ?>
                            </div>
                            <p id="hot-comment-text-<?php echo $comment['id']; ?>" class="text-gray-300" data-comment-text><?php echo htmlspecialchars($comment['content']); ?></p>
                          </div>
                        </div>
                  <?php endforeach; endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4">
              <p class="text-center text-gray-100">No posts yet.</p>
            </div>
          <?php endif; ?>
        </div>

        <div id="followingFeed" class="hidden h-full">
          <?php if (!empty($followingPosts)): ?>
            <?php foreach ($followingPosts as $post): ?>
              <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4">
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
                <p class="mb-3"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                <?php if (!empty($post['image_path'])): ?>
                  <div class="mb-3 border-2 border-white">
                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image" class="w-full max-h-96 object-cover object-center">
                  </div>
                <?php endif; ?>
                <!-- Post actions -->
                <div class="flex justify-between text-sm border-t border-gray-400 pt-2">
                  <div class="flex items-center gap-2">
                    <button 
                      class="like-btn flex items-center gap-1 hover:scale-110 <?php echo $postController->hasLikedPost($post['id'], $user_id) ? 'text-pink-300' : ''; ?>" 
                      data-post-id="<?php echo $post['id']; ?>" 
                      data-liked="<?php echo $postController->hasLikedPost($post['id'], $user_id) ? 'true' : 'false'; ?>"
                      type="button"
                    >
                      <?php echo $postController->hasLikedPost($post['id'], $user_id) ? '❤️' : '🤍'; ?>
                      <span><?php echo $postController->getLikeCount($post['id']); ?> likes</span>
                    </button>
                  </div>
                  <div class="flex items-center gap-3">
                    <button 
                      type="button" 
                      onclick="toggleCommentForm('following', <?php echo $post['id']; ?>)" 
                      class="flex items-center gap-1 text-gray-200 hover:text-blue-300 transition"
                    >
                      <i data-feather="message-square" class="w-4 h-4"></i>
                      Comment
                    </button>
                  </div>
                </div>
                <!-- Comments Section -->
                <div class="mt-4 border-t border-gray-400 pt-2">
                  <div class="comment-form hidden" id="following-comment-form-<?php echo $post['id']; ?>">
                    <form method="POST" class="flex items-center space-x-2 mb-2">
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
                          <div class="w-6 h-6 rounded-full overflow-hidden border border-gray-500">
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
                              <?php if ($comment['username'] === $_SESSION['username']): ?>
                                <div class="flex gap-2 text-xs">
                                  <button 
                                    type="button" 
                                    class="edit-comment-btn bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition"
                                    data-comment-id="<?php echo $comment['id']; ?>"
                                  >
                                    Edit
                                  </button>
                                  <form method="POST" class="inline">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <button 
                                      type="submit" 
                                      name="delete_comment" 
                                      class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 transition"
                                    >
                                      Delete
                                    </button>
                                  </form>
                                </div>
                              <?php endif; ?>
                            </div>
                            <p id="following-comment-text-<?php echo $comment['id']; ?>" class="text-gray-300" data-comment-text><?php echo htmlspecialchars($comment['content']); ?></p>
                          </div>
                        </div>
                  <?php endforeach; endif; ?>
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
    <div class="md:col-span-1 md:sticky md:top-8 md:self-start md:h-[calc(100vh-6rem)] md:overflow-y-auto space-y-4 flex flex-col">
      <div class="flex flex-col h-full justify-between">
        <!--  Following  -->
        <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4 h-full">
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
        <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-4 ">
          <h3 class="font-bold mb-3">SYSTEM ALERTS</h3>
          <div class="space-y-2">
            <div class="bg-black bg-opacity-20 p-2">
              <p class="text-xs matrix-text">Welcome to the Matrix </p>
            </div>
            <div class="bg-black bg-opacity-20 p-2">
              <p class="text-xs matrix-text">
                Connected as <?php echo htmlspecialchars($_SESSION['username']); ?> 
                (Role: <?php echo !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] ? 'Admin' : 'User'; ?>)
              </p>
            </div>
            <div class="bg-black bg-opacity-20 p-2">
              <p class="text-xs matrix-text"><?php echo "Session ID: " . session_id(); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</main>
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