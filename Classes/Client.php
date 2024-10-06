<?php

namespace DELIVERY\Client;

use DELIVERY\Database\Database;
use \PDO; 

class Client {
    private $conn;
    private $client_id;

    public function __construct($client_id) {
        $this->conn = new Database();
        $this->client_id = $client_id;
    }

    public function getClientInfo() {
        $clientQuery = "SELECT fullname FROM user WHERE id = :client_id";
        $stmtClient = $this->conn->getConnection()->prepare($clientQuery);
        $stmtClient->bindParam(':client_id', $this->client_id);
        $stmtClient->execute();
        return $stmtClient->fetch(PDO::FETCH_ASSOC);
    }

    public function viewOrders($limit, $offset) {
        $query = "SELECT * FROM orders WHERE client_id = :client_id LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->getConnection()->prepare($query);
        $stmt->bindParam(':client_id', $this->client_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalOrdersCount() {
        $query = "SELECT COUNT(*) as total FROM orders WHERE client_id = :client_id";
        $stmt = $this->conn->getConnection()->prepare($query);
        $stmt->bindParam(':client_id', $this->client_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
