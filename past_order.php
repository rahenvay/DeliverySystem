<?php
session_start();
require_once 'Database/Database.php';
use DELIVERY\Database\Database;

// Sign out logic
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset(); 
    session_destroy(); 
    header('Location: login.php'); 
    exit;
}

// Check if the user is logged in and is a client
if (!isset($_SESSION['permission']) || $_SESSION['permission'] !== 'client') {
    header('Location: login.php');
    exit;
}

$conn = new Database();
$client_id = $_SESSION['user_id'];

// Fetch client info
$clientQuery = "SELECT fullname FROM user WHERE id = :client_id";
$stmtClient = $conn->getConnection()->prepare($clientQuery);
$stmtClient->bindParam(':client_id', $client_id);
$stmtClient->execute();
$clientInfo = $stmtClient->fetch(PDO::FETCH_ASSOC);

// Pagination setup
$ordersPerPage = 7; // Number of orders per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page from URL
$offset = ($page - 1) * $ordersPerPage; // Calculate the offset for SQL query

// Fetch total number of delivered orders for this client
$totalOrdersQuery = "SELECT COUNT(*) FROM orders WHERE client_id = :client_id AND status = 'delivered'";
$stmtTotalOrders = $conn->getConnection()->prepare($totalOrdersQuery);
$stmtTotalOrders->bindParam(':client_id', $client_id);
$stmtTotalOrders->execute();
$totalOrders = $stmtTotalOrders->fetchColumn();

// Fetch delivered orders for this client with pagination
// Fetch delivered orders for this client with pagination
$query = "
    SELECT orders.*, user.fullname AS driver_name 
    FROM orders 
    LEFT JOIN user ON orders.driver_id = user.id 
    WHERE orders.client_id = :client_id AND orders.status = 'delivered' 
    LIMIT :limit OFFSET :offset";
$stmt = $conn->getConnection()->prepare($query);
$stmt->bindParam(':client_id', $client_id);
$stmt->bindParam(':limit', $ordersPerPage, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$pastOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Calculate total pages
$totalPages = ceil($totalOrders / $ordersPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Orders</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: row;
            background-color: #f8f9fa;
        }
        #sidebar {
            background-color: #001f3f;
            padding-top: 20px;
            min-height: 100vh;
        }
        #sidebar .nav-link {
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
        }
        #sidebar .nav-link:hover {
            background-color: #007bff;
            color: white;
        }
        #sidebar .nav-link.active {
            background-color: #007bff;
            color: white;
        }
        .client-info {
            color: #fff;
            padding: 15px;
            border-bottom: 1px solid #007bff;
            text-align: center;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
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
        .pagination {
            margin-top: 20px;
            justify-content: center;
        }
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            #sidebar {
                min-height: auto;
            }
            .content {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav id="sidebar" class="d-flex flex-column col-md-3 col-lg-2 p-3">
    <h4 class="text-white text-center">Client Dashboard</h4>
    <div class="client-info">
        <p><strong>Name:</strong> <?= htmlspecialchars($clientInfo['fullname']) ?></p>
        <p><strong>ID:</strong> <?= htmlspecialchars($client_id) ?></p>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link active" href="checkStatus.php">Check Status</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="past_order.php">Past Orders</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="?action=logout">Sign Out</a>
        </li>
    </ul>
</nav>

<!-- Page Content -->
<div class="content col-md-9 ms-sm-auto col-lg-10">
    <div class="container mt-4">
        <h2>Past Orders</h2>
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Pickup Point</th>
                    <th>Destination</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Driver Name</th> <!-- New column for Driver Name -->
                </tr>
            </thead>
            <tbody>
                <?php if (count($pastOrders) > 0): ?>
                    <?php foreach ($pastOrders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['pickup_point']) ?></td>
                            <td><?= htmlspecialchars($order['destination']) ?></td>
                            <td><?= htmlspecialchars($order['price']) ?></td>
                            <td><?= htmlspecialchars($order['status']) ?></td>
                            <td><?= htmlspecialchars($order['driver_name']) ?></td> <!-- Display Driver Name -->
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No past orders found.</td> <!-- Adjust colspan -->
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>


        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
