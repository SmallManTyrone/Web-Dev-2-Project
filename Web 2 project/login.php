<?php


$servername = "localhost"; // Replace with your database server
$username = "serveruser"; // Replace with your database username
$password = "gorgonzola7!"; // Replace with your database password
$dbname = "serverside"; // Replace with your database name

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = ''; // Initialize the error message

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Check the 'admins' table for admin login
    $admin_sql = "SELECT * FROM admins WHERE Username = ?";
    $stmt = $conn->prepare($admin_sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin_result = $stmt->get_result()->fetch_assoc();

    // Try authenticating as an admin
    if ($admin_result && $password === $admin_result['Password']) {
        $_SESSION['admin_id'] = $admin_result['AdminID'];
        $_SESSION['is_admin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['login_success'] = true;

        // Redirect to the admin dashboard
        header("Location: index.php");
        exit();
    }

    // If the username is not in the 'admins' table, it's a regular user
    $user_sql = "SELECT * FROM user WHERE Username = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    // If admin authentication fails, try authenticating as a regular user
    if ($result && password_verify($password, $result['Password'])) {
        $_SESSION['user_id'] = $result['UserID'];
        $_SESSION['is_admin'] = false; // Marking as a regular user
        $_SESSION['username'] = $username;
        $_SESSION['login_success'] = true;

        // Redirect to the user dashboard
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Login failed. Check your credentials.";
        header("Location: login.php?error=" . urlencode($error_message)); // Redirect with error message
        exit();
    }
}

require('authenticate.php');

// Check if a user is already logged in
if (isLoggedIn()) {
    header("Location: index.php"); // Redirect to user dashboard
    exit();
}

if (isAdminLoggedIn()) {
    header("Location: index.php"); // Redirect to user dashboard
    exit();
}

$error_message = ''; // Initialize the error message
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE, edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Styles.css">
    <script src="success_message.js"></script>
    <title>Login</title>
</head>
<body>
<ul>
    <li><a href="index.php">Home</a></li>
</ul>
<h1>Login</h1>
<div class="login-box">
    <?php
    if (isset($_GET['error'])) {
        $error_message = urldecode($_GET['error']);
        echo '<div class="error-message">' . $error_message . '</div>';
        echo '<p><a href="registration.php">Register</a></p>';
    }
    ?>
    <form action="login.php" method="post" onsubmit="showLoginSuccess()">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" name="login">Login</button>
    </form>
</div>
</body>
</html>
