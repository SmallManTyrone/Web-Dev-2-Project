<?php
/*
Name: Tyson La
Date: September 20th
Description: Blog Home Page
*/

require('authenticate.php');

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




// Query to retrieve the most recent blog posts
$sql = "SELECT * FROM movie"; // Update the table name to match your database structure
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Styles.css"> <!-- Include your CSS file for styling -->
    <title>Welcome to Movie CMS!</title>

</head>

<body>

    <!-- Movie CMS header -->
    <div class="movie-cms-box">
        <h1>Welcome to Movie CMS</h1>
        <?php


    

    


    ?>


        <ul class="navigation-menu">
            <li><a href="index.php">Home</a></li>
            <?php


if (isLoggedIn() || isAdminLoggedIn()) {
    // Show links for both users and admins
    echo '<li><a href="post.php">Add Movie</a></li>';
    echo '<li><a href="view-list.php">View Movies</a></li>';
    echo '<li><a href="logout.php">Log Out</a></li>'; // Add the logout link here
}

            

    
    
            
            ?>
            <li><a href="register.php">Make Account</a></li>
            <li><a href="login.php">Login</a></li>
        </ul>
        <div class="movie-header">

        </div>
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
            $genre = $row["Genre"];
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
                <p>Genre: <?= $genre ?></p>

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