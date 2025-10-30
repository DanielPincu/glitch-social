<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['password'], $_POST['confirm_password']) || $_POST['password'] !== $_POST['confirm_password']) {
        $error = "Passwords do not match.";
    }
}
?>
<div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-blue-900 to-indigo-700 text-white px-4">
  <div class="bg-black bg-opacity-60 p-8 rounded-xl border border-blue-500 shadow-lg w-full max-w-md">
    <h2 class="text-3xl font-extrabold mb-6 text-center">Reset Password</h2>
    <?php if ($error): ?>
      <div class="bg-red-600 text-white p-3 rounded mb-6 text-center">
        <?php echo htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>
    <form method="POST" class="space-y-6">
      <input type="password" name="password" placeholder="New Password" required
        class="w-full rounded border border-blue-600 bg-transparent px-4 py-3 text-white placeholder-blue-300 focus:border-blue-400 focus:outline-none" />
      <input type="password" name="confirm_password" placeholder="Confirm New Password" required
        class="w-full rounded border border-blue-600 bg-transparent px-4 py-3 text-white placeholder-blue-300 focus:border-blue-400 focus:outline-none" />
      <button type="submit"
        class="w-full rounded bg-blue-600 py-3 text-white font-semibold hover:bg-blue-700 transition">
        Update Password
      </button>
    </form>
  </div>
</div>