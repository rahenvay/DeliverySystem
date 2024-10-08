<?php
session_start();
require_once 'Classes/Admin.php';
require_once 'Database/Database.php'; // Include Database class

use DELIVERY\Classes\Admin\Admin;
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

$database = new Database(); // Create a new Database instance
$admin = new Admin($database); // Pass Database instance to Admin

// Count total orders for pagination
$totalOrders = $admin->countTotalOrders();
$limit = 7; // Number of orders to display per page
$totalPages = ceil($totalOrders / $limit); // Calculate total pages

// Get current page from URL, default to 1 if not set
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$currentPage = max(1, min($currentPage, $totalPages)); // Ensure current page is within bounds

$offset = ($currentPage - 1) * $limit; // Calculate offset for SQL query

// Fetch orders for the current page
$orders = $admin->fetchAllOrders($limit, $offset);

// Fetch all drivers
$drivers = $admin->fetchAllDrivers();

// Count total accounts
$totalAccounts = $admin->countTotalAccounts();

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

    <!-- Search and Filter Form -->
    <form method="GET" action="admin.php" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="client_name" class="form-control" placeholder="Search by Client Name" value="<?= isset($_GET['client_name']) ? $_GET['client_name'] : '' ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Filter by Status</option>
                    <option value="pending" <?= isset($_GET['status']) && $_GET['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="assigned" <?= isset($_GET['status']) && $_GET['status'] == 'assigned' ? 'selected' : '' ?>>Assigned</option>
                    <option value="delivered" <?= isset($_GET['status']) && $_GET['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="driver_id" class="form-select">
                    <option value="">Filter by Driver</option>
                    <?php foreach ($drivers as $driver): ?>
                        <option value="<?= $driver['id'] ?>" <?= isset($_GET['driver_id']) && $_GET['driver_id'] == $driver['id'] ? 'selected' : '' ?>>
                            <?= $driver['fullname'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>

    <!-- Fetch and Display Orders based on filters -->
    <?php
    $clientName = isset($_GET['client_name']) ? $_GET['client_name'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $driverId = isset($_GET['driver_id']) ? $_GET['driver_id'] : '';

    $orders = $admin->fetchFilteredOrders($clientName, $status, $driverId, $limit, $offset);
    ?>

<!-- Orders Table (unchanged) -->
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
            <td><?= $order['price'] ?></td>
            <td><?= $order['status'] ?></td>
            
            <!-- Disable Driver Assignment if delivered -->
            <td>
                <?php if ($order['status'] === 'delivered'): ?>
                    <span class="text-success">Delivery Completed</span>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                        <select name="driver_id" class="form-select">
                            <option value="">Select Driver</option>
                            <?php foreach ($drivers as $driver): ?>
                                <option value="<?= $driver['id'] ?>"><?= $driver['fullname'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary mt-1">Assign</button>
                    </form>
                <?php endif; ?>
            </td>
            
            <!-- Disable Status Update if delivered -->
            <td>
                <?php if ($order['status'] === 'delivered'): ?>
                    <span class="text-success">Delivery Completed</span>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                        <select name="status" class="form-select">
                            <option value="">Select Status</option>
                            <option value="pending">Pending</option>
                            <option value="assigned">Assigned</option>
                            <option value="delivered">Delivered</option>
                        </select>
                        <button type="submit" class="btn btn-primary mt-1">Update</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
    </table>
</div>
        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item <?= ($currentPage == 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage - 1 ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($currentPage == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($currentPage == $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $currentPage + 1 ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
</body>
</html>
