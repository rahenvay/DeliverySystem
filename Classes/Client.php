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

    public function viewOrders() {
        $query = "SELECT * FROM orders WHERE client_id = :client_id";
        $stmt = $this->conn->getConnection()->prepare($query);
        $stmt->bindParam(':client_id', $this->client_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
