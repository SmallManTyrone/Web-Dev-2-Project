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
    $genre = $_POST['genre'];

    // Check for file upload errors
    if ($_FILES['movie_poster']['error'] === UPLOAD_ERR_OK) {
        $tempFile = $_FILES['movie_poster']['tmp_name'];
        $targetPath = 'path_to_your_upload_directory/' . $_FILES['movie_poster']['name'];

        // Move the uploaded file to its final location
        if (move_uploaded_file($tempFile, $targetPath)) {
            $moviePoster = base64_encode(file_get_contents($targetPath));

            $director = $_POST['director'];
            $actors = $_POST['actors'];

            // Validate and sanitize the input as needed

            // Insert the movie into the database
            $sql = "INSERT INTO movie (Title, Release_Date, Age_Rating, Description, Language, Runtime, Movie_Poster, Director, Actors, Genre) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssss", $title, $releaseDate, $ageRating, $description, $language, $runtime, $moviePoster, $director, $actors, $genre);

            if ($stmt->execute()) {
                // Movie added successfully
                header("Location: index.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Error moving the uploaded file.";
        }
    } else {
        echo "File upload error: " . $_FILES['movie_poster']['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
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
            <input type="text" id="release_date" name="release_date" required>

            <label for="age_rating">Age Rating:</label>
            <input type="text" id="age_rating" name="age_rating" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="language">Language:</label>
            <input type="text" id="language" name="language" required>

            <label for="runtime">Runtime:</label>
            <input type="text" id="runtime" name="runtime" required>

            <label for="movie_poster">Movie Poster:</label>
            <input type="file" id="movie_poster" name="movie_poster" required>

            <label for="director">Director:</label>
            <input type="text" id="director" name="director" required>

            <label for="actors">Actors:</label>
            <input type="text" id="actors" name="actors" required>

            <label for="genre">Genre:</label>
            <input type="text" id="genre" name="genre" required>

            <button type="submit">Add Movie</button>
        </form>
    </div>
</body>
</html>
