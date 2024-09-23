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

// Fetch all orders assigned to the logged-in driver
$query = "SELECT * FROM orders WHERE driver_id = :driver_id";
$stmt = $conn->getStarted()->prepare($query);
$stmt->bindParam(':driver_id', $driver_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Update the order status
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
    <h2>Assigned Orders</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Pickup Point</th>
                <th>Destination</th>
                <th>Status</th>
                <th>Update Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= $order['order_id'] ?></td>
                    <td><?= $order['pickup_point'] ?></td>
                    <td><?= $order['destination'] ?></td>
                    <td><?= $order['status'] ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <select name="status" class="form-control">
                                <option value="picked_up">Picked Up</option>
                                <option value="in_transit">In Transit</option>
                                <option value="delivered">Delivered</option>
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
