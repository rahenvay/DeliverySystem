<?php
session_start();
require_once '../Classes/User.php';
require_once '../Database/Database.php'; // Include Database class

use DELIVERY\Classes\User;
use DELIVERY\Database\Database;

// Check if the user is logged in and has admin permission
if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

// Handle POST request for creating a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $permission = $_POST['permission'];

    $user = new User($email, $password, $fullname, $permission);

    // Check if the full name is valid
    if (!$user->isValidFullName()) {
        $error = "Full name must not contain numbers.";
    } elseif ($user->userExists()) {
        $error = "User with this email or full name already exists.";
    } else {
        // Create the user in the database
        if ($user->createUser()) {
            $success = "User created successfully!";
        } else {
            $error = "Failed to create user. Please try again.";
        }
    }
}

// Set the current page for sidebar highlighting
$current_page = 'create_user';
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
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: #f8f9fa;
        }
        #sidebar {
            height: 100%;
            background-color: #001f3f;
            padding-top: 20px;
            width: 100%;
            max-width: 250px;
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
            flex-grow: 1;
        }
        @media (max-width: 768px) {
            #sidebar {
                width: 100%;
                position: static;
                max-width: none;
            }
            .content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar" class="d-flex flex-column">
    <h4 class="text-white text-center">Admin Dashboard</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="admin.php" class="<?= ($current_page === 'admin') ? 'active' : '' ?>">Manage Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="assign_driver.php" class="<?= ($current_page === 'assign_driver') ? 'active' : '' ?>">Assign Driver</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="create_order.php" class="<?= ($current_page === 'create_order') ? 'active' : '' ?>">Create Order</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="create_user.php" class="<?= ($current_page === 'create_user') ? 'active' : '' ?>">Create User</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="user_overview.php" class="<?= ($current_page === 'user_overview') ? 'active' : '' ?>">User Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="?action=logout">Sign Out</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <h2>Create User</h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="create_user.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="mb-3">
                <label for="fullname" class="form-label">Full Name</label>
                <input type="text" class="form-control" name="fullname" required>
            </div>
            <div class="mb-3">
                <label for="permission" class="form-label">Permission</label>
                <select name="permission" class="form-select" required>
                    <option value="">Select Permission</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create User</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
</body>
</html>
