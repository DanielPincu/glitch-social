<?php if (!empty($_SESSION['error'])): ?>
    <div style="background-color: #ff0000; color: #fff; padding: 12px; border-radius: 6px; font-weight: bold; text-align: center; margin-bottom: 20px; box-shadow: 0 0 10px rgba(255,0,0,0.6);">
      <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>
<div class="max-w-4xl mx-auto px-4 py-8 relative z-10 text-black h-[1200px]">
    <section class="text-center mb-10">
        <?php if (!empty($profileData['avatar_url'])): ?>
            <img src="<?php echo htmlspecialchars($profileData['avatar_url']); ?>"
                 alt="Profile Avatar"
                 class="w-64 h-64 mx-auto border-4 border-gray-300 object-cover shadow-md">
        <?php else: ?>
            <div class="w-64 h-64 mx-auto flex items-center justify-center rounded-full border-4 border-gray-300 bg-gray-100 shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user text-green-400 w-20 h-20">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
        <?php endif; ?>
        <h2 class="mt-4 text-2xl font-bold text-gray-800">@<?php echo htmlspecialchars($profileData['username']) ?></h2>
        <p class="text-gray-600 italic mt-2"><?php echo htmlspecialchars($profileData['bio'] ?? 'No bio yet.') ?></p>
        <p class="text-gray-700 mt-1"><strong>📍</strong> <?php echo htmlspecialchars($profileData['location'] ?? 'Unknown') ?></p>
        <?php if (!empty($profileData['website'])): ?>
            <p class="text-blue-600 mt-1">
                <strong>🔗</strong>
                <a href="<?php echo htmlspecialchars($profileData['website']) ?>" target="_blank" class="hover:underline">
                    <?php echo htmlspecialchars($profileData['website']) ?>
                </a>
            </p>
        <?php endif; ?>
    </section>

    <?php if (!$canEditProfile): ?>
        <?php
            $viewerId = $session->getUserId();
            $isBlocked = $userController->isUserBlockedByUser($viewerId, $profileData['id']);
            $isFollowing = $controller->isFollowing($session->getUserId(), $profileData['id']);
            $followCounts = $controller->getFollowCounts($profileData['id']);
        ?>
        <div class="mt-4">
            <form method="POST" action="index.php?page=profile&id=<?php echo $profileData['id']; ?>" class="inline-block ml-2">
                <input type="hidden" name="blocked_id" value="<?php echo $profileData['id']; ?>">
                <input type="hidden" name="unfollow_on_block" value="1">
                <button type="submit" name="<?php echo $isBlocked ? 'unblock_user' : 'block_user'; ?>"
                    class="px-4 py-2 font-semibold rounded transition duration-200
                    <?php echo $isBlocked ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'; ?> text-white shadow-md">
                    <?php echo $isBlocked ? 'Unblock' : 'Block'; ?>
                </button>
            </form>

            <?php if (!$isBlocked): ?>
            <form method="POST" action="index.php?page=profile&id=<?php echo $profileData['id']; ?>" class="inline-block ml-2">
                <input type="hidden" name="followed_id" value="<?php echo $profileData['id']; ?>">
                <button type="submit" name="follow_action"
                    class="px-4 py-2 font-semibold rounded transition
                    <?php echo $isFollowing ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'; ?> text-white">
                    <?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?>
                </button>
            </form>
            <?php endif; ?>

            <div class="mt-2 text-gray-700">
                <span class="font-semibold"><?php echo $followCounts['followers']; ?></span> Followers ·
                <span class="font-semibold"><?php echo $followCounts['following']; ?></span> Following
            </div>
        </div>
    <?php else: ?>
        <?php
            $followCounts = $controller->getFollowCounts($profileData['id']);
        ?>
        <div class="mt-2 text-gray-700 text-center">
            <span class="font-semibold"><?php echo $followCounts['followers']; ?></span> Followers ·
            <span class="font-semibold"><?php echo $followCounts['following']; ?></span> Following
        </div>
    <?php endif; ?>

    <?php if ($canEditProfile): ?>
        <div class="flex justify-end items-center mb-6 space-x-3">
            <button 
                id="toggleEdit"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Edit Profile
            </button>
            <a href="index.php?page=settings" 
               class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
               Manage Posts
            </a>
        </div>

        <section id="editSection" class="hidden bg-white rounded-lg shadow-md p-6 mb-10">
            <h3 class="text-lg font-semibold mb-4">Edit Your Profile</h3>
            <form action="index.php?page=profile&id=<?php echo $profileData['id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Bio</label>
                    <textarea name="bio" rows="3" class="w-full border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200"><?php echo htmlspecialchars($profileData['bio'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($profileData['location'] ?? '') ?>" class="w-full border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Website</label>
                    <input type="text" name="website" value="<?php echo htmlspecialchars($profileData['website'] ?? '') ?>" class="w-full border-gray-300 rounded-lg p-2 focus:ring focus:ring-blue-200">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-1">Avatar</label>
                    <input 
                        type="file" 
                        name="avatar" 
                        id="avatarInput" 
                        class="block w-full text-gray-700"
                        onchange="previewImage(event, 'avatar')">
                    <div id="imagePreview-avatar" class="hidden mt-3">
                        <p class="text-gray-600 text-sm mb-1">Preview:</p>
                        <img id="previewImg-avatar" src="" alt="Avatar Preview" class="w-32 h-32 object-cover border-2 border-gray-300 shadow-md">
                        <p id="fileName-avatar" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                    <div class="mt-3">
                        <button 
                            type="submit" 
                            name="delete_avatar" 
                            class="px-4 py-2 bg-red-600 text-white font-semibold rounded hover:bg-red-700 transition"
                            onclick="return confirm('Are you sure you want to delete your avatar? This action cannot be undone.');">
                            Delete Avatar
                        </button>
                        <p class="text-xs text-gray-500 mt-1">This will remove your current avatar and reset it to default.</p>
                    </div>
                </div>
                <button type="submit" name="update" class="px-4 py-2 bg-green-600 text-white font-semibold rounded hover:bg-green-700 transition">
                    Save Changes
                </button>
            </form>
        </section>

        <script>
            document.getElementById('toggleEdit').addEventListener('click', function() {
                const section = document.getElementById('editSection');
                section.classList.toggle('hidden');
                this.textContent = section.classList.contains('hidden') ? 'Edit Profile' : 'Cancel';
            });
        </script>
    <?php endif; ?>

    
    <!-- User Posts Section -->
    <section class="mt-10">
        <h3 class="text-xl font-semibold text-gray-800 mb-6 text-center">Posts by <?php echo htmlspecialchars($profileData['username']); ?></h3>

        <?php if (!empty($posts)): ?>
            <div class="space-y-6">
                <?php foreach ($posts as $post): ?>
                    <div class="bg-white rounded-lg shadow-md p-5">
                        <div class="flex items-center mb-3">
                            <div class="w-10 h-10 rounded-full overflow-hidden mr-3 border-2 border-green-400">
                                <?php if (!empty($post['avatar_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" alt="Avatar" class="object-cover w-full h-full">
                                <?php else: ?>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user text-green-400 w-5 h-5 m-auto">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">@<?php echo htmlspecialchars($post['username']); ?></p>
                                <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($post['created_at']); ?></p>
                            </div>
                            <span class="ml-2 px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">
                                <?php echo ucfirst(htmlspecialchars($post['visibility'])); ?>
                            </span>
                        </div>

                        <p class="text-gray-700 mb-3"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>

                        <?php if (!empty($post['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image" class="rounded-lg max-h-80 w-full object-cover mb-3">
                        <?php endif; ?>

                        

                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-500 italic">This user hasn't posted anything yet.</p>
        <?php endif; ?>
    </section>
<script src="scripts/image-previewer.js"></script>
</div>