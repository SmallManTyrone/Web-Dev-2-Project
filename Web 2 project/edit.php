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


$checkConstraintSql = "SELECT COUNT(*) as count
                       FROM information_schema.TABLE_CONSTRAINTS
                       WHERE CONSTRAINT_SCHEMA = :dbname
                       AND CONSTRAINT_NAME = 'movie_category_ibfk_1'
                       AND TABLE_NAME = 'movie_category'
                       AND CONSTRAINT_TYPE = 'FOREIGN KEY'";

$checkConstraintStmt = $conn->prepare($checkConstraintSql);
$checkConstraintStmt->bindParam(':dbname', $dbname, PDO::PARAM_STR);
$checkConstraintStmt->execute();
$count = $checkConstraintStmt->fetchColumn();

if ($count == 0) {
    $alterTableSql = "ALTER TABLE `movie_category`
                      ADD CONSTRAINT `movie_category_ibfk_1`
                      FOREIGN KEY (`movie_id`)
                      REFERENCES `movie` (`MovieID`)
                      ON DELETE CASCADE";

    $alterTableStmt = $conn->prepare($alterTableSql);

    try {
        $alterTableStmt->execute();
        echo "Foreign key constraint updated successfully.";
    } catch (PDOException $e) {
        echo "Error updating foreign key constraint: " . $e->getMessage();
        // Handle the error as needed
    }
} else {
    echo "Foreign key constraint already exists.";
}

// Function to get the category name based on category ID
function getCategoryName($categoryId, $conn)
{
    $sql = "SELECT category_name FROM categories WHERE category_id = :categoryId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the category is found
    if ($result !== false) {
        return $result['category_name'];
    } else {
        // If the category is not found, return the category ID as a fallback
        return $categoryId;
    }
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

    $ownersql = "SELECT movie.*, user_movie.UserID as ownerUserID 
        FROM movie
        LEFT JOIN user_movie ON movie.MovieID = user_movie.MovieID
        WHERE movie.MovieID = :movieId";
    $ownerstmt = $conn->prepare($ownersql);
    $ownerstmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
    $ownerstmt->execute();
    $ownerresult = $ownerstmt->fetch(PDO::FETCH_ASSOC);

    if ($ownerresult) {
        $title = $ownerresult['Title'];
        $releaseDate = $ownerresult['Release_Date'];
        $ageRating = $ownerresult['Age_Rating'];
        $description = $ownerresult['Description'];
        $language = $ownerresult['Language'];
        $runtime = $ownerresult['Runtime'];
        $director = $ownerresult['Director'];
        $actors = $ownerresult['Actors'];
        $posterData = $ownerresult['Movie_Poster'];
        $movieUserID = $ownerresult['ownerUserID'];
    } else {
        // Invalid or non-existent ID, redirect to the index page
        header("Location: index.php");
        exit();
    }

  
}

// Debug: Print out admin-related session variables
echo "Admin ID in Session: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'Not set') . "<br>";
echo "Is Admin in Session: " . (isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 'Not set') . "<br>";

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    // This block will execute if $_SESSION['is_admin'] is set to true
    $userIsAdmin = true; // Optionally set a local variable to indicate admin status.
} else {
    $userIsAdmin = false; // Optionally set a local variable to indicate non-admin status.
}

// Check if the currently logged-in user is the owner of the movie post
$loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$userIsOwner = false;

if ($loggedInUserId && $movieUserID && $loggedInUserId == $movieUserID) {
    $userIsOwner = true;
}
echo "Debug - Logged-in User ID: $loggedInUserId<br>";
echo "Debug - Movie Owner UserID: $movieUserID<br>";

echo "Debug - Session Data: " . print_r($_SESSION, true) . "<br>";


// Retrieve the list of genres associated with the movie
$genreSql = "SELECT genre.name FROM genre
             INNER JOIN movie_genre ON genre.genre_id = movie_genre.genre_id
             WHERE movie_genre.movie_id = :movieId";
$genreStmt = $conn->prepare($genreSql);
$genreStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
$genreStmt->execute();
$genres = $genreStmt->fetchAll(PDO::FETCH_COLUMN);


// Fetch the actual category name from the movie_category junction table
$categoryNameSql = "SELECT c.category_name 
                   FROM movie_category mc
                   JOIN categories c ON mc.category_id = c.category_id
                   WHERE mc.movie_id = :movieId";
$categoryNameStmt = $conn->prepare($categoryNameSql);
$categoryNameStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);

if ($categoryNameStmt->execute()) {
    $categoryName = $categoryNameStmt->fetchColumn();
    echo "Debug - Retrieved Category Name: $categoryName";
} else {
    echo "Debug - Error executing SQL query: " . print_r($categoryNameStmt->errorInfo(), true);
}

