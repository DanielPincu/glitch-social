<div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-blue-900 to-indigo-700 text-white px-4">
  <div class="bg-black bg-opacity-60 p-8 rounded-xl border border-blue-500 shadow-lg w-full max-w-md">
    <h2 class="text-3xl font-extrabold mb-6 text-center">Reset Password</h2>
    <?php if ($error): ?>
      <div class="bg-red-600 text-white p-3 rounded mb-6 text-center">
        <?php echo htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="POST" class="space-y-6">
      <div class="relative">
        <input type="password" name="password" id="password" placeholder="New Password" required
          class="w-full rounded border border-blue-600 bg-transparent px-4 py-3 text-white placeholder-blue-300 focus:border-blue-400 focus:outline-none" />
        <button type="button" id="togglePassword" class="absolute right-3 top-3 text-white">
          👁️
        </button>
      </div>
      <p class="text-white text-xs">
        Password must be at least 6 characters, include at least one uppercase letter, one symbol, and one number.
      </p>
      <div class="relative">
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required
          class="w-full rounded border border-blue-600 bg-transparent px-4 py-3 text-white placeholder-blue-300 focus:border-blue-400 focus:outline-none" />
        <button type="button" id="toggleConfirmPassword" class="absolute right-3 top-3 text-white">
          👁️
        </button>
      </div>
      <button type="submit"
        class="w-full rounded bg-blue-600 py-3 text-white font-semibold hover:bg-blue-700 transition">
        Update Password
      </button>
    </form>
  </div>
    <script>
      document.getElementById('togglePassword').addEventListener('click', function () {
          const field = document.getElementById('password');
          field.type = field.type === 'password' ? 'text' : 'password';
      });
      document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
          const field = document.getElementById('confirm_password');
          field.type = field.type === 'password' ? 'text' : 'password';
      });
      </script>
</div>