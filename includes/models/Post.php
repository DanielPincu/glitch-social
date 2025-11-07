<?php

class Post {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Create a new post with given image path and visibility
    public function create($user_id, $content, $imagePath = null, $visibility = 'public') {
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (user_id, content, image_path, visibility)
            VALUES (:user_id, :content, :image_path, :visibility)
        ");
        if ($stmt->execute([
            ':user_id' => $user_id,
            ':content' => $content,
            ':image_path' => $imagePath,
            ':visibility' => $visibility
        ])) {
            return $this->pdo->lastInsertId(); // return the new post ID
        }
        return false;
    }


    // Fetch all posts
    // If $isAdmin is true, fetch all posts (no visibility restrictions).
    // Otherwise, filter by visibility for the viewer.
    public function fetchAll($viewer_id = null, $isAdmin = false) {
        if ($isAdmin) {
            $stmt = $this->pdo->prepare("
                SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.is_pinned, posts.created_at, 
                       users.username, profiles.avatar_url,
                       (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
                FROM posts
                JOIN users ON posts.user_id = users.id
                LEFT JOIN profiles ON profiles.user_id = users.id
                ORDER BY posts.is_pinned DESC, posts.created_at DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $this->pdo->prepare("
                SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.is_pinned, posts.created_at, 
                       users.username, profiles.avatar_url,
                       (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
                FROM posts
                JOIN users ON posts.user_id = users.id
                LEFT JOIN profiles ON profiles.user_id = users.id
                LEFT JOIN followers ON followers.user_id = posts.user_id AND followers.follower_id = :viewer_id
                LEFT JOIN blocked_users AS viewer_blocks ON viewer_blocks.blocker_id = :viewer_id AND viewer_blocks.blocked_id = posts.user_id
                LEFT JOIN blocked_users AS author_blocks ON author_blocks.blocker_id = posts.user_id AND author_blocks.blocked_id = :viewer_id
                WHERE (
                    posts.visibility = 'public'
                    OR (posts.visibility = 'followers' AND followers.follower_id IS NOT NULL)
                    OR (posts.visibility = 'private' AND posts.user_id = :viewer_id)
                )
                AND viewer_blocks.id IS NULL
                AND author_blocks.id IS NULL
                ORDER BY posts.is_pinned DESC, posts.created_at DESC
            ");
            $stmt->execute([':viewer_id' => $viewer_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Like a post
    public function like($post_id, $user_id) {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO likes (post_id, user_id) VALUES (:post_id, :user_id)");
        return $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id
        ]);
    }

    // Unlike a post
    public function unlike($post_id, $user_id) {
        $stmt = $this->pdo->prepare("DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id");
        return $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id
        ]);
    }

    // Check if user has liked a post
    public function hasLiked($post_id, $user_id) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM likes WHERE post_id = :post_id AND user_id = :user_id LIMIT 1");
        $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id
        ]);
        return $stmt->fetchColumn() !== false;
    }

    // Add a new comment to a post
    public function addComment($post_id, $user_id, $content) {
        $stmt = $this->pdo->prepare("
            INSERT INTO comments (post_id, user_id, content)
            VALUES (:post_id, :user_id, :content)
        ");
        return $stmt->execute([
            ':post_id' => $post_id,
            ':user_id' => $user_id,
            ':content' => $content
        ]);
    }

    // Fetch all comments for a specific post, excluding comments by admin-blocked users
    public function getComments($post_id) {
        $stmt = $this->pdo->prepare("
            SELECT comments.id, comments.user_id, comments.content, comments.created_at,
                   users.username, profiles.avatar_url
            FROM comments
            JOIN users ON comments.user_id = users.id
            LEFT JOIN profiles ON profiles.user_id = users.id
            WHERE comments.post_id = :post_id
              AND users.is_blocked = 0
            ORDER BY comments.created_at ASC
        ");
        $stmt->execute([':post_id' => $post_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    // Update an existing comment 
public function updateComment($comment_id, $user_id, $new_content) {
    $stmt = $this->pdo->prepare("
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

// Delete a comment by its ID (authorization handled in controller)
public function deleteCommentById($comment_id) {
    $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = :comment_id");
    return $stmt->execute([':comment_id' => $comment_id]);
}



    // Get like count for a post
    public function getLikeCount($post_id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post_id");
        $stmt->execute([
            ':post_id' => $post_id
        ]);
        return (int)$stmt->fetchColumn();
    }
    // Delete a post by ID
    public function delete($post_id) {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        return $stmt->execute([':id' => $post_id]);
    }

    // Get posts by specific user
    public function getPostsByUser($user_id, $viewer_id = null) {
        $query = "
            SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.visibility, posts.is_pinned, posts.created_at,
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
                LEFT JOIN blocked_users AS viewer_blocks ON viewer_blocks.blocker_id = :viewer_id AND viewer_blocks.blocked_id = posts.user_id
                LEFT JOIN blocked_users AS author_blocks ON author_blocks.blocker_id = posts.user_id AND author_blocks.blocked_id = :viewer_id
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
                AND viewer_blocks.id IS NULL
                AND author_blocks.id IS NULL
            ";
        }

        $query .= " ORDER BY posts.is_pinned DESC, posts.created_at DESC";

        $params = [':user_id' => $user_id];
        if ($viewer_id !== null && $viewer_id !== $user_id) {
            $params[':viewer_id'] = $viewer_id;
        }

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Delete a post by its ID (authorization handled in controller)
    public function deletePostById($post_id) {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        return $stmt->execute([':id' => $post_id]);
    }

    // Update specific fields for a post (controller decides what fields to send)
    public function updateFields($post_id, array $fields) {
        if (empty($fields)) {
            return false;
        }

        $setParts = [];
        $params = [':post_id' => $post_id];

        foreach ($fields as $column => $value) {
            $placeholder = ':' . $column;
            $setParts[] = "$column = $placeholder";
            $params[$placeholder] = $value;
        }

        $sql = "UPDATE posts SET " . implode(', ', $setParts) . " WHERE id = :post_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    // Get posts from users that the current user follows, filtered by visibility (updated to allow user to see their own followers-only posts)
    public function getPostsFromFollowing($user_id, $viewer_id) {
    $query = "
        SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.visibility, posts.is_pinned, posts.created_at,
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
        ORDER BY posts.is_pinned DESC, posts.created_at DESC
    ";

    $stmt = $this->pdo->prepare($query);
    $stmt->execute([':user_id' => $user_id, ':viewer_id' => $viewer_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Get posts filtered by visibility for a viewer
    public function getPosts($viewer_id) {
        $stmt = $this->pdo->prepare("
            SELECT posts.id, posts.user_id, posts.content, posts.image_path, posts.visibility, posts.is_pinned, posts.created_at, 
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
            ORDER BY posts.is_pinned DESC, posts.created_at DESC
        ");
        $stmt->execute([':viewer_id' => $viewer_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Fetch a single comment by its ID
    public function getCommentById($comment_id) {
        $stmt = $this->pdo->prepare("
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
        $stmt = $this->pdo->prepare("
            SELECT id, user_id, content, created_at, image_path, visibility
            FROM posts
            WHERE id = :post_id
            LIMIT 1
        ");
        $stmt->execute([':post_id' => $post_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Toggle a post's pin status
    public function togglePin($post_id, $user_id, $is_pinned) {
        $stmt = $this->pdo->prepare("
            UPDATE posts 
            SET is_pinned = :is_pinned 
            WHERE id = :post_id AND user_id = :user_id
        ");
        return $stmt->execute([
            ':is_pinned' => $is_pinned,
            ':post_id' => $post_id,
            ':user_id' => $user_id
        ]);
    }
}