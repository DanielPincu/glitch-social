<?php

class StatisticsController
{
    private Statistics $statistics;
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->statistics = new Statistics($pdo);
        $this->pdo = $pdo;
    }

    public function index()
    {
        $pdo = $this->pdo;
        $usersPosts = $this->statistics->getUsersAndPosts();
        $likesComments = $this->statistics->getLikesAndComments();
        $topUsers = $this->statistics->getTopActiveUsers();

        require __DIR__ . '/../views/header.php';
        require __DIR__ . '/../views/statistics_view.php';
        require __DIR__ . '/../views/footer.php';
    }
}