<?php

class Statistics
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getUsersAndPosts(): array
    {
        $query = $this->pdo->query("
            SELECT total_users, total_posts 
            FROM view_total_users_posts
        ");
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getLikesAndComments(): array
    {
        $query = $this->pdo->query("
            SELECT total_likes, total_comments
            FROM view_total_likes_comments
        ");
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    public function getTopActiveUsers(): array
    {
        $query = $this->pdo->query("
            SELECT * FROM view_top3_active_users
        ");
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}