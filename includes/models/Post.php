<?php
require_once __DIR__ . '/Database.php';

class Post {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Create a new post with optional image
    public function create($user_id, $content, $file = null) {
        $imagePath = null;

        // Handle image upload or direct image path
        if (is_array($file) && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($file['type'], $allowedTypes) && $file['size'] <= 2 * 1024 * 1024) { // 2MB max
                $uploadDir = __DIR__ . '/../../img/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $filename = time() . '_' . basename($file['name']);
                $destination = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $imagePath = 'img/' . $filename; // relative path for DB
                }
            }
        } elseif (is_string($file)) {
            $imagePath = $file;
        }

        $stmt = $this->db->prepare("INSERT INTO posts (user_id, content, image_path) VALUES (:user_id, :content, :image_path)");
        return $stmt->execute([
            ':user_id' => $user_id,
            ':content' => $content,
            ':image_path' => $imagePath
        ]);
    }

    // Fetch all posts with user info and like count
    public function fetchAll() {
        $stmt = $this->db->query("
            SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.created_at, 
                   users.username, profiles.avatar_url,
                   (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
            FROM posts
            JOIN users ON posts.user_id = users.id
            LEFT JOIN profiles ON profiles.user_id = users.id
            ORDER BY posts.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Like a post
    public function like($post_id, $user_id) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO likes (post_id, user_id) VALUES (:post_id, :user_id)");
        return $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id
        ]);
    }

    // Unlike a post
    public function unlike($post_id, $user_id) {
        $stmt = $this->db->prepare("DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id");
        return $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id
        ]);
    }

    // Check if user has liked a post
    public function hasLiked($post_id, $user_id) {
        $stmt = $this->db->prepare("SELECT 1 FROM likes WHERE post_id = :post_id AND user_id = :user_id LIMIT 1");
        $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id
        ]);
        return $stmt->fetchColumn() !== false;
    }

    // Get like count for a post
    public function getLikeCount($post_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
        $stmt->execute([
            ':post_id' => $post_id
        ]);
        return (int)$stmt->fetchColumn();
    }
    // Delete a post by ID (also unlink image if exists)
    public function delete($post_id) {
        // Fetch the post record
        $stmt = $this->db->prepare("SELECT image_path FROM posts WHERE id = :id");
        $stmt->execute([':id' => $post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        // If an image exists, unlink it
        if ($post && !empty($post['image_path'])) {
            $filePath = __DIR__ . '/../../' . $post['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Now delete the post itself
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id");
        return $stmt->execute([':id' => $post_id]);
    }

    // Get posts by specific user
    public function getPostsByUser($user_id) {
        $stmt = $this->db->prepare("
            SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.created_at, 
                   users.username, profiles.avatar_url,
                   (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
            FROM posts
            JOIN users ON posts.user_id = users.id
            LEFT JOIN profiles ON profiles.user_id = users.id
            WHERE posts.user_id = :user_id
            ORDER BY posts.created_at DESC
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete a post by ID, but only if it belongs to this user
    public function deleteByUser($post_id, $user_id) {
        // Fetch the post record and ensure ownership
        $stmt = $this->db->prepare("SELECT image_path FROM posts WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $post_id, ':user_id' => $user_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            return false; // not found or not owned
        }

        // If an image exists, unlink it
        if (!empty($post['image_path'])) {
            $filePath = __DIR__ . '/../../' . $post['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete post
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = :id AND user_id = :user_id");
        return $stmt->execute([':id' => $post_id, ':user_id' => $user_id]);
    }
}