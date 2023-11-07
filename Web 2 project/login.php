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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    // Try authenticating as an admin
    if (authenticateAdmin($db, $username, $password,)) {
        header("Location: index.php"); // Redirect to admin dashboard
        exit();
    }

    // If admin authentication fails, try authenticating as a regular user
    if (authenticateUser($db, $username, $password,)) {
        header("Location: index.php"); // Redirect to user dashboard
        exit();
    }

    echo "Login failed. Check your credentials.";
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Styles.css">
    <title>Login</title>
</head>
<body>
    <div class="login-box">
        <h1>Login</h1>
        <form action="login_process.php" method="post" onsubmit="showLoginSuccess()">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>

