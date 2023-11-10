<?php
$servername = "localhost"; // Replace with your database server
$username = "serveruser"; // Replace with your database username
$password = "gorgonzola7!"; // Replace with your database password
$dbname = "serverside"; // Replace with your database name

// Create a database connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['newGenre'])) {
        // Adding a new genre
        $newGenre = $_POST['newGenre'];

        // Implement appropriate validation and sanitization for $newGenre

        // Insert the new genre into the database
        $sql = "INSERT INTO genre (name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $newGenre); // 's' represents a string parameter
        $stmt->execute();

        // Redirect back to the page with the form and a success message
        header("Location: CRUDgenre.php?success=newGenre");
        exit();
    } elseif (isset($_POST['existingGenre']) && isset($_POST['updatedGenre'])) {
        // Updating an existing genre
        $genreId = $_POST['existingGenre'];
        $updatedGenre = $_POST['updatedGenre'];

        // Implement appropriate validation and sanitization for $updatedGenre

        // Update the genre in the database
        $sql = "UPDATE genre SET name = ? WHERE genre_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $updatedGenre, $genreId); // 's' for string, 'i' for integer
        $stmt->execute();

        // Redirect back to the page with the form and a success message
        header("Location: CRUDgenre.php?success=updatedGenre");
        exit();
    } elseif (isset($_POST['deleteGenre'])) {
        // Deleting an existing genre
        $genreId = $_POST['deleteGenre'];

        // Implement appropriate validation and sanitization for $genreId

        // Delete the genre from the database
        $sql = "DELETE FROM genre WHERE genre_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $genreId); // 'i' represents an integer parameter
        $stmt->execute();

        // Redirect back to the page with the form and a success message
        header("Location: CRUDgenre.php?success=deleteGenre");
        exit();
    }
}

// Handle any other cases or errors as needed
?>
