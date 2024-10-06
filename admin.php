<?php
session_start();
require_once 'Classes/Admin.php';
use DELIVERY\Classes\Admin\Admin;

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$admin = new Admin();

// Fetch all orders
$orders = $admin->fetchAllOrders();

// Fetch all drivers
$drivers = $admin->fetchAllDrivers();

// Count total accounts and total orders
$totalAccounts = $admin->countTotalAccounts();
$totalOrders = $admin->countTotalOrders();

// Handle POST request for updating status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    if ($admin->updateOrderStatus($order_id, $status)) {
        echo "<div class='alert alert-success'>Status updated to {$status} for order ID: {$order_id}</div>";
    } else {
        echo "<div class='alert alert-danger'>Invalid status value!</div>";
    }
}

// Handle POST request for assigning a driver
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['driver_id']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $driver_id = $_POST['driver_id'];
    
    if ($admin->assignDriverToOrder($order_id, $driver_id)) {
        echo "<div class='alert alert-success'>Driver assigned to order ID: {$order_id}</div>";
    } else {
        echo "<div class='alert alert-danger'>Failed to assign driver!</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            max-width: 250px;
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
    </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar" class="d-flex flex-column">
    <h4 class="text-white text-center">Admin Dashboard</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link active" href="admin.php">Manage Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="assign_driver.php">Assign Driver</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="create_order.php">Create Order</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="user_overview.php">User Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="?action=logout">Sign Out</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <div class="overview-table">
            <h2>Admin Overview</h2>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Total Accounts</th>
                            <th>Total Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $totalAccounts ?></td>
                            <td><?= $totalOrders ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Orders Table -->
        <h2>Manage Orders</h2>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Client ID</th>
                        <th>Pickup Point</th>
                        <th>Destination</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Assign Driver</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= $order['order_id'] ?></td>
                            <td><?= $order['client_id'] ?></td>
                            <td><?= $order['pickup_point'] ?></td>
                            <td><?= $order['destination'] ?></td>
                            <td><?= number_format($order['price'], 2) ?> Baht</td>
                            <td><?= ucfirst($order['status']) ?></td>
                            <td>
                                <?php if ($order['status'] == 'delivered'): ?>
                                    <span class="badge bg-success">Driver with ID: <?= $order['driver_id'] ?> has delivered the item</span>
                                <?php elseif ($order['status'] == 'pending'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <select name="driver_id" class="form-select">
                                            <?php foreach ($drivers as $driver): ?>
                                                <option value="<?= $driver['id'] ?>">Driver: <?= $driver['fullname'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary mt-1">Assign Driver</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-info">Assigned to driver with ID: <?= $order['driver_id'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="status" class="form-select">
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="assigned" <?= $order['status'] == 'assigned' ? 'selected' : '' ?>>Assigned</option>
                                        <option value="picked_up" <?= $order['status'] == 'picked_up' ? 'selected' : '' ?>>Picked Up</option>
                                        <option value="in_transit" <?= $order['status'] == 'in_transit' ? 'selected' : '' ?>>In Transit</option>
                                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-warning mt-1">Update Status</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
