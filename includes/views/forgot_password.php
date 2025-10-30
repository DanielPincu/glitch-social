<div class="min-h-screen flex justify-center items-center bg-gradient-to-br from-blue-900 to-indigo-700 text-white">
  <div class="bg-black bg-opacity-60 p-8 rounded-xl border border-blue-500 shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-4 text-center">Forgot Password</h2>
    <?php if (!empty($error)): ?>
      <div class="bg-red-600 text-white font-semibold text-center p-3 mb-4 rounded-md">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>
    <form method="POST">
      <input type="email" name="email" placeholder="Enter your email" required
        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
        class="w-full p-3 rounded bg-gray-800 border border-blue-400 text-white mb-4 focus:ring focus:ring-blue-300">
      <button type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded transition">
        Send Reset Link
      </button>
    </form>
    <?php if (!empty($message)): ?>
      <p class="mt-4 text-center text-green-300"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
  </div>
</div>