<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'driver') {
    header('Location: login.php');
    exit;
}

$conn = new Database();
$driver_id = $_SESSION['user_id'];

// Fetch orders assigned to the driver
$query = "SELECT * FROM orders WHERE driver_id = :driver_id";
$stmt = $conn->getStarted()->prepare($query);
$stmt->bindParam(':driver_id', $driver_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Update order status if POST request is made
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    $updateQuery = "UPDATE orders SET status = :status WHERE order_id = :order_id";
    $stmt = $conn->getStarted()->prepare($updateQuery);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();

    echo "<div class='alert alert-success'>Order status updated successfully!</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Driver Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Driver Dashboard</h2>

    <h3>Assigned Orders</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Client ID</th>
                <th>Pickup Point</th>
                <th>Destination</th>
                <th>Price</th>
                <th>Status</th>
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
                    <td>
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <select name="status" class="form-control" required>
                                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="assigned" <?= $order['status'] === 'assigned' ? 'selected' : '' ?>>Assigned</option>
                                <option value="pickedup" <?= $order['status'] === 'pickedup' ? 'selected' : '' ?>>Picked Up</option>
                                <option value="intransit" <?= $order['status'] === 'intransit' ? 'selected' : '' ?>>In Transit</option>
                                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="btn btn-primary mt-2">Update Status</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
