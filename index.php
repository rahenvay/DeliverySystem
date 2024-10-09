<?php
session_start(); // Start session for CSRF token

require_once 'Classes/User.php';  // Include the User class
require_once 'autoload.php';


use DELIVERY\Classes\User;

$errors = [];

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token. Please refresh the page and try again.";
    }

    // Sanitize input to prevent XSS attacks
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');
    $fullname = htmlspecialchars($_POST['fullname'], ENT_QUOTES, 'UTF-8');
    $permission = htmlspecialchars($_POST['permission'], ENT_QUOTES, 'UTF-8');

    // Create a new User object
    $user = new User($email, $password, $fullname, $permission);

    // Validate the full name for numbers
    if ($user->isValidFullName() === false) {
        $errors[] = "Full name should not contain numbers. Please enter a valid name.";
    }

    // Check if the user already exists
    if ($user->userExists()) {
        $errors[] = "A user with the same email or full name already exists. Please choose a different email or name.";
    }

    // If no errors, create the user
    if (empty($errors)) {
        if ($user->createUser()) {
            echo "<div class='alert alert-success'>User created successfully as a $permission. <a href='login.php'>Click here to login</a></div>";
        } else {
            $errors[] = "Error creating user. Please try again.";
        }
    }
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
            background-color: #001f3f; 
            color: #fff;
        }
        .container {
            max-width: 600px; 
            margin-top: 50px;
            padding: 20px;
            background-color: #007bff; 
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            width: 100px; 
        }
        .form-label {
            color: #fff;
        }
        .btn-primary {
            background-color: #0056b3; 
        }
        .btn-primary:hover {
            background-color: #004494; 
        }
        .error-message {
            color: red;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo">
        <img src="logo.png" alt="App Logo"> 
    </div>
    <h2 class="text-center">Create User</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="createUserForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
        
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
            <span class="error-message" id="fullnameError"></span>
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

<script>
// JavaScript for full name validation
document.getElementById('createUserForm').addEventListener('submit', function(event) {
    var fullname = document.getElementById('fullname').value;
    var fullnameError = document.getElementById('fullnameError');

    // Check if full name contains numbers
    var nameRegex = /^[A-Za-z\s]+$/;

    if (!nameRegex.test(fullname)) {
        event.preventDefault();  // Stop form from submitting
        fullnameError.textContent = "Full name should not contain numbers.";
    } else {
        fullnameError.textContent = "";  // Clear the error message
    }
});
</script>
</body>
</html>
