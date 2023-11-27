<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = $_POST['password'];

    // Try authenticating as an admin
    if (authenticateAdmin($db, $username, $password)) {
        header("Location: index.php"); // Redirect to admin dashboard
        exit();
    }

    // If admin authentication fails, try authenticating as a regular user
    if (authenticateUser($db, $username, $password)) {
        header("Location: index.php"); // Redirect to user dashboard
        exit();
    }

    $error_message = "Login failed. Check your credentials.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE, edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
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
        echo '<p><a href="register.php">Register</a></p>';
    }
    ?>
    <form class = "login" action="login_process.php" method="post" onsubmit="showLoginSuccess()">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button class="login-button" type="submit" name="login">Login</button>
    </form>
</div>
</body>
</html>