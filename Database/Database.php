<?php
namespace DELIVERY\Database;

use PDO;
use PDOException;

class Database {
    private $host = 'localhost'; // Database host
    private $db = 'delivery';     // Database name
    private $user = 'delivery';   // Database username
    private $pass = 'delivery';   // Database password
    private $charset = 'utf8mb4'; // Character set
    private $pdo = null;          // Initialize as null

    // Method to get the PDO connection
    public function getConnection() {
        if ($this->pdo === null) {
            try {
                // Create a Data Source Name (DSN)
                $dsn = "mysql:host={$this->host};dbname={$this->db};charset={$this->charset}";
                // Create a new PDO instance
                $this->pdo = new PDO($dsn, $this->user, $this->pass);
                // Set the PDO error mode to exception
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // Handle connection error
                die("Error connecting to the database: " . $e->getMessage());
            }
        }
        return $this->pdo; // Return the PDO connection
    }
}
