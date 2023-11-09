<?php
/*
Name: Tyson La
Date: September 20, 2023
Description: Movie Listing Page
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

// Handle new movie submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $releaseDate = $_POST['release_date'];
    $ageRating = $_POST['age_rating'];
    $description = $_POST['description'];
    $language = $_POST['language'];
    $runtime = $_POST['runtime'];
    $director = $_POST['director'];
    $actors = $_POST['actors'];

    // Retrieve and sanitize the genres input
    $genresInput = $_POST['genres'];
    $genresArray = array_map('trim', explode(',', $genresInput));

    // Initialize moviePoster as null (no file uploaded)
    $moviePoster = null;

    // Check for file upload errors
    if ($_FILES['movie_poster']['error'] === UPLOAD_ERR_OK) {
        // Read the file content
        $moviePoster = file_get_contents($_FILES['movie_poster']['tmp_name']);
        
        // Add image resizing code
        if (extension_loaded('gd')) {
            list($origWidth, $origHeight) = getimagesize($_FILES['movie_poster']['tmp_name']);
            $maxWidth = 500; // Define the maximum width for the poster
            $maxHeight = 750; // Define the maximum height for the poster
            
            if ($origWidth > $maxWidth || $origHeight > $maxHeight) {
                $ratio = $origWidth / $origHeight;
                
                if ($maxWidth / $maxHeight > $ratio) {
                    $maxWidth = $maxHeight * $ratio;
                } else {
                    $maxHeight = $maxWidth / $ratio;
                }

                $resizedPoster = imagecreatetruecolor($maxWidth, $maxHeight);
                $sourceImage = imagecreatefromstring($moviePoster);
                
                imagecopyresampled($resizedPoster, $sourceImage, 0, 0, 0, 0, $maxWidth, $maxHeight, $origWidth, $origHeight);

                ob_start(); // Turn on output buffering
                imagejpeg($resizedPoster, null, 90);
                $moviePoster = ob_get_clean(); // Get the resized image content
                imagedestroy($sourceImage);
                imagedestroy($resizedPoster);
            }
        }
        // End of image resizing code
    }

    // Insert the movie into the database
    $sql = "INSERT INTO movie (Title, Release_Date, Age_Rating, Description, Language, Runtime, Movie_Poster, Director, Actors) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $title, $releaseDate, $ageRating, $description, $language, $runtime, $moviePoster, $director, $actors);

    if ($stmt->execute()) {
        // Movie added successfully
        $movieId = $stmt->insert_id; // Get the ID of the inserted movie

        // Initialize an array to store genre IDs
        $genreIds = [];

        foreach ($genresArray as $genre) {
            // Sanitize and validate the genre as needed
            $genre = trim($genre);

            // Check if the genre already exists in the "genre" table
            $genreCheckSql = "SELECT genre_id FROM genre WHERE name = ?";
            $genreCheckStmt = $conn->prepare($genreCheckSql);
            $genreCheckStmt->bind_param("s", $genre);
            $genreCheckStmt->execute();
            $genreCheckStmt->store_result();

            if ($genreCheckStmt->num_rows > 0) {
                $genreCheckStmt->bind_result($genreId);
                $genreCheckStmt->fetch();
                // The genre already exists, use its ID
                $genreIds[] = $genreId;
            } else {
                $genreInsertSql = "INSERT INTO genre (name) VALUES (?)";
                $genreInsertStmt = $conn->prepare($genreInsertSql);
                $genreInsertStmt->bind_param("s", $genre);
                $genreInsertStmt->execute();

                $genreId = $conn->insert_id;
                $genreIds[] = $genreId;
            }

            // Close the statement for checking and inserting genres
            $genreCheckStmt->close();
        }

        // Insert genres and associate them with the movie in the "movie_genre" junction table
        foreach ($genreIds as $genreId) {
            $movieGenreSql = "INSERT INTO movie_genre (movie_id, genre_id) VALUES (?, ?)";
            $movieGenreStmt = $conn->prepare($movieGenreSql);
            $movieGenreStmt->bind_param("ii", $movieId, $genreId);
            $movieGenreStmt->execute();
            $movieGenreStmt->close();
        }

        header("Location: index.php");
        exit();
    } else {
        echo "Database error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Styles.css">
    <title>Movie CMS</title>
</head>

<body>
    <div class="add-movie">
        <h2>Add Movie</h2>
        <form action="post.php" method="post" enctype="multipart/form-data">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="release_date">Release Date:</label>
            <input type="text" id="release_date" name "release_date" required>

            <label for="age_rating">Age Rating:</label>
            <input type="text" id="age_rating" name="age_rating" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="language">Language:</label>
            <input type="text" id="language" name="language" required>

            <label for="runtime">Runtime:</label>
            <input type="text" id="runtime" name="runtime" required>

            <label for="movie_poster">Movie Poster:</label>
            <input type="file" id="movie_poster" name="movie_poster">

            <label for="director">Director:</label>
            <input type="text" id="director" name="director" required>

            <label for="actors">Actors:</label>
            <input type="text" id="actors" name="actors" required>

            <label for="genres">Genres:</label>
            <input type="text" id="genres" name="genres" placeholder="Enter genres (e.g., Action, Comedy, Drama)">

            <button type="submit">Add Movie</button>
        </form>
    </div>
</body>
</html>
