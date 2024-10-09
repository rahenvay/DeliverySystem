<?php

namespace DELIVERY\Order;

require_once '../Database/Database.php';

use DELIVERY\Database\Database;
use PDO; // Add this line to import the PDO class

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

    // Method to update the order status to 'delivered'
    public function updateOrderStatus($order_id) {
        // Fetch the driver's name based on the driver's ID from the orders table
        $orderQuery = "SELECT driver_id FROM orders WHERE order_id = :order_id";
        $stmtOrder = $this->conn->getConnection()->prepare($orderQuery);
        $stmtOrder->bindParam(':order_id', $order_id);
        $stmtOrder->execute();
        $orderInfo = $stmtOrder->fetch(PDO::FETCH_ASSOC);

        if ($orderInfo) {
            $driver_id = $orderInfo['driver_id'];

            // Now fetch the driver's name from the user table
            $driverQuery = "SELECT fullname FROM user WHERE id = :driver_id AND permission = 'driver'";
            $stmtDriver = $this->conn->getConnection()->prepare($driverQuery);
            $stmtDriver->bindParam(':driver_id', $driver_id);
            $stmtDriver->execute();
            $driverInfo = $stmtDriver->fetch(PDO::FETCH_ASSOC);

            if ($driverInfo) {
                $driver_name = $driverInfo['fullname'];

                // Update the order status to 'delivered'
                $updateOrderQuery = "UPDATE orders SET status = 'delivered' WHERE order_id = :order_id";
                $stmtUpdate = $this->conn->getConnection()->prepare($updateOrderQuery);
                $stmtUpdate->bindParam(':order_id', $order_id);

                if ($stmtUpdate->execute()) {
                    return "Order status updated to delivered by " . htmlspecialchars($driver_name);
                } else {
                    return "Error updating order status.";
                }
            } else {
                return "Error: Driver not found or permission not valid.";
            }
        } else {
            return "Error: Order not found.";
        }
    }
}
