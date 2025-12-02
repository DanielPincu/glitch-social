<div class="min-h-screen flex items-center justify-center p-6 z-10 relative">
  <div class="bg-[#008080] rounded-lg shadow-lg w-full max-w-md p-8 border border-gray-400">
    <h1 class="text-4xl font-bold mb-6 select-none">Register</h1>
    <div class="text-sm mb-4 text-red-500 bg-red-100 text-center">
      <?php echo htmlspecialchars($register_error ?? ($message ?? ''), ENT_QUOTES); ?>
    </div>

  
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES); ?>">
      <input
        type="text"
        name="username"
        placeholder="Username"
        required
        value="<?php echo htmlspecialchars($old_username ?? '', ENT_QUOTES); ?>"
        class="text-black w-full px-3 py-2 mb-4 border rounded border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
      <input
        type="email"
        name="email"
        placeholder="Email"
        required
        value="<?php echo htmlspecialchars($old_email ?? '', ENT_QUOTES); ?>"
        class="text-black w-full px-3 py-2 mb-4 border rounded border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
      <div class="relative mb-2">
  <input
    type="password"
    name="password"
    id="password"
    placeholder="Password"
    required
    class="text-black w-full px-3 py-2 border rounded border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
  >
  <button type="button" id="togglePassword" class="absolute right-3 top-2 text-black">
    👁️
  </button>
</div>
<p class="text-white text-xs mb-4">
  Password must be at least 6 characters, include at least one uppercase letter, one symbol, and one number.
</p>
      <div class="relative mb-4">
  <input
    type="password"
    name="confirm_password"
    id="confirm_password"
    placeholder="Confirm Password"
    required
    class="text-black w-full px-3 py-2 border rounded border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
  >
  <button type="button" id="toggleConfirmPassword" class="absolute right-3 top-2 text-black">
    👁️
  </button>
</div>
      <div class="flex items-center mb-4 text-white text-sm">
        <input type="checkbox" id="accept_terms" name="accept_terms" required class="mr-2">
        <label for="accept_terms">
          I have read and agree to the 
          <a href="index.php?page=terms" class="underline hover:text-blue-300">Terms and Regulations</a>.
        </label>
      </div>
      <button
        type="submit"
        name="register"
        class="w-full bg-gradient-to-b from-red-500 to-green-700 text-white font-semibold px-5 py-2 rounded border border-green-800 shadow-md hover:from-green-600 hover:to-green-800 active:translate-y-0.5 active:shadow-none transition duration-150"
      >
        Register
      </button>
    </form>
    <div class="text-center text-white mt-4 text-sm">
      Already have an account?
      <a href="index.php?page=login" class="text-white text-xl hover:underline">Login</a>
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
</div>