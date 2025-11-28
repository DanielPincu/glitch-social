<div class="min-h-screen flex justify-center items-center bg-gradient-to-br from-blue-900 to-indigo-700 text-white">
  <div class="bg-black bg-opacity-60 p-8 rounded-xl border border-blue-500 shadow-lg w-full max-w-2xl">
    <h2 class="text-3xl font-bold mb-6 text-center">Terms and Regulations</h2>
    <div class="bg-gray-800 p-4 rounded-lg h-96 overflow-y-auto text-sm leading-relaxed mb-6 border border-blue-400">
      <?php if (!empty($terms['content'])): ?>
        <div class="whitespace-pre-line"><?php echo nl2br($terms['content']); ?></div>
      <?php else: ?>
        <p class="text-gray-400 italic">No terms and regulations available at the moment.</p>
      <?php endif; ?>
    </div>
    <div class="text-center">
      <a href="javascript: if (history.length > 1) { history.back(); } else { window.location = 'index.php'; }" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded transition duration-200">
        I have read the terms and conditions
      </a>
    </div>
  </div>
</div>