<?php

namespace DELIVERY\Order;

require_once 'Database/Database.php';
use DELIVERY\Database\Database;

class Order {
    private $client_id;
    private $pickup_point;
    private $destination;
    private $price;
    private $status;
    private $conn;

    // Constructor to initialize database connection and properties
    public function __construct($client_id, $pickup_point, $destination, $price) {
        $this->client_id = $client_id;
        $this->pickup_point = $pickup_point;
        $this->destination = $destination;
        $this->price = $price;
        $this->status = 'pending';
        $this->conn = new Database(); // Initialize the database connection
    }

    // Validate the order data (price should be non-negative, etc.)
    public function validate() {
        if ($this->price < 0) {
            return "Price cannot be negative!";
        }
        if (empty($this->client_id) || empty($this->pickup_point) || empty($this->destination)) {
            return "All fields are required!";
        }
        return true;
    }

    // Method to create a new order in the database
    public function createOrder() {
        $validation = $this->validate();
        if ($validation === true) {
            $insertOrderQuery = "INSERT INTO orders (client_id, pickup_point, destination, price, status) 
                                 VALUES (:client_id, :pickup_point, :destination, :price, :status)";
            $stmt = $this->conn->getConnection()->prepare($insertOrderQuery);
            $stmt->bindParam(':client_id', $this->client_id);
            $stmt->bindParam(':pickup_point', $this->pickup_point);
            $stmt->bindParam(':destination', $this->destination);
            $stmt->bindParam(':price', $this->price);
            $stmt->bindParam(':status', $this->status);
            
            if ($stmt->execute()) {
                return "Order created successfully!";
            } else {
                return "Error creating order!";
            }
        } else {
            return $validation; // Return validation errors
        }
    }
}
