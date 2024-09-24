<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn = new Database();
    $query = "SELECT * FROM user WHERE email = :email";
    $stmt = $conn->getStarted()->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['permission'] = $user['permission'];

        if ($user['permission'] === 'admin') {
            header('Location: admin.php');
        } elseif ($user['permission'] === 'client') {
            header('Location: client.php');
        } elseif ($user['permission'] === 'driver') {
            header('Location: driver.php');
        }
    } else {
        echo "<div class='alert alert-danger'>Invalid email or password.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 400px; /* Limit the width for larger screens */
            margin-top: 100px; /* Space from top for better visual */
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Login</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>
</body>
</html>
