<main class="container mx-auto px-4 pb-16 pt-8 relative z-10">
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

    

    <!-- Left Sidebar -->
    <div class="md:col-span-1 md:sticky md:top-8 md:self-start md:h-[calc(100vh-6rem)] md:overflow-y-auto space-y-4 flex flex-col">
      <!-- System Status -->
      <div class="xp-window bg-[#008080] p-4">
        <h3 class="font-bold mb-3">SYSTEM STATUS</h3>
        <div class="space-y-1 text-xs font-mono matrix-text">
          <p>CPU Usage: 37%</p>
          <p>RAM: 1.2GB / 4GB</p>
          <p>Uptime: 12h 42m</p>
          <p>Latency: 42ms</p>
        </div>
      </div>
      <!-- Weather Forecast -->
      <div class="xp-window bg-[#008080] p-4">
        <h3 class="font-bold mb-3">WEATHER STATUS</h3>
        <div class="space-y-1 text-xs font-mono matrix-text">
          <p>Location: Zion Underground</p>
          <p>Condition: Acid Rain ☂</p>
          <p>Temp: 21°C</p>
          <p>Clouds: Digital Haze</p>
          <p>Forecast: System glitch expected...</p>
        </div>
      </div>
      <!-- Daily Quote -->
      <div class="xp-window bg-[#008080] p-4 h-96">
        <h3 class="font-bold mb-3 matrix-text">DAILY QUOTE</h3>
        <blockquote class="italic text-sm matrix-text">“There is no spoon.”</blockquote>
        <p class="text-xs text-right mt-2 matrix-text">- The Matrix</p>
      </div>
      <!-- Glitches / Errors -->
      <div class="xp-window bg-[#008080] matrix-text p-4">
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
        <h3 class="font-bold mb-3 text-green-500">TERMINAL</h3>
        <div class="space-y-1 text-xs font-mono matrix-text">
          <p class="console-line">&gt; boot sequence initiated</p>
          <p>&gt; loading kernel modules...</p>
          <p>&gt; decrypting Zion keys...</p>
          <p>&gt; access granted</p>
        </div>
      </div>
    </div>

    <!-- Main Feed -->
    <div class="md:col-span-2 space-y-4">
      <?php if (!empty($blocked_message)): ?>
        <div class="xp-window bg-[#008080] p-4 mb-4">
          <p class="text-red-400 font-bold text-center"><?php echo htmlspecialchars($blocked_message); ?></p>
        </div>
      <?php else: ?>
        <!-- Create Post -->
        <div class="xp-window bg-[#008080] p-4">
          <form method="post" enctype="multipart/form-data">
            <div class="flex items-center space-x-3 mb-3">
              <div class="w-10 h-10 bg-black border-2 border-white rounded-full flex items-center justify-center">
                <i data-feather="edit-2" class="text-green-500"></i>
              </div>
              <textarea name="content" placeholder="What's on your mind?" required class="bg-[#c0c0c0] text-black px-3 py-2 w-full border border-gray-400 rounded resize-y"></textarea>
            </div>
            <div class="flex justify-between items-center">
              <div class="flex space-x-2">
                <label class="flex items-center text-xs hover:underline cursor-pointer">
                  <i data-feather="image" class="w-4 h-4 mr-1"></i>
                  <span>Image</span>
                  <input type="file" name="imageFile" id="imageFile" onchange="previewImage(event)" class="hidden">
                </label>
              </div>
              <button type="submit" name="post_submit" class="xp-button px-4 py-1">
                Post
              </button>
            </div>
            <!-- Preview Section -->
            <div id="imagePreview" class="mt-3 hidden">
              <p class="text-xs text-green-400 mb-1">Image attached: <span id="fileName"></span></p>
              <img id="previewImg" src="" alt="Preview" class="max-h-40 border border-gray-300">
            </div>
          </form>
        </div>

        <!-- Posts Feed -->
        <?php if (!empty($posts)): ?>
          <?php foreach ($posts as $post): ?>
            <div class="xp-window bg-[#008080] p-4">
              <div class="flex justify-between items-center mb-3">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-black border-2 border-white rounded-full flex items-center justify-center">
                    <i data-feather="user" class="text-green-500"></i>
                  </div>
                  <div>
                    <h4 class="font-bold"><?php echo htmlspecialchars($post['username']); ?></h4>
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
                  <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post image" class="w-full max-h-96 object-contain">
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
                  <span class="flex items-center gap-1 text-gray-200">
                    <i data-feather="message-square" class="w-4 h-4"></i> Comment
                  </span>
                  <span class="flex items-center gap-1 text-gray-200">
                    <i data-feather="share-2" class="w-4 h-4"></i> Share
                  </span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="xp-window bg-[#008080] p-4">
            <p class="text-center text-gray-100">No posts yet.</p>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Right Sidebar -->
    <div class="md:col-span-1 md:sticky md:top-8 md:self-start md:h-[calc(100vh-6rem)] md:overflow-y-auto space-y-4 flex flex-col">
      <div class="flex flex-col h-full justify-between">
        <!--  Following  -->
        <div class="xp-window bg-[#008080] p-4 h-full">
          <h3 class="font-bold mb-3">FOLLOWING</h3>
          <div class="space-y-2">
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-black border-2 border-green-500 rounded-full flex items-center justify-center">
                <i data-feather="user" class="text-green-500 text-xs"></i>
              </div>
              <div>
                <p class="text-sm font-bold">Cypher</p>
                <p class="text-xs">@backstabber</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-black border-2 border-green-500 rounded-full flex items-center justify-center">
                <i data-feather="user" class="text-green-500 text-xs"></i>
              </div>
              <div>
                <p class="text-sm font-bold">Oracle</p>
                <p class="text-xs">@cookie_giver</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-black border-2 border-green-500 rounded-full flex items-center justify-center">
                <i data-feather="user" class="text-green-500 text-xs"></i>
              </div>
              <div>
                <p class="text-sm font-bold">Agent Smith</p>
                <p class="text-xs">@mrbinary</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-black border-2 border-green-500 rounded-full flex items-center justify-center">
                <i data-feather="user" class="text-green-500 text-xs"></i>
              </div>
              <div>
                <p class="text-sm font-bold">Neo</p>
                <p class="text-xs">@theone</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-black border-2 border-green-500 rounded-full flex items-center justify-center">
                <i data-feather="user" class="text-green-500 text-xs"></i>
              </div>
              <div>
                <p class="text-sm font-bold">Trinity</p>
                <p class="text-xs">@trinity</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-black border-2 border-green-500 rounded-full flex items-center justify-center">
                <i data-feather="user" class="text-green-500 text-xs"></i>
              </div>
              <div>
                <p class="text-sm font-bold">Morpheus</p>
                <p class="text-xs">@prophet</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-black border-2 border-green-500 rounded-full flex items-center justify-center">
                <i data-feather="user" class="text-green-500 text-xs"></i>
              </div>
              <div>
                <p class="text-sm font-bold">Niobe</p>
                <p class="text-xs">@pilotqueen</p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <div class="w-8 h-8 bg-black border-2 border-green-500 rounded-full flex items-center justify-center">
                <i data-feather="user" class="text-green-500 text-xs"></i>
              </div>
              <div>
                <p class="text-sm font-bold">The Architect</p>
                <p class="text-xs">@system_master</p>
              </div>
            </div>
          </div>
        </div>
        <!-- System Alerts -->
        <div class="xp-window bg-[#008080] p-4 ">
          <h3 class="font-bold mb-3">SYSTEM ALERTS</h3>
          <div class="space-y-2">
            <div class="bg-black bg-opacity-20 p-2">
              <p class="text-xs matrix-text">Welcome to the Matrix </p>
            </div>
            <div class="bg-black bg-opacity-20 p-2">
              <p class="text-xs matrix-text">Connected as  <?php echo htmlspecialchars($_SESSION['username']); ?> </p>
            </div>
            <div class="bg-black bg-opacity-20 p-2">
              <p class="text-xs matrix-text"><?php echo "Session ID: " . session_id(); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  <script src="scripts/like.js"></script>
  <script src="scripts/image-previewer.js"></script>
</main>