<div class="p-6 w-full h-full z-10 relative overflow-y-auto">
    <div class="bg-gradient-to-br from-[#3A6EA5]/70 to-[#5CACEE]/70 rounded-lg w-full px-5 mx-auto border border-gray-400 text-white min-h-[90vh] backdrop-blur-sm">

        <div class="-mx-5 mb-6 border-b-2 border-blue-700 bg-gradient-to-b from-blue-600 via-blue-500 to-blue-600 h-9 flex items-center justify-between px-3 shadow-lg">
            <div class="text-white font-semibold text-sm tracking-wide select-none">
                Statistics.exe
            </div>
            <div class="flex items-center space-x-1 -mx-2">
                <span class="pointer-events-none opacity-60 w-7 h-7 flex items-center justify-center rounded-sm border border-white bg-gradient-to-b from-blue-700 via-blue-500 to-blue-700 text-white text-sm">—</span>
                <span class="pointer-events-none opacity-60 w-7 h-7 flex items-center justify-center rounded-sm border border-white bg-gradient-to-b from-blue-700 via-blue-500 to-blue-700 text-white text-sm">▣</span>
                <a href="index.php"
                   class="w-7 h-7 flex items-center justify-center rounded-sm border border-white bg-gradient-to-b from-red-700 via-red-500 to-red-700 text-white font-bold text-sm hover:scale-110 hover:shadow-[0_0_8px_rgba(255,0,0,0.8)] transition-all duration-150 cursor-pointer">
                  ╳
                </a>
            </div>
        </div>

        <div class="w-full mx-auto mt-10 px-4">

    <h2 class="text-3xl font-semibold mb-10 text-center text-white glitch-text"><span>Sσcιαl Bεнανισυя</span></h2>

    <div class="flex justify-center gap-4 mb-8">
      <a href="index.php?page=terms" class="px-4 py-2 bg-green-700 hover:bg-blue-700 text-white rounded shadow">
        Read more about Rules and Regulations
      </a>
      <a href="index.php?page=about" class="px-4 py-2 bg-blue-700 hover:bg-green-700 text-white rounded shadow">
        Read more about the Website
      </a>
    </div>

        <div id="matrix-container" class="my-[1px] bg-black h-20 rounded-t-lg">
                <canvas id="matrix-canvas"></canvas>
        </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
        <div>

            <!-- Users & Posts Wheel -->
            <div class="bg-gray-800 bg-opacity-40 p-6 rounded-b-lg shadow-lg backdrop-blur flex flex-col items-center gap-6">

                <h3 class="text-xl font-semibold text-white">Users and Posts</h3>

                <!-- WHEEL: TOTAL USERS -->
                <div class="flex flex-col items-center">
                    <div class="relative w-36 h-36">
                        <div class="absolute inset-0 rounded-full border-4 border-gray-700"></div>

                        <div class="absolute inset-3 rounded-full bg-gray-900 flex items-center justify-center">
                            <span class="text-2xl font-bold text-blue-400">
                                <?php echo $usersPosts['total_users']; ?>
                            </span>
                        </div>
                    </div>
                    <p class="text-gray-300 mt-2">Total Users</p>
                </div>

                <!-- WHEEL: TOTAL POSTS -->
                <div class="flex flex-col items-center">
                    <div class="relative w-36 h-36">
                        <div class="absolute inset-0 rounded-full border-4 border-gray-700"></div>


                        <div class="absolute inset-3 rounded-full bg-gray-900 flex items-center justify-center">
                            <span class="text-2xl font-bold text-blue-400">
                                <?php echo $usersPosts['total_posts']; ?>
                            </span>
                        </div>
                    </div>
                    <p class="text-gray-300 mt-2">Total Posts</p>
                </div>

            </div>
        </div>
        <div>
            <!-- Likes & Comments Wheel -->
            <div class="bg-gray-800 bg-opacity-40 p-6 rounded-b-lg shadow-lg backdrop-blur flex flex-col items-center gap-6">

                <h3 class="text-xl font-semibold text-white">Likes and Comments</h3>

                <!-- WHEEL: TOTAL LIKES -->
                <div class="flex flex-col items-center">
                    <div class="relative w-36 h-36">
                        <div class="absolute inset-0 rounded-full border-4 border-gray-700"></div>

                        <div class="absolute inset-3 rounded-full bg-gray-900 flex items-center justify-center">
                            <span class="text-2xl font-bold text-green-400">
                                <?php echo $likesComments['total_likes']; ?>
                            </span>
                        </div>
                    </div>
                    <p class="text-gray-300 mt-2">Total Likes</p>
                </div>

                <!-- WHEEL: TOTAL COMMENTS -->
                <div class="flex flex-col items-center">
                    <div class="relative w-36 h-36">
                        <div class="absolute inset-0 rounded-full border-4 border-gray-700"></div>
                        <div class="absolute inset-3 rounded-full bg-gray-900 flex items-center justify-center">
                            <span class="text-2xl font-bold text-green-400">
                                <?php echo $likesComments['total_comments']; ?>
                            </span>
                        </div>
                    </div>
                    <p class="text-gray-300 mt-2">Total Comments</p>
                </div>

            </div>
        </div>
        <div class="h-full">
            <div class="bg-gray-800 bg-opacity-40 p-6 rounded-b-lg shadow-lg backdrop-blur h-full flex flex-col">
                <h3 class="text-xl font-semibold text-white mb-2 text-center">Top 3 Most Active Users</h3>
                <p class="text-gray-300 text-sm mb-1 text-center opacity-80">
                    Activity Score: Posts = 2 points, Comments = 1 point, Likes Given = 0.5 point
                </p>

                <div class="flex-1 flex flex-col justify-between">
                <?php foreach ($topUsers as $index => $user): ?>
                <?php
                    $color = $index === 0 ? 'from-blue-300 to-blue-500' :
                             ($index === 1 ? 'from-gray-200 to-gray-400' :
                                             'from-green-300 to-green-500');
                    $title = $index === 0 ? 'Ctrl+Z Historian' :
                             ($index === 1 ? 'Alt+F4 Strategist' :
                                             'Shift+Delete Diplomat');
                ?>
                    <a href="index.php?page=profile&id=<?php echo $user['user_id']; ?>" class="block">
                        <div class="relative overflow-hidden rounded-lg mb-4 p-4 bg-gradient-to-r <?php echo $color; ?> shadow-md transition-all duration-300 transform hover:scale-105 hover:-translate-y-1 hover:shadow-2xl">

                            <div class="absolute inset-0 opacity-20 pointer-events-none"></div>

                            <div class="relative flex items-center justify-between">
                                <div class="relative flex items-center gap-3">
    <?php if (!empty($user['avatar_url'])): ?>
        <div class="relative">
            <img src="<?php echo $user['avatar_url']; ?>"
                 alt="avatar"
                 class="w-16 h-16 rounded-full border border-black/20 object-cover shadow-md">
        </div>
    <?php else: ?>
        <div class="relative w-16 h-16 rounded-full border border-black/20 bg-black bg-opacity-40 flex items-center justify-center shadow-md">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
    <?php endif; ?>

    </div>
    <span class="text-5xl drop-shadow ml-2">
        <?php echo $index === 0 ? "🥇" : ($index === 1 ? "🥈" : "🥉"); ?>
    </span>
    <div class="flex flex-col w-32 overflow-hidden">
        <span class="text-black text-xl font-semibold truncate">
            <?php echo $user['username']; ?>
        </span>
        <p class="text-black text-[10px] italic opacity-80 -mt-1 truncate">
            <?php echo $title; ?>
        </p>
    </div>

                                <p class="text-black text-sm mt-1">Activity Score</p>
                                <span class="text-black font-bold text-3xl drop-shadow">
                                    <?php echo $user['activity_score']; ?>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>
</div>
<script src="scripts/matrix-cipher.js"></script>