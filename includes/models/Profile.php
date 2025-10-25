<?php
require_once __DIR__ . '/Database.php';

class Profile {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Fetch profile and user info by user_id
    public function getByUserId($user_id) {
        $stmt = $this->db->prepare("
            SELECT users.id, users.username, users.email,
                   profiles.bio, profiles.avatar_url, profiles.location, profiles.website
            FROM users
            LEFT JOIN profiles ON users.id = profiles.user_id
            WHERE users.id = :user_id
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create or update a profile, keeping existing avatar if not uploading a new one
    public function save($user_id, $bio, $location, $website, $avatar_url = null) {
        if ($avatar_url) {
            $stmt = $this->db->prepare("
                INSERT INTO profiles (user_id, bio, location, website, avatar_url)
                VALUES (:user_id, :bio, :location, :website, :avatar_url)
                ON DUPLICATE KEY UPDATE
                    bio = VALUES(bio),
                    location = VALUES(location),
                    website = VALUES(website),
                    avatar_url = VALUES(avatar_url)
            ");
            $params = [
                ':user_id' => $user_id,
                ':bio' => $bio,
                ':location' => $location,
                ':website' => $website,
                ':avatar_url' => $avatar_url
            ];
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO profiles (user_id, bio, location, website)
                VALUES (:user_id, :bio, :location, :website)
                ON DUPLICATE KEY UPDATE
                    bio = VALUES(bio),
                    location = VALUES(location),
                    website = VALUES(website)
            ");
            $params = [
                ':user_id' => $user_id,
                ':bio' => $bio,
                ':location' => $location,
                ':website' => $website
            ];
        }

        return $stmt->execute($params);
    }
    // Follow another user
    public function followUser($follower_id, $user_id) {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO followers (user_id, follower_id)
            VALUES (:user_id, :follower_id)
        ");
        return $stmt->execute([
            ':user_id' => $user_id,      // the user being followed
            ':follower_id' => $follower_id // the user who follows
        ]);
    }

    // Unfollow a user
    public function unfollowUser($follower_id, $user_id) {
        $stmt = $this->db->prepare("
            DELETE FROM followers
            WHERE user_id = :user_id AND follower_id = :follower_id
        ");
        return $stmt->execute([
            ':user_id' => $user_id,
            ':follower_id' => $follower_id
        ]);
    }

    // Check if a user is following another user
    public function isFollowing($follower_id, $user_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM followers
            WHERE user_id = :user_id AND follower_id = :follower_id
        ");
        $stmt->execute([
            ':user_id' => $user_id,
            ':follower_id' => $follower_id
        ]);
        return $stmt->fetchColumn() > 0;
    }

    // Count how many followers a user has
    public function countFollowers($user_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM followers WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    // Count how many users a person follows
    public function countFollowing($follower_id) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM followers WHERE follower_id = :follower_id
        ");
        $stmt->execute([':follower_id' => $follower_id]);
        return $stmt->fetchColumn();
    }

    public function getFollowingList($user_id) {
    $stmt = $this->db->prepare("
        SELECT users.id, users.username, profiles.avatar_url
        FROM followers
        JOIN users ON followers.user_id = users.id
        LEFT JOIN profiles ON profiles.user_id = users.id
        WHERE followers.follower_id = :user_id
    ");
    $stmt->execute([':user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    // Delete the user's avatar
    public function deleteAvatar($user_id) {
        $stmt = $this->db->prepare("
            UPDATE profiles
            SET avatar_url = NULL
            WHERE user_id = :user_id
        ");
        return $stmt->execute([':user_id' => $user_id]);
    }
}