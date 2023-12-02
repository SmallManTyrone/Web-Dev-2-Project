<?php
$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['create_user'])) {
    // Sanitize and validate the username
    $username = filter_var($_POST['username'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    if (empty($username)) {
        die("Error: Username is required.");
    }

    // Sanitize and validate the email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email format.");
    }

    // Sanitize and validate the password
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if (empty($password) || $password !== $confirm_password) {
        die("Error: Passwords do not match. Please try again.");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user into the database
    $sql = "INSERT INTO user (Username, email, Password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        echo "User created successfully.";
    } else {
        echo "Error creating user: " . $stmt->error;
    }
}
?>




<!DOCTYPE html>
<html>

<head>
    <title>Create User</title>
    <link rel="stylesheet" href="Styles.css">
</head>

<body>
    <ul>
        <?php
                     if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'user_management.php') !== false) {
                        echo '<li><a href="user_management.php">Go back to manage users</a></li>';
                    }
                    ?>
        <li><a href="index.php">Home</a></li>
    </ul>
    <h2>Create User</h2>
    <form method="post" action="create-user.php">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
        <button type="submit" name="create_user">Create User</button>
    </form>
    <a href="user-management.php">Back to User Management</a>
</body>

</html>