<?php
$servername = "localhost"; // Replace with your database server
$username = "serveruser"; // Replace with your database username
$password = "gorgonzola7!"; // Replace with your database password
$dbname = "serverside"; // Replace with your database name

// Create a database connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve search query
$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';

// Perform search logic (modify according to your database structure)
// Example SQL query: SELECT * FROM movie WHERE title LIKE '%$searchQuery%'
$sql = "SELECT * FROM movie WHERE title LIKE '%$searchQuery%'";
$result = $conn->query($sql);

// Check for database errors
if (!$result) {
    die("Error: " . $conn->error);
}

// Close the database connection
$conn->close();

// Process search results
if ($result && $result->num_rows > 0) {
    // Display a list of links to all found pages
    echo '<h2>Search Results</h2>';
    echo '<ul>';
    
    while ($row = $result->fetch_assoc()) {
        // Check if 'MovieID' and 'Title' keys exist in $row before accessing them
        if (isset($row['MovieID'], $row['Title'])) {
            // Display each movie result as a clickable link
            echo '<li><a href="show.php?id=' . $row['MovieID'] . '">' . $row['Title'] . '</a></li>';
        } else {
            // Output the entire $row array for debugging purposes
            echo '<li>Error: Missing data for this movie - ' . print_r($row, true) . '</li>';
        }
    }

    echo '</ul>';
    
    // Add pagination links here if needed
} else {
    echo 'No results found. <a href="index.php">Go back to the index page</a>.';
}
?>
