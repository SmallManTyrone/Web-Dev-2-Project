<?php
require('authenticate.php');
// Database connection (use your actual database details)
$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    // Fetch user details based on $user_id
    $result = $conn->query("SELECT * FROM user WHERE UserID = $user_id");
    $row = $result->fetch_assoc();

    if ($row) {
        // User details found, show edit form
        if (isset($_POST['update_user'])) {
            $newUsername = $_POST['new_username'];
            $newEmail = $_POST['new_email'];

            // Check if a new password was provided
            $newPassword = $_POST['new_password'];
            if (!empty($newPassword)) {
                // If a new password is provided, update the password
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE user SET Username = '$newUsername', email = '$newEmail', Password = '$newPasswordHash' WHERE UserID = $user_id";
            } else {
                // If no new password is provided, update without changing the password
                $sql = "UPDATE user SET Username = '$newUsername', email = '$newEmail' WHERE UserID = $user_id";
            }

            if ($conn->query($sql) === TRUE) {
                // Redirect back to user management page
                header("Location: user-management.php");
                exit; // Ensure that no further code is executed after the redirect
            } else {
                echo "Error updating user: " . $conn->error;
            }
        }
    } else {
        echo "User not found.";
    }
} else {
    echo "User ID not provided.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
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
    
    <h2>Edit User</h2>
    <form method="post" action="edit-user.php?user_id=<?php echo $user_id; ?>">
        <input type="text" name="new_username" placeholder="New Username" value="<?php echo $row['Username']; ?>" required>
        <input type="email" name="new_email" placeholder="New Email" value="<?php echo $row['email']; ?>" required>
        <input type="password" name="new_password" placeholder="New Password (leave blank to keep the current password)">
        <button type="submit" name="update_user">Update</button>
    </form>
</body>
</html>
