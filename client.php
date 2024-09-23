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

// Fetch client's information
$clientQuery = "SELECT fullname FROM user WHERE id = :client_id";
$stmtClient = $conn->getStarted()->prepare($clientQuery);
$stmtClient->bindParam(':client_id', $client_id);
$stmtClient->execute();
$clientInfo = $stmtClient->fetch(PDO::FETCH_ASSOC);

// Fetch client's orders
$query = "SELECT * FROM orders WHERE client_id = :client_id";
$stmt = $conn->getStarted()->prepare($query);
$stmt->bindParam(':client_id', $client_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle new order form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $pickup_point = $_POST['pickup_point'];
    $destination = $_POST['destination'];
    $delivery_date = $_POST['delivery_date'];

    $insertOrderQuery = "INSERT INTO orders (client_id, fullname, pickup_point, destination, delivery_date, status) VALUES (:client_id, :fullname, :pickup_point, :destination, :delivery_date, 'pending')";
    $stmtInsert = $conn->getStarted()->prepare($insertOrderQuery);
    $stmtInsert->bindParam(':client_id', $client_id);
    $stmtInsert->bindParam(':fullname', $fullname);
    $stmtInsert->bindParam(':pickup_point', $pickup_point);
    $stmtInsert->bindParam(':destination', $destination);
    $stmtInsert->bindParam(':delivery_date', $delivery_date);
    $stmtInsert->execute();

    echo "<div class='alert alert-success'>New order created successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }
        #sidebar {
            height: 100%;
            background-color: #343a40;
            padding-top: 20px;
            width: 250px;
            position: fixed;
        }
        #sidebar .nav-link {
            color: #fff;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
        }
        .client-info {
            color: white;
            margin-left: 15px;
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
            <a class="nav-link" href="past_orders.php">Past Orders</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <h2>Your Orders</h2>
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
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
