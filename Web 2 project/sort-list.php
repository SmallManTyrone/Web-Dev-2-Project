<?php
/*
Name: Tyson La
Date: November 2023
Description: Sorting Page
*/

require('authenticate.php');

$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a sorting parameter is provided in the URL, default to sorting by MovieID if not set
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'MovieID';

// Define sorting order (ASC or DESC), default to ascending order
$sortOrder = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Get the selected sorting options from the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sortColumn = $_POST['sort'];
    $sortOrder = $_POST['order'];
}

// Modify the SQL query to include genre information
$sql = "SELECT movie.MovieID, movie.Title, movie.Movie_Poster
        FROM movie
        ORDER BY $sortColumn $sortOrder";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='stylesheet' href='styles.css'> <!-- Include your CSS file for styling -->
    <title>Welcome to Movie CMS!</title>
</head>

<body>
    <!-- Movie CMS header -->
    <div class='movie-cms-box'>
        <h1>Welcome to Movie CMS</h1>
        <nav>
            <div>

                <!-- Search bar -->
                <form action="search.php" method="GET">
                    <input type="text" name="q" placeholder="Search movies...">
                    <button type="submit">Search</button>
                </form>
            </div>
        </nav>
        <a href='index.php' class='nav-link'>Home</a>
        <div class='sorting-options'>
        <form method='post' action='sort-list.php'>

                <label for='sort'>Sort by:</label>
                <select id='sort' name='sort'>
                    <option value='MovieID' <?php if ($sortColumn == 'MovieID') echo 'selected'; ?>>Movie ID</option>
                    <option value='Title' <?php if ($sortColumn == 'Title') echo 'selected'; ?>>Title</option>
                    <option value='Release_Date' <?php if ($sortColumn == 'Release_Date') echo 'selected'; ?>>Release
                        Date</option>
                    <option value='Age_Rating' <?php if ($sortColumn == 'Age_Rating') echo 'selected'; ?>>Age Rating
                    </option>
                    <!-- Add more options for other columns if needed -->
                </select>
                <label for='order'>Order:</label>
                <select id='order' name='order'>
                    <option value='ASC' <?php if ($sortOrder == 'ASC') echo 'selected'; ?>>Ascending</option>
                    <option value='DESC' <?php if ($sortOrder == 'DESC') echo 'selected'; ?>>Descending</option>
                </select>
                <input type='submit' value='Apply'>
            </form>
        </div>
        <div class='movie-list'>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $movieId = $row['MovieID'];
                    $title = $row['Title'];
                    $poster = $row['Movie_Poster'];
                    ?>
            <div class='movie'>
                <h2><a href='show.php?id=<?= $movieId ?>&source=sort-list'><?= $title ?></a></h2>

                <!-- Display the movie poster here -->
                <img src='data:image/jpeg;base64,<?= base64_encode($poster) ?>' alt='Movie Poster' width='200'>
            </div>
            <?php
                }
            }
            ?>
        </div>
    </div>
</body>

</html>