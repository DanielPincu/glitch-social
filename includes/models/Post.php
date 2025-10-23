<?php
require_once __DIR__ . '/Database.php';

class Post {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Create a new post with optional image and visibility
    public function create($user_id, $content, $file = null, $visibility = 'public') {
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

        $stmt = $this->db->prepare("
            INSERT INTO posts (user_id, content, image_path, visibility)
            VALUES (:user_id, :content, :image_path, :visibility)
        ");

        if ($stmt->execute([
            ':user_id' => $user_id,
            ':content' => $content,
            ':image_path' => $imagePath,
            ':visibility' => $visibility
        ])) {
            return $this->db->lastInsertId(); // return the new post ID
        }

        return false;
    }

    // Notify all followers of a user about a new post
    public function notifyFollowersOfPost($user_id, $post_id) {
        $stmt = $this->db->prepare("SELECT follower_id FROM followers WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $followers = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$followers) return;

        $insert = $this->db->prepare("
            INSERT INTO notifications (user_id, actor_id, post_id, type)
            VALUES (?, ?, ?, 'post')
        ");
        foreach ($followers as $follower_id) {
            $insert->execute([$follower_id, $user_id, $post_id]);
        }
    }

    // Fetch all posts
    // If $isAdmin is true, fetch all posts (no visibility restrictions).
    // Otherwise, filter by visibility for the viewer.
    public function fetchAll($viewer_id = null, $isAdmin = false) {
        if ($isAdmin) {
            $stmt = $this->db->prepare("
                SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.created_at, 
                       users.username, profiles.avatar_url,
                       (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
                FROM posts
                JOIN users ON posts.user_id = users.id
                LEFT JOIN profiles ON profiles.user_id = users.id
                ORDER BY posts.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $this->db->prepare("
                SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.created_at, 
                       users.username, profiles.avatar_url,
                       (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
                FROM posts
                JOIN users ON posts.user_id = users.id
                LEFT JOIN profiles ON profiles.user_id = users.id
                LEFT JOIN followers ON followers.user_id = posts.user_id AND followers.follower_id = :viewer_id
                WHERE
                    posts.visibility = 'public'
                    OR (posts.visibility = 'followers' AND followers.follower_id IS NOT NULL)
                    OR (posts.visibility = 'private' AND posts.user_id = :viewer_id)
                ORDER BY posts.created_at DESC
            ");
            $stmt->execute([':viewer_id' => $viewer_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
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

    // Add a new comment to a post
    public function addComment($post_id, $user_id, $content) {
        $stmt = $this->db->prepare("
            INSERT INTO comments (post_id, user_id, content)
            VALUES (:post_id, :user_id, :content)
        ");
        return $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id,
            ':content' => $content
        ]);
    }

    // Fetch all comments for a specific post
    public function getComments($post_id) {
        $stmt = $this->db->prepare("
            SELECT comments.id, comments.user_id, comments.content, comments.created_at,
                   users.username, profiles.avatar_url
            FROM comments
            JOIN users ON comments.user_id = users.id
            LEFT JOIN profiles ON profiles.user_id = users.id
            WHERE comments.post_id = :post_id
            ORDER BY comments.created_at ASC
        ");
        $stmt->execute([':post_id' => $post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    // Update an existing comment (only if owned by the user)
public function updateComment($comment_id, $user_id, $new_content) {
    $stmt = $this->db->prepare("
        UPDATE comments 
        SET content = :content 
        WHERE id = :comment_id AND user_id = :user_id
    ");
    return $stmt->execute([
        ':content' => $new_content,
        ':comment_id' => $comment_id,
        ':user_id' => $user_id
    ]);
}

// Delete a comment if authorized: comment owner, post owner, or admin
public function deleteComment($comment_id, $user_id, $isAdmin = false) {
    // Fetch the comment
    $comment = $this->getCommentById($comment_id);
    if (!$comment) {
        return false; // Comment not found
    }
    // Fetch the post
    $post = $this->getPostById($comment['post_id']);
    if (!$post) {
        return false; // Post not found
    }
    // Check authorization
    if (
        $comment['user_id'] == $user_id ||
        $post['user_id'] == $user_id ||
        $isAdmin
    ) {
        $stmt = $this->db->prepare("
            DELETE FROM comments
            WHERE id = :comment_id
        ");
        return $stmt->execute([
            ':comment_id' => $comment_id
        ]);
    }
    // Not authorized
    return false;
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
    public function getPostsByUser($user_id, $viewer_id = null) {
        $query = "
            SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.visibility, posts.created_at,
                   users.username, profiles.avatar_url,
                   (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
            FROM posts
            JOIN users ON posts.user_id = users.id
            LEFT JOIN profiles ON profiles.user_id = users.id
        ";

        // Viewer is the same user OR no viewer (e.g. settings/profile page)
        if ($viewer_id === null || $viewer_id === $user_id) {
            $query .= " WHERE posts.user_id = :user_id";
        } else {
            // Viewer is someone else (apply visibility rules)
            $query .= "
                WHERE posts.user_id = :user_id
                AND (
                    posts.visibility = 'public'
                    OR (
                        posts.visibility = 'followers'
                        AND (
                            EXISTS (
                                SELECT 1 FROM followers
                                WHERE followers.user_id = :user_id
                                AND followers.follower_id = :viewer_id
                            )
                            OR posts.user_id = :viewer_id
                        )
                    )
                )
            ";
        }

        $query .= " ORDER BY posts.created_at DESC";

        $stmt = $this->db->prepare($query);
        $params = [':user_id' => $user_id];
        if ($viewer_id !== null && $viewer_id !== $user_id) {
            $params[':viewer_id'] = $viewer_id;
        }

        $stmt->execute($params);
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

    // Update a post’s content, image, and optionally its visibility (only if it belongs to the user)
    public function updateContent($post_id, $new_content, $user_id, $new_image_path = null, $remove_image = false, $visibility = null) {
        $sql = "UPDATE posts SET content = :content";
        $params = [
            ':content' => $new_content,
            ':post_id' => $post_id,
            ':user_id' => $user_id
        ];

        // Handle image update or removal
        if ($remove_image) {
            $sql .= ", image_path = NULL";
        } elseif (!empty($new_image_path)) {
            $sql .= ", image_path = :image_path";
            $params[':image_path'] = $new_image_path;
        }

        // Handle visibility update if provided
        if ($visibility !== null) {
            $sql .= ", visibility = :visibility";
            $params[':visibility'] = $visibility;
        }

        $sql .= " WHERE id = :post_id AND user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // Get posts from users that the current user follows, filtered by visibility (updated to allow user to see their own followers-only posts)
    public function getPostsFromFollowing($user_id, $viewer_id) {
    $query = "
        SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.visibility, posts.created_at,
               users.username, profiles.avatar_url,
               (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
        FROM posts
        JOIN users ON posts.user_id = users.id
        LEFT JOIN profiles ON profiles.user_id = users.id
        LEFT JOIN followers ON followers.user_id = posts.user_id AND followers.follower_id = :viewer_id
        LEFT JOIN blocked_users AS viewer_blocks ON viewer_blocks.blocker_id = :viewer_id AND viewer_blocks.blocked_id = posts.user_id
        LEFT JOIN blocked_users AS author_blocks ON author_blocks.blocker_id = posts.user_id AND author_blocks.blocked_id = :viewer_id
        WHERE posts.user_id IN (SELECT user_id FROM followers WHERE follower_id = :user_id)
        AND (
            posts.visibility = 'public'
            OR (posts.visibility = 'followers' AND followers.follower_id IS NOT NULL)
        )
        AND viewer_blocks.id IS NULL
        AND author_blocks.id IS NULL
        AND posts.user_id != :viewer_id
        ORDER BY posts.created_at DESC
    ";

    $stmt = $this->db->prepare($query);
    $stmt->execute([':user_id' => $user_id, ':viewer_id' => $viewer_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Get posts filtered by visibility for a viewer
    public function getPosts($viewer_id) {
        $stmt = $this->db->prepare("
            SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.visibility, posts.created_at, 
                   users.username, profiles.avatar_url,
                   (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
            FROM posts
            JOIN users ON posts.user_id = users.id
            LEFT JOIN profiles ON profiles.user_id = users.id
            LEFT JOIN followers ON followers.user_id = posts.user_id AND followers.follower_id = :viewer_id
            LEFT JOIN blocked_users AS viewer_blocks ON viewer_blocks.blocker_id = :viewer_id AND viewer_blocks.blocked_id = posts.user_id
            LEFT JOIN blocked_users AS author_blocks ON author_blocks.blocker_id = posts.user_id AND author_blocks.blocked_id = :viewer_id
            WHERE
                posts.visibility = 'public'
                OR (posts.user_id = :viewer_id)
                OR (posts.visibility = 'followers' AND (followers.follower_id IS NOT NULL OR posts.user_id = :viewer_id))
            AND viewer_blocks.id IS NULL
            AND author_blocks.id IS NULL
            ORDER BY posts.created_at DESC
        ");
        $stmt->execute([':viewer_id' => $viewer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Fetch a single comment by its ID
    public function getCommentById($comment_id) {
        $stmt = $this->db->prepare("
            SELECT id, post_id, user_id, content, created_at
            FROM comments
            WHERE id = :comment_id
            LIMIT 1
        ");
        $stmt->execute([':comment_id' => $comment_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch a single post by its ID
    public function getPostById($post_id) {
        $stmt = $this->db->prepare("
            SELECT id, user_id, content, created_at, image_path, visibility
            FROM posts
            WHERE id = :post_id
            LIMIT 1
        ");
        $stmt->execute([':post_id' => $post_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Count unread notifications for a user
    public function countUnreadNotifications($user_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $user_id]);
        return (int) $stmt->fetchColumn();
    }

    // Fetch the 10 most recent notifications for a user
    public function getRecentNotifications($user_id) {
        $stmt = $this->db->prepare("
            SELECT n.*, u.username AS actor_name, p.content AS post_content
            FROM notifications n
            JOIN users u ON n.actor_id = u.id
            LEFT JOIN posts p ON n.post_id = p.id
            WHERE n.user_id = :user_id
            ORDER BY n.id DESC
            LIMIT 10
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Delete all notifications for a user
    public function deleteAllNotifications($user_id) {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $user_id]);
    }
}