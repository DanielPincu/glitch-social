<div class="min-h-screen flex items-center justify-center p-6 z-10 relative">
    <div class="bg-[#008080] rounded-lg shadow-lg w-full max-w-md p-8 border border-gray-400">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 select-none">Login</h1>
        <?php if (!empty($login_error)): ?>
            <p class="mb-4 text-red-600 font-semibold"><?php echo htmlspecialchars($login_error, ENT_QUOTES); ?></p>
        <?php endif; ?>
        <form method="post" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES); ?>">
            <input type="text" name="username" placeholder="Username" required class="w-full px-4 py-2 border border-gray-400 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-black">
            <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border border-gray-400 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-black">
            <button type="submit" name="login" class="w-full bg-gradient-to-b from-blue-500 to-blue-700 text-white font-semibold px-5 py-2 rounded border border-blue-800 shadow-md hover:from-blue-600 hover:to-blue-800 active:translate-y-0.5 active:shadow-none transition duration-150">Login</button>
        </form>
        <p class="text-center text-white mt-4 text-sm">Don't have an account? <a href="index.php?page=register" class="text-white text-xl hover:underline">Register</a></p>
        <p class="text-center text-white mt-2 text-sm">
            Forgot your password?
            <a href="index.php?page=forgot_password" class="text-white text-xl hover:underline">Reset it here</a>
        </p>
    </div>
</div>