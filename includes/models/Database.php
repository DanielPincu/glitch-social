<?php

//Søren Feedback: Added secure storage for DB credentials using environment variables.
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;

    public function __construct() {
        $this->host     = $_SERVER['DB_HOST']    ?? 'localhost';
        $this->db_name  = $_SERVER['DB_NAME']    ?? 'Glitch_Social';
        $this->username = $_SERVER['DB_USER']    ?? 'root';
        $this->password = $_SERVER['DB_PASS']    ?? '';
        $this->charset  = $_SERVER['DB_CHARSET'] ?? 'utf8mb4';
    }

    public function connect(): PDO {
        try {
            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}",
                $this->username,
                $this->password
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_bin");
            return $pdo;
        } catch (PDOException $e) {
            die("DB Connection failed: " . $e->getMessage());
        }
    }
}
