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

$error_message = ''; // Initialize the error message

// Handle new movie submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize form fields
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $releaseDate = filter_input(INPUT_POST, 'release_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $ageRating = filter_input(INPUT_POST, 'age_rating', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $language = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $runtime = filter_input(INPUT_POST, 'runtime', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $director = filter_input(INPUT_POST, 'director', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $actors = filter_input(INPUT_POST, 'actors', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validate and sanitize category ID
    $category = filter_input(INPUT_POST, 'category', FILTER_VALIDATE_INT);

    // Retrieve and sanitize the genres input
    $genresInput = $_POST['genres'];
    $genresArray = array_map('trim', explode(',', $genresInput));

    // Validate and sanitize file input for movie poster
    $posterFile = $_FILES['movie_poster'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $posterExtension = pathinfo($posterFile['name'], PATHINFO_EXTENSION);

    if (in_array(strtolower($posterExtension), $allowedExtensions)) {
        // Proceed with file handling
    } else {
        // Invalid file extension
        echo "Invalid file extension for movie poster.";
        exit();
    }

    try {
        // Prepare the movie insertion statement
        $stmt = $conn->prepare("INSERT INTO movie (category_id, Title, Release_Date, Age_Rating, Description, Language, Runtime, Movie_Poster, Director, Actors) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        // Execute the movie insertion statement
        if ($stmt->execute()) {
            // Rest of your code...
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
    }

    try {
        // Prepare the movie insertion statement
        $stmt = $conn->prepare("INSERT INTO movie (category_id, Title, Release_Date, Age_Rating, Description, Language, Runtime, Movie_Poster, Director, Actors) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception($conn->error);
        }

        // Bind parameters for movie insertion
        $stmt->bind_param("isssssssss", $category, $title, $releaseDate, $ageRating, $description, $language, $runtime, $resizedImageContents, $director, $actors);

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
            $categoryStmt->bind_param("ii", $movieId, $category);

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

                    $userMovieSql = "INSERT INTO user_movie (userid, movieid) VALUES (?, ?)";
                    $userMovieStmt = $conn->prepare($userMovieSql);
                    $userMovieStmt->bind_param("ii", $userId, $movieId);
                    $userMovieStmt->execute();
                    $userMovieStmt->close();
                }

                // Set the success message
                $successMessage = "Movie successfully added!";

                // Store the success message in the session
                $_SESSION['successMessage'] = $successMessage;

                // Redirect to index.php
                header("Location: post.php");
                exit();
            }
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
    <title>Make A post</title>
    <link rel="stylesheet" href="poststyles.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
</head>

<body>

    <div class="add-movie">
        <h2>Add Movie</h2>
        <nav>
            <div>

                <!-- Search bar -->
                <form action="search.php" method="GET">
                    <input type="text" name="q" placeholder="Search movies...">
                    <button type="submit">Search</button>
                </form>
            </div>
        </nav>
        <ul>
            <?php
            if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'user_management.php') !== false) {
                echo '<li><a href="user_management.php">Go back to manage users</a></li>';
            }
            ?>
            <li><a href="index.php">Home</a></li>
        </ul>

        <form action="post.php" method="post" enctype="multipart/form-data">

            <label for="category">Category:</label>
            <select name="category" id="category" required>
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
            <input type="date" id="release_date" name="release_date" required>

            <label for="age_rating">Age Rating:</label>
            <input type="text" id="age_rating" name="age_rating" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="language">Language:</label>
            <input type="text" id="language" name="language" required>

            <label for="runtime">Runtime:</label>
            <input type="text" id="runtime" name="runtime" required>

            <label for="director">Director:</label>
            <input type="text" id="director" name="director" required>

            <label for="actors">Actors:</label>
            <input type="text" id="actors" name="actors" required>

            <label for="genres">Genres:</label>
            <input type="text" id="genres" name="genres" placeholder="Enter genres (e.g., Action, Comedy, Drama)" required>

            <label for="movie_poster">Movie Poster:</label>
            <input type="file" id="movie_poster" name="movie_poster">

            <button type="submit">Add Movie</button>
        </form>
    </div>

</body>

</html>
