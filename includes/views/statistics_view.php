<div class="w-full mx-auto mt-10 px-4">

    <h2 class="text-3xl font-semibold mb-8 text-center text-white">Site Statistics</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>

            <!-- Users & Posts Wheel -->
            <div class="bg-gray-800 bg-opacity-40 p-6 rounded-lg shadow-lg backdrop-blur flex flex-col items-center gap-6">

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
            <div class="bg-gray-800 bg-opacity-40 p-6 rounded-lg shadow-lg backdrop-blur flex flex-col items-center gap-6">

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
            <div class="bg-gray-800 bg-opacity-40 p-6 rounded-lg shadow-lg backdrop-blur h-full flex flex-col">
                <h3 class="text-2xl font-semibold text-white mb-6 text-center">Top 3 Most Active Users</h3>
                <p class="text-gray-300 text-sm mb-6 text-center opacity-80">
                    Activity Score: Posts = 2 points, Comments = 1 point, Likes Given = 0.5 point
                </p>

                <div class="flex-1 flex flex-col justify-between">
                <?php foreach ($topUsers as $index => $user): ?>
                <?php
                    $color = $index === 0 ? 'from-blue-300 to-blue-500' :
                             ($index === 1 ? 'from-gray-200 to-gray-400' :
                                             'from-green-300 to-green-500');
                    $title = $index === 0 ? 'Luna Matrix Champion' :
                             ($index === 1 ? 'Silver System Operator' :
                                             'Olive Code Sentinel');
                ?>
                    <a href="index.php?page=profile&id=<?php echo $user['user_id']; ?>" class="block">
                        <div class="relative overflow-hidden rounded-lg mb-4 p-4 bg-gradient-to-r <?php echo $color; ?> shadow-md transition-all duration-300 transform hover:scale-105 hover:-translate-y-1 hover:shadow-2xl">

                            <div class="absolute inset-0 opacity-20 pointer-events-none"></div>

                            <div class="relative flex items-center justify-between">
                                <div>
                                    <span class="text-black text-xl font-semibold flex items-center gap-2">
                                        <span class="text-4xl leading-none">
                                            <?php echo $index === 0 ? "🥇" : ($index === 1 ? "🥈" : "🥉"); ?>
                                        </span>
                                        <?php echo $user['username']; ?>
                                    </span>
                                    <p class="text-black text-sm italic opacity-80"><?php echo $title; ?></p>
                                </div>

                                <p class="text-black text-sm -mr-32 mt-1">Activity Score</p>
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