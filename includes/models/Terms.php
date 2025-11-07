<?php
class Terms {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get the latest terms content
    public function getCurrent() {
        $stmt = $this->pdo->query("SELECT * FROM terms ORDER BY id DESC LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Admin updates the terms text
    public function updateTerms($content, $adminId = null) {
        if ($adminId) {
            $stmt = $this->pdo->prepare("INSERT INTO terms (content, updated_by, updated_at) VALUES (:content, :updated_by, NOW())");
            return $stmt->execute([':content' => $content, ':updated_by' => $adminId]);
        } else {
            $stmt = $this->pdo->prepare("INSERT INTO terms (content, updated_at) VALUES (:content, NOW())");
            return $stmt->execute([':content' => $content]);
        }
    }
}