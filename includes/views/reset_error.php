<div class="min-h-screen flex justify-center items-center bg-gradient-to-br from-blue-900 to-indigo-700 text-white">
  <div class="bg-black bg-opacity-60 p-8 rounded-xl border border-red-500 shadow-lg w-full max-w-md text-center">
    <h2 class="text-2xl font-bold mb-4 text-red-400">Password Reset Failed</h2>
    <p class="text-gray-200 mb-6"><?php echo htmlspecialchars($error ?? 'Something went wrong. Please try again later.'); ?></p>
    <a href="index.php?page=forgot_password" class="inline-block bg-gradient-to-b from-blue-500 to-blue-700 text-white font-semibold px-6 py-2 rounded border border-blue-800 shadow-md hover:from-blue-600 hover:to-blue-800 transition duration-150">
      Try Again
    </a>
  </div>
</div>