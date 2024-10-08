<?php
session_start();
require_once 'Database/Database.php';
require_once 'Classes/Driver.php'; // Update the path to match the correct file location

use DELIVERY\Driver\Driver; // Update the namespace to match the correct one

// Check if the user is logged in as a driver
if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'driver') {
    header('Location: login.php');
    exit;
}

// Initialize the driver object with the logged-in driver's ID
$driver = new Driver($_SESSION['user_id']);

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Update the order status via the Driver class
    $driver->updateOrderStatus($order_id, $status);
    
    // Debugging output after updating
    echo "<div class='alert alert-success'>Order status updated to: $status for Order ID: $order_id</div>";
}

// Fetch the driver's assigned orders via the Driver class
$orders = $driver->viewAssignedOrders();

// Fetch past deliveries via the Driver class
$pastDeliveries = $driver->viewPastDeliveries(); // New line to fetch past deliveries

// Handle sign out
if (isset($_POST['sign_out'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
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
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-sign-out {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Assigned Orders</h2>

    <div class="text-end">
        <form method="POST" class="mb-3">
            <button type="submit" name="sign_out" class="btn btn-sign-out">Sign Out</button>
        </form>
    </div>

    <!-- Current Assigned Orders -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Pickup Point</th>
                    <th>Destination</th>
                    <th>Client Email</th>
                    <th>Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['pickup_point']) ?></td>
                            <td><?= htmlspecialchars($order['destination']) ?></td>
                            <td><?= htmlspecialchars($order['client_email']) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                                    <select name="status" class="form-select">
                                        <option value="picked_up">Picked Up</option>
                                        <option value="in_transit">In Transit</option>
                                        <option value="delivered">Delivered</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary mt-2 w-100">Update Status</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No assigned orders at the moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h2 class="text-center mt-5">Past Deliveries</h2>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Pickup Point</th>
                    <th>Destination</th>
                    <th>Client Email</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($pastDeliveries)): ?>
                    <?php foreach ($pastDeliveries as $delivery): ?>
                        <tr>
                            <td><?= htmlspecialchars($delivery['order_id']) ?></td>
                            <td><?= htmlspecialchars($delivery['pickup_point']) ?></td>
                            <td><?= htmlspecialchars($delivery['destination']) ?></td>
                            <td><?= htmlspecialchars($delivery['client_email']) ?></td>
                            <td><?= htmlspecialchars($delivery['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No past deliveries found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
