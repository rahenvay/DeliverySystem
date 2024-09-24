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

// Fetch client info
$clientQuery = "SELECT fullname FROM user WHERE id = :client_id";
$stmtClient = $conn->getStarted()->prepare($clientQuery);
$stmtClient->bindParam(':client_id', $client_id);
$stmtClient->execute();
$clientInfo = $stmtClient->fetch(PDO::FETCH_ASSOC);

// Fetch all delivered orders for this client
$query = "SELECT * FROM orders WHERE client_id = :client_id AND status = 'delivered'";
$stmt = $conn->getStarted()->prepare($query);
$stmt->bindParam(':client_id', $client_id);
$stmt->execute();
$pastOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Orders</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: row;
            background-color: #f8f9fa;
        }
        #sidebar {
            background-color: #001f3f;
            padding-top: 20px;
            min-height: 100vh;
        }
        #sidebar .nav-link {
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
        }
        #sidebar .nav-link:hover {
            background-color: #007bff;
            color: white;
        }
        #sidebar .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .client-info {
            color: #fff;
            padding: 15px;
            border-bottom: 1px solid #007bff;
            text-align: center;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
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
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            #sidebar {
                min-height: auto;
            }
            .content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar" class="d-flex flex-column col-md-3 col-lg-2 p-3">
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
            <a class="nav-link" href="past_order.php">Past Orders</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content col-md-9 ms-sm-auto col-lg-10">
    <div class="container mt-4">
        <h2>Past Orders</h2>
        <table class="table table-hover table-bordered">
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
                <?php foreach ($pastOrders as $order): ?>
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
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
