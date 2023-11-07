<?php

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password === $confirmPassword) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Prepare and execute an SQL query to insert data into the 'users' table
        $sql = "INSERT INTO user (Username, Password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $username, $passwordHash);

        if ($stmt->execute()) {
            // Registration successful, you can redirect to a login page or other actions
            header("Location: login.php");
            exit();
        } else {
            echo "Registration failed: " . $stmt->error;
        }
    } else {
        echo "Passwords do not match. Please try again.";
    }
}

?>


