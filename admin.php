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

// Handle POST request for setting price or updating status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['price']) && isset($_POST['order_id'])) {
        // Set price
        $order_id = $_POST['order_id'];
        $price = floatval($_POST['price']);
        
        $updateQuery = "UPDATE orders SET price = :price WHERE order_id = :order_id";
        $stmt = $conn->getStarted()->prepare($updateQuery);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        
        echo "<div class='alert alert-success'>Price updated successfully for order ID: {$order_id}</div>";
    } elseif (isset($_POST['status']) && isset($_POST['order_id'])) {
        // Update delivery status
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        
        $updateStatusQuery = "UPDATE orders SET status = :status WHERE order_id = :order_id";
        $stmt = $conn->getStarted()->prepare($updateStatusQuery);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        
        echo "<div class='alert alert-success'>Status updated to {$status} for order ID: {$order_id}</div>";
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
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <h2>Manage Orders</h2>
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
                    <th>Set Price</th>
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
                        <td><?= $order['price'] ?? 'Not Set' ?> Baht</td>
                        <td><?= ucfirst($order['status']) ?></td>
                        <td>
                            <?php if ($order['status'] == 'delivered'): ?>
                                <span class="badge bg-success">The driver with ID: <?= $order['driver_id'] ?> has delivered the item</span>
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
                            <?php if ($order['status'] == 'pending' && $order['price'] == null): ?>
                                <button class="btn btn-primary" onclick="setPrice(<?= $order['order_id'] ?>)">Set Price</button>
                            <?php elseif ($order['price'] !== null): ?>
                                <span class="badge bg-primary"><?= $order['price'] ?> Baht</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($order['status'] != 'delivered'): ?>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <select name="status" class="form-control" required>
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="in progress" <?= $order['status'] == 'in progress' ? 'selected' : '' ?>>In Progress</option>
                                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                    </select>
                                    <button type="submit" class="btn btn-warning mt-2">Update Status</button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-success">Delivered</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- JavaScript for setting the price -->
<script>
function setPrice(orderId) {
    const price = prompt("Please enter the price in Baht:");
    if (price != null && !isNaN(price) && price.trim() !== "") {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'admin.php';
        
        const orderInput = document.createElement('input');
        orderInput.type = 'hidden';
        orderInput.name = 'order_id';
        orderInput.value = orderId;
        form.appendChild(orderInput);
        
        const priceInput = document.createElement('input');
        priceInput.type = 'hidden';
        priceInput.name = 'price';
        priceInput.value = price;
        form.appendChild(priceInput);
        
        document.body.appendChild(form);
        form.submit();
    } else {
        alert("Please enter a valid price.");
    }
}
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
