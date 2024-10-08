<?php
namespace DELIVERY\Classes\Admin;

require_once __DIR__ . '/../Database/Database.php';

use DELIVERY\Database\Database;

class Admin {
    private $conn;

    public function __construct(Database $database) { // Accept Database instance
        $this->conn = $database; // Store the Database instance
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
    // Fetch all orders with search and filter options
    public function fetchFilteredOrders($clientName = '', $status = '', $driverId = '', $limit = 7, $offset = 0) {
        $query = "SELECT * FROM orders WHERE 1=1"; // Basic query
        
        // Add search by client name
        if (!empty($clientName)) {
            $query .= " AND client_id IN (SELECT id FROM user WHERE fullname LIKE :clientName)";
        }
        
        // Add filter by status
        if (!empty($status)) {
            $query .= " AND status = :status";
        }
        
        // Add filter by driver
        if (!empty($driverId)) {
            $query .= " AND driver_id = :driverId";
        }
        
        $query .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->getConnection()->prepare($query);
        
        // Bind parameters
        if (!empty($clientName)) {
            $stmt->bindValue(':clientName', '%' . $clientName . '%');
        }
        if (!empty($status)) {
            $stmt->bindValue(':status', $status);
        }
        if (!empty($driverId)) {
            $stmt->bindValue(':driverId', $driverId);
        }
        
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
}
?>
