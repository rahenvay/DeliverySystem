<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

// Check if the user is logged in as a driver
if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'driver') {
    header('Location: login.php');
    exit;
}

// Database connection
$conn = new Database();
$driver_id = $_SESSION['user_id'];

// Fetch all current orders assigned to the logged-in driver (excluding delivered) and client information
$query = "
    SELECT o.order_id, o.pickup_point, o.destination, o.status, u.email AS client_email
    FROM orders o
    JOIN user u ON o.client_id = u.id
    WHERE o.driver_id = :driver_id AND o.status != 'delivered'
";
$stmt = $conn->getStarted()->prepare($query);
$stmt->bindParam(':driver_id', $driver_id);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];

    // Update the order status
    $updateQuery = "UPDATE orders SET status = :status WHERE order_id = :order_id";
    $stmt = $conn->getStarted()->prepare($updateQuery);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();

    // If the order status is delivered, unassign the driver (set driver_id to NULL)
    if ($status === 'delivered') {
        $unassignDriverQuery = "UPDATE orders SET driver_id = NULL WHERE order_id = :order_id";
        $stmtUnassign = $conn->getStarted()->prepare($unassignDriverQuery);
        $stmtUnassign->bindParam(':order_id', $order_id);
        $stmtUnassign->execute();
    }

    echo "<div class='alert alert-success'>Order status updated successfully!</div>";
}

// Handle sign out
if (isset($_POST['sign_out'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
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
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-sign-out {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Assigned Orders</h2>

    <div class="text-end">
        <form method="POST" class="mb-3">
            <button type="submit" name="sign_out" class="btn btn-sign-out">Sign Out</button>
        </form>
    </div>

    <!-- Current Assigned Orders -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Pickup Point</th>
                    <th>Destination</th>
                    <th>Client Email</th>
                    <th>Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['pickup_point']) ?></td>
                            <td><?= htmlspecialchars($order['destination']) ?></td>
                            <td><?= htmlspecialchars($order['client_email']) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order_id']) ?>">
                                    <select name="status" class="form-select">
                                        <option value="picked_up">Picked Up</option>
                                        <option value="in_transit">In Transit</option>
                                        <option value="delivered">Delivered</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary mt-2 w-100">Update Status</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No assigned orders at the moment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
