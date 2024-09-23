<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'client') {
    header('Location: login.php');
    exit;
}

$conn = new Database();
$client_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pickup_point = $_POST['pickup_point'];
    $destination = $_POST['destination'];
    
    // Default price set to 0.00 when creating a new order
    $default_price = 0.00;

    // Insert the new order into the database with default price
    $insertOrderQuery = "INSERT INTO orders (client_id, pickup_point, destination, price, status) VALUES (:client_id, :pickup_point, :destination, :price, 'pending')";
    $stmtInsert = $conn->getStarted()->prepare($insertOrderQuery);
    $stmtInsert->bindParam(':client_id', $client_id);
    $stmtInsert->bindParam(':pickup_point', $pickup_point);
    $stmtInsert->bindParam(':destination', $destination);
    $stmtInsert->bindParam(':price', $default_price);
    $stmtInsert->execute();

    echo "<div class='alert alert-success'>New order created successfully!</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Order</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Create New Order</h2>
    <form method="POST">
        <div class="form-group">
            <label for="pickup_point">Pickup Point</label>
            <input type="text" name="pickup_point" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="destination">Destination</label>
            <input type="text" name="destination" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Submit Order</button>
    </form>
</div>
</body>
</html>
