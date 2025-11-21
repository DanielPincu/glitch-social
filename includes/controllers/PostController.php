<?php
    class PostController {
        private $post;
        private $pdo;

        public function __construct($pdo) {
            $this->pdo = $pdo;
            $this->post = new Post($this->pdo);
        }

        // Create a new post with optional image file and visibility
        public function createPost($user_id, $content, $file = null, $visibility = 'public') {
            $imagePath = null;
            if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK && $file['size'] > 0) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!in_array($file['type'], $allowedTypes)) {
                    $_SESSION['error'] = "Invalid image type. Allowed types: jpg, jpeg, png, gif.";
                    return false;
                }
                if ($file['size'] > 20 * 1024 * 1024) {
                    $_SESSION['error'] = "Image size exceeds 20MB limit.";
                    return false;
                }
                $uploadDir = __DIR__ . '/../../uploads/posts/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileTmp = $file['tmp_name'];
                if (!\ImageResizer::isValidImage($fileTmp)) {
                    $_SESSION['error'] = "Whoa! That image upload bends the Matrix — max dimensions are 5000x5000px and 20MB. The Oracle suggests resizing before reality crashes.";
                    return false;
                }
                $fileName = basename($file['name']);
                $targetPath = $uploadDir . uniqid('post_', true) . '_' . $fileName;
                if (move_uploaded_file($fileTmp, $targetPath)) {
                    $imageResizer = new \ImageResizer();
                    $imageResizer->resizePostImage($targetPath);
                    $imagePath = 'uploads/posts/' . basename($targetPath);
                } else {
                    $_SESSION['error'] = "Failed to save uploaded image.";
                    return false;
                }
            }
            $post_id = $this->post->create($user_id, $content, $imagePath, $visibility);
            return $post_id;
        }

        // Fetch all posts for displaying, with visibility based on viewer
        public function getPosts($user_id) {
            if (isset($_SESSION['is_blocked']) && $_SESSION['is_blocked'] == 1) {
                return []; // Blocked users see no posts at all
            }
            return $this->post->fetchAll($user_id);
        }
        // Like a post
        public function likePost($post_id, $user_id) {
            return $this->post->like($post_id, $user_id);
        }

        // Unlike a post
        public function unlikePost($post_id, $user_id) {
            return $this->post->unlike($post_id, $user_id);
        }

        // Check if user has liked a post
        public function hasLikedPost($post_id, $user_id) {
            return $this->post->hasLiked($post_id, $user_id);
        }

        // Get the number of likes for a post
        public function getLikeCount($post_id) {
            return $this->post->getLikeCount($post_id);
        }

        // Delete a post
        public function deletePost($post_id) {
            $post = $this->post->getPostById($post_id);
            if ($post && !empty($post['image_path'])) {
                $this->deleteImageFile($post['image_path']);
            }
            return $this->post->deletePostById($post_id);
        }

        // Get all posts by a specific user
        public function getPostsByUser($user_id) {
            if (isset($_SESSION['is_blocked']) && $_SESSION['is_blocked'] == 1) {
                return []; // Blocked users see no posts at all
            }
            return $this->post->getPostsByUser($user_id);
        }

        // Delete a post by a specific user
        public function deletePostByUser($post_id, $user_id) {
            $post = $this->post->getPostById($post_id);
            if (!$post || $post['user_id'] != $user_id) {
                return false;
            }
            if (!empty($post['image_path'])) {
                $this->deleteImageFile($post['image_path']);
            }
            return $this->post->deletePostById($post_id);
        }
        // Get all posts (alias for admin use)
        public function getAllPosts() {
            return $this->getPosts(null);
        }
        // Update a post’s content, optionally its image, and optionally its visibility
        public function updatePostContent($post_id, $new_content, $user_id, $new_image_file = null, $remove_image = false, $visibility = null) {
            $new_image_path = null;
            $oldPost = $this->post->getPostById($post_id);
            if (!$oldPost || $oldPost['user_id'] != $user_id) {
                return false;
            }
            // Handle new image upload
            if ($new_image_file && isset($new_image_file['error']) && $new_image_file['error'] === UPLOAD_ERR_OK && $new_image_file['size'] > 0) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!in_array($new_image_file['type'], $allowedTypes)) {
                    $_SESSION['error'] = "Invalid image type. Allowed types: jpg, jpeg, png, gif.";
                    return false;
                }
                if ($new_image_file['size'] > 20 * 1024 * 1024) {
                    $_SESSION['error'] = "Image size exceeds 20MB limit.";
                    return false;
                }
                $uploadDir = __DIR__ . '/../../uploads/posts/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileTmp = $new_image_file['tmp_name'];
                if (!\ImageResizer::isValidImage($fileTmp)) {
                    $_SESSION['error'] = "Whoa! That image upload bends the Matrix — max dimensions are 5000x5000px and 20MB. The Oracle suggests resizing before reality crashes.";
                    return false;
                }
                $fileName = basename($new_image_file['name']);
                $targetPath = $uploadDir . uniqid('post_', true) . '_' . $fileName;
                if (move_uploaded_file($fileTmp, $targetPath)) {
                    // Delete old image if exists
                    if (!empty($oldPost['image_path'])) {
                        $this->deleteImageFile($oldPost['image_path']);
                    }
                    $imageResizer = new \ImageResizer();
                    $imageResizer->resizePostImage($targetPath);
                    $new_image_path = 'uploads/posts/' . basename($targetPath);
                } else {
                    $_SESSION['error'] = "Failed to save uploaded image.";
                    return false;
                }
            } elseif ($remove_image) {
                // Remove old image if requested
                if (!empty($oldPost['image_path'])) {
                    $this->deleteImageFile($oldPost['image_path']);
                }
                $new_image_path = null;
            } else {
                // No image change: don't overwrite existing path
                $new_image_path = null;
            }

            $fields = ['content' => $new_content];
            if ($visibility !== null) {
                $fields['visibility'] = $visibility;
            }
            if ($new_image_path !== null) {
                $fields['image_path'] = $new_image_path;
            } elseif ($remove_image) {
                $fields['image_path'] = null;
            }
            return $this->post->updateFields($post_id, $fields);
        }
        // Get posts from users the current user follows
        public function getPostsFromFollowing($user_id) {
            if (isset($_SESSION['is_blocked']) && $_SESSION['is_blocked'] == 1) {
                return []; // Blocked users see no posts at all
            }
            return $this->post->getPostsFromFollowing($user_id, $user_id);
        }

        // Add a comment to a post
        public function addComment($post_id, $user_id, $content) {
            return $this->post->addComment($post_id, $user_id, $content);
        }

        // Get comments for a post
        public function getComments($post_id) {
            return $this->post->getComments($post_id);
        }

        public function updateComment($comment_id, $user_id, $new_content) {
        return $this->post->updateComment($comment_id, $user_id, $new_content);
        }

        public function deleteComment($comment_id, $user_id) {
            $comment = $this->post->getCommentById($comment_id);
            if (!$comment) {
                return false; // Comment not found
            }

            // Fetch the post related to the comment
            $post = $this->post->getPostById($comment['post_id']);
            if (!$post) {
                return false; // Post not found
            }

            // Check admin status safely
            $userModel = new User($this->pdo);
            $isAdmin = $userModel->isAdmin($user_id) || (!empty($_SESSION['is_admin']) && $_SESSION['is_admin']);

            // Authorization: comment owner, post owner, or admin
            if ($comment['user_id'] == $user_id || $post['user_id'] == $user_id || $isAdmin) {
                return $this->post->deleteComment($comment_id);
            }

            return false; // Not authorized
        }

        public function getCommentById($comment_id) {
            return $this->post->getCommentById($comment_id);
        }

        public function getPostById($post_id, $redirectOnFail = true) {
            // Validate numeric
            if (!is_numeric($post_id)) {
                if ($redirectOnFail) {
                    header("Location: index.php?page=404");
                    exit();
                }
                return false;
            }

            $post = $this->post->getPostById($post_id);

            // Redirect or return false if not found
            if (!$post) {
                if ($redirectOnFail) {
                    header("Location: index.php?page=404");
                    exit();
                }
                return false;
            }

            return $post;
        }

        public function validatePostId($post_id) {
            if (empty($post_id) || !is_numeric($post_id)) {
                header("Location: index.php?page=404");
                exit();
            }

            $post = $this->post->getPostById($post_id);
            if (!$post) {
                header("Location: index.php?page=404");
                exit();
            }

            return true;
        }

        // Handles new post creation, including optional image upload and validation, with rate limiting.
        public function handleNewPost($session) {
            if (isset($_POST['post_submit'])) {
                // CSRF validation
                if (empty($_POST['csrf_token']) || !$session->validateCsrfToken($_POST['csrf_token'])) {
                    die('Security verification failed. Please refresh the page and try again.');
                }
                // Check if user is logged in
                if (!$session->isLoggedIn()) {
                    $session->generateCsrfToken();
                    header("Location: index.php");
                    exit();
                }
                // Session-based rate limiting: 5 minutes between posts
                if (!isset($_SESSION)) { session_start(); }
                $now = time();
                $cooldown = 300; 
                if (isset($_SESSION['last_post_time']) && ($now - $_SESSION['last_post_time']) < $cooldown) {
                    $remaining = $cooldown - ($now - $_SESSION['last_post_time']);
                    $minutes = floor($remaining / 60);
                    $seconds = $remaining % 60;
                    $_SESSION['error'] = "🕒 Slow down, Operator... You can only post once every 5 minutes. Time left: {$minutes}m {$seconds}s.";
                    $session->generateCsrfToken();
                    header("Location: index.php");
                    exit();
                }
                $user_id = $session->getUserId();
                $allowed = '<strong><em><u><span><img>';
                $content = isset($_POST['content']) ? strip_tags($_POST['content'], $allowed) : '';
                $content = $this->convertYouTubeLinks($content);
                $visibility = isset($_POST['visibility']) ? $_POST['visibility'] : 'public';
                $imageFile = $_FILES['imageFile'] ?? null;
                $trimmedContent = trim(strip_tags($content));

                // Require text always, even if an image is uploaded
                if ($trimmedContent === '') {
                    $_SESSION['error'] = "A post needs actual text to say the least before it can be transmitted down the wire...";
                    $session->generateCsrfToken();
                    header("Location: index.php");
                    exit();
                }
                $post_id = $this->createPost($user_id, $content, $imageFile, $visibility);
                // Always update last_post_time after a successful or failed submission (after cooldown passes)
                $_SESSION['last_post_time'] = $now;
                if ($post_id === false) {
                    $session->generateCsrfToken();
                    header("Location: index.php");
                    exit();
                }
                $session->generateCsrfToken();
                header("Location: index.php");
                exit();
            }
        }

        // Handles comment actions: add, update, delete.
        public function handleCommentActions($session) {
            $user_id = $session->getUserId();
            if (!$user_id) return;
            // Add comment
            if (isset($_POST['add_comment'])) {
                $post_id = $_POST['post_id'] ?? null;
                $comment_content = trim($_POST['comment_content'] ?? '');
                if ($post_id && $comment_content !== '') {
                    $this->addComment($post_id, $user_id, $comment_content);
                }
                header("Location: index.php");
                exit();
            }
            // Update comment
            if (isset($_POST['update_comment'])) {
                $comment_id = $_POST['comment_id'] ?? null;
                $new_content = trim($_POST['new_comment_content'] ?? '');
                if ($comment_id && $new_content !== '') {
                    $this->updateComment($comment_id, $user_id, $new_content);
                }
                header("Location: index.php");
                exit();
            }
            // Delete comment
            if (isset($_POST['delete_comment'])) {
                $comment_id = $_POST['comment_id'] ?? null;
                if ($comment_id) {
                    $this->deleteComment($comment_id, $user_id);
                }
                header("Location: index.php");
                exit();
            }
        }

        // Handles updating a post, including image management and visibility.
        public function handlePostUpdate($session) {
            if (isset($_POST['update_post'])) {
                $user_id = $session->getUserId();
                if (!$user_id) {
                    header("Location: index.php");
                    exit();
                }
                $post_id = $_POST['post_id'] ?? null;
                $allowed = '<strong><em><u><span><img><iframe>';
                $new_content = isset($_POST['new_content']) ? strip_tags($_POST['new_content'], $allowed) : '';
                $new_content = $this->convertYouTubeLinks($new_content);
                $remove_image = !empty($_POST['remove_image']);
                // Ignore remove checkbox if a new image is being uploaded
                $new_image_file = $_FILES['new_image'] ?? null;
                if ($new_image_file && $new_image_file['error'] === UPLOAD_ERR_OK && $new_image_file['size'] > 0) {
                    $remove_image = false;
                }
                $visibility = $_POST['visibility'] ?? null;

                $result = $this->updatePostContent($post_id, $new_content, $user_id, $new_image_file, $remove_image, $visibility);
                if ($result === false) {
                    header("Location: index.php?page=settings");
                    exit();
                }
                header("Location: index.php?page=settings");
                exit();
            }
        }

        // Handles deleting a user's own post.
        public function handlePostDelete($session) {
            if (isset($_POST['delete_post'])) {
                $user_id = $session->getUserId();
                if (!$user_id) {
                    header("Location: index.php");
                    exit();
                }
                $post_id = $_POST['post_id'] ?? null;
                if ($post_id) {
                    $this->deletePostByUser($post_id, $user_id);
                }
                header("Location: index.php?page=settings");
                exit();
            }
        }

        public function handlePinAction($session) {
            if (isset($_POST['toggle_pin'])) {
                $user_id = $session->getUserId();
                if (!$user_id) {
                    header("Location: index.php");
                    exit();
                }

                $post_id = $_POST['post_id'] ?? null;
                $is_pinned = isset($_POST['is_pinned']) ? (int)$_POST['is_pinned'] : 0;

                if ($post_id) {
                    $this->togglePin($post_id, $user_id, $is_pinned);
                }

                header("Location: index.php?page=profile&user_id=" . $user_id);
                exit();
            }
        }

        public function togglePin($post_id, $user_id, $is_pinned) {
            return $this->post->togglePin($post_id, $user_id, $is_pinned);
        }

        // Helper function to delete image file from server
        private function deleteImageFile($relativePath) {
            $fullPath = __DIR__ . '/../../' . $relativePath;
            if ($relativePath && file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        // Converts plain YouTube URLs and Shorts into embed iframes
        private function convertYouTubeLinks($text) {
            // Match /shorts/<id>
            $shortsPattern = '/https?:\/\/(?:www\.)?youtube\.com\/shorts\/([A-Za-z0-9_-]+)/i';

            // Convert Shorts to tall embed
            $text = preg_replace(
                $shortsPattern,
                '<iframe class="yt-embed" width="315" height="560" src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
                $text
            );

            // Match normal YouTube URLs, ignoring timestamp (&t=...) and other query params
            $pattern = '/https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]+)(?:[^\s<]*)/i';

            // Convert standard video links using a callback to strip timestamp and query data
            return preg_replace_callback(
                $pattern,
                function ($m) {
                    return '<iframe class="yt-embed" width="560" height="315" src="https://www.youtube.com/embed/' . $m[1] . '" frameborder="0" allowfullscreen></iframe>';
                },
                $text
            );
        }
}