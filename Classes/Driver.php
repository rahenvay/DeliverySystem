<?php
namespace DELIVERY\Driver;

use DELIVERY\Database\Database;

class Driver {
    private $driver_id;
    private $conn;

    public function __construct($driver_id) {
        $this->driver_id = $driver_id;
        $this->conn = new Database();
    }

    // Fetch all orders assigned to the driver, excluding 'delivered'
    public function viewAssignedOrders() {
        $query = "
            SELECT o.order_id, o.pickup_point, o.destination, o.status, u.email AS client_email
            FROM orders o
            JOIN user u ON o.client_id = u.id
            WHERE o.driver_id = :driver_id AND o.status != 'delivered'
        ";
        $stmt = $this->conn->getConnection()->prepare($query);
        $stmt->bindParam(':driver_id', $this->driver_id);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Update the status of a specific order
    public function updateOrderStatus($order_id, $status) {
        $updateQuery = "UPDATE orders SET status = :status WHERE order_id = :order_id";
        $stmt = $this->conn->getConnection()->prepare($updateQuery);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();

        // If status is 'delivered', unassign the driver
        if ($status === 'delivered') {
            $unassignDriverQuery = "UPDATE orders SET driver_id = NULL WHERE order_id = :order_id";
            $stmtUnassign = $this->conn->getConnection()->prepare($unassignDriverQuery);
            $stmtUnassign->bindParam(':order_id', $order_id);
            $stmtUnassign->execute();
        }
    }
}
