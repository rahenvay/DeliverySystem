<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

// Ensure the user is logged in and is a client
if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'client') {
    header('Location: login.php');
    exit;
}

$conn = new Database();
$client_id = $_SESSION['user_id'];

// Fetch client's information
$clientQuery = "SELECT fullname FROM user WHERE id = :client_id";
$stmtClient = $conn->getStarted()->prepare($clientQuery);
$stmtClient->bindParam(':client_id', $client_id);
$stmtClient->execute();
$clientInfo = $stmtClient->fetch(PDO::FETCH_ASSOC);

// Fetch client's orders
$orderQuery = "SELECT * FROM orders WHERE client_id = :client_id";
$stmtOrders = $conn->getStarted()->prepare($orderQuery);
$stmtOrders->bindParam(':client_id', $client_id);
$stmtOrders->execute();
$orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Status</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: #f8f9fa;
        }
        #sidebar {
            height: 100%;
            background-color: #001f3f;
            padding-top: 20px;
            width: 250px;
            position: fixed;
        }
        #sidebar .nav-link {
            color: #fff;
        }
        #sidebar .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
        }
        .client-info {
            color: white;
            padding: 15px;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f0f8ff;
        }
        .table tbody tr:hover {
            background-color: #d3e3f3;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar" class="d-flex flex-column">
    <h4 class="text-white text-center">Client Dashboard</h4>
    <div class="client-info">
        <p><strong>Name:</strong> <?= $clientInfo['fullname'] ?></p>
        <p><strong>ID:</strong> <?= $client_id ?></p>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link active" href="checkStatus.php">Check Status</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="new_order.php">New Order</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="past_order.php">Past Orders</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <h2>Your Order Status</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Pickup Point</th>
                    <th>Destination</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Driver ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['order_id'] ?></td>
                        <td><?= $order['pickup_point'] ?></td>
                        <td><?= $order['destination'] ?></td>
                        <td><?= $order['price'] ?? 'Pending' ?></td>
                        <td><?= $order['status'] ?></td>
                        <td><?= $order['driver_id'] ?? 'Not Assigned' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
