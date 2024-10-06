<?php
namespace DELIVERY\Database;

use PDO;
use PDOException;

class Database {
    private $host = 'localhost';
    private $db = 'delivery';
    private $user = 'delivery';
    private $pass = 'delivery';
    private $charset = 'utf8mb4';
    private $pdo = null; // Initialize as null

    // Method to get the PDO connection
    public function getConnection() {
        if ($this->pdo === null) {
            try {
                $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
                $this->pdo = new PDO($dsn, $this->user, $this->pass);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // Handle connection error
                die("Error connecting to the database: " . $e->getMessage());
            }
        }
        return $this->pdo; // Return the PDO connection
    }
}
