

<?php
class Notification {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function countUnreadNotifications($user_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        return (int) $stmt->fetchColumn();
    }

    public function getRecentNotifications($user_id, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT n.*, u.username AS actor_name, p.content AS post_content
            FROM notifications n
            JOIN users u ON n.actor_id = u.id
            LEFT JOIN posts p ON n.post_id = p.id
            WHERE n.user_id = :user_id
            ORDER BY n.id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteAllNotifications($user_id) {
        $stmt = $this->db->prepare("DELETE FROM notifications WHERE user_id = :user_id");
        return $stmt->execute([':user_id' => $user_id]);
    }
}