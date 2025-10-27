<?php
require_once __DIR__ . '/Database.php'; 
require_once __DIR__ . '/User.php';

class ZionChat {
    private $pdo;

    public function __construct() {
        $db = new Database();
        $this->pdo = $db->connect();
    }

    // Returns IDs of users the viewer blocked OR who have blocked the viewer
    private function getPeerBlockIds(int $viewer_id): array {
        if ($viewer_id <= 0) return [];
        try {
            $sql = "(SELECT blocked_id AS uid FROM blocked_users WHERE blocker_id = :v)
                  UNION
                  (SELECT blocker_id AS uid FROM blocked_users WHERE blocked_id = :v)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':v' => $viewer_id]);
            return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        } catch (Exception $e) {
            return [];
        }
    }

    public function insertMessage($user_id, $content) {
        if (trim($content) === '') {
            return null;
        }
        try {
            $sql = "INSERT INTO zion_messages (user_id, content) VALUES (:uid, :content)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':uid' => $user_id,
                ':content' => $content
            ]);
            $id = (int)$this->pdo->lastInsertId();
            $message = $this->getById($id);
            return $message ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function getById($id) {
        try {
            $sql = "SELECT zm.id, zm.user_id, zm.content, zm.created_at,
                           u.username,
                           p.avatar_url,
                           CONCAT('index.php?page=profile&id=', u.id) AS profile_url
                    FROM zion_messages zm
                    JOIN users u ON u.id = zm.user_id
                    LEFT JOIN profiles p ON p.user_id = zm.user_id
                    WHERE zm.id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    // fetch messages newer than since_id (long-poll friendly)
    public function fetchSinceId($since_id = 0, $limit = 50, $viewer_id = null) {
        try {
            $sql = "SELECT zm.id, zm.user_id, zm.content, zm.created_at,
                           u.username,
                           p.avatar_url,
                           CONCAT('index.php?page=profile&id=', u.id) AS profile_url
                    FROM zion_messages zm
                    JOIN users u ON u.id = zm.user_id
                    LEFT JOIN profiles p ON p.user_id = zm.user_id
                    WHERE zm.id > :sid AND TRIM(zm.content) != ''
                    ORDER BY zm.id ASC
                    LIMIT :lim";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':sid', (int)$since_id, PDO::PARAM_INT);
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $userModel = new User();
            $rows = array_filter($rows, function($msg) use ($userModel) {
                return !$userModel->isBlocked($msg['user_id']);
            });
            if (!is_null($viewer_id)) {
                $peerBlocked = $this->getPeerBlockIds((int)$viewer_id);
                if (!empty($peerBlocked)) {
                    $rows = array_filter($rows, function($msg) use ($peerBlocked) {
                        return !in_array((int)$msg['user_id'], $peerBlocked, true);
                    });
                }
            }
            return $rows ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    // initial load (recent N)
    public function fetchRecentMessages($limit = 50, $viewer_id = null) {
        try {
            $sql = "SELECT zm.id, zm.user_id, zm.content, zm.created_at,
                           u.username,
                           p.avatar_url,
                           CONCAT('index.php?page=profile&id=', u.id) AS profile_url
                    FROM zion_messages zm
                    JOIN users u ON u.id = zm.user_id
                    LEFT JOIN profiles p ON p.user_id = zm.user_id
                    WHERE TRIM(zm.content) != ''
                    ORDER BY zm.id DESC
                    LIMIT :lim";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $userModel = new User();
            $rows = array_filter($rows, function($msg) use ($userModel) {
                return !$userModel->isBlocked($msg['user_id']);
            });
            if (!is_null($viewer_id)) {
                $peerBlocked = $this->getPeerBlockIds((int)$viewer_id);
                if (!empty($peerBlocked)) {
                    $rows = array_filter($rows, function($msg) use ($peerBlocked) {
                        return !in_array((int)$msg['user_id'], $peerBlocked, true);
                    });
                }
            }
            return $rows ? array_reverse($rows) : [];
        } catch (Exception $e) {
            return [];
        }
    }

    public function fetchAllMessages($viewer_id = null) {
        try {
            $sql = "SELECT zm.id, zm.user_id, zm.content, zm.created_at,
                           u.username,
                           p.avatar_url,
                           CONCAT('index.php?page=profile&id=', u.id) AS profile_url
                    FROM zion_messages zm
                    JOIN users u ON u.id = zm.user_id
                    LEFT JOIN profiles p ON p.user_id = zm.user_id
                    ORDER BY zm.id ASC";
            $stmt = $this->pdo->query($sql);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $userModel = new User();
            $rows = array_filter($rows, function($msg) use ($userModel) {
                return !$userModel->isBlocked($msg['user_id']);
            });
            if (!is_null($viewer_id)) {
                $peerBlocked = $this->getPeerBlockIds((int)$viewer_id);
                if (!empty($peerBlocked)) {
                    $rows = array_filter($rows, function($msg) use ($peerBlocked) {
                        return !in_array((int)$msg['user_id'], $peerBlocked, true);
                    });
                }
            }
            return $rows ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}