<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glιτch Sσcιαl</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <script>var TENOR_API_KEY = "<?php echo getenv('TENOR_API_KEY'); ?>";</script>
  <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
  <link rel="stylesheet" href="styles/styles.css">
  <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>

<body class="matrix-bg font-[Windows Regular] text-white z-10 relative">
  <!-- Matrix Rain Background -->
  <canvas id="matrix-rain"></canvas>
  <?php if (!empty($_SESSION['user_id'])) { ?>
  <!-- Taskbar -->
  <nav class="fixed bottom-0 left-0 right-0 bg-gradient-to-b from-blue-600 via-blue-500 to-blue-600 h-10 flex items-center px-4 z-50">
    <button id="start-button" class="xp-button px-4 py-1 rounded-sm flex items-center gap-1 bg-gradient-to-b from-[#6aa8f7] to-[#3b6db3] border border-[#3a6ea5] shadow-[inset_1px_1px_0px_#ffffff,inset_-1px_-1px_0px_#2d4c75] active:translate-y-[2px] active:shadow-inner active:bg-blue-700">
      <i data-feather="grid" class="w-4 h-4"></i>
      <span>START</span>
    </button>

    <div class="flex ml-4 space-x-1">
      <?php $page = $_GET['page'] ?? ''; ?>
      <a href="index.php?page=home" class="px-10 py-2 min-w-[140px] text-sm flex items-center justify-center rounded-sm border border-[#3a6ea5] shadow-[inset_1px_1px_0px_#ffffff,inset_-1px_-1px_0px_#2d4c75] active:translate-y-[2px] active:shadow-inner active:bg-blue-700 <?php echo ($page === 'home' || $page === '' ? 'bg-gradient-to-b from-[#5e9af5] to-[#2f5fa3]' : 'bg-gradient-to-b from-[#aecdff] to-[#6693d1] hover:from-[#9bc2ff] hover:to-[#5a86c4]'); ?>">
        <i data-feather="home" class="w-4 h-4"></i>
      </a>
      <a href="index.php?page=settings" class="px-10 py-2 min-w-[140px] text-sm flex items-center justify-center rounded-sm border border-[#3a6ea5] shadow-[inset_1px_1px_0px_#ffffff,inset_-1px_-1px_0px_#2d4c75] active:translate-y-[2px] active:shadow-inner active:bg-blue-700 <?php echo ($page === 'settings' ? 'bg-gradient-to-b from-[#5e9af5] to-[#2f5fa3]' : 'bg-gradient-to-b from-[#aecdff] to-[#6693d1] hover:from-[#9bc2ff] hover:to-[#5a86c4]'); ?>">
        <i data-feather="settings" class="w-4 h-4"></i>
      </a>
      <a href="index.php?page=profile" class="px-10 py-2 min-w-[140px] text-sm flex items-center justify-center rounded-sm border border-[#3a6ea5] shadow-[inset_1px_1px_0px_#ffffff,inset_-1px_-1px_0px_#2d4c75] active:translate-y-[2px] active:shadow-inner active:bg-blue-700 <?php echo ($page === 'profile' ? 'bg-gradient-to-b from-[#5e9af5] to-[#2f5fa3]' : 'bg-gradient-to-b from-[#aecdff] to-[#6693d1] hover:from-[#9bc2ff] hover:to-[#5a86c4]'); ?>">
        <i data-feather="user" class="w-4 h-4"></i>
      </a>
      <a href="index.php?page=statistics" class="px-10 py-2 min-w-[140px] text-sm flex items-center justify-center rounded-sm border border-[#3a6ea5] shadow-[inset_1px_1px_0px_#ffffff,inset_-1px_-1px_0px_#2d4c75] active:translate-y-[2px] active:shadow-inner active:bg-blue-700 <?php echo ($page === 'statistics' ? 'bg-gradient-to-b from-[#5e9af5] to-[#2f5fa3]' : 'bg-gradient-to-b from-[#aecdff] to-[#6693d1] hover:from-[#9bc2ff] hover:to-[#5a86c4]'); ?>">
        <i data-feather="bar-chart-2" class="w-4 h-4"></i>
      </a>
    </div>

    <!-- Notification Bell moved to taskbar right side -->

    <div class="ml-auto flex items-center space-x-2">
      <div class="bg-green-500 px-2 py-1 text-xs">
        ONLINE
      </div>
      <div class="relative">
        <button id="notif-button" class="bg-gray-200 bg-opacity-20 hover:bg-opacity-30 px-3 py-1 rounded-sm flex items-center relative active:translate-y-[2px] active:shadow-inner active:bg-blue-700">
          <i data-feather="bell" class="w-4 h-4"></i>
          <?php
            $userId = isset($session) && method_exists($session, 'getUserId') ? $session->getUserId() : ($_SESSION['user_id'] ?? null);
            $notificationController = new NotificationController($pdo);
            $notifCount = $userId ? $notificationController->countUnreadNotifications($userId) : 0;
            if ($notifCount > 0): ?>
              <span class="absolute -top-2 -right-2 bg-red-600 text-white text-sm rounded-full px-2 py-[1px] font-bold"><?php echo $notifCount; ?></span>
          <?php endif; ?>
        </button>

        <!-- Notification Dropdown -->
        <div id="notif-dropdown" class="hidden absolute bottom-10 right-0 w-64 bg-[#222] border border-gray-600 rounded shadow-lg z-50">
<?php
  $userId = isset($session) && method_exists($session, 'getUserId') ? $session->getUserId() : ($_SESSION['user_id'] ?? null);
  $notifs = $userId ? $notificationController->getRecentNotifications($userId) : [];
?>
<div class="p-2 text-sm text-gray-300 flex justify-between items-center">
  <strong>Notifications</strong>
  <?php if (!empty($notifs)): ?>
    <button id="delete-all-notifications" class="text-xs text-red-400 hover:underline p-2">
      Delete all
    </button>
  <?php endif; ?>
</div>

<div class="max-h-64 overflow-y-auto">
  <?php if (empty($notifs)): ?>
    <div class="p-3 text-xs text-gray-400 text-center">
      💤 No notifications yet — you're all caught up!
    </div>
  <?php else: ?>
    <?php foreach ($notifs as $n): ?>
      <div class="p-2 border-b border-gray-700 text-xs notification-item"
           data-id="<?php echo $n['id']; ?>">
        <a
          href="index.php?page=profile&amp;id=<?php echo urlencode($n['actor_id']); ?>"
          class="text-green-400 hover:underline"
        ><?php echo htmlspecialchars($n['actor_name']); ?></a>
        <?php if ($n['type'] === 'post'): ?>
          <a href="index.php?page=home#post-<?php echo $n['post_id']; ?>" class="text-blue-400 hover:underline">
            posted something new.
          </a>
        <?php elseif ($n['type'] === 'follow'): ?>
          started following you.
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
        </div>
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
    <?php
      // Use existing Session/Profile objects if available
      if (!isset($session)) {
        $session = new Session();
      }

      if (!isset($profile)) {
        $profile = new Profile($pdo);
      }

      // Get current user info only if logged in
      $user = null;
      if ($session->isLoggedIn()) {
        $user_id = $session->getUserId();
        $user = $profile->getByUserId($user_id);
      }
    ?>

    <div class="bg-gradient-to-b from-blue-600 via-blue-500 to-blue-600 p-3 flex items-center space-x-3 border-b border-black">
      <div class="w-12 h-12 bg-black border-2 border-white flex items-center justify-center overflow-hidden">
        <?php if (!empty($user['avatar_url'])): ?>
          <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Avatar" class="w-full h-full object-cover">
        <?php else: ?>
          <i data-feather="user" class="text-green-500"></i>
        <?php endif; ?>
      </div>
      <div>
        <h4 class="font-bold"><?php echo htmlspecialchars($user['username']); ?></h4>
        <p class="text-xs">@<?php echo htmlspecialchars($user['username']); ?></p>
      </div>
    </div>

    <!-- Menu Items -->
    <div class="bg-[#c0c0c0] text-black text-sm">
      <a href="index.php?page=home" class="block">
        <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
          <i data-feather="home" class="w-4 h-4"></i> Home
        </button>
      </a>
      <!-- <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
        <i data-feather="users" class="w-4 h-4"></i> Friends
      </button> -->
      <!-- <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
        <i data-feather="message-square" class="w-4 h-4"></i> Messages
      </button> -->
      <a href="index.php?page=profile" class="block">
        <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
          <i data-feather="user" class="w-4 h-4"></i> Profile
        </button>
      </a>
      <a href="index.php?page=statistics" class="block">
        <button class="w-full text-left px-4 py-2 hover:bg-[#0078d7] hover:text-white flex items-center gap-2">
          <i data-feather="bar-chart-2" class="w-4 h-4"></i> Statistics
        </button>
      </a>
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
  <?php } ?>