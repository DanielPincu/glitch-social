<?php
class Terms {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    // Get the latest terms content
    public function getCurrent() {
        $stmt = $this->db->query("SELECT * FROM terms ORDER BY id DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Admin updates the terms text
    public function updateTerms($content, $adminId = null) {
        if ($adminId) {
            $stmt = $this->db->prepare("INSERT INTO terms (content, updated_by) VALUES (:content, :updated_by)");
            return $stmt->execute([':content' => $content, ':updated_by' => $adminId]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO terms (content) VALUES (:content)");
            return $stmt->execute([':content' => $content]);
        }
    }

    // Record that a user accepted the latest terms
    public function recordAcceptance($user_id) {
        $stmt = $this->db->prepare("INSERT INTO user_terms (user_id, accepted_at) VALUES (:user_id, NOW())");
        return $stmt->execute([':user_id' => $user_id]);
    }
}