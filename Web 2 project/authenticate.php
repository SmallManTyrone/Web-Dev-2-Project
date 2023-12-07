<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require('connect.php'); 

$servername = "localhost"; 
$username = "serveruser"; 
$password = "gorgonzola7!"; 
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
}






function authenticateAdmin($db, $username, $password) {
    // Check the 'admins' table for admin login
    $admin_sql = "SELECT * FROM admins WHERE Username = ?";
    $stmt = $db->prepare($admin_sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin_result = $stmt->get_result()->fetch_assoc();

    // If the username exists in the 'admins' table and the password is correct
    if ($admin_result && password_verify($password, $admin_result['Password'])) {
        $_SESSION['admin_id'] = $admin_result['AdminID'];
        $_SESSION['is_admin'] = true;
        $_SESSION['username'] = $username;
        
        return true;
    }

    return false;
}

// Function to authenticate a user by username and password
function authenticateUser($db, $username, $password) {
    // Check the 'admins' table for admin login
    


    // Check the 'users' table for regular user login
    $user_sql = "SELECT * FROM user WHERE Username = ?";
    $stmt = $db->prepare($user_sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $user_result = $stmt->get_result()->fetch_assoc();
    
    // If the username exists in the 'users' table and the password is correct
    if ($user_result && password_verify($password, $user_result['Password'])) {
        $_SESSION['user_id'] = $user_result['UserID'];
        $_SESSION['is_admin'] = false;
        $_SESSION['username'] = $username;
        return true;
    }

    return false;
}

