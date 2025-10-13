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

    // Create or update a profile
    public function save($user_id, $bio, $location, $website, $avatar_url = null) {
        $stmt = $this->db->prepare("
            INSERT INTO profiles (user_id, bio, location, website, avatar_url)
            VALUES (:user_id, :bio, :location, :website, :avatar_url)
            ON DUPLICATE KEY UPDATE
                bio = VALUES(bio),
                location = VALUES(location),
                website = VALUES(website),
                avatar_url = VALUES(avatar_url)
        ");
        return $stmt->execute([
            ':user_id' => $user_id,
            ':bio' => $bio,
            ':location' => $location,
            ':website' => $website,
            ':avatar_url' => $avatar_url
        ]);
    }
}