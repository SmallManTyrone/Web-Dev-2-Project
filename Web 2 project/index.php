<?php

/*
Name: Tyson La
Date: September 20th
Description: Movie Listing Page
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



$sql = "SELECT movie.*, GROUP_CONCAT(genre.name SEPARATOR ', ') AS genre_list
        FROM movie
        LEFT JOIN movie_genre ON movie.MovieID = movie_genre.movie_id
        LEFT JOIN genre ON movie_genre.genre_id = genre.genre_id
        GROUP BY movie.MovieID";

// Check if a category is selected
if (isset($_GET['category'])) {
    $selectedCategory = $_GET['category'];
    $selectedCategory = $conn->real_escape_string($selectedCategory);

    $sql = "SELECT movie.*, GROUP_CONCAT(genre.name SEPARATOR ', ') AS genre_list
            FROM movie
            LEFT JOIN movie_genre ON movie.MovieID = movie_genre.movie_id
            LEFT JOIN genre ON movie_genre.genre_id = genre.genre_id
            INNER JOIN movie_category ON movie.MovieID = movie_category.movie_id
            INNER JOIN categories ON movie_category.category_id = categories.category_id
            WHERE categories.category_name = '$selectedCategory'
            GROUP BY movie.MovieID";
}

$result = $conn->query($sql);

$categorySql = "SELECT category_name FROM categories";
$categoryResult = $conn->query($categorySql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>

    <title>Movies</title>
</head>

<body>
    <div class="movie-cms-box">
        <?php
    if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
        echo 'Hello, Admin ' . $username;
    } else {
        echo 'Hello, User ' . $username;
    }
} else {
    echo 'Hello, Guest';
}
?>
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
        <?php
        if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
            echo '<div class="success-message-container" id="successMessageContainer">';
            echo '<div class="success-message">Login was successful! Click anywhere to close.</div>';
            echo '</div>';
            $_SESSION['login_success'] = false;
        }
        ?>
        <ul class="navigation-menu">
            <li><a href="index.php">Home</a></li>
            <?php
            if (isAdminLoggedIn()) {
                echo '<li><a href="sort-list.php">Sort Movies</a></li>';
                echo '<li><a href="logout.php">Log Out</a></li>';
                echo '<li><a href="user_management.php">User Management and Content Management</a>';
            } else if (isLoggedIn()) {
                echo '<li><a href="post.php">Add Movie</a></li>';
                echo '<li><a href="sort-list.php">Sort Movies</a></li>';
                echo '<li><a href="logout.php">Log Out</a></li>';
                echo '<li><a href="CRUDcategory.php">Edit Categories</a></li>';
            } else {
                echo '<li><a href="register.php">Make Account</a></li>';
                echo '<li><a href="login.php">Login</a></li>';
            }
            ?>
        </ul>
        <div class="category-dropdown">
            <?php
            if ($categoryResult->num_rows > 0) {
                echo '<label for="category">Sort by Category:</label>';
                echo '<select id="category" name="category" onchange="location = this.value;">';
                echo '<option value="index.php" selected>All Movies</option>';

                while ($categoryRow = $categoryResult->fetch_assoc()) {
                    $category = $categoryRow['category_name'];
                    $selected = (isset($_GET['category']) && $_GET['category'] == $category) ? 'selected' : '';
                    echo "<option value='index.php?category=$category' $selected>$category</option>";
                }

                echo '</select>';
            }
            ?>
        </div>

        <div class="movie-header"></div>
        <div class="movie-list">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $movieId = $row["MovieID"];
                    $title = $row["Title"];
                    $poster = $row["Movie_Poster"];
            ?>
            <div class="movie">
                <h2><a href='show.php?id=<?= $movieId ?>'><?= $title ?></a></h2>
                <?php
if (!empty($poster)) {
    echo '<img src="data:image/jpeg;base64,' . base64_encode($poster) . '" alt="Movie Poster" width="200">';
}
?>

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