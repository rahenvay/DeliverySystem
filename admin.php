<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = new Database();

// Fetch all orders
$query = "SELECT * FROM orders";
$orders = $conn->getStarted()->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Fetch all drivers
$driverQuery = "SELECT * FROM user WHERE permission = 'driver'";
$drivers = $conn->getStarted()->query($driverQuery)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['driver_id']) && isset($_POST['order_id'])) {
        // Assign driver to order
        $order_id = $_POST['order_id'];
        $driver_id = $_POST['driver_id'];

        $updateQuery = "UPDATE orders SET driver_id = :driver_id, status = 'assigned' WHERE order_id = :order_id";
        $stmt = $conn->getStarted()->prepare($updateQuery);
        $stmt->bindParam(':driver_id', $driver_id);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();

        echo "<div class='alert alert-success'>Order assigned to driver successfully!</div>";
    } elseif (isset($_POST['price'])) {
        // Set price for order
        $order_id = $_POST['order_id'];
        $price = $_POST['price'];

        $updateQuery = "UPDATE orders SET price = :price WHERE order_id = :order_id";
        $stmt = $conn->getStarted()->prepare($updateQuery);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();

        echo "<div class='alert alert-success'>Price updated successfully!</div>";
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
            <a class="nav-link" href="#">Order Status</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Drivers</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <h2>Manage Orders</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Client ID</th>
                    <th>Pickup Point</th>
                    <th>Destination</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Assign Driver</th>
                    <th>Set Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['order_id'] ?></td>
                        <td><?= $order['client_id'] ?></td>
                        <td><?= $order['pickup_point'] ?></td>
                        <td><?= $order['destination'] ?></td>
                        <td><?= $order['price'] ?? 'Not Set' ?></td>
                        <td><?= $order['status'] ?></td>
                        <td>
                            <?php if ($order['status'] == 'pending'): ?>
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
                                <span>Assigned to Driver ID: <?= $order['driver_id'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($order['status'] == 'pending' && $order['price'] == null): ?>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <input type="number" name="price" class="form-control" placeholder="Set price" required>
                                    <button type="submit" class="btn btn-primary mt-2">Set Price</button>
                                </form>
                            <?php elseif ($order['price'] !== null): ?>
                                <span>Price: <?= $order['price'] ?></span>
                            <?php endif; ?>
                        </td>
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
