<?php

session_start();
require('authenticate.php');

function convertSpacesToDashes($text) {
    return str_replace(' ', '-', $text);
}


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
            $name = $comment['name'] ? $comment['name'] : 'Anonymous'; // Set default to 'Anonymous' if name is blank
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
        } else {
            $category = 'N/A';
        }

    
        ?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='stylesheet' href='styles.css'>
    <script src="tinymce\js\tinymce\tinymce.min.js"></script>
    <script>
function submitForm() {
    var editorContent = tinymce.get('comment').getContent();

    // Use DOMParser to extract text content from HTML
    var parser = new DOMParser();
    var doc = parser.parseFromString(editorContent, 'text/html');
    var textContent = doc.body.textContent || '';

    // Replace &nbsp; entities with an empty string
    textContent = textContent.replace(/&nbsp;/g, '');

    // Remove extra whitespaces (including leading and trailing) within the text
    textContent = textContent.replace(/\s+/g, ' ').trim();

    console.log('Editor Content:', editorContent);
    console.log('Trimmed Content:', textContent);

    if (!textContent) {
        alert('Please enter a non-empty comment.');
        return false; // Prevent form submission
    }

    console.log('Form will be submitted.'); // Debug statement

    return true; // Allow form submission
}



    document.addEventListener('DOMContentLoaded', function () {
        tinymce.init({
            selector: '#comment',
            plugins: 'autoresize',
            autoresize_bottom_margin: 16,
            menubar: false
        });

        document.querySelector('form').addEventListener('submit', function (event) {
            // This event listener is unnecessary. You can remove it.
        });
    });
</script>
    <title>Movie Details</title>
</head>

<body>
    <div class='movie-cms-box'>
        <h1>Movie Details</h1>
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
                    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'search.php') !== false) {
                        // Assuming you have a variable $searchQuery containing the search query
                        echo '<li><a href="search.php?q=' . $_SESSION['searchQuery'] . '">Go back to searches</a></li>';


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
            <form action='post_comment.php' method='post' onsubmit='return submitForm();'>
                <input type='hidden' name='movie_id' value='<?= $movieId; ?>'>
                <label for='name'>Name:</label>
                <?php
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        echo "<input type='text' id='name' name='name' value='$username' readonly>";
    } else {
        echo "<input type='text' id='name' name='name'>";
    }
    ?>
                <label for='comment'>Comment:</label>
                <textarea id='comment' name='comment'></textarea>
                <button type='submit' name='submitComment'>Submit Comment</button>
            </form>



            <?php displayComments($conn, $movieId); ?>
        </div>
</body>

</html>

<?php
    } else {
        echo "Movie not found.";
        header("Location:index.php");
    }

    $stmt->close();
} else {
    echo "Movie ID not provided.";
    header("Location:index.php");
}
?>