<?php
session_start();
require_once '../Classes/Order.php';  // Include the Order class
use DELIVERY\Order\Order;

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../Database/Database.php';
$conn = new \DELIVERY\Database\Database();

// Fetch all clients
$clientQuery = "SELECT * FROM user WHERE permission = 'client'";
$clients = $conn->getConnection()->query($clientQuery)->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission to create a new order
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = $_POST['client_id'];
    $pickup_point = $_POST['pickup_point'];
    $destination = $_POST['destination'];
    $price = $_POST['price'];

    // Validate the price
    if (!is_numeric($price) || $price < 0 || $price > 1000000) { // Change the upper limit as necessary
        $error_message = "Please enter a reasonable price (0 - 1,000,000 Baht).";
    } else {
        // Use the Order class to create a new order
        $order = new Order($client_id, $pickup_point, $destination, $price);
        $result = $order->createOrder(); // Create the order and get the result message

        echo "<div class='alert alert-" . (strpos($result, 'success') !== false ? 'success' : 'danger') . "'>$result</div>";
    }
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
        <li class="nav-item">
            <a class="nav-link" href="create_user.php">Create User</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="user_overview.php">User Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="?action=logout">Sign Out</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <h2>Create New Order</h2>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <form method="POST" class="mt-4">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="client_id">Select Client:</label>
                        <select name="client_id" id="client_id" class="form-control" required>
                            <option value="">Choose Client</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= htmlspecialchars($client['id']) ?>"><?= htmlspecialchars($client['fullname']) ?> (ID: <?= htmlspecialchars($client['id']) ?>)</option>
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
                        <input type="number" name="price" id="price" class="form-control" placeholder="Enter Price" required min="0">
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
