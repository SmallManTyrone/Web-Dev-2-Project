<?php
session_start(); // Start or resume the session

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Unset and destroy the session data
    session_unset();
    session_destroy();

    // Redirect the user to the login page or any other appropriate page
    header("Location: index.php"); // Change the URL to the desired page
    exit();
} else {
    // If the user is not logged in, you can handle it in a different way, such as redirecting to the home page or displaying an error message.
    header("Location: index.php"); // Redirect to the home page or another page
    exit();
}
?>
