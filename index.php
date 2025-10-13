<?php
require_once __DIR__ . '/includes/controllers/UserController.php';
require_once __DIR__ . '/includes/controllers/PostController.php';
require_once __DIR__ . '/includes/controllers/AdminController.php';
require_once __DIR__ . '/includes/controllers/ProfileController.php';
require_once __DIR__ . '/includes/helpers/Session.php';

// Central Routing System
$session = new Session();
$userController = new UserController();
$postController = new PostController();

// Handle AJAX like/unlike functionality (HTMX compatible)
if (isset($_POST['ajax']) && $_POST['ajax'] === 'like') {
    if (!$session->isLoggedIn()) {
        echo '<button class="text-red-400">Login required</button>';
        exit;
    }

    $user_id = $session->getUserId();
    if ($userController->isBlocked($user_id)) {
        echo '<button class="text-yellow-400">Blocked user</button>';
        exit;
    }

    $post_id = (int)($_POST['post_id'] ?? 0);
    $action = $_POST['action'] ?? null;

    if ($post_id && $action) {
        if ($action === 'like') {
            $postController->likePost($post_id, $user_id);
        } elseif ($action === 'unlike') {
            $postController->unlikePost($post_id, $user_id);
        }

        $liked = $postController->hasLikedPost($post_id, $user_id);
        $likeCount = $postController->getLikeCount($post_id);

        ?>
        <button
          hx-post="index.php"
          hx-target="this"
          hx-swap="outerHTML"
          hx-vals='{"ajax": "like", "post_id": "<?php echo $post_id; ?>", "action": "<?php echo $liked ? 'unlike' : 'like'; ?>"}'
          class="like-btn flex items-center gap-1 hover:scale-110 <?php echo $liked ? 'text-pink-300' : ''; ?>"
        >
          <?php echo $liked ? '❤️' : '🤍'; ?>
          <span><?php echo $likeCount; ?> Like<?php echo $likeCount != 1 ? 's' : ''; ?></span>
        </button>
        <?php
    } else {
        echo '<button class="text-gray-400">Error</button>';
    }
    exit;
}

