<?php
class Reset {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id, email, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function saveResetToken($userId, $token, $expires) {
        $hashedToken = password_hash($token, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        return $stmt->execute([$hashedToken, $expires, $userId]);
    }

    public function hasActiveResetToken($email) {
        $stmt = $this->pdo->prepare("SELECT reset_expires FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;
        return !empty($user['reset_expires']) && strtotime($user['reset_expires']) > time();
    }

    public function findUserByToken($token) {
        $stmt = $this->pdo->prepare("SELECT id, reset_token FROM users WHERE reset_expires > NOW()");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($users as $user) {
            if (password_verify($token, $user['reset_token'])) {
                return ['id' => $user['id']];
            }
        }
        return false;
    }

    public function updatePassword($userId, $password) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        return $stmt->execute([$password, $userId]);
    }
}