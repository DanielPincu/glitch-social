<?php
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    
    // Register a new user
    
    public function register($username, $email, $password) {
        // Check if username or email exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        if ($stmt->fetch()) return false; // username or email exists

        // Insert new user
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        return $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':password' => $hashed
        ]);
    }

    
    // Login user
    
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['is_admin']  = $user['is_admin'];
            $_SESSION['email']     = $user['email'];
            return true;
        }
        return false;
    }

    
    // Block or unblock a user (ADMIN-LEVEL account lock)
    
    public function setBlocked($user_id, $blocked) {
        $sql = $blocked == 1
            ? "UPDATE users SET is_blocked = 1, is_admin = 0 WHERE id = :id"
            : "UPDATE users SET is_blocked = 0 WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $user_id]);
    }

    
    // Fetch a single user by ID
    
    public function getUserById($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    // Fetch all users
    
    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, username, email, is_admin, is_blocked FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    // Check if a user is blocked (ADMIN-LEVEL lock)
    
    public function isBlocked($user_id) {
        $stmt = $this->db->prepare("SELECT is_blocked FROM users WHERE id = :id");
        $stmt->bindValue(':id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user && (int)$user['is_blocked'] === 1;
    }

    
    // Set admin status for a user
    
    public function setAdmin($user_id, $is_admin) {
        $stmt = $this->db->prepare("UPDATE users SET is_admin = :is_admin WHERE id = :id");
        $stmt->bindValue(':is_admin', $is_admin);
        $stmt->bindValue(':id', $user_id);
        return $stmt->execute();
    }

    
    // User-to-user block list (social blocking)
    
    public function blockUser($blocker_id, $blocked_id) {
        if ((int)$blocker_id === (int)$blocked_id) return false; // can't block yourself
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO blocked_users (blocker_id, blocked_id)
            VALUES (:blocker_id, :blocked_id)
        ");
        return $stmt->execute([
            ':blocker_id' => $blocker_id,
            ':blocked_id' => $blocked_id
        ]);
    }

    public function unblockUser($blocker_id, $blocked_id) {
        $stmt = $this->db->prepare("
            DELETE FROM blocked_users 
            WHERE blocker_id = :blocker_id AND blocked_id = :blocked_id
        ");
        return $stmt->execute([
            ':blocker_id' => $blocker_id,
            ':blocked_id' => $blocked_id
        ]);
    }

    public function isUserBlocked($blocker_id, $blocked_id) {
        $stmt = $this->db->prepare("
            SELECT 1 FROM blocked_users 
            WHERE blocker_id = :blocker_id AND blocked_id = :blocked_id
            LIMIT 1
        ");
        $stmt->execute([
            ':blocker_id' => $blocker_id,
            ':blocked_id' => $blocked_id
        ]);
        return $stmt->fetchColumn() !== false;
    }

    public function getBlockedUsers($blocker_id) {
        $stmt = $this->db->prepare("
            SELECT users.id, users.username, profiles.avatar_url
            FROM blocked_users
            JOIN users ON users.id = blocked_users.blocked_id
            LEFT JOIN profiles ON profiles.user_id = users.id
            WHERE blocked_users.blocker_id = :blocker_id
            ORDER BY blocked_users.blocked_id DESC
        ");
        $stmt->execute([':blocker_id' => $blocker_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Maybe I scrap this later. Maybe...
    // Alias kept because some controllers may call this older name
    // public function getBlockedUsersByUser($blocker_id) {
    //     return $this->getBlockedUsers($blocker_id);
    // }

    public function isAdmin($user_id) {
        $stmt = $this->db->prepare("SELECT is_admin FROM users WHERE id = :id");
        $stmt->execute([':id' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['is_admin'] === 1;
    }
}