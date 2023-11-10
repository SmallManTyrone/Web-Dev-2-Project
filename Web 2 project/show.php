<?php
/*
Name: Tyson La
Date: September 20th
Description: Movie Details Page
*/

require('authenticate.php');

// Check if the 'id' parameter is provided in the URL
if (isset($_GET['id'])) {
    $movieId = $_GET['id'];

    // Query the database to retrieve movie details for the given ID
    $sql = "SELECT * FROM movie WHERE MovieID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $movieId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Query the database to retrieve movie details and associated genres for the given ID
$sql = "SELECT m.*, GROUP_CONCAT(g.name ORDER BY g.name ASC) AS genres
FROM movie m
LEFT JOIN movie_genre mg ON m.MovieID = mg.movie_id
LEFT JOIN genre g ON mg.genre_id = g.genre_id
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
        $genres = $row['genres']; // Genres as a comma-separated string

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
    <?php
            if (isset($_GET['source']) && $_GET['source'] === 'sort-list') {
                $returnLabel = 'Return to Sort List';
                $returnURL = 'sort-list.php';
            } else {
                $returnLabel = 'Return Home';
                $returnURL = 'index.php';
            }
            ?>
    <a href="<?= $returnURL ?>"><?= $returnLabel ?></a>
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
            <p>Genres: <?= isset($genres) ? $genres : 'N/A'; ?></p>

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