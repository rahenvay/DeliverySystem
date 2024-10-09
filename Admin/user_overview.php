<?php
session_start();
require_once '../Database/Database.php';
use DELIVERY\Database\Database;

if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ../login.php');
    exit;
}

$conn = new Database();

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';


$query = "SELECT * FROM user";
if ($filter === 'clients') {
    $query .= " WHERE permission = 'client'";
} elseif ($filter === 'drivers') {
    $query .= " WHERE permission = 'driver'";
} elseif ($filter === 'admins') {
    $query .= " WHERE permission = 'admin'";
}

$users = $conn->getConnection()->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Handle account deletion
if (isset($_POST['delete_user_id'])) {
    $deleteUserId = $_POST['delete_user_id'];
    $deleteQuery = "DELETE FROM user WHERE id = :id";
    $stmt = $conn->getConnection()->prepare($deleteQuery);
    $stmt->bindParam(':id', $deleteUserId);
    $stmt->execute();
    
   
    header('Location: user_overview.php?filter=' . $filter);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Overview</title>
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
            <a class="nav-link" href="admin.php">Manage Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="assign_driver.php">Assign Driver</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="create_order.php">Create Order</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="user_overview.php">User Overview</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="?action=logout">Sign Out</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content">
    <div class="container mt-5">
        <h2>User Overview</h2>
        
        <!-- Filter options -->
        <div class="mb-3">
            <form method="GET" action="user_overview.php">
                <select name="filter" class="form-control" onchange="this.form.submit()">
                    <option value="all" <?= ($filter === 'all') ? 'selected' : '' ?>>All Users</option>
                    <option value="clients" <?= ($filter === 'clients') ? 'selected' : '' ?>>Clients</option>
                    <option value="drivers" <?= ($filter === 'drivers') ? 'selected' : '' ?>>Drivers</option>
                    <option value="admins" <?= ($filter === 'admins') ? 'selected' : '' ?>>Admins</option>
                </select>
            </form>
        </div>

        <!-- User table -->
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Permission</th>
                        <th>Date Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['id'] ?></td>
                                <td><?= $user['fullname'] ?></td>
                                <td><?= $user['email'] ?></td>
                                <td><?= ucfirst($user['permission']) ?></td>
                                <td><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this account?');">
                                        <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.min.js"></script>
</body>
</html>
