<?php
require('authenticate.php');


function displayComments($conn, $movieId)
{
    // In displayComments function
    $commentsSql = "SELECT * FROM comments WHERE movie_id = ? ORDER BY created_at DESC";
    $commentsStmt = $conn->prepare($commentsSql);
    $commentsStmt->bind_param("i", $movieId);
    $commentsStmt->execute();
    $commentsResult = $commentsStmt->get_result();

    echo "<h2>Comments</h2>";

    if ($commentsResult->num_rows > 0) {
        while ($comment = $commentsResult->fetch_assoc()) {
            $commentId = $comment['id']; // Assuming the comment ID is stored in the 'id' column
            $name = $comment['name'];
            $commentText = $comment['comment'];
            $createdAt = $comment['created_at'];
            $moderationStatus = $comment['moderation_status']; // Add this line

            // Check if the comment should be displayed
            if ($moderationStatus === 'approved' || isAdminLoggedIn()) {
                echo "<div class='comment'>";
                echo "<p><strong>$name:</strong> $commentText</p>";
                echo "<small>Posted on $createdAt</small>";

                // Check if the logged-in user is an admin before showing the "Moderate" button
                if (isAdminLoggedIn()) {
                    // Add a "Moderate" button with a link to the admin_moderate_comment.php page
                    echo "<form action='admin_moderate_comment.php' method='get'>";
                    echo "<input type='hidden' name='comment_id' value='$commentId'>";
                    echo "<button type='submit'>Moderate</button>";
                    echo "</form>";

                    // Add an "Unhide" button if the comment is hidden
                    if ($moderationStatus == 'hidden') {
                        echo "<form action='admin_moderate_comment.php' method='post'>";
                        echo "<input type='hidden' name='comment_id' value='$commentId'>";
                        echo "<input type='hidden' name='movie_id' value='$movieId'>";
                        echo "<button type='submit' name='action' value='unhide'>Unhide</button>";
                        echo "</form>";
                    }
                }

                echo "</div>";
            }
        }
    } else {
        echo "<p>No comments yet. Be the first to comment!</p>";
    }
}

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
        $genres = $row['genres']; // Fetch genres

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

        // Debugging line - print category and genres information
        echo "Genres: " . $genres . "<br>";
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
        <ul>
            <?php
                    if (isAdminLoggedIn()) {
                        echo '<li><a href="user-management.php">go to manage users</a></li>';
                    }
                    ?>
            <li><a href="index.php">Home</a></li>
        </ul>
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
            <p>Genres: <?= $genres; ?></p>

            <img src='data:image/jpeg;base64,<?= base64_encode($poster); ?>' alt='Movie Poster' width='300'>
        </div>
        <!-- Comment Form -->
<div class='comment-form'>
    <h2>Add a Comment</h2>
    <form action='post_comment.php' method='post' onsubmit='return validateCaptcha();'>
        <input type='hidden' name='movie_id' value='<?= $movieId; ?>'>
        <label for='name'>Name:</label>
        <?php
        if (isset($_SESSION['username'])) {
            $username = $_SESSION['username'];
            echo "<input type='text' id='name' name='name' value='$username' readonly>";
        } else {
            echo "<input type='text' id='name' name='name' required>";
        }
        ?>
        <label for='comment'>Comment:</label>
        <textarea id='comment' name='comment' required></textarea>
        <button type='submit'>Submit Comment</button>
    </form>
</div>




        <?php displayComments($conn, $movieId); ?>
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

<?php
