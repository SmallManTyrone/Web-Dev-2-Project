<?php
session_start();
require('connect.php'); // Include your database connection script

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Check the 'admins' table for admin login
    $admin_sql = "SELECT * FROM admins WHERE Username = ?";
    $stmt = $conn->prepare($admin_sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin_result = $stmt->get_result()->fetch_assoc();
    
    if ($admin_result && $password === $admin_result['Password']) {
        $_SESSION['admin_id'] = $admin_result['AdminID'];
        $_SESSION['is_admin'] = true;
        $_SESSION['username'] = $username;

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

    if ($result && password_verify($password, $result['Password'])) {
        $_SESSION['user_id'] = $result['UserID'];
        $_SESSION['is_admin'] = false; // Marking as a regular user
        $_SESSION['username'] = $username;

        // Redirect to the user dashboard
        header("Location: index.php");
        exit();
    } else {
        echo "Login failed. Check your credentials.";
    }
}


?>
