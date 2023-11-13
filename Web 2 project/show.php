<?php
require('authenticate.php');

// Check if the 'id' parameter is provided in the URL
if (isset($_GET['id'])) {
    $movieId = $_GET['id'];

    // Query the database to retrieve movie details and associated genres for the given ID
    $sql = "SELECT m.*, GROUP_CONCAT(g.name ORDER BY g.name ASC) AS genres, mc.category_id
            FROM movie m
            LEFT JOIN movie_genre mg ON m.MovieID = mg.movie_id
            LEFT JOIN genre g ON mg.genre_id = g.genre_id
            LEFT JOIN movie_category mc ON m.MovieID = mc.movie_id
            WHERE m.MovieID = ?
            GROUP BY m.MovieID";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movieId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch and display movie details
        $row = $result->fetch_assoc();
      

        $title = $row['Title'];
        $releaseDate = $row['Release_Date'];
        $ageRating = $row['Age_Rating'];
        $description = $row['Description'];
        $language = $row['Language'];
        $runtime = $row['Runtime'];
        $director = $row['Director'];
        $actors = $row['Actors'];
        $poster = $row['Movie_Poster'];
        $categoryId = $row['category_id'];

        // Fetch category name
        $categorySql = "SELECT category_id, category_name FROM categories WHERE category_id = ?";
        $categoryStmt = $conn->prepare($categorySql);
        $categoryStmt->bind_param("i", $categoryId);
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();

        if ($categoryResult->num_rows > 0) {
            $categoryRow = $categoryResult->fetch_assoc();
            $category = $categoryRow['category_name'];
            echo "Category ID: " . $categoryId . "<br>";
            echo "Category Name: " . $category . "<br>";
        } else {
            $category = 'N/A';
            echo "No category found for ID: " . $categoryId;
        }

        // Debugging line - print category information

        ?>

        <!DOCTYPE html>
        <html lang='en'>

        <head>
            <meta charset='UTF-8'>
            <meta http-equiv='X-UA-Compatible' content='IE=edge'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <link rel='stylesheet' href='styles.css'>
            <title>Movie Details</title>
        </head>

        <body>
            <div class='movie-cms-box'>
                <h1>Movie Details</h1>
                <div class='movie'>
                    <h2><?php echo $title; ?></h2>
                    <p>Release Date: <?= $releaseDate; ?></p>
                    <p>Age Rating: <?= $ageRating; ?></p>
                    <p>Description: <?= $description; ?></p>
                    <p>Language: <?= $language; ?></p>
                    <p>Runtime: <?= $runtime; ?> Minutes</p>
                    <p>Director: <?= $director; ?></p>
                    <p>Actors: <?= $actors; ?></p>
                    <p>Category: <?= $category; ?></p>

                    <img src='data:image/jpeg;base64,<?= base64_encode($poster); ?>' alt='Movie Poster' width='300'>
                </div>
            </div>
        </body>

        </html>

        <?php
    } else {
        echo "Movie not found.";
    }

    $stmt->close();
} else {
    echo "Movie ID not provided.";
}
?>
