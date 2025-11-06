<?php
    class PostController {
        private $post;

        public function __construct() {
            $this->post = new Post();
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
                $oldImagePath = __DIR__ . '/../../' . $post['image_path'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            return $this->post->delete($post_id);
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
            if ($post && $post['user_id'] == $user_id && !empty($post['image_path'])) {
                $oldImagePath = __DIR__ . '/../../' . $post['image_path'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            return $this->post->deleteByUser($post_id, $user_id);
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
                        $oldImagePath = __DIR__ . '/../../' . $oldPost['image_path'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
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
                    $oldImagePath = __DIR__ . '/../../' . $oldPost['image_path'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $new_image_path = null;
            } else {
                // No image change: don't overwrite existing path
                $new_image_path = null;
            }

            return $this->post->updateContent($post_id, $new_content, $user_id, $new_image_path, $remove_image, $visibility);
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
            // Load the comment
            $comment = $this->post->getCommentById($comment_id);
            if (!$comment) {
                return false;
            }
            // If user is the comment owner, allow
            if ($comment['user_id'] == $user_id) {
                return $this->post->deleteComment($comment_id, $user_id);
            }
            // Otherwise, check if user is the post owner
            $post = $this->post->getPostById($comment['post_id']);
            if ($post && $post['user_id'] == $user_id) {
                return $this->post->deleteComment($comment_id, $user_id);
            }
            // Otherwise, check if user is admin
            $userModel = new User();
            if ($userModel->isAdmin($user_id)) {
                return $this->post->deleteComment($comment_id, $user_id);
            }
            // Not authorized
            return false;
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

        // Handles new post creation, including optional image upload and validation.
        public function handleNewPost($session) {
            if (isset($_POST['post_submit'])) {
                // CSRF validation
                if (empty($_POST['csrf_token']) || !$session->validateCsrfToken($_POST['csrf_token'])) {
                    die('Security verification failed. Please refresh the page and try again.');
                }
                // Check if user is logged in
                if (!$session->isLoggedIn()) {
                    header("Location: index.php");
                    exit();
                }
                $user_id = $session->getUserId();
                $content = isset($_POST['content']) ? trim($_POST['content']) : '';
                $visibility = isset($_POST['visibility']) ? $_POST['visibility'] : 'public';
                $imageFile = $_FILES['imageFile'] ?? null;
                $post_id = $this->createPost($user_id, $content, $imageFile, $visibility);
                if ($post_id === false) {
                    header("Location: index.php");
                    exit();
                }
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
                $new_content = trim($_POST['new_content'] ?? '');
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
    }