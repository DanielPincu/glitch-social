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
            $stmt = $this->db->prepare("INSERT INTO terms (content, updated_by, updated_at) VALUES (:content, :updated_by, NOW())");
            return $stmt->execute([':content' => $content, ':updated_by' => $adminId]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO terms (content, updated_at) VALUES (:content, NOW())");
            return $stmt->execute([':content' => $content]);
        }
    }
}