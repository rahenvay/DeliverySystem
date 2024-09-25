<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;


if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy(); 
    header('Location: login.php'); 
    exit;
}

if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = new Database();

// Fetch all orders including the price
$query = "SELECT order_id, client_id, pickup_point, destination, price, status, driver_id FROM orders";
$orders = $conn->getStarted()->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch all drivers
$driverQuery = "SELECT * FROM user WHERE permission = 'driver'";
$drivers = $conn->getStarted()->query($driverQuery)->fetchAll(PDO::FETCH_ASSOC);

// Count total accounts (drivers, clients, and admins)
$totalAccountsQuery = "SELECT COUNT(*) as total_accounts FROM user";
$totalAccounts = $conn->getStarted()->query($totalAccountsQuery)->fetch(PDO::FETCH_ASSOC)['total_accounts'];

// Count total orders
$totalOrdersQuery = "SELECT COUNT(*) as total_orders FROM orders";
$totalOrders = $conn->getStarted()->query($totalOrdersQuery)->fetch(PDO::FETCH_ASSOC)['total_orders'];

// Handle POST request for updating status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status']) && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $validStatuses = ['pending', 'assigned', 'picked_up', 'in_transit', 'delivered', 'cancelled'];
    
    if (in_array($status, $validStatuses)) {
        $updateStatusQuery = "UPDATE orders SET status = :status WHERE order_id = :order_id";
        $stmt = $conn->getStarted()->prepare($updateStatusQuery);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        
        echo "<div class='alert alert-success'>Status updated to {$status} for order ID: {$order_id}</div>";
    } else {
        echo "<div class='alert alert-danger'>Invalid status value!</div>";
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
                                        <select name="driver_id" class="form-control" required>
                                            <option value="">Select Driver</option>
                                            <?php foreach ($drivers as $driver): ?>
                                                <option value="<?= $driver['id'] ?>"><?= $driver['fullname'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-success mt-2">Assign</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-info">Assigned to Driver ID: <?= $order['driver_id'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($order['status'] != 'delivered'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                        <select name="status" class="form-control" required>
                                            <option value="">Update Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="assigned">Assigned</option>
                                            <option value="picked_up">Picked Up</option>
                                            <option value="in_transit">In Transit</option>
                                            <option value="delivered">Delivered</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                        <button type="submit" class="btn btn-warning mt-2">Update</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
</body>
</html>
