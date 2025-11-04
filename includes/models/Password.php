<?php
class PasswordModel {
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
        $stmt = $this->pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        return $stmt->execute([$token, $expires, $userId]);
    }

    public function hasActiveResetToken($email) {
        $stmt = $this->pdo->prepare("SELECT reset_expires FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;
        return !empty($user['reset_expires']) && strtotime($user['reset_expires']) > time();
    }

    public function findUserByToken($token) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($userId, $password) {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        return $stmt->execute([$password, $userId]);
    }
}