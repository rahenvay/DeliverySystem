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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pickup_point = $_POST['pickup_point'];
    $destination = $_POST['destination'];
    
    // Default price set to 0.00 when creating a new order
    $default_price = 0.00;

    // Insert the new order into the database with default price
    $insertOrderQuery = "INSERT INTO orders (client_id, pickup_point, destination, price, status) VALUES (:client_id, :pickup_point, :destination, :price, 'pending')";
    $stmtInsert = $conn->getStarted()->prepare($insertOrderQuery);
    $stmtInsert->bindParam(':client_id', $client_id);
    $stmtInsert->bindParam(':pickup_point', $pickup_point);
    $stmtInsert->bindParam(':destination', $destination);
    $stmtInsert->bindParam(':price', $default_price);
    $stmtInsert->execute();

    echo "<div class='alert alert-success'>New order created successfully!</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Order</title>
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
            color: #fff;
            padding: 15px;
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
            <a class="nav-link" href="checkStatus.php">Check Status</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="new_order.php">New Order</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="past_order.php">Past Orders</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <h2>Create New Order</h2>
        <form method="POST">
            <div class="form-group">
                <label for="pickup_point">Pickup Point</label>
                <input type="text" name="pickup_point" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="destination">Destination</label>
                <input type="text" name="destination" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Submit Order</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
