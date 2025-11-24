<?php

class ProfileController {
    private $profileModel;
    private $postModel;
    private $userModel;
    private $pdo;

    
    public function __construct($pdo, $profileModel, $postModel, $userModel) {
        $this->pdo = $pdo;
        $this->profileModel = $profileModel;
        $this->postModel = $postModel;
        $this->userModel = $userModel;
    }

    public function isUserBlockedByUser($blockerId, $blockedId) {
        return $this->userModel->isUserBlocked($blockerId, $blockedId);
    }

    public function handleBlockActions($session)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (!$session || !$session->isLoggedIn()) return;

        $blockerId = (int)$session->getUserId();
        if (!$blockerId) return;

        if (isset($_POST['block_user'], $_POST['blocked_id'])) {
            $blockedId = (int)$_POST['blocked_id'];
            if ($blockedId > 0 && $blockedId !== $blockerId) {
                $this->blockUserAndUnfollow($blockerId, $blockedId);
            }
            $redirect = isset($_POST['from_settings']) ? 'index.php?page=settings' : ('index.php?page=profile&id=' . $blockedId);
            header('Location: ' . $redirect);
            exit;
        }

        if (isset($_POST['unblock_user'], $_POST['blocked_id'])) {
            $blockedId = (int)$_POST['blocked_id'];
            if ($blockedId > 0 && $blockedId !== $blockerId) {
                $this->userModel->unblockUser($blockerId, $blockedId);
            }
            $redirect = isset($_POST['from_settings']) ? 'index.php?page=settings' : ('index.php?page=profile&id=' . $blockedId);
            header('Location: ' . $redirect);
            exit;
        }
    }

    public function handleFollowActions($session)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (!$session || !$session->isLoggedIn()) return;

        if (!isset($_POST['follow_action'])) return;

        $followerId = (int)$session->getUserId();
        $userId = (int)($_POST['followed_id'] ?? 0);

        if ($userId > 0 && $userId !== $followerId) {
            $this->toggleFollow($followerId, $userId);
        }

        header('Location: index.php?page=profile&id=' . $userId);
        exit;
    }

    // Get profile info and their posts
    public function showProfile($user_id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_pin'])) {
            $user_id_logged = $_SESSION['user_id'] ?? null;
            if ($user_id_logged && isset($_POST['post_id'])) {
                $this->postModel->togglePin(
                    $_POST['post_id'],
                    $user_id_logged,
                    isset($_POST['is_pinned']) ? (int)$_POST['is_pinned'] : 0
                );
            }
            header('Location: index.php?page=profile&id=' . urlencode($user_id_logged));
            exit;
        }

        if (!is_numeric($user_id) || $user_id <= 0) {
            header("Location: index.php?page=404");
            exit();
        }

        $profile = $this->profileModel->getByUserId($user_id);
        if (!$profile) {
            header("Location: index.php?page=404");
            exit();
        }

        $viewer_id = $_SESSION['user_id'] ?? null;

        if ($viewer_id && $this->userModel->isUserBlocked($viewer_id, $user_id)) {
            return [
                'profile' => [
                    'id' => $user_id,
                    'username' => 'Blocked User',
                    'bio' => 'You have blocked this user.',
                    'location' => '',
                    'website' => '',
                    'avatar_url' => null
                ],
                'posts' => []
            ];
        }

        if ($viewer_id && $this->userModel->isUserBlocked($user_id, $viewer_id)) {
            return [
                'profile' => [
                    'id' => $user_id,
                    'username' => 'Not Available',
                    'bio' => 'This user has blocked you.',
                    'location' => '',
                    'website' => '',
                    'avatar_url' => null
                ],
                'posts' => []
            ];
        }

        if ($this->userModel->isBlocked($user_id)) {
            return [
                'profile' => [
                    'id' => $user_id,
                    'username' => $profile['username'] ?? 'Blocked User',
                    'bio' => 'This account has been blocked by an administrator.',
                    'location' => '',
                    'website' => '',
                    'avatar_url' => null
                ],
                'posts' => []
            ];
        }

        $posts = $this->postModel->getPostsByUser($user_id, $viewer_id);
        return ['profile' => $profile, 'posts' => $posts];
    }

    public function updateProfile($user_id, $bio, $location, $website, $avatarPath = null, $email = null) {
        return $this->profileModel->save($user_id, $bio, $location, $website, $avatarPath, $email);
    }

    public function handleProfileUpdate($user_id, $session) {
        if (isset($_POST['delete_account'])) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $user_id != $session->getUserId()) {
            return;
        }

        if (isset($_POST['delete_avatar'])) {
            $oldProfile = $this->profileModel->getByUserId($user_id);
            if ($oldProfile && !empty($oldProfile['avatar_url'])) {
                $oldAvatarPath = __DIR__ . '/../../' . $oldProfile['avatar_url'];
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }

            $this->profileModel->deleteAvatar($user_id);
            header('Location: index.php?page=profile&id=' . urlencode($user_id));
            exit;
        }

        $avatarPath = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            require_once __DIR__ . '/../helpers/ImageResizer.php';
            $uploadDir = __DIR__ . '/../../uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $file = $_FILES['avatar'];
            $tmpName = $file['tmp_name'];
            $originalName = basename($file['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $imageResizer = new ImageResizer();

            if (!ImageResizer::isValidImage($tmpName)) {
                $_SESSION['error'] = "Whoa! You tried to upload an image that’s too powerful for the Matrix to process. Max avatar size: 5000x5000px, 20MB.";
                header('Location: index.php?page=profile&id=' . urlencode($user_id));
                exit;
            }

            $newName = uniqid('avatar_', true) . '.' . $ext;
            $targetPath = $uploadDir . $newName;
            if (move_uploaded_file($tmpName, $targetPath)) {
                $imageResizer->resizeAvatarImage($targetPath);
                $avatarPath = 'uploads/avatars/' . basename($targetPath);
            } else {
                $session->setFlash('error', 'Failed to upload avatar image.');
                header('Location: index.php?page=profile&id=' . urlencode($user_id));
                exit;
            }
        }

        if ($avatarPath) {
            $oldProfile = $this->profileModel->getByUserId($user_id);
            if ($oldProfile && !empty($oldProfile['avatar_url'])) {
                $oldAvatarPath = __DIR__ . '/../../' . $oldProfile['avatar_url'];
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }
        }

        $bio = $_POST['bio'] ?? '';
        $location = $_POST['location'] ?? '';
        $website = $_POST['website'] ?? '';
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Please enter a valid email address.";
            header('Location: index.php?page=profile&id=' . urlencode($user_id));
            exit;
        }

        $this->updateProfile($user_id, $bio, $location, $website, $avatarPath, $email);
        header('Location: index.php?page=profile&id=' . urlencode($user_id));
        exit;
    }

    public function handleAccountDeletion($user_id, $session) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $user_id != $session->getUserId()) {
            return;
        }

        $password = trim($_POST['password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if (empty($password) || empty($confirm_password) || $password !== $confirm_password) {
            $_SESSION['error'] = "Passwords do not match.";
            header('Location: index.php?page=profile&id=' . urlencode($user_id));
            exit;
        }

        $user = $this->userModel->getUserById($user_id);
        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['error'] = "Incorrect password.";
            header('Location: index.php?page=profile&id=' . urlencode($user_id));
            exit;
        }

        $this->userModel->deleteAccount($user_id);

        session_destroy();
        header('Location: index.php?page=login&deleted=1');
        exit;
    }

    public function toggleFollow($follower_id, $user_id) {
        if ($this->userModel->isUserBlocked($follower_id, $user_id) || $this->userModel->isUserBlocked($user_id, $follower_id)) {
            return false;
        }

        if ($this->profileModel->isFollowing($follower_id, $user_id)) {
            $this->profileModel->unfollowUser($follower_id, $user_id);
            return false;
        } else {
            $this->profileModel->followUser($follower_id, $user_id);
            return true;
        }
    }

    public function blockUserAndUnfollow($blocker_id, $blocked_id) {
        $this->userModel->blockUser($blocker_id, $blocked_id);
        $this->profileModel->unfollowUser($blocker_id, $blocked_id);
        $this->profileModel->unfollowUser($blocked_id, $blocker_id);
    }

    public function isFollowing($follower_id, $user_id) {
        return $this->profileModel->isFollowing($follower_id, $user_id);
    }

    public function getFollowCounts($user_id) {
        $followers = $this->profileModel->countFollowers($user_id);
        $following = $this->profileModel->countFollowing($user_id);
        return ['followers' => $followers, 'following' => $following];
    }

    public function getFollowingList($user_id) {
        return $this->profileModel->getFollowingList($user_id);
    }

    public function handleProfile($session) {
        $user_id = isset($_GET['id']) ? $_GET['id'] : $session->getUserId();

        if (!is_numeric($user_id) || $user_id <= 0) {
            header("Location: index.php?page=404");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_pin'])) {
            $user_id_logged = $session->getUserId();
            if ($user_id_logged && isset($_POST['post_id'])) {
                $this->postModel->togglePin(
                    $_POST['post_id'],
                    $user_id_logged,
                    isset($_POST['is_pinned']) ? (int)$_POST['is_pinned'] : 0
                );
            }
            header('Location: index.php?page=profile&id=' . urlencode($user_id_logged));
            exit;
        }

        $this->handleProfileUpdate($user_id, $session);
        $this->handleAccountDeletion($user_id, $session);

        $this->handleBlockActions($session);
        $this->handleFollowActions($session);

        $data = $this->showProfile($user_id);

        if (!$data || !isset($data['profile'])) {
            header("Location: index.php?page=404");
            exit();
        }

        $profileData = $data['profile'];
        $posts = $data['posts'];
        $canEditProfile = ($session->getUserId() == $profileData['id']);
        $controller = $this;

        // Important: keep $userController name for the view
        $userController = $this;

        $pdo = $this->pdo;
        require_once __DIR__ . '/../views/header.php';
        require_once __DIR__ . '/../views/profile_view.php';
        require_once __DIR__ . '/../views/footer.php';
    }
    public function getBlockedUsers($userId) {
        if (empty($userId)) {
            return [];
        }
        return $this->userModel->getBlockedUsers($userId);
    }
}