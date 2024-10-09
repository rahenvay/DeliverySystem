<?php
namespace DELIVERY\Classes;

use DELIVERY\Database\Database;

class User {
    private $email;
    private $password;
    private $fullname;
    private $permission;
    private $db;

    public function __construct($email, $password, $fullname, $permission) {
        $this->email = $email;
        $this->password = $password;
        $this->fullname = $fullname;
        $this->permission = $permission;
        
        // Get the connection from Database class
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Method to validate the full name (no numbers)
    public function isValidFullName() {
        return !preg_match('/\d/', $this->fullname); // Check if the name contains any digits
    }

    // Method to create a new user in the database
    public function createUser() {
        $sql = "INSERT INTO user (email, password, fullname, permission, created_at) VALUES (?, ?, ?, ?, NOW())"; // Updated to 'user'
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;  // Handle preparation error
        }

        // Hash the password
        $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);
        
        // Bind parameters using bindValue
        $stmt->bindValue(1, $this->email, \PDO::PARAM_STR);
        $stmt->bindValue(2, $hashedPassword, \PDO::PARAM_STR);
        $stmt->bindValue(3, $this->fullname, \PDO::PARAM_STR);
        $stmt->bindValue(4, $this->permission, \PDO::PARAM_STR);

        return $stmt->execute();
    }

    // Method to check if a user with the same email or fullname already exists
    public function userExists() {
        $sql = "SELECT id FROM user WHERE email = ? OR fullname = ?"; // Updated to 'user'
        $stmt = $this->db->prepare($sql);
    
        if (!$stmt) {
            return false;  // Handle preparation error
        }
    
        // Bind the email and fullname parameters using bindValue
        $stmt->bindValue(1, $this->email, \PDO::PARAM_STR);
        $stmt->bindValue(2, $this->fullname, \PDO::PARAM_STR);
        $stmt->execute();
    
        // Check if any record exists
        return $stmt->rowCount() > 0;  // If any rows exist, the user already exists
    }
}