// If you want to fetch category name instead of ID, you can use the getCategoryName function
$category = $categoryName;

// Before the update operation
// Fetch the current category ID before the update
$currentCategorySql = "SELECT category_id FROM movie_category WHERE movie_id = :movieId";
$currentCategoryStmt = $conn->prepare($currentCategorySql);
$currentCategoryStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
$currentCategoryStmt->execute();
$currentCategoryId = $currentCategoryStmt->fetchColumn();

echo "Debug - Current Category ID in movie_category: $currentCategoryId<br>";


// After retrieving $currentCategoryId, set the default value
$selectedCategoryId = isset($currentCategoryId) ? $currentCategoryId : null;



// Handle form submission for updating or deleting the movie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize the input as needed
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $releaseDate = filter_input(INPUT_POST, 'releaseDate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $ageRating = filter_input(INPUT_POST, 'ageRating', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $language = filter_input(INPUT_POST, 'language', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $runtime = filter_input(INPUT_POST, 'runtime', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $director = filter_input(INPUT_POST, 'director', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $actors = filter_input(INPUT_POST, 'actors', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $selectedCategoryId = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    echo "Debug - Selected Category ID: " . (is_array($selectedCategoryId) ? 'No category selected' : $selectedCategoryId);


    // Identify categories to be added and removed
    $categoriesToAdd = array_diff([$selectedCategoryId], [$currentCategoryId]);
    $categoriesToRemove = array_diff([$currentCategoryId], [$selectedCategoryId]);

    // Add new category
    foreach ($categoriesToAdd as $categoryId) {
        if ($categoryId !== null) {
            $addCategorySql = "INSERT INTO movie_category (movie_id, category_id) VALUES (:movieId, :categoryId)";
            $addCategoryStmt = $conn->prepare($addCategorySql);
            $addCategoryStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
            $addCategoryStmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $addCategoryStmt->execute();
        }
    }

    // Remove old category
    foreach ($categoriesToRemove as $categoryId) {
        if ($categoryId !== null) {
            $removeCategorySql = "DELETE FROM movie_category WHERE movie_id = :movieId AND category_id = :categoryId";
            $removeCategoryStmt = $conn->prepare($removeCategorySql);
            $removeCategoryStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
            $removeCategoryStmt->bindParam(':categoryId', $categoryId, PDO::PARAM_INT);
            $removeCategoryStmt->execute();
        }
    }
    $genresInput = $_POST['genres'];
    $submittedGenres = array_map('trim', explode(',', $genresInput));

    // Retrieve the genres associated with the movie from the database
    $currentGenresSql = "SELECT genre_id FROM movie_genre WHERE movie_id = :movieId";
    $currentGenresStmt = $conn->prepare($currentGenresSql);
    $currentGenresStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
    $currentGenresStmt->execute();
    $currentGenres = $currentGenresStmt->fetchAll(PDO::FETCH_COLUMN);

    // Identify genres to be added and removed
    $genresToAdd = array_diff($submittedGenres, $currentGenres);
    $genresToRemove = array_diff($currentGenres, $submittedGenres);

    // Add new genres
    foreach ($submittedGenres as $genreName) {
        $findGenreSql = "SELECT genre_id FROM genre WHERE name = :genreName";
        $findGenreStmt = $conn->prepare($findGenreSql);
        $findGenreStmt->bindParam(':genreName', $genreName, PDO::PARAM_STR);
        $findGenreStmt->execute();
        $genreId = $findGenreStmt->fetchColumn();

        if ($genreId) {
            $addGenreSql = "INSERT INTO movie_genre (movie_id, genre_id) VALUES (:movieId, :genreId)";
            $addGenreStmt = $conn->prepare($addGenreSql);
            $addGenreStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
            $addGenreStmt->bindParam(':genreId', $genreId, PDO::PARAM_INT);
            $addGenreStmt->execute();
        }
    }

    // Remove old genres
    foreach ($genresToRemove as $genreId) {
        $removeGenreSql = "DELETE FROM movie_genre WHERE movie_id = :movieId AND genre_id = :genreId";
        $removeGenreStmt = $conn->prepare($removeGenreSql);
        $removeGenreStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
        $removeGenreStmt->bindParam(':genreId', $genreId, PDO::PARAM_INT);
        $removeGenreStmt->execute();
    }

    if (isset($_POST['removeImage']) && $_POST['removeImage'] === "1") {
        // Set the posterData to an empty value or null
        $posterData = null; // or $posterData = ''; to ensure it's empty
    }

    if (isset($_POST['confirmDelete']) && $userIsAdmin) {
        // Handle admin's confirmation to delete the movie
        $deleteSql = "DELETE FROM movie WHERE MovieID = :movieId";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
    
        if ($deleteStmt->execute()) {
            echo "Movie deleted successfully.";
            // Movie deleted successfully, redirect to the index page
            header("Location: index.php");
            exit();
        } else {
            echo "Error deleting the movie: " . $deleteStmt->errorInfo()[2];
        }
    }
    
    if (isset($_POST['delete']) && ($userIsOwner || $userIsAdmin)) {
        // Handle owner or admin's request to delete the movie
        $deleteSql = "DELETE FROM movie WHERE MovieID = :movieId";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
    
        if ($deleteStmt->execute()) {
            echo "Movie deleted successfully.";
            // Movie deleted successfully, redirect to the index page
            header("Location: index.php");
            exit();
        } else {
            echo "Error deleting the movie: " . $deleteStmt->errorInfo()[2];
        }
    } elseif ($userIsOwner || $userIsAdmin) {
        // Handle file upload and update the movie
        if ($_FILES['movie_poster']['error'] === UPLOAD_ERR_OK) {
            // Get the uploaded file's temporary name and read its contents
            $posterTmpName = $_FILES['movie_poster']['tmp_name'];
            $posterData = file_get_contents($posterTmpName);
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
        Actors = :actors
        WHERE MovieID = :movieId";

        $updateStmt = $conn->prepare($updateSql);

        $updateStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
        $updateStmt->bindParam(':title', $title, PDO::PARAM_STR);
        $updateStmt->bindParam(':releaseDate', $releaseDate, PDO::PARAM_STR);
        $updateStmt->bindParam(':ageRating', $ageRating, PDO::PARAM_STR);
        $updateStmt->bindParam(':description', $description, PDO::PARAM_STR);
        $updateStmt->bindParam(':language', $language, PDO::PARAM_STR);
        $updateStmt->bindParam(':runtime', $runtime, PDO::PARAM_STR);
        $updateStmt->bindParam(':posterData', $posterData, PDO::PARAM_LOB);
        $updateStmt->bindParam(':director', $director, PDO::PARAM_STR);
        $updateStmt->bindParam(':actors', $actors, PDO::PARAM_STR);

        if ($updateStmt->execute()) {
            echo "Movie updated successfully.";
            // Movie updated successfully, handle genre associations

            // Delete existing genre associations for the movie
            $deleteGenreSql = "DELETE FROM movie_genre WHERE movie_id = :movieId";
            $deleteGenreStmt = $conn->prepare($deleteGenreSql);
            $deleteGenreStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
            $deleteGenreStmt->execute();

            // Then, insert the new genre associations
            $updateGenreSql = "INSERT INTO movie_genre (movie_id, genre_id) VALUES (:movieId, :genreId)";
            $updateGenreStmt = $conn->prepare($updateGenreSql);

            foreach ($submittedGenres as $genreName) {
                // First, find the genre ID based on the genre name
                $findGenreSql = "SELECT genre_id FROM genre WHERE name = :genreName";
                $findGenreStmt = $conn->prepare($findGenreSql);
                $findGenreStmt->bindParam(':genreName', $genreName, PDO::PARAM_STR);
                $findGenreStmt->execute();
                $genreId = $findGenreStmt->fetchColumn();

                if ($genreId) {
                    // Associate the genre with the movie
                    $updateGenreStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
                    $updateGenreStmt->bindParam(':genreId', $genreId, PDO::PARAM_INT);
                    $updateGenreStmt->execute();
                }

// Delete existing category associations for the movie
$deleteCategorySql = "DELETE FROM movie_category WHERE movie_id = :movieId";
$deleteCategoryStmt = $conn->prepare($deleteCategorySql);
$deleteCategoryStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
$deleteCategoryStmt->execute();

// Insert the new category association
$insertCategorySql = "INSERT INTO movie_category (movie_id, category_id) VALUES (:movieId, :categoryId)";
$insertCategoryStmt = $conn->prepare($insertCategorySql);
$insertCategoryStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
$insertCategoryStmt->bindParam(':categoryId', $selectedCategoryId, PDO::PARAM_INT);
$insertCategoryStmt->execute();



$deleteCategory = isset($_POST['deleteCategory']) && $_POST['deleteCategory'] === "1";
if ($deleteCategory) {
    // Delete category association only if $selectedCategoryId is not null
    if ($selectedCategoryId !== null) {
        $deleteCategorySql = "DELETE FROM movie_category WHERE movie_id = :movieId";
        $deleteCategoryStmt = $conn->prepare($deleteCategorySql);
        $deleteCategoryStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);
        $deleteCategoryStmt->execute();
        $selectedCategoryId = null; // Set selected category ID to null after deletion
    } else {
        echo "Error: Category ID is null.";
    }
}

// Update the category association in the movie_category table
$updateCategorySql = "UPDATE movie_category SET category_id = :categoryId WHERE movie_id = :movieId";
$updateCategoryStmt = $conn->prepare($updateCategorySql);
$updateCategoryStmt->bindParam(':movieId', $movieId, PDO::PARAM_INT);

// Bind $selectedCategoryId to the parameter, or bind null if it's null
$updateCategoryStmt->bindValue(':categoryId', $selectedCategoryId, $selectedCategoryId !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);



            }

            // Redirect to the index page or the movie details page
           header("Location: index.php?id=$movieId");
           exit();
        } else {
            echo "Error updating the movie: " . $updateStmt->errorInfo()[2];
        }

        // If you want to fetch category name instead of ID, you can use the getCategoryName function
    }
}

