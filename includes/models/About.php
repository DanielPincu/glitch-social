<?php

class About {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fetch existing about content (latest entry)
    public function getCurrent() {
        $sql = "SELECT * FROM about ORDER BY id DESC LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update or insert new about text
    public function updateAbout($content, $admin_id) {
        $sql = "INSERT INTO about (content, updated_by, updated_at) VALUES (:content, :updated_by, NOW())";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':content' => $content,
            ':updated_by' => $admin_id
        ]);
    }
}