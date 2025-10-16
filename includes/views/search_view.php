
<main class="container mx-auto px-4 pb-16 pt-8 relative z-10">
  
  <div class="xp-window bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] p-6 rounded-lg shadow-lg border border-[#b0b0b0]">
    <h2 class="mb-4 text-green-400 italic">Follow the white rabbit... search begins.</h2>
    

    <?php if (!empty($searchQuery)): ?>
      <p class="mb-4 text-gray-200">Results for: <span class="font-bold">"<?php echo htmlspecialchars($searchQuery); ?>"</span></p>
    <?php endif; ?>

    <?php if (!empty($searchResults)): ?>
      <div class="space-y-3">
        <?php foreach ($searchResults as $user): ?>
          <div class="flex items-center gap-3 bg-black bg-opacity-40 p-3 rounded border border-green-400">
            <a href="index.php?page=profile&id=<?php echo $user['id']; ?>">
              <div class="w-12 h-12 bg-black border-2 border-green-500 flex items-center justify-center overflow-hidden rounded-full">
                <?php if (!empty($user['avatar_url'])): ?>
                  <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" 
                       alt="<?php echo htmlspecialchars($user['username']); ?>'s avatar" 
                       class="object-cover w-full h-full">
                <?php else: ?>
                  <i data-feather="user" class="text-green-400"></i>
                <?php endif; ?>
              </div>
            </a>
            <div>
              <a href="index.php?page=profile&id=<?php echo $user['id']; ?>" class="text-green-200 font-semibold hover:underline">
                @<?php echo htmlspecialchars($user['username']); ?>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-gray-300 italic">No users found.</p>
    <?php endif; ?>

    <div class="mt-6">
      <a href="index.php">
        <button type="button" class="bg-gradient-to-b from-blue-500 to-blue-700 text-white font-semibold px-5 py-2 rounded border border-blue-800 shadow-md hover:from-blue-600 hover:to-blue-800 active:translate-y-0.5 active:shadow-none">
          Go Back Home
        </button>
      </a>
      <div id="matrix-container" class="mt-2 bg-black">
          <canvas id="matrix-canvas"></canvas>
      </div>
    </div>
  </div>
</main>
<script src="scripts/matrix-cipher.js"></script>