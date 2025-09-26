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

        // Handle image upload
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
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
        }

        $stmt = $this->db->prepare("INSERT INTO posts (user_id, content, image_path) VALUES (:user_id, :content, :image_path)");
        return $stmt->execute([
            ':user_id' => $user_id,
            ':content' => $content,
            ':image_path' => $imagePath
        ]);
    }

    // Fetch all posts with user info
    public function fetchAll() {
        $stmt = $this->db->query("
            SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.created_at, users.username
            FROM posts
            JOIN users ON posts.user_id = users.id
            ORDER BY posts.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}