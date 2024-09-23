<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'client') {
    header('Location: login.php');
    exit;
}

$conn = new Database();
$client_id = $_SESSION['user_id'];

// Fetch client's orders
$query = "SELECT * FROM orders WHERE client_id = :client_id";
$stmt = $conn->getStarted()->prepare($query);
$stmt->bindParam(':client_id', $client_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Client Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Client Dashboard</h2>

    <h3>Your Orders</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Pickup Point</th>
                <th>Destination</th>
                <th>Price</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td><?= $order['pickup_point'] ?></td>
                    <td><?= $order['destination'] ?></td>
                    <td><?= $order['price'] ?></td>
                    <td><?= $order['status'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
