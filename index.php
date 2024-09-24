<?php
require_once 'Classes/Admin.php';
require_once 'Classes/User.php';

use DELIVERY\Classes\Admin\Admin;
use DELIVERY\Database\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form input values
    $email = $_POST['email'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $permission = $_POST['permission'];  

    // Create a new Admin object (you can handle different roles here, or adjust the logic)
    $admin = new Admin($email, $password, $fullname, $permission);

    // Create the user in the database
    $admin->createUser($email, $password, $fullname, $permission);

    // Show success message and login option
    echo "<div class='alert alert-success'>User created successfully as a $permission. <a href='login.php'>Click here to login</a></div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #001f3f; /* Navy Blue */
            color: #fff;
        }
        .container {
            max-width: 600px; /* Limit the width for larger screens */
            margin-top: 50px;
            padding: 20px;
            background-color: #007bff; /* Bootstrap primary color */
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: 100px; /* Adjust the logo size */
        }
        .form-label {
            color: #fff;
        }
        .btn-primary {
            background-color: #0056b3; /* Darker shade of blue */
        }
        .btn-primary:hover {
            background-color: #004494; /* Even darker shade on hover */
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <img src="logo.png" alt="App Logo"> <!-- Replace with your logo path -->
    </div>
    <h2 class="text-center">Create User</h2>
    <form method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="fullname" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="fullname" name="fullname" required>
        </div>
        <div class="mb-3">
            <label for="permission" class="form-label">Permission</label>
            <select class="form-control" id="permission" name="permission" required>
                <option value="admin">Admin</option>
                <option value="client">Client</option>
                <option value="driver">Driver</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">Create Account</button>
    </form>

    <div class="mt-3 text-center">
        Already have an account? <a href="login.php" class="text-light">Login here</a>.
    </div>
</div>
</body>
</html>
