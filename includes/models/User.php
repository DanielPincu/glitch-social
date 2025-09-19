<?php
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Register a new user
    public function register($username, $password) {
        // Check if username exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetch()) return false; // username exists

        // Insert new user
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        return $stmt->execute([':username' => $username, ':password' => $hashed]);
    }

    // Login user
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = $user['is_admin'];
            return true;
        }
        return false;
    }


    // Block or unblock a user
public function setBlocked($user_id, $blocked) {
    $stmt = $this->db->prepare("UPDATE users SET is_blocked = :blocked WHERE id = :id");
    return $stmt->execute([
        ':blocked' => $blocked,
        ':id' => $user_id
    ]);
}

// Fetch all users
public function getAllUsers() {
    $stmt = $this->db->query("SELECT id, username, is_admin, is_blocked FROM users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function isBlocked($user_id) {
    $stmt = $this->db->prepare("SELECT is_blocked FROM users WHERE id = :id");
    $stmt->bindValue(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch();
    return $user && $user['is_blocked'] == 1;
}
// Set admin status for a user
public function setAdmin(int $user_id, int $is_admin): bool {
    $stmt = $this->db->prepare("UPDATE users SET is_admin = :is_admin WHERE id = :id");
    $stmt->bindValue(':is_admin', $is_admin);
    $stmt->bindValue(':id', $user_id);
    return $stmt->execute();
}

}

