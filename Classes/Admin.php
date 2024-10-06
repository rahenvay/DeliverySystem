<?php
namespace DELIVERY\Classes\Admin;

require_once __DIR__ . '/../Database/Database.php'; // Corrected path

use DELIVERY\Database\Database;

class Admin {
    private $conn;

    public function __construct() {
        $this->conn = new Database(); // Initialize the database connection
    }

    // Fetch all orders from the 'orders' table
    public function fetchAllOrders() {
        $query = "SELECT * FROM orders";
        return $this->conn->getConnection()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Fetch all drivers (users with 'driver' permission) from the 'user' table
    public function fetchAllDrivers() {
        $driverQuery = "SELECT * FROM user WHERE permission = 'driver'";
        return $this->conn->getConnection()->query($driverQuery)->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Count the total number of accounts in the 'user' table
    public function countTotalAccounts() {
        $totalAccountsQuery = "SELECT COUNT(*) as total_accounts FROM user";
        return $this->conn->getConnection()->query($totalAccountsQuery)->fetch(\PDO::FETCH_ASSOC)['total_accounts'];
    }

    // Count the total number of orders in the 'orders' table
    public function countTotalOrders() {
        $totalOrdersQuery = "SELECT COUNT(*) as total_orders FROM orders";
        return $this->conn->getConnection()->query($totalOrdersQuery)->fetch(\PDO::FETCH_ASSOC)['total_orders'];
    }

    // Update the status of an order based on the order ID
    public function updateOrderStatus($order_id, $status) {
        $validStatuses = ['pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'cancelled'];
        
        if (in_array($status, $validStatuses)) {
            $updateStatusQuery = "UPDATE orders SET status = :status WHERE order_id = :order_id";
            $stmt = $this->conn->getConnection()->prepare($updateStatusQuery);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':order_id', $order_id);
            return $stmt->execute();
        } else {
            return false; // Invalid status
        }
    }

    // Assign a driver to an order by updating the driver_id and status
    public function assignDriverToOrder($order_id, $driver_id) {
        $assignDriverQuery = "UPDATE orders SET driver_id = :driver_id, status = 'assigned' WHERE order_id = :order_id";
        $stmt = $this->conn->getConnection()->prepare($assignDriverQuery);
        $stmt->bindParam(':driver_id', $driver_id);
        $stmt->bindParam(':order_id', $order_id);
        return $stmt->execute();
    }
}
