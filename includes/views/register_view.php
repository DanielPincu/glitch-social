<div class="min-h-screen flex items-center justify-center p-6">
  <div class="bg-[#008080] rounded-lg shadow-lg w-full max-w-md p-8 border border-gray-400">
    <h1 class="text-4xl font-bold mb-6 select-none">Register</h1>
    <div class="text-sm mb-4"><?php echo $message ?? ''; ?></div>
    <form method="post">
      <input
        type="text"
        name="username"
        placeholder="Username"
        required
        class="text-black w-full px-3 py-2 mb-4 border rounded border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
      <input
        type="email"
        name="email"
        placeholder="Email"
        required
        class="text-black w-full px-3 py-2 mb-4 border rounded border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
      <input
        type="password"
        name="password"
        placeholder="Password"
        required
        class="text-black w-full px-3 py-2 mb-4 border rounded border-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
      >
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
      <a href="login_loader.php" class="text-white text-xl hover:underline">Login</a>
    </div>
  </div>
</div>