<?php
require('authenticate.php');



$servername = "localhost"; // Replace with your database server
$username = "serveruser"; // Replace with your database username
$password = "gorgonzola7!"; // Replace with your database password
$dbname = "serverside"; // Replace with your database name

// Create a database connection using PDO
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to validate if a string is an integer
function isInteger($str) {
    return preg_match('/^[0-9]+$/', $str);
}

// Check if the 'id' parameter is set in the URL and validate it as an integer
if (isset($_GET['id']) && isInteger($_GET['id'])) {
    $movieId = intval($_GET['id']);

    // Retrieve the movie data based on the movie ID from your database using PDO
    $sql = "SELECT * FROM movie WHERE MovieID = :movieId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $title = $result['Title'];
        $releaseDate = $result['Release_Date'];
        $ageRating = $result['Age_Rating'];
        $description = $result['Description'];
        $language = $result['Language'];
        $runtime = $result['Runtime'];
        $director = $result['Director'];
        $actors = $result['Actors'];
        $actors = $result['Genre'];

        // Retrieve and encode the movie poster data
        $posterData = $result['Movie_Poster'];
    } else {
        // Invalid or non-existent ID, redirect to the index page
        header("Location: index.php");
        exit();
    }
}

// Handle form submission for updating or deleting the movie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize the input as needed
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (isset($_POST['delete'])) {
        // Delete the movie from the database
        $deleteSql = "DELETE FROM movie WHERE MovieID = :movieId";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);

        if ($deleteStmt->execute()) {
            // Movie deleted successfully, redirect to the index page
            header("Location: index.php");
            exit();
        } else {
            error_log("Error deleting the movie: " . $deleteStmt->errorInfo()[2]);
            echo "An error occurred while deleting the movie.";
        }
    } else {
        // Update the movie in the database, including all columns

        // Handle movie poster update
        if (isset($_FILES['movie_poster']) && $_FILES['movie_poster']['error'] === UPLOAD_ERR_OK) {
            $posterTmpName = $_FILES['movie_poster']['tmp_name'];

            if (is_uploaded_file($posterTmpName)) {
                $posterData = file_get_contents($posterTmpName);
            }
        }

        $updateSql = "UPDATE movie SET 
        Title = :title, 
        Release_Date = :releaseDate, 
        Age_Rating = :ageRating, 
        Description = :description, 
        Language = :language, 
        Runtime = :runtime, 
        Movie_Poster = :posterData, 
        Director = :director, 
        Actors = :actors,
        Genre = :genre 
        WHERE MovieID = :movieId";

        $updateStmt = $conn->prepare($updateSql);

        // Bind parameters for all fields
        $updateStmt->bindParam(':title', $title, PDO::PARAM_STR);
        $updateStmt->bindParam(':releaseDate', $releaseDate, PDO::PARAM_STR);
        $updateStmt->bindParam(':ageRating', $ageRating, PDO::PARAM_STR);
        $updateStmt->bindParam(':description', $description, PDO::PARAM_STR);
        $updateStmt->bindParam(':language', $language, PDO::PARAM_STR);
        $updateStmt->bindParam(':runtime', $runtime, PDO::PARAM_STR);
        $updateStmt->bindParam(':posterData', $posterData, PDO::PARAM_LOB); // Assuming Movie_Poster is a BLOB type
        $updateStmt->bindParam(':director', $director, PDO::PARAM_STR);
        $updateStmt->bindParam(':actors', $actors, PDO::PARAM_STR);
        $updateStmt->bindParam(':genre', $genre, PDO::PARAM_STR);
        $updateStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);

        if ($updateStmt->execute()) {
            // Movie updated successfully, redirect to the index page or the movie details page
            header("Location: index.php?id=$movieId");
            exit();
        } else {
            error_log("Error updating the movie: " . $updateStmt->errorInfo()[2]);
            echo "An error occurred while updating the movie.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Edit Movie</title>
</head>

<body>
    <div class="movie-cms-box">
        <h1>Edit Movie</h1>
        <div class="error-message">
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && (strlen($title) < 1 || strlen($description) < 1)) {
                echo "Validation error: Title and description must be at least 1 character in length.";
            }
            ?>
        </div>
        <div class="movie-details-and-edit">
            <!-- Movie details -->
            <div class="movie-details">
                <h2>Title: <?= $title ?></h2>
                <p>Release Date: <?= $releaseDate ?></p>
                <p>Age Rating: <?= $ageRating ?></p>
                <p>Description: <?= $description ?></p>
                <p>Language: <?= $language ?></p>
                <p>Runtime: <?= $runtime ?></p>
                <p>Director: <?= $director ?></p>
                <p>Actors: <?= $actors ?></p>
                <p>Genre: <?= $genre ?></p>
                <img src="data:image/jpeg;base64,<?= base64_encode($posterData) ?>" alt="Movie Poster">
            </div>

            <!-- Edit form -->
            <div class="movie-edit-form">
                <h2>Edit Movie</h2>
                <form action="edit.php?id=<?php echo $movieId; ?>" method="post" enctype="multipart/form-data">
                    <!-- Your form elements go here -->
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" value="<?= ($title); ?>" required>

                    <label for="releaseDate">Release Date:</label>
                    <input type="text" id="releaseDate" name="releaseDate" value="<?= ($releaseDate); ?>" required>

                    <label for="ageRating">Age Rating:</label>
                    <input type="text" id="ageRating" name="ageRating" value="<?= ($ageRating); ?>" required>

                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required><?= ($description); ?></textarea>

                    <label for="language">Language:</label>
                    <input type="text" id="language" name="language" value="<?= ($language); ?>" required>

                    <label for="runtime">Runtime:</label>
                    <input type="text" id="runtime" name="runtime" value="<?= ($runtime); ?>" required>

                    <label for="director">Director:</label>
                    <input type="text" id="director" name="director" value="<?= ($director); ?>" required>

                    <label for="actors">Actors:</label>
                    <input type="text" id="actors" name="actors" value="<?= ($actors); ?>" required>

                    <label for="genre">Genre:</label>
                    <input type="text" id="genre" name="genre" value="<?= ($genre); ?>" required>

                    <label for="movie_poster">Movie Poster:</label>
                    <input type="file" id="movie_poster" name="movie_poster">

                    <button type="submit">Update</button>
                    <button type="submit" name="delete">Delete Movie</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
