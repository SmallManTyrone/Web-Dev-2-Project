<?php
/*
Name: Tyson La
Date: September 20, 2023
Description: Movie Listing Page
*/

require('authenticate.php');

$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = '';

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

    echo "Selected Category: " . $_POST['category'] . "<br>";

    // Retrieve and sanitize the genres input
    $genresInput = $_POST['genres'];
    $genresArray = array_map('trim', explode(',', $genresInput));

    // Debugging: Output genres for inspection
    echo "Genres Input: " . $genresInput . "<br>";
    echo "Genres Array: ";
    print_r($genresArray);
    echo "<br>";

    // Initialize moviePoster as null (no file uploaded)
    $moviePoster = null;

  // Check for file upload errors
if ($_FILES['movie_poster']['error'] === UPLOAD_ERR_OK) {
    // Read the file content
    $moviePoster = file_get_contents($_FILES['movie_poster']['tmp_name']);

    // Add image resizing code
    if (extension_loaded('gd')) {
        // Create an image resource from the file content
        $sourceImage = imagecreatefromstring($moviePoster);

        // Specify the new width and height for the resized image
        $newWidth = 182; 
        $newHeight = 268; 

        // Create a new image resource with the desired width and height
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // Resize the image
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($sourceImage), imagesy($sourceImage));

        // Output the resized image to a variable
        ob_start();
        imagejpeg($resizedImage, null, 100); // You can change the image format and quality if needed
        $resizedImageData = ob_get_clean();

        // Clean up resources
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

    }
 
}


    // Assuming you have a column named 'category_id' in your movie table to store the category ID.
    $sql = "INSERT INTO movie (category_id, Title, Release_Date, Age_Rating, Description, Language, Runtime, Movie_Poster, Director, Actors) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    try {
        // Prepare the movie insertion statement
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        // Bind parameters for movie insertion
        $stmt->bind_param("isssssssss", $_POST['category'], $title, $releaseDate, $ageRating, $description, $language, $runtime, $moviePoster, $director, $actors);

        // Execute the movie insertion statement
        if ($stmt->execute()) {
            // Movie added successfully
            $movieId = $stmt->insert_id; // Get the ID of the inserted movie
            $stmt->close(); // Close the movie insertion statement

            // Insert the category into the movie_category table
            $categorySql = "INSERT INTO movie_category (movie_id, category_id) VALUES (?, ?)";
            $categoryStmt = $conn->prepare($categorySql);

            if (!$categoryStmt) {
                throw new Exception($conn->error);
            }

            // Bind parameters for category insertion
            $categoryStmt->bind_param("ii", $movieId, $_POST['category']);

            // Execute the category insertion statement
            if ($categoryStmt->execute()) {
                // Category added successfully
                $categoryStmt->close(); // Close the category insertion statement

                // Process and insert genres into the movie_genre table
                $genreIds = []; // Initialize an array to store genre IDs

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
                        // Insert the genre into the "genre" table
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

                // Insert the user-movie relationship into the "user_movie" table
                if (isLoggedIn()) {
                    $userId = $_SESSION['user_id'];
                    echo "User ID: " . $userId . "<br>"; // Add this line for debugging

                    $userMovieSql = "INSERT INTO user_movie (userid, movieid) VALUES (?, ?)";
                    $userMovieStmt = $conn->prepare($userMovieSql);
                    $userMovieStmt->bind_param("ii", $userId, $movieId);
                    $userMovieStmt->execute();
                    $userMovieStmt->close();
                }


                $successMessage = "Movie successfully added!";
            } else {
                throw new Exception($categoryStmt->error);
            }
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="poststyles.css">
</head>

<body>
    
    <div class="add-movie">
        <h2>Add Movie</h2>
        <ul>
            <?php
        if (isAdminLoggedIn()) {
                echo '<li><a href="user-management.php">go to manage users</a></li>';
                
            }
            ?>
            <li><a href="index.php">Home</a></li>
        </ul>
        <?php if (!empty($successMessage)) : ?>
            <div class="success-message">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <form action="post.php" method="post" enctype="multipart/form-data">

            <label for="category">Category:</label>
            <select name="category" id="category">
                <option value="">Select a category</option>
                <?php
                // Fetch categories from the database and generate options
                $categorySql = "SELECT * FROM categories";
                $categoryResult = $conn->query($categorySql);
                while ($category = $categoryResult->fetch_assoc()) {
                    echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                }
                ?>
            </select>
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
