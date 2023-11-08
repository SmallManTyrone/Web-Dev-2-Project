<?php
/*
Name: Tyson La
Date: September 20th
Description: Movie Listing Page
*/

require('authenticate.php');



// Check if the login success message is set


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

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        // The user is an admin
        echo 'Hello, Admin ' . $username;
    } else {
        // The user is a regular user
        echo 'Hello, User ' . $username;
    }
} else {
    // User is not logged in
    echo 'Hello, Guest';
}

// Query to retrieve the most recent movie data along with associated genres
$sql = "SELECT movie.*, GROUP_CONCAT(genre.name SEPARATOR ', ') AS genre_list
        FROM movie
        LEFT JOIN movie_genre ON movie.MovieID = movie_genre.movie_id
        LEFT JOIN genre ON movie_genre.genre_id = genre.genre_id
        GROUP BY movie.MovieID";

$result = $conn->query($sql);




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
    
    <title>Movies</title>
   

</head>

<body>
    <div class="movie-cms-box">
        <h1>Welcome to Movie CMS</h1>
        <?php
        if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
    echo '<div class="success-message-container" id="successMessageContainer">';
    echo '<div class="success-message">Login was successful! Click anywhere to close.</div>';
    echo '</div>';
    $_SESSION['login_success'] = false;
   
}
?>
        <ul class="navigation-menu">
            <li><a href="index.php">Home</a></li>
            <?php



        
if (isAdminLoggedIn()) {
    echo '<li><a href="post.php">Add Movie</a></li>';
    echo '<li><a href="sort-list.php">Sort Movies</a></li>';
    echo '<li><a href="logout.php">Log Out</a></li>';
    echo '<li><a href="user-management.php">User Management</a>'; // Show the link only for admin
} else if (isLoggedIn()) { // Separate condition for regular users
    echo '<li><a href="post.php">Add Movie</a></li>';
    echo '<li><a href="sort-list.php">Sort Movies</a></li>';
    echo '<li><a href="logout.php">Log Out</a></li>';
} else {
    echo '<li><a href="register.php">Make Account</a></li>';
    echo '<li><a href="login.php">Login</a></li>';
}
            ?>
        </ul>
        <div class="movie-header"></div>
        <div class="movie-list">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $movieId = $row["MovieID"];
                    $title = $row["Title"];
                    $releaseDate = $row["Release_Date"];
                    $ageRating = $row["Age_Rating"];
                    $description = $row["Description"];
                    $language = $row["Language"];
                    $runtime = $row["Runtime"];
                    $poster = $row["Movie_Poster"];
                    $director = $row["Director"];
                    $actors = $row["Actors"];
                    $genres = $row["genre_list"]; // List of associated genres
            ?>
            <div class="movie">
                <h2><a href='show.php?id=<?= $movieId ?>'><?= $title ?></a></h2>
                <p>Release Date: <?= $releaseDate ?></p>
                <p>Age Rating: <?= $ageRating ?></p>
                <p>Description: <?= $description ?></p>
                <p>Language: <?= $language ?></p>
                <p>Runtime: <?= $runtime . " Minutes"?></p>
                <p>Director: <?= $director ?></p>
                <p>Actors: <?= $actors ?></p>
                <p>Genres: <?= $genres ?></p> <!-- Display associated genres here -->

                <!-- Display the movie poster here -->
                <img src="data:image/jpeg;base64,<?= base64_encode($poster) ?>" alt="Movie Poster" width="200">
                <?php
                if (isLoggedIn() || isAdminLoggedIn()) {
                    echo '<a href="edit.php?id=' . $movieId . '" class="edit-button">edit</a>';
                }
                ?>
            </div>
            <?php
            }
        }
        ?>
        </div>
    </div>
</body>
</html>
