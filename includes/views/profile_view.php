<?php if (!empty($_SESSION['error'])): ?>
    <div class="bg-red-600 text-white font-bold text-center p-3 mb-5 rounded-md">
        <?php echo htmlspecialchars($_SESSION['error']);
        unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="p-6 w-full h-full z-10 relative overflow-y-auto">
    <div class="bg-gradient-to-br from-[#3A6EA5] to-[#5CACEE] rounded-lg w-full max-w-7xl px-5 mx-auto border border-gray-400 space-y-12 text-white min-h-[90vh]">

        <div class="-mx-5 border-b-2 border-blue-700 bg-gradient-to-b from-blue-600 via-blue-500 to-blue-600 h-9 flex items-center justify-between px-3 shadow-lg z-50">
            <div class="text-white font-semibold text-sm tracking-wide select-none">
                Profile.exe
            </div>
            <div class="flex items-center space-x-1 -mx-2">
                <span class="pointer-events-none opacity-60 w-7 h-7 flex items-center justify-center rounded-sm border border-white bg-gradient-to-b from-blue-700 via-blue-500 to-blue-700 text-white text-sm">
                  —
                </span>
                <span class="pointer-events-none opacity-60 w-7 h-7 flex items-center justify-center rounded-sm border border-white bg-gradient-to-b from-blue-700 via-blue-500 to-blue-700 text-white text-sm">
                  ▣
                </span>
                <a href="index.php" 
                   class="w-7 h-7 flex items-center justify-center rounded-sm border border-white bg-gradient-to-b from-red-700 via-red-500 to-red-700 text-white font-bold text-sm hover:scale-110 hover:shadow-[0_0_8px_rgba(255,0,0,0.8)] transition-all duration-150 cursor-pointer">
                  ╳
                </a>
            </div>
        </div>

        <section class="border border-teal-400 rounded-lg p-6 bg-black bg-opacity-60 text-center">
            <section class="text-center mb-10">
                <?php if (!empty($profileData['avatar_url'])): ?>
                    <img src="<?php echo htmlspecialchars($profileData['avatar_url']); ?>"
                        alt="Profile Avatar"
                        class="w-64 h-64 mx-auto border-4 border-teal-400 object-cover shadow-md">
                <?php else: ?>
                    <div class="w-64 h-64 mx-auto flex items-center justify-center rounded-full border-4 border-teal-400 bg-black bg-opacity-50 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user text-green-400 w-20 h-20">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                <?php endif; ?>
                <h2 class="mt-4 text-3xl font-bold text-teal-300 mb-2 border-b border-teal-400 inline-block pb-1">@<?php echo htmlspecialchars($profileData['username']) ?></h2>
                <p class="text-gray-200 italic mt-2"><?php echo htmlspecialchars($profileData['bio'] ?? 'No bio yet.') ?></p>
                <p class="text-gray-200 mt-1"><strong class="text-green-400">📍</strong> <?php echo htmlspecialchars($profileData['location'] ?? 'Unknown') ?></p>
                <?php if (!empty($profileData['website'])): ?>
                    <p class="text-green-400 mt-1">
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
                            class="<?php echo $isBlocked ? 'px-4 py-2 rounded border bg-teal-700 border-teal-900 text-white font-semibold hover:bg-teal-800 active:bg-teal-900 transition' : 'px-4 py-2 rounded border bg-red-700 border-red-900 text-white font-semibold hover:bg-red-800 active:bg-red-900 transition'; ?>">
                            <?php echo $isBlocked ? 'Unblock' : 'Block'; ?>
                        </button>
                    </form>

                    <?php if (!$isBlocked): ?>
                        <form method="POST" action="index.php?page=profile&id=<?php echo $profileData['id']; ?>" class="inline-block ml-2">
                            <input type="hidden" name="followed_id" value="<?php echo $profileData['id']; ?>">
                            <button type="submit" name="follow_action"
                                class="px-4 py-2 rounded border bg-teal-700 border-teal-900 text-white font-semibold hover:bg-teal-800 active:bg-teal-900 transition">
                                <?php echo $isFollowing ? 'Unfollow' : 'Follow'; ?>
                            </button>
                        </form>
                    <?php endif; ?>

                    <div class="mt-2 text-gray-200">
                        <span class="font-semibold"><?php echo $followCounts['followers']; ?></span> Followers ·
                        <span class="font-semibold"><?php echo $followCounts['following']; ?></span> Following
                    </div>
                </div>
            <?php else: ?>
                <?php
                $followCounts = $controller->getFollowCounts($profileData['id']);
                ?>
                <div class="mt-2 text-gray-200 text-center">
                    <span class="font-semibold"><?php echo $followCounts['followers']; ?></span> Followers ·
                    <span class="font-semibold"><?php echo $followCounts['following']; ?></span> Following
                </div>
            <?php endif; ?>

            <?php if ($canEditProfile): ?>
                <div class="flex justify-end items-center mb-6 space-x-3">
                    <button
                        id="toggleEdit"
                        class="px-4 py-2 rounded border bg-teal-700 border-teal-900 text-white font-semibold hover:bg-teal-800 active:bg-teal-900 transition">
                        Edit Profile
                    </button>
                    <a href="index.php?page=settings"
                        class="px-4 py-2 rounded border bg-teal-700 border-teal-900 text-white font-semibold hover:bg-teal-800 active:bg-teal-900 transition">
                        Manage Posts
                    </a>
                </div>

                <section id="editSection" class="hidden bg-black bg-opacity-50 rounded-lg shadow-md p-6 mb-10 border border-teal-400">
                    <h3 class="text-lg font-semibold mb-4 text-teal-300">Edit Your Profile</h3>
                    <form action="index.php?page=profile&id=<?php echo $profileData['id'] ?>" method="POST" enctype="multipart/form-data" class="space-y-4 text-gray-200">
                        <div>
                            <label class="block text-gray-200 font-medium mb-1">Bio</label>
                            <textarea name="bio" rows="3" class="w-full border border-teal-400 rounded-lg p-2 bg-black bg-opacity-70 focus:ring focus:ring-teal-400"><?php echo htmlspecialchars($profileData['bio'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label class="block text-gray-200 font-medium mb-1">Location</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($profileData['location'] ?? '') ?>" class="w-full border border-teal-400 rounded-lg p-2 bg-black bg-opacity-70 focus:ring focus:ring-teal-400">
                        </div>
                        <div>
                            <label class="block text-gray-200 font-medium mb-1">Website</label>
                            <input type="text" name="website" value="<?php echo htmlspecialchars($profileData['website'] ?? '') ?>" class="w-full border border-teal-400 rounded-lg p-2 bg-black bg-opacity-70 focus:ring focus:ring-teal-400">
                        </div>
                        <div>
                            <label class="block text-gray-200 font-medium mb-1">Avatar</label>
                            <input
                                type="file"
                                name="avatar"
                                id="avatarInput"
                                class="block w-full text-gray-200 bg-black bg-opacity-50 border border-teal-400 rounded"
                                onchange="previewImage(event, 'avatar')">
                            <div id="imagePreview-avatar" class="hidden mt-3">
                                <p class="text-gray-400 text-sm mb-1">Preview:</p>
                                <img id="previewImg-avatar" src="" alt="Avatar Preview" class="w-32 h-32 object-cover border-2 border-teal-400 shadow-md">
                                <p id="fileName-avatar" class="text-xs text-gray-500 mt-1"></p>
                            </div>
                            <div class="mt-3">
                                <button
                                    type="submit"
                                    name="delete_avatar"
                                    class="px-4 py-2 rounded border bg-red-700 border-red-900 text-white font-semibold hover:bg-red-800 active:bg-red-900 transition"
                                    onclick="return confirm('Are you sure you want to delete your avatar? This action cannot be undone.');">
                                    Delete Avatar
                                </button>
                                <p class="text-xs text-gray-500 mt-1">This will remove your current avatar and reset it to default.</p>
                            </div>
                        </div>
                        <button type="submit" name="update" class="px-4 py-2 rounded border bg-teal-700 border-teal-900 text-white font-semibold hover:bg-teal-800 active:bg-teal-900 transition">
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
        </section>


        <!-- User Posts Section -->
        <section class="border border-cyan-400 rounded-lg p-6 bg-black bg-opacity-60 mt-10">
            <h3 class="text-2xl font-bold text-cyan-300 mb-4 border-b border-cyan-400 pb-2 text-center">Posts by <?php echo htmlspecialchars($profileData['username']); ?></h3>

            <?php if (!empty($posts)): ?>
                <div class="space-y-6">
                    <?php foreach ($posts as $post): ?>
                        <div class="bg-black bg-opacity-70 border border-cyan-500 rounded-lg p-4">
                            <div class="flex items-center mb-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden mr-3 border-2 border-green-400">
                                    <?php if (!empty($post['avatar_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['avatar_url']); ?>" alt="Avatar" class="object-cover w-full h-full">
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user text-green-400 w-5 h-5 m-auto">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-semibold text-cyan-300">@<?php echo htmlspecialchars($post['username']); ?></p>
                                    <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($post['created_at']); ?></p>
                                </div>
                                <span class="ml-2 px-2 py-1 text-xs rounded bg-gray-800 text-cyan-300">
                                    <?php echo ucfirst(htmlspecialchars($post['visibility'])); ?>
                                </span>
                            </div>

                            <a href="index.php?page=post&id=<?php echo $post['id']; ?>" class="text-cyan-300 hover:underline block mb-3">
                                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                            </a>

                            <?php if (!empty($post['image_path'])): ?>
                                <a href="index.php?page=post&id=<?php echo $post['id']; ?>" class="block mb-3">
                                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image" class="rounded-lg max-h-80 w-full object-cover border border-cyan-500 hover:opacity-90 transition">
                                </a>
                            <?php endif; ?>


                            <?php if ($canEditProfile): ?>
                                <form method="POST" action="index.php?page=profile&id=<?php echo $profileData['id']; ?>" class="mt-3 text-right">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="is_pinned" value="<?php echo $post['is_pinned'] ? 0 : 1; ?>">
                                    <button
                                        type="submit"
                                        name="toggle_pin"
                                        class="px-3 py-1 rounded border text-sm font-semibold transition
                                            <?php echo $post['is_pinned']
                                                ? 'bg-yellow-600 border-yellow-800 text-white hover:bg-yellow-700 active:bg-yellow-800'
                                                : 'bg-teal-700 border-teal-900 text-white hover:bg-teal-800 active:bg-teal-900'; ?>">
                                        <?php echo $post['is_pinned'] ? 'Unpin' : 'Pin'; ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-400 italic">This user hasn't posted anything yet.</p>
            <?php endif; ?>
        </section>
    </div>
</div>
<script src="scripts/image-previewer.js"></script>