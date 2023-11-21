<?php
require('authenticate.php'); // Include your authentication logic

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the required fields and captcha are set
    if (isset($_POST['movie_id'], $_POST['name'], $_POST['comment'], $_POST['captcha'])) {
        session_start();

        // Validate the captcha code
        $userEnteredCaptcha = $_POST['captcha'];
        $correctCaptcha = $_SESSION['captcha_code'] ?? '';

        if (empty($userEnteredCaptcha) || $userEnteredCaptcha !== $correctCaptcha) {
            // Captcha validation failed
            echo "Captcha validation failed. Please try again.";
            exit();
        }

        // Sanitize and validate other input
        $movieId = intval($_POST['movie_id']); // Assuming movie_id is an integer
        $name = htmlspecialchars($_POST['name']);
        $commentText = htmlspecialchars($_POST['comment']);

        // Insert the comment into the database
        $sql = "INSERT INTO comments (movie_id, name, comment, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $movieId, $name, $commentText);

        if ($stmt->execute()) {
            // Comment added successfully
            header("Location: show.php?id=$movieId"); // Redirect back to the movie details page
            exit();
        } else {
            // Log detailed error and show a generic message
            error_log("Error adding comment: " . $stmt->error);
            echo "Error adding comment. Please try again later.";
        }

        $stmt->close();
    } else {
        echo "Missing required fields.";
    }
} else {
    echo "Invalid request method.";
}
?>
