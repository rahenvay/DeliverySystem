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
    $order_id = $_POST['order_id'];
    $driver_id = $_POST['driver_id'];

    $updateQuery = "UPDATE orders SET driver_id = :driver_id, status = 'assigned' WHERE order_id = :order_id";
    $stmt = $conn->getStarted()->prepare($updateQuery);
    $stmt->bindParam(':driver_id', $driver_id);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();

    echo "<div class='alert alert-success'>Order assigned to driver successfully!</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Admin Dashboard</h2>

    <h3>Orders</h3>
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
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