// Debug: Print out user information
echo "User Is Admin: " . ($userIsAdmin ? 'Yes' : 'No') . "<br>";
echo "User Is Owner: " . ($userIsOwner ? 'Yes' : 'No') . "<br>";

echo "User Is Owner: " . ($userIsOwner ? 'Yes' : 'No') . "<br>";
echo "Movie Owner UserID: " . $movieUserID . "<br>";
echo "Logged-in User ID: " . $loggedInUserId . "<br>";

if ($userIsOwner) {
    echo "User is the owner.";
} else {
    echo "User is not the owner.";
}

if ($userIsAdmin) {
    echo "User is an admin.";
} elseif ($userIsOwner) {
    echo "User is the owner.";
} else {
    echo "User is not an admin and not the owner.";
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
    <ul>
        <li><a href="index.php">Home</a></li>
    </ul>
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
            <img id="previewMoviePoster" src="data:image/jpeg;base64,<?= base64_encode($posterData) ?>"
                alt="Movie Poster">
        </div>
        <!-- Edit form -->
        <div class="movie-edit-form">
            <h2>Edit Movie</h2>
            <form action="edit.php?id=<?= $movieId; ?>" method="post" enctype="multipart/form-data">
                <p>Category:

                    <?php
                    
        $allCategoriesSql = "SELECT * FROM categories";
        $allCategoriesStmt = $conn->prepare($allCategoriesSql);
        $allCategoriesStmt->execute();
        $allCategories = $allCategoriesStmt->fetchAll(PDO::FETCH_ASSOC);
        ?>
                    <label for="category">Category:</label>
                    <select name="category" id="category">
                        <option value="">Select a category</option>
                        <?php
    foreach ($allCategories as $category) {
        $categoryId = $category['category_id'];
        $categoryName = $category['category_name'];
        $isSelected = ($categoryId == $selectedCategoryId) ? 'selected' : '';
        echo "<option value=\"$categoryId\" $isSelected>$categoryName</option>";
    }
    ?>
                    </select>

                    <!-- New checkbox for deleting the category -->
                    <label for="deleteCategory">Delete Category:</label>
                    <input type="checkbox" id="deleteCategory" name="deleteCategory" value="1">

                </p>

                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?= $title; ?>" required>
                <label for="releaseDate">Release Date:</label>
                <input type="text" id="releaseDate" name="releaseDate" value="<?= $releaseDate; ?>" required>
                <label for="ageRating">Age Rating:</label>
                <input type="text" id="ageRating" name="ageRating" value="<?= $ageRating; ?>" required>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?= $description; ?></textarea>
                <label for="language">Language:</label>
                <input type="text" id="language" name="language" value="<?= $language; ?>" required>
                <label for="runtime">Runtime:</label>
                <input type="text" id="runtime" name="runtime" value="<?= $runtime; ?>" required>
                <label for="director">Director:</label>
                <input type="text" id="director" name="director" value="<?= $director; ?>" required>
                <label for="actors">Actors:</label>
                <input type="text" id="actors" name="actors" value="<?= $actors; ?>" required>
                <label for="genres">Genres:</label>
                <input type="text" id="genres" name="genres" value="<?= implode(', ', $genres); ?>"
                    placeholder="Enter genres (e.g., Action, Comedy, Drama)">
                <?php if (!empty($posterData)) { ?>
                <label for="removeImage">Remove Image:</label>
                <input type="checkbox" id="removeImage" name="removeImage" value="1">
                <?php } ?>
                <label for="movie_poster">Movie Poster:</label>
                <input type="file" id="movie_poster" name="movie_poster">
                <?php if ($userIsAdmin === true) : ?>
                <button type="submit" name="update">Update</button>
                <button type="submit" name="confirmDelete">Confirm Delete</button>
                <?php elseif ($userIsOwner === true) : ?>
                <button type="submit" name="update">Update</button>
                <button type="submit" name="delete">Delete Movie</button>
                <?php else : ?>
                <p>You are not authorized to delete or update this movie.</p>
                <?php endif; ?>


            </form>

        </div>
    </div>
</body>

</html>