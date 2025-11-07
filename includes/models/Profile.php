<?php

class Profile {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fetch profile and user info by user_id
    public function getByUserId($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT users.id, users.username, users.email,
                   profiles.bio, profiles.avatar_url, profiles.location, profiles.website
            FROM users
            LEFT JOIN profiles ON users.id = profiles.user_id
            WHERE users.id = :user_id
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Create or update a profile, keeping existing avatar if not uploading a new one, and update email
    public function save($user_id, $bio, $location, $website, $avatar_url = null, $email = null) {
        try {
            $this->pdo->beginTransaction();

            // Update the users table for email
            if ($email !== null) {
                $stmtUser = $this->pdo->prepare("
                    UPDATE users SET email = :email WHERE id = :user_id
                ");
                $stmtUser->execute([
                    ':email' => $email,
                    ':user_id' => $user_id
                ]);
            }

            // Update or insert into profiles table
            if ($avatar_url) {
                $stmtProfile = $this->pdo->prepare("
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
                $stmtProfile = $this->pdo->prepare("
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

            $stmtProfile->execute($params);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Profile save error: " . $e->getMessage());
            return false;
        }
    }
    // Follow another user
    public function followUser($follower_id, $user_id) {
        $stmt = $this->pdo->prepare("
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
        $stmt = $this->pdo->prepare("
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
        $stmt = $this->pdo->prepare("
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
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM followers WHERE user_id = :user_id
        ");
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchColumn();
    }

    // Count how many users a person follows
    public function countFollowing($follower_id) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM followers WHERE follower_id = :follower_id
        ");
        $stmt->execute([':follower_id' => $follower_id]);
        return $stmt->fetchColumn();
    }

    public function getFollowingList($user_id) {
    $stmt = $this->pdo->prepare("
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
        $stmt = $this->pdo->prepare("
            UPDATE profiles
            SET avatar_url = NULL
            WHERE user_id = :user_id
        ");
        return $stmt->execute([':user_id' => $user_id]);
    }
}