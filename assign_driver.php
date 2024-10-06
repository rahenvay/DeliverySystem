<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Instantiate the Database object
$conn = new Database();

// Fetch all orders that need a driver assigned
$orderQuery = "SELECT * FROM orders WHERE status = 'pending'";
$orders = $conn->getConnection()->query($orderQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch all drivers
$driverQuery = "SELECT * FROM user WHERE permission = 'driver'";
$drivers = $conn->getConnection()->query($driverQuery)->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission to assign driver
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['driver_id']) && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        $driver_id = $_POST['driver_id'];

        try {
            // Assign the driver and update order status
            $updateQuery = "UPDATE orders SET driver_id = :driver_id, status = 'assigned' WHERE order_id = :order_id";
            $stmt = $conn->getConnection()->prepare($updateQuery);
            $stmt->bindParam(':driver_id', $driver_id);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();

            // Success message
            echo "<div class='alert alert-success'>Driver assigned to order successfully!</div>";
        } catch (PDOException $e) {
            // Error handling
            echo "<div class='alert alert-danger'>Error assigning driver: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Please select a driver and an order!</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Driver</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
        #sidebar .nav-link.sign-out {
            color: red; 
        }
        .content {
            margin-left: 260px;
            padding: 20px;
            flex-grow: 1;
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
            #sidebar {
                width: 100%;
                position: static;
                max-width: none;
            }
            .content {
                margin-left: 0;
            }
        }
        @media (max-width: 576px) {
            .table thead {
                display: none;
            }
            .table-responsive table td {
                display: block;
                width: 100%;
                border: none;
            }
            .table-responsive table td:before {
                content: attr(data-label);
                font-weight: bold;
                display: block;
                margin-bottom: 5px;
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
            <a class="nav-link active" href="assign_driver.php">Assign Driver</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="create_order.php">Create Order</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="user_overview.php">User Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link sign-out" href="?action=logout">Sign Out</a> 
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <h2>Assign Driver</h2>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Client ID</th>
                        <th>Pickup Point</th>
                        <th>Destination</th>
                        <th>Assign Driver</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td data-label="Order ID"><?= $order['order_id'] ?></td>
                            <td data-label="Client ID"><?= $order['client_id'] ?></td>
                            <td data-label="Pickup Point"><?= $order['pickup_point'] ?></td>
                            <td data-label="Destination"><?= $order['destination'] ?></td>
                            <td data-label="Assign Driver">
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="driver_id" class="form-control" required>
                                        <option value="">Select Driver</option>
                                        <?php foreach ($drivers as $driver): ?>
                                            <option value="<?= $driver['id'] ?>"><?= $driver['fullname'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-success mt-2">Assign</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
