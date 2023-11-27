<?php

session_start();
$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

// Create a database connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve search query and category
$searchQuery = isset($_GET['q']) ? $_GET['q'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Store search query and category in session
$_SESSION['searchQuery'] = $searchQuery;
$_SESSION['searchCategory'] = $category;

// Build SQL query with category filter
$sql = "SELECT m.* FROM movie m 
        LEFT JOIN movie_category mc ON m.MovieID = mc.movie_id
        WHERE m.title LIKE '%$searchQuery%'
        AND ('$category' = '' OR mc.category_id = '$category')";

$result = $conn->query($sql);

// Check for database errors
if (!$result) {
    die("Error: " . $conn->error);
}

// Fetch categories for dropdown menu
$categoryQuery = "SELECT * FROM categories";
$categoryResult = $conn->query($categoryQuery);

// Check for database errors
if (!$categoryResult) {
    die("Error: " . $conn->error);
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Search Results</title>
</head>

<body>
    <h2>Search Results</h2>
    <!-- Search form with category dropdown -->
    <form action="search.php" method="GET">
        <input type="text" name="q" placeholder="Search movies..." value="<?= $searchQuery ?>">
        <select name="category">
            <option value="" <?php if ($category == '') echo 'selected'; ?>>All Categories</option>
            <?php
            while ($categoryRow = $categoryResult->fetch_assoc()) {
                $categoryId = $categoryRow['category_id'];
                $categoryName = $categoryRow['category_name'];
                echo "<option value='$categoryId' " . ($category == $categoryId ? 'selected' : '') . ">$categoryName</option>";
            }
            ?>
        </select>
        <button type="submit">Search</button>
    </form>

    <ul>
        <?php
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
        ?>
    </ul>

    <!-- Add pagination links here if needed -->
    <a href="index.php">Go back to the home page</a>
</body>

</html>
