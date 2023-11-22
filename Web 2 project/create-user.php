<?php
// Include the database connection code
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

if (isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password === $confirm_password) {
        // Passwords match, proceed with user creation
        // The $conn variable should now be defined from the included database connection file

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
    } else {
        echo "Passwords do not match. Please try again.";
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
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
