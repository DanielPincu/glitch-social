<!-- XP Logout Modal -->
<div id="logout-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50">
  <div class="w-96 rounded-lg shadow-xl bg-gradient-to-b from-gray-200 to-gray-400 text-white font-sans">
    <!-- Header Bar -->
    <div class="flex justify-between items-center bg-gradient-to-b from-blue-500 to-blue-700 px-4 py-2 border-b border-blue-800">
      <h2 class="text-lg font-semibold select-none">Turn off computer</h2>
      <button onclick="closeLogoutModal()" aria-label="Close" class="text-white font-bold text-xl leading-none hover:text-gray-300">×</button>
    </div>
    <!-- Buttons Row -->
    <div class="flex justify-center space-x-12 py-8">
      <!-- Stand By -->
      <button onclick="closeLogoutModal()" class="flex flex-col items-center focus:outline-none hover:brightness-90">
        <div class="w-20 h-20 rounded-full bg-gradient-to-b from-yellow-400 to-yellow-500 shadow-inner shadow-yellow-700 flex items-center justify-center cursor-pointer">
          <i data-feather='moon' class="w-10 h-10 text-white drop-shadow"></i>
        </div>
        <span class="mt-2 text-yellow-700 font-bold text-sm font-mono select-none">Stand By</span>
      </button>
      <!-- Turn Off -->
      <button onclick="window.location='index.php?page=logout'" class="flex flex-col items-center focus:outline-none hover:brightness-110">
        <div class="w-20 h-20 rounded-full bg-gradient-to-b from-red-500 to-red-700 shadow-inner shadow-red-900 flex items-center justify-center cursor-pointer">
          <i data-feather='power' class="w-10 h-10 text-white drop-shadow"></i>
        </div>
        <span class="mt-2 text-red-600 font-bold text-sm font-mono select-none">Turn Off</span>
      </button>
      <!-- Restart -->
      <button onclick="alert('Restart not implemented')" class="flex flex-col items-center focus:outline-none hover:brightness-110">
        <div class="w-20 h-20 rounded-full bg-gradient-to-b from-green-500 to-green-700 shadow-inner shadow-green-900 flex items-center justify-center cursor-pointer">
          <i data-feather='refresh-ccw' class="w-10 h-10 text-white drop-shadow"></i>
        </div>
        <span class="mt-2 text-green-600 font-bold text-sm font-mono select-none">Restart</span>
      </button>
    </div>
    <!-- Bottom Buttons -->
    <div class="flex justify-center space-x-4 pb-6">
      <button onclick="window.location='index.php?page=logout'" class="bg-gradient-to-b from-blue-500 to-blue-700 border border-blue-800 text-white font-bold px-6 py-2 rounded shadow-md hover:brightness-90 focus:outline-none select-none">
        Log Off
      </button>
      <button onclick="closeLogoutModal()" class="bg-gradient-to-b from-gray-400 to-gray-600 border border-gray-700 text-gray-900 font-bold px-6 py-2 rounded shadow-md hover:brightness-90 focus:outline-none select-none">
        Cancel
      </button>
    </div>
  </div>
</div>


<script src="scripts/matrix-rain.js"></script>

<?php if (!empty($_SESSION['user_id'])): ?>
  <script src="scripts/logout.js"></script>
  <script src="scripts/taskbar.js"></script>
  <script src="scripts/notifications.js"></script>
<?php endif; ?>


</body>
</html>