<?php
class AjaxController
{
    private $session;
    private $userController;
    private $postController;
    private $zionChat;
    private $pdo;

    public function __construct($session, $userController, $postController, $pdo)
    {
        $this->session = $session;
        $this->userController = $userController;
        $this->postController = $postController;
        $this->zionChat = new ZionChat($pdo);
        $this->pdo = $pdo;
    }

    public function handleRequest()
    {
        $ajaxAction = null;
        if (isset($_POST['ajax'])) {
            $ajaxAction = $_POST['ajax'];
        } elseif (isset($_GET['ajax'])) {
            $ajaxAction = $_GET['ajax'];
        } else {
            return;
        }

        switch ($ajaxAction) {
            case 'like':
                $this->handleLike();
                break;
            case 'comment':
                $this->handleComment();
                break;
            case 'update_comment':
                $this->handleUpdateComment();
                break;
            case 'delete_comment':
                $this->handleDeleteComment();
                break;
            case 'delete_all_notifications':
                $this->handleDeleteAllNotifications();
                break;
            case 'zion_chat':
                $this->handleZionChat();
                break;
            case 'fetch_chat':
                $this->handleFetchChat();
                break;
            case 'delete_message':
                $this->handleDeleteMessage();
                break;
        }
        exit;
    }

    private function handleLike()
    {
        header('Content-Type: application/json');

        if (!$this->session->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $user_id = $this->session->getUserId();
        if ($this->userController->isBlocked($user_id)) {
            echo json_encode(['success' => false, 'message' => 'User is blocked']);
            exit;
        }

        $post_id = $_POST['post_id'] ?? 0;
        $action = $_POST['action'] ?? null;

        if ($post_id && $action) {
            if ($action === 'like') {
                $this->postController->likePost($post_id, $user_id);
            } elseif ($action === 'unlike') {
                $this->postController->unlikePost($post_id, $user_id);
            }
            $likeCount = $this->postController->getLikeCount($post_id);
            echo json_encode(['success' => true, 'likes' => $likeCount]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
    }

    private function handleComment()
    {
        header('Content-Type: application/json');
        if (!isset($_SESSION)) { session_start(); }

        // CSRF protection for comment submission
        if (empty($_POST['csrf_token']) || !$this->session->validateCsrfToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        $now = time();
        $cooldown = 10;
        $lastSent = $_SESSION['last_comment_time'] ?? 0;

        if ($now - $lastSent < $cooldown) {
            $remaining = $cooldown - ($now - $lastSent);

            echo json_encode([
                'success' => false,
                'message' => "Slow down, Operator... Wait {$remaining}s."
            ]);
            exit;
        }

        if (!$this->session->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $post_id = $_POST['post_id'] ?? 0;
        $content = trim($_POST['content'] ?? '');
        $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

        if ($post_id && $content) {
            $user_id = $this->session->getUserId();
            $this->postController->addComment($post_id, $user_id, $content);
            $_SESSION['last_comment_time'] = $now;

            $comments = $this->postController->getComments($post_id);
            $newComment = end($comments); // latest comment

            ob_start();
            ?>
            <div class="flex items-start space-x-2 mb-1" data-comment-id="<?php echo $newComment['id']; ?>">
                <div class="w-6 h-6 rounded-full overflow-hidden border border-gray-500 flex items-center justify-center bg-black">
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
                                <button type="button"
                                    class="edit-comment-btn bg-blue-600 text-white px-2 py-1 rounded hover:bg-blue-700 transition"
                                    data-comment-id="<?php echo $newComment['id']; ?>">
                                    Edit
                                </button>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="comment_id" value="<?php echo $newComment['id']; ?>">
                                    <button type="submit" name="delete_comment" class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 transition"
                                        onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p id="comment-text-<?php echo $newComment['id']; ?>" class="text-gray-300" data-comment-text>
                        <?php echo htmlspecialchars($newComment['content']); ?>
                    </p>
                </div>
            </div>
            <?php
            $html = ob_get_clean();

            echo json_encode(['success' => true, 'html' => $html]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
    }

    private function handleUpdateComment()
    {
        header('Content-Type: application/json');

        if (!$this->session->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $comment_id = $_POST['comment_id'] ?? 0;
        $new_content = trim($_POST['new_content'] ?? '');
        $user_id = $this->session->getUserId();

        if ($comment_id && $new_content) {
            $this->postController->updateComment($comment_id, $user_id, $new_content);
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
    }

    private function handleDeleteComment()
    {
        header('Content-Type: application/json');

        if (!$this->session->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $comment_id = $_POST['comment_id'] ?? 0;
        $user_id = $this->session->getUserId();

        if ($comment_id) {
            $comment = $this->postController->getCommentById($comment_id);
            $canDelete = false;

            if ($this->session->isAdmin()) {
                $canDelete = true;
            } elseif ($comment && $comment['user_id'] == $user_id) {
                $canDelete = true;
            } elseif ($comment) {
                $post = $this->postController->getPostById($comment['post_id']);
                if ($post && $post['user_id'] == $user_id) {
                    $canDelete = true;
                }
            }

            if ($canDelete) {
                $this->postController->deleteComment($comment_id, $user_id);
                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Not authorized']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
    }

    private function handleDeleteAllNotifications()
    {
        header('Content-Type: application/json');

        if (!$this->session->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $userId = $this->session->getUserId();
        $notificationController = new NotificationController($this->pdo);
        $notificationController->deleteAllNotifications($userId);

        echo json_encode(['success' => true]);
        exit;
    }

    private function handleZionChat()
    {
        header('Content-Type: application/json');

        if (!$this->session->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }
        // CSRF protection for chat message submission
        if (empty($_POST['csrf_token']) || !$this->session->validateCsrfToken($_POST['csrf_token'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }

        $user_id = $this->session->getUserId();
        if ($this->userController->isBlocked($user_id)) {
            echo json_encode(['success' => false, 'message' => 'Access denied. You are blocked from using chat.']);
            exit;
        }

        $message = trim($_POST['message'] ?? '');
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        if ($message === '') {
            echo json_encode(['success' => false, 'message' => 'Invalid message']);
            exit;
        }

        // === Rate limiting: prevent chat spam ===
        if (!isset($_SESSION)) {
            session_start();
        }

        $now = time();
        $cooldown = 3; // seconds between messages
        $lastSent = $_SESSION['last_chat_message_time'] ?? 0;

        if ($now - $lastSent < $cooldown) {
            echo json_encode([
                'success' => false,
                'message' => '🕒 Slow down, Operator...'
            ]);
            exit;
        }

        // Update timestamp after passing the check
        $_SESSION['last_chat_message_time'] = $now;

        $zionChat = $this->zionChat;
        $zionChat->insertMessage($user_id, $message);

        // Fetch the most recent messages after insert
        $messages = $zionChat->fetchRecentMessages(50, $user_id);

        // Ensure valid JSON response
        if (is_array($messages)) {
            $messages = array_map(function($m) {
                return [
                    'id' => $m['id'] ?? null,
                    'user_id' => $m['user_id'] ?? null, // Added user_id for ownership check
                    'username' => $m['username'] ?? 'Unknown',
                    'content' => $m['content'] ?? '',
                    'created_at' => $m['created_at'] ?? '',
                    'avatar_url' => $m['avatar_url'] ?? '',
                    'profile_url' => $m['profile_url'] ?? ''
                ];
            }, $messages);
            echo json_encode(['success' => true, 'messages' => $messages]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to load messages']);
            exit;
        }
    }

    private function handleFetchChat()
    {
        header('Content-Type: application/json');

        if (!$this->session->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }
        $user_id = $this->session->getUserId();
        if ($this->userController->isBlocked($user_id)) {
            echo json_encode(['success' => false, 'message' => 'Access denied. You are blocked from using chat.']);
            exit;
        }

        $zionChat = $this->zionChat;
        $messages = $zionChat->fetchRecentMessages(50, $user_id);

        if (is_array($messages)) {
            $messages = array_map(function($m) {
                return [
                    'id' => $m['id'] ?? null,
                    'user_id' => $m['user_id'] ?? null, // Added user_id for ownership check
                    'username' => $m['username'] ?? 'Unknown',
                    'content' => $m['content'] ?? '',
                    'created_at' => $m['created_at'] ?? '',
                    'avatar_url' => $m['avatar_url'] ?? '',
                    'profile_url' => $m['profile_url'] ?? ''
                ];
            }, $messages);
            echo json_encode(['success' => true, 'messages' => $messages]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to load messages']);
            exit;
        }
    }

    private function handleDeleteMessage()
    {
        header('Content-Type: application/json');

        if (!$this->session->isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $message_id = $_POST['message_id'] ?? 0;
        $user_id = $this->session->getUserId();

        if (!$message_id) {
            echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
            exit;
        }

        $zionChat = $this->zionChat;
        $success = $zionChat->deleteMessage($message_id, $user_id);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Not authorized or failed to delete message']);
        }
        exit;
    }
}