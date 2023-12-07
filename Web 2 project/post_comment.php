<?php
require('authenticate.php'); // authentication logic

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $movieId = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $commentText = trim($_POST['comment']);

    // Check if the required fields are set and are valid
    if ($movieId !== false && $name !== false && isset($commentText)) {
        $commentText = filter_var($commentText, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!empty($commentText)) {
            // Insert the comment into the database using prepared statements
            $sql = "INSERT INTO comments (movie_id, name, comment, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("iss", $movieId, $name, $commentText);

                if ($stmt->execute()) {
                    // Comment added successfully
                    header("Location: show.php?id=$movieId"); // Redirect back to the movie details page
                    exit();
                } else {
                    echo "Error adding comment: " . $stmt->error;
                }

                $stmt->close();
            } else {
                echo "Error preparing statement: " . $conn->error;
            }
        } else {
            echo "Comment must contain non-empty content.";
        }
    } else {
        echo "Invalid or missing required fields.";
    }
} else {
    echo "Invalid request method.";
}
?>
