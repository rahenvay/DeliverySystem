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

    // Method to create a new user in the database
    public function createUser() {
        $sql = "INSERT INTO users (email, password, fullname, permission, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);

        if (!$stmt) {
            return false;  // Handle preparation error
        }

        // Bind parameters and hash the password
        $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);
        $stmt->bind_param('ssss', $this->email, $hashedPassword, $this->fullname, $this->permission);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
