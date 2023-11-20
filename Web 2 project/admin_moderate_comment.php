<?php
require('authenticate.php');

$servername = "localhost"; // Replace with your database server
$username = "serveruser"; // Replace with your database username
$password = "gorgonzola7!"; // Replace with your database password
$dbname = "serverside"; // Replace with your database name

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you perform your moderation actions here
    $commentId = $_POST['comment_id'];
    $movieId = $_POST['movie_id'];
    $action = $_POST['action'];

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Perform moderation actions based on the selected action
    switch ($action) {
        case 'delete':
            $deleteSql = "DELETE FROM comments WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $commentId);
            
            if ($deleteStmt->execute()) {
                echo "Comment deleted successfully.";
            } else {
                echo "Error deleting comment: " . $deleteStmt->error;
            }
            
            $deleteStmt->close();
            break;

        case 'hide':
            // Assuming you have a 'moderation_status' column
            $hideSql = "UPDATE comments SET moderation_status = 'hidden' WHERE id = ?";
            $hideStmt = $conn->prepare($hideSql);
            $hideStmt->bind_param("i", $commentId);
            
            if ($hideStmt->execute()) {
                echo "Comment hidden successfully.";
            } else {
                echo "Error hiding comment: " . $hideStmt->error;
            }
            
            $hideStmt->close();
            break;

       
            

            // Fetch the comment text
            $commentTextSql = "SELECT comment FROM comments WHERE id = ?";
            $commentTextStmt = $conn->prepare($commentTextSql);
            $commentTextStmt->bind_param("i", $commentId);
            $commentTextStmt->execute();
            $commentTextResult = $commentTextStmt->get_result();

            if ($commentTextResult->num_rows > 0) {
                $commentTextRow = $commentTextResult->fetch_assoc();
                $commentText = $commentTextRow['comment'];

                // Update the comment with disemvoweled text
                $updateSql = "UPDATE comments SET comment = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("si", $disemvoweledText, $commentId);
                
                if ($updateStmt->execute()) {
                    echo "Comment disemvoweled successfully.";
                } else {
                    echo "Error disemvoweling comment: " . $updateStmt->error;
                }
                
                $updateStmt->close();
            } else {
                echo "Comment text not found.";
            }

            $commentTextStmt->close();
            break;

        case 'unhide':
            // Assuming you have a 'moderation_status' column
            $unhideSql = "UPDATE comments SET moderation_status = 'approved' WHERE id = ?";
            $unhideStmt = $conn->prepare($unhideSql);
            $unhideStmt->bind_param("i", $commentId);
            
            if ($unhideStmt->execute()) {
                echo "Comment unhidden successfully.";
            } else {
                echo "Error unhiding comment: " . $unhideStmt->error;
            }
            
            $unhideStmt->close();
            break;

        default:
            echo "Invalid action.";
            break;
    }

    // Close the database connection
    $conn->close();

    // Redirect back to the show.php page
    header("Location: show.php?id={$movieId}");
    exit();
}

// If the 'comment_id' parameter is provided in the URL
if (isset($_GET['comment_id'])) {
    $commentId = $_GET['comment_id'];

    // Fetch the comment details from the database based on the comment ID
    $commentSql = "SELECT * FROM comments WHERE id = ?";
    $commentStmt = $conn->prepare($commentSql);
    $commentStmt->bind_param("i", $commentId);
    $commentStmt->execute();
    $commentResult = $commentStmt->get_result();

    if ($commentResult->num_rows > 0) {
        $commentDetails = $commentResult->fetch_assoc();
        echo "<li><a href='show.php?id={$commentDetails['movie_id']}'>Go back to movie</a></li>";
        // Display the comment details
        echo "<h1>Comment Details</h1>";
        echo "<p>Comment ID: " . $commentDetails['id'] . "</p>";
        echo "<p>Name: " . $commentDetails['name'] . "</p>";
        echo "<p>Comment: " . $commentDetails['comment'] . "</p>";
    } else {
        echo "Comment not found.";
    }

    $commentStmt->close();
} else {
    echo "Comment ID not provided.";
}
?>

        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Moderation</title>
            <!-- Add your stylesheets or additional head content here -->
        </head>

        <body>
            <header>
            <h1>Admin Comment Moderation</h1>
            </header>

            <main>
                <!-- Admin moderation form in admin_moderate_comments.php -->
                <form action="admin_moderate_comment.php" method="post">
                    <input type="hidden" name="comment_id" value="<?= $commentDetails['id']; ?>">
                    <input type="hidden" name="movie_id" value="<?= $commentDetails['movie_id']; ?>">

                    <div>
                        <label for="delete">Delete Comment:</label>
                        <button type="submit" name="action" value="delete" id="delete">Delete</button>
                    </div>

                    <div>
                        <label for="hide">Hide Comment:</label>
                        <button type="submit" name="action" value="hide" id="hide">Hide</button>
                    </div>

                </form>
            </main>

            <footer>
                <!-- Add your footer content here -->
            </footer>

            <!-- Add your scripts or additional body content here -->
        </body>

        </html>