// Handle AJAX comment submission
if (isset($_POST['ajax']) && $_POST['ajax'] === 'comment') {
    header('Content-Type: application/json');

    if (!$session->isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    $post_id = (int)($_POST['post_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if ($post_id && $content) {
        $user_id = $session->getUserId();
        $postController->addComment($post_id, $user_id, $content);

        // fetch latest comment
        $comments = $postController->getComments($post_id);
        $newComment = end($comments); // latest comment

        ob_start();
        ?>
<div class="flex items-start space-x-2 mb-1">
  <div class="w-6 h-6 rounded-full overflow-hidden border border-gray-500">
    <?php if (!empty($newComment['avatar_url'])): ?>
      <img src="<?php echo htmlspecialchars($newComment['avatar_url']); ?>" class="w-full h-full object-cover">
    <?php else: ?>
      <i data-feather="user" class="text-green-400 w-4 h-4"></i>
    <?php endif; ?>
  </div>
  <div class="text-sm flex flex-col w-full">
    <div class="flex justify-between items-center">
      <span class="font-semibold text-green-200"><?php echo htmlspecialchars($newComment['username']); ?></span>
      <?php if ($newComment['username'] === $_SESSION['username']): ?>
        <div class="flex gap-2 text-xs">
          <button type="button" onclick="toggleEditComment(<?php echo $newComment['id']; ?>)" class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition">Edit</button>
          <form method="POST" class="inline">
            <input type="hidden" name="comment_id" value="<?php echo $newComment['id']; ?>">
            <button type="submit" name="delete_comment" class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 transition" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
          </form>
        </div>
      <?php endif; ?>
    </div>
    <p id="comment-text-<?php echo $newComment['id']; ?>" class="text-gray-300"><?php echo htmlspecialchars($newComment['content']); ?></p>

    <!-- Hidden inline edit form -->
    <form method="POST" id="edit-form-<?php echo $newComment['id']; ?>" class="hidden mt-1 flex space-x-2">
      <input type="hidden" name="comment_id" value="<?php echo $newComment['id']; ?>">
      <input type="text" name="new_comment_content" value="<?php echo htmlspecialchars($newComment['content']); ?>" 
             class="w-full bg-gray-800 text-white text-sm px-3 py-1 rounded border border-gray-600 focus:outline-none">
      <button type="submit" name="update_comment" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-xs transition">
        Save
      </button>
    </form>
  </div>
</div>
        <?php
        $html = ob_get_clean();

        echo json_encode(['success' => true, 'html' => $html]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
    exit;
}

// Handle AJAX comment update
if (isset($_POST['ajax']) && $_POST['ajax'] === 'update_comment') {
    header('Content-Type: application/json');

    if (!$session->isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    $comment_id = (int)($_POST['comment_id'] ?? 0);
    $new_content = trim($_POST['new_content'] ?? '');
    $user_id = $session->getUserId();

    if ($comment_id && $new_content) {
        $postController->updateComment($comment_id, $user_id, $new_content);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
    exit;
}

// Handle AJAX comment delete
if (isset($_POST['ajax']) && $_POST['ajax'] === 'delete_comment') {
    header('Content-Type: application/json');

    if (!$session->isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    $comment_id = (int)($_POST['comment_id'] ?? 0);
    $user_id = $session->getUserId();

    if ($comment_id) {
        $postController->deleteComment($comment_id, $user_id);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
    }
    exit;
}

$page = $_GET['page'] ?? 'home';

// --- Helper: render comments partial for HTMX swaps ---
function renderCommentsPartial($post_id, $postController) {
    ob_start();
    ?>
    <div id="comments-<?php echo (int)$post_id; ?>" class="mt-4 border-t border-gray-400 pt-2">
        <!-- Add comment form -->
        <div class="comment-form mb-2">
            <form 
                hx-post="index.php"
                hx-target="#comments-<?php echo $post_id; ?>"
                hx-swap="outerHTML"
                class="flex items-center space-x-2 mb-2"
            >
                <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                <input type="text" name="comment_content" placeholder="Add a comment..."
                       class="w-full bg-gray-800 text-white text-sm px-3 py-2 rounded border border-gray-600 focus:outline-none">
                <button type="submit" name="add_comment"
                        class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                    Post
                </button>
            </form>
        </div>

        <?php
        $comments = $postController->getComments($post_id);
        if (!empty($comments)):
            foreach ($comments as $comment): ?>
                <div class="flex items-start space-x-2 mb-1">
                    <div class="w-6 h-6 rounded-full overflow-hidden border border-gray-500">
                        <?php if (!empty($comment['avatar_url'])): ?>
                            <img src="<?php echo htmlspecialchars($comment['avatar_url']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <i data-feather="user" class="text-green-400 w-4 h-4"></i>
                        <?php endif; ?>
                    </div>
                    <div class="text-sm flex flex-col w-full">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-green-200"><?php echo htmlspecialchars($comment['username']); ?></span>
                            <?php if (!empty($_SESSION['username']) && $comment['username'] === $_SESSION['username']): ?>
                                <div class="flex gap-2 text-xs">
                                    <button type="button" onclick="toggleEditComment(<?php echo $comment['id']; ?>)" class="bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition">Edit</button>
                                    <button 
                                        hx-post="index.php"
                                        hx-confirm="Are you sure you want to delete this comment?"
                                        hx-vals='{"delete_comment": 1, "comment_id": "<?php echo $comment['id']; ?>", "post_id": "<?php echo $post_id; ?>"}'
                                        hx-target="#comments-<?php echo $post_id; ?>"
                                        hx-swap="outerHTML"
                                        class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 transition text-xs">
                                        Delete
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p id="comment-text-<?php echo $comment['id']; ?>" class="text-gray-300"><?php echo htmlspecialchars($comment['content']); ?></p>

                        <form 
                            hx-post="index.php"
                            hx-target="#comments-<?php echo $post_id; ?>"
                            hx-swap="outerHTML"
                            id="edit-form-<?php echo $comment['id']; ?>"
                            class="hidden mt-1 flex space-x-2">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                            <input type="text" name="new_comment_content" value="<?php echo htmlspecialchars($comment['content']); ?>" 
                                   class="w-full bg-gray-800 text-white text-sm px-3 py-1 rounded border border-gray-600 focus:outline-none">
                            <button type="submit" name="update_comment" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-xs transition">
                                Save
                            </button>
                        </form>
                    </div>
                </div>
        <?php endforeach; endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

// --- HTMX handlers for comments partial ---
if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
    header('Content-Type: text/html');

    if (!$session->isLoggedIn()) {
        echo '<div class="p-2 text-red-400">Not logged in.</div>';
        exit;
    }

    // Add comment
    if (isset($_POST['add_comment'], $_POST['post_id'], $_POST['comment_content'])) {
        $post_id = (int)$_POST['post_id'];
        $content = trim($_POST['comment_content']);
        if ($post_id && $content !== '') {
            $postController->addComment($post_id, $session->getUserId(), $content);
        }
        echo renderCommentsPartial($post_id, $postController);
        exit;
    }

    // Update comment
    if (isset($_POST['update_comment'], $_POST['comment_id'], $_POST['post_id'], $_POST['new_comment_content'])) {
        $comment_id = (int)$_POST['comment_id'];
        $post_id = (int)$_POST['post_id'];
        $new_content = trim($_POST['new_comment_content']);
        if ($comment_id && $new_content !== '') {
            $postController->updateComment($comment_id, $session->getUserId(), $new_content);
        }
        echo renderCommentsPartial($post_id, $postController);
        exit;
    }

    // Delete comment
    if (isset($_POST['delete_comment'], $_POST['comment_id'], $_POST['post_id'])) {
        $comment_id = (int)$_POST['comment_id'];
        $post_id = (int)$_POST['post_id'];
        if ($comment_id) {
            $postController->deleteComment($comment_id, $session->getUserId());
        }
        echo renderCommentsPartial($post_id, $postController);
        exit;
    }
}
$title = '';

// Handle follow/unfollow actions
if (isset($_POST['follow_action'], $_POST['followed_id']) && $session->isLoggedIn()) {
    $controller = new ProfileController();
    $controller->toggleFollow($session->getUserId(), (int)$_POST['followed_id']);
    header("Location: index.php?page=profile&id=" . (int)$_POST['followed_id']);
    exit;
}

switch ($page) {
    case 'login':
        // If already logged in, redirect to home
        if ($session->isLoggedIn()) {
            header("Location: index.php");
            exit;
        }
        $login_error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            if ($userController->login($username, $password)) {
                header("Location: index.php");
                exit;
            } else {
                $login_error = "Invalid username or password";
            }
        }
        $title = "Login";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/login_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'register':
        $register_error = '';
        $register_success = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            if ($password !== $confirm_password) {
                $register_error = "Passwords do not match";
            } else {
                $result = $userController->register($username, $email, $password);
                if ($result) {
                    $register_success = true;
                } else {
                    $register_error = "Registration failed. Username might already be taken.";
                }
            }
        }
        $title = "Register";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/register_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'logout':
        $session->destroy();
        header("Location: index.php?page=login");
        exit;

    case 'profile':
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }

        $controller = new ProfileController();

        // Determine which profile to show (user’s own or another user’s)
        $user_id = $_GET['id'] ?? $session->getUserId();

        // Handle profile updates (only allowed on own profile)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id == $session->getUserId()) {
            $controller->updateProfile(
                $user_id,
                $_POST['bio'] ?? '',
                $_POST['location'] ?? '',
                $_POST['website'] ?? '',
                $_FILES['avatar'] ?? null
            );
            header("Location: index.php?page=profile&id={$user_id}");
            exit;
        }

        // Fetch profile data and user posts
        $data = $controller->showProfile($user_id);
        $profileData = $data['profile'];
        $posts = $data['posts'];
        $canEditProfile = ($session->getUserId() == $profileData['id']);

        $title = "Profile";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/profile_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'settings':
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }

        $user_id = $session->getUserId();
        $currentUserId = $user_id;
        $isAdmin = $session->isAdmin();

        if ($isAdmin) {
            $adminController = new AdminController();
        }

        // Update post content and image
        if (isset($_POST['update_post'], $_POST['post_id'])) {
            $post_id = (int)$_POST['post_id'];
            $new_content = trim($_POST['new_content'] ?? '');
            $remove_image = isset($_POST['remove_image']);
            $file = $_FILES['new_image'] ?? null;
            $new_image_path = null;

            // Handle new image upload
            if ($file && $file['error'] === UPLOAD_ERR_OK && $file['size'] > 0) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (in_array($mimeType, $allowedTypes) && $file['size'] <= $maxSize) {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $targetDir = __DIR__ . '/uploads/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    $basename = uniqid('img_', true) . '.' . $ext;
                    $targetPath = $targetDir . $basename;
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $new_image_path = 'uploads/' . $basename;
                    }
                }
            }

            if (!empty($new_content) || $new_image_path || $remove_image) {
                $postController->updatePostContent($post_id, $new_content, $user_id, $new_image_path, $remove_image);
            }

            header("Location: index.php?page=settings");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Delete own post
            if (isset($_POST['delete_post'], $_POST['post_id'])) {
                $postController->deletePostByUser((int)$_POST['post_id'], $user_id);
                header("Location: index.php?page=settings");
                exit;
            }

            // Admin actions
            if ($isAdmin) {
                if (isset($_POST['block_user'], $_POST['user_id'])) {
                    // Demote user before blocking
                    $adminController->demoteFromAdmin((int)$_POST['user_id']);
                    $adminController->blockUser((int)$_POST['user_id']);
                }
                if (isset($_POST['unblock_user'], $_POST['user_id'])) {
                    $adminController->unblockUser((int)$_POST['user_id']);
                }
                if (isset($_POST['promote_user'], $_POST['user_id'])) {
                    $adminController->promoteToAdmin((int)$_POST['user_id']);
                }
                if (isset($_POST['demote_user'], $_POST['user_id'])) {
                    $adminController->demoteFromAdmin((int)$_POST['user_id']);
                }
                if (isset($_POST['admin_delete_post'], $_POST['post_id'])) {
                    $adminController->deletePost((int)$_POST['post_id']);
                }
                header("Location: index.php?page=settings");
                exit;
            }
        }

        // My own posts
        $posts = $postController->getPostsByUser($user_id);

        // Admin-only
        $allUsers = [];
        $allPosts = [];
        if ($isAdmin) {
            $allUsers = $userController->getAllUsers();
            $allPosts = $postController->getPosts(); // fetch all posts for admin
        }

        $title = "Settings";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/settings_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;

    case 'home':
    default:
        if (!$session->isLoggedIn()) {
            header("Location: index.php?page=login");
            exit;
        }
        $user_id = $session->getUserId();
        $blocked_message = '';
        if ($userController->isBlocked($user_id)) {
            $blocked_message = "You are blocked. You cannot access the feed.";
        }
        // Handle new comment submission
        if (isset($_POST['add_comment']) && isset($_POST['post_id']) && !empty($_POST['comment_content'])) {
            $post_id = (int)$_POST['post_id'];
            $comment_content = trim($_POST['comment_content']);
            if (!empty($comment_content)) {
                $postController->addComment($post_id, $user_id, $comment_content);
            }
            header("Location: index.php");
            exit;
        }

        // Handle comment update
        if (isset($_POST['update_comment'], $_POST['comment_id'])) {
            $comment_id = (int)$_POST['comment_id'];
            $new_content = trim($_POST['new_comment_content'] ?? '');
            if (!empty($new_content)) {
                $postController->updateComment($comment_id, $user_id, $new_content);
            }
            header("Location: index.php");
            exit;
        }

        // Handle comment delete
        if (isset($_POST['delete_comment'], $_POST['comment_id'])) {
            $comment_id = (int)$_POST['comment_id'];
            $postController->deleteComment($comment_id, $user_id);
            header("Location: index.php");
            exit;
        }
        // Handle new post submission (with image upload validation)
        if (isset($_POST['post_submit']) && !$blocked_message) {
            $content = $_POST['content'] ?? '';
            $file = $_FILES['imageFile'] ?? null;
            $imagePath = null;
            if ($file && $file['error'] === UPLOAD_ERR_OK && $file['size'] > 0) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (in_array($mimeType, $allowedTypes) && $file['size'] <= $maxSize) {
                    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $targetDir = __DIR__ . '/uploads/';
                    if (!is_dir($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    $basename = uniqid('img_', true) . '.' . $ext;
                    $targetPath = $targetDir . $basename;
                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $imagePath = 'uploads/' . $basename;
                    }
                }
            }
            // Create post with optional image path
            $postController->createPost($user_id, $content, $imagePath);
            header("Location: index.php");
            exit;
        }
        $followingPosts = $postController->getPostsFromFollowing($user_id);
        $profileController = new ProfileController();
        $followingList = $profileController->getFollowingList($user_id);
        $posts = $postController->getPosts();
        $title = "Home";
        require __DIR__ . '/includes/views/header.php';
        require __DIR__ . '/includes/views/home_view.php';
        require __DIR__ . '/includes/views/footer.php';
        break;
}
