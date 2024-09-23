<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$conn = new Database();

// Fetch all drivers
$driverQuery = "SELECT * FROM user WHERE permission = 'driver'";
$drivers = $conn->getStarted()->query($driverQuery)->fetchAll(PDO::FETCH_ASSOC);

// Fetch all orders without a driver assigned
$orderQuery = "SELECT * FROM orders WHERE status = 'pending' AND driver_id IS NULL";
$orders = $conn->getStarted()->query($orderQuery)->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['driver_id'])) {
    $order_id = $_POST['order_id'];
    $driver_id = $_POST['driver_id'];

    // Assign the driver to the order
    $updateOrderQuery = "UPDATE orders SET driver_id = :driver_id, status = 'assigned' WHERE order_id = :order_id";
    $stmt = $conn->getStarted()->prepare($updateOrderQuery);
    $stmt->bindParam(':driver_id', $driver_id);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();

    echo "<div class='alert alert-success'>Driver assigned successfully!</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Driver</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Assign Driver</h2>
    <ul class="list-group">
        <?php foreach ($drivers as $driver): ?>
            <li class="list-group-item">
                <?= $driver['fullname'] ?> (Driver ID: <?= $driver['id'] ?>)
                <form method="POST" class="d-inline-block">
                    <input type="hidden" name="driver_id" value="<?= $driver['id'] ?>">
                    <select name="order_id" class="form-control d-inline-block w-50">
                        <?php foreach ($orders as $order): ?>
                            <option value="<?= $order['order_id'] ?>">Order ID: <?= $order['order_id'] ?> (<?= $order['pickup_point'] ?> to <?= $order['destination'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-success">Assign Driver</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
</body>
</html>
