<?php
// includes/models/Database.php

class Database {
    private $host = 'localhost';
    private $db_name = 'Glitch_Social';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8';

    public function connect(): PDO {
        try {
            $pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}",
                $this->username,
                $this->password
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("DB Connection failed: " . $e->getMessage());
        }
    }
}
