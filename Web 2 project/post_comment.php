<?php
require('authenticate.php'); // Include your authentication logic

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the required fields are set
    if (isset($_POST['movie_id'], $_POST['name'], $_POST['comment'])) {
        $movieId = $_POST['movie_id'];
        $name = $_POST['name'];
        $commentText = $_POST['comment'];

        // Insert the comment into the database
        $sql = "INSERT INTO comments (movie_id, name, comment, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
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
        echo "Missing required fields.";
    }
} else {
    echo "Invalid request method.";
}
?>
