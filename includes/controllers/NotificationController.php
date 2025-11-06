<?php

class NotificationController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    public function countUnreadNotifications($userId)
    {
        return $this->notificationModel->countUnreadNotifications($userId);
    }

    public function getRecentNotifications($userId, $limit = 10)
    {
        return $this->notificationModel->getRecentNotifications($userId, $limit);
    }

    public function deleteAllNotifications($userId)
    {
        return $this->notificationModel->deleteAllNotifications($userId);
    }

    public function handleActions() {
        if (isset($_GET['action']) && $_GET['action'] === 'delete_all' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'] ?? null;
            if ($userId) {
                $this->notificationModel->deleteAllNotifications($userId);
                echo 'success';
            } else {
                echo 'error';
            }
            exit;
        }
    }
}
