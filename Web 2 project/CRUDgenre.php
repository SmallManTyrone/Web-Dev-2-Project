<?php
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


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Genres</title>
</head>

<body>
    <h1>Manage Genres</h1>
    <li><a href="index.php">Home</a></li>

    <!-- Form for creating a new genre -->
    <h2>Add New Genre</h2>
    <form action="process_genre.php" method="post">
        <label for="newGenre">Genre Name:</label>
        <input type="text" id="newGenre" name="newGenre" required>
        <button type="submit">Add Genre</button>
        <?php
    if (isset($_GET['success']) && $_GET['success'] === 'newGenre') {
        echo "<p>Genre added successfully!</p>";
    }
    ?>
    </form>

    <!-- Form for updating existing genres -->
    <!-- Form for updating existing genres -->
    <h2>Update Existing Genres</h2>
    <form action="process_genre.php" method="post">
        <label for="existingGenre">Select Genre to Update:</label>
        <select id="existingGenre" name="existingGenre" required>
            <?php
        // Fetch genres from the database and populate the dropdown
        $sql = "SELECT * FROM genre";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['genre_id'] . "'>" . $row['name'] . "</option>";
            }
        }
        ?>
        </select>
        <label for="updatedGenre">New Genre Name:</label>
        <input type="text" id="updatedGenre" name="updatedGenre" required>
        <button type="submit">Update Genre</button>
        <?php
    if (isset($_GET['success']) && $_GET['success'] === 'updatedGenre') {
        echo "<p>Genre updated successfully!</p>";
    }
    ?>
    </form>

    <!-- Form for deleting a genre -->
<h2>Delete Existing Genres</h2>
<form action="process_genre.php" method="post">
    <label for="deleteGenre">Select Genre to Delete:</label>
    <select id="deleteGenre" name="deleteGenre" required>
        <?php
        // Fetch genres from the database and populate the dropdown for deletion
        $sql = "SELECT * FROM genre";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['genre_id'] . "'>" . $row['name'] . "</option>";
            }
        }
        ?>
    </select>
    <button type="submit" name="delete">Delete Genre</button>
    <?php
    if (isset($_GET['success']) && $_GET['success'] === 'deleteGenre') {
        echo "<p>Genre deleted successfully!</p>";
    }
    ?>
</form>
</body>

</html>