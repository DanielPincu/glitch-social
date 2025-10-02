<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MatrixConnect - Social Platform</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <link rel="stylesheet" href="styles/styles.css">
</head>

<body class="matrix-bg font-[Windows Regular] text-white z-10 relative">
  <!-- Matrix Rain Background -->
  <canvas id="matrix-rain"></canvas>
  <!-- Taskbar -->
  <nav class="fixed bottom-0 left-0 right-0 bg-[#0078d7] h-10 flex items-center px-4 z-50">
    <button class="xp-button px-4 py-1 rounded-sm flex items-center gap-1">
      <i data-feather="grid" class="w-4 h-4"></i>
      <span>START</span>
    </button>

    <div class="flex ml-4 space-x-1">
      <?php $page = $_GET['page'] ?? ''; ?>
      <a href="index.php?page=home" class="<?= ($page === 'home' ? 'bg-green-500' : 'bg-gray-200 bg-opacity-20 hover:bg-opacity-30') ?> px-3 py-1 rounded-sm flex items-center">
        <i data-feather="home" class="w-4 h-4"></i>
      </a>
      <a href="index.php?page=settings" class="<?= ($page === 'settings' ? 'bg-green-500' : 'bg-gray-200 bg-opacity-20 hover:bg-opacity-30') ?> px-3 py-1 rounded-sm flex items-center">
        <i data-feather="settings" class="w-4 h-4"></i>
      </a>
      <a href="index.php?page=messages" class="<?= ($page === 'messages' ? 'bg-green-500' : 'bg-gray-200 bg-opacity-20 hover:bg-opacity-30') ?> px-3 py-1 rounded-sm flex items-center">
        <i data-feather="message-square" class="w-4 h-4"></i>
      </a>
      <a href="index.php?page=profile" class="<?= ($page === 'profile' ? 'bg-green-500' : 'bg-gray-200 bg-opacity-20 hover:bg-opacity-30') ?> px-3 py-1 rounded-sm flex items-center">
        <i data-feather="user" class="w-4 h-4"></i>
      </a>
    </div>

    <div class="ml-auto flex items-center space-x-2">
      <div class="bg-green-500 px-2 py-1 text-xs">
        ONLINE
      </div>
      <div class="text-xs">
        <span id="current-time"></span>
      </div>
    </div>
  </nav>

  <!-- START MENU -->
  <div id="start-menu"
    class="hidden fixed bottom-10 left-0 w-64 bg-[#0078d7] border-t-2 border-l-2 border-white border-r-2 border-b-2 border-black shadow-2xl z-50">
    <!-- Profile Info -->
    <div class="bg-[#0064b4] p-3 flex items-center space-x-3 border-b border-black">
      <div class="w-12 h-12 bg-black border-2 border-white flex items-center justify-center">
        <i data-feather="user" class="text-green-500"></i>
      </div>
      <div>
        <h4 class="font-bold">Neo</h4>
        <p class="text-xs">@theone</p>
      </div>
    </div>

    <!-- Menu Items -->
    <div class="bg-[#c0c0c0] text-black text-sm">
      <a href="index.php?page=home" class="block">
        <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
          <i data-feather="home" class="w-4 h-4"></i> Home
        </button>
      </a>
      <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
        <i data-feather="users" class="w-4 h-4"></i> Friends
      </button>
      <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
        <i data-feather="message-square" class="w-4 h-4"></i> Messages
      </button>
      <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
        <i data-feather="user" class="w-4 h-4"></i> Profile
      </button>
      <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
        <a href="index.php?page=settings" class="block">
          <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
            <i data-feather="settings" class="w-4 h-4"></i> Settings
          </button>
        </a>
      <?php else: ?>
        <a href="index.php?page=settings" class="block">
          <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
            <i data-feather="settings" class="w-4 h-4"></i> Settings
          </button>
        </a>
      <?php endif; ?>
      <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2"
        onclick="openLogoutModal()">
        <i data-feather="log-out" class="w-4 h-4"></i> Logout
      </button>
    </div>
  </div>