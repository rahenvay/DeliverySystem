<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = new Database();

// Fetch all clients
$clientQuery = "SELECT * FROM user WHERE permission = 'client'";
$clients = $conn->getStarted()->query($clientQuery)->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission to create a new order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = $_POST['client_id'];
    $pickup_point = $_POST['pickup_point'];
    $destination = $_POST['destination'];
    $price = $_POST['price'];

    // Insert the new order into the database
    $insertOrderQuery = "INSERT INTO orders (client_id, pickup_point, destination, price, status) 
                         VALUES (:client_id, :pickup_point, :destination, :price, 'pending')";
    $stmt = $conn->getStarted()->prepare($insertOrderQuery);
    $stmt->bindParam(':client_id', $client_id);
    $stmt->bindParam(':pickup_point', $pickup_point);
    $stmt->bindParam(':destination', $destination);
    $stmt->bindParam(':price', $price);
    $stmt->execute();

    echo "<div class='alert alert-success'>Order created successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Order</title>
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
            width: 100%;
        }
        #sidebar .nav-link {
            color: #fff;
        }
        #sidebar .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        @media (min-width: 768px) {
            #sidebar {
                width: 250px;
                position: fixed;
            }
            .content {
                margin-left: 260px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar" class="d-flex flex-column">
    <h4 class="text-white text-center">Admin Dashboard</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="admin.php">Manage Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="assign_driver.php">Assign Driver</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="create_order.php">Create Order</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <h2>Create New Order</h2>
        <form method="POST" class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="client_id">Select Client:</label>
                        <select name="client_id" id="client_id" class="form-control" required>
                            <option value="">Choose Client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= $client['fullname'] ?> (ID: <?= $client['id'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt-3 mt-md-0">
                        <label for="pickup_point">Pickup Point:</label>
                        <input type="text" name="pickup_point" id="pickup_point" class="form-control" placeholder="Enter Pickup Point" required>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="destination">Destination:</label>
                        <input type="text" name="destination" id="destination" class="form-control" placeholder="Enter Destination" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mt-3 mt-md-0">
                        <label for="price">Price (Baht):</label>
                        <input type="number" name="price" id="price" class="form-control" placeholder="Enter Price" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-4">Create Order</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
