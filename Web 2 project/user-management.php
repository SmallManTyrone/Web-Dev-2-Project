<?php
// Database connection (use your actual database details)
$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create User (Create)
if (isset($_POST['create_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO user (Username, email, Password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        echo "User created successfully.";
    } else {
        echo "Error creating user: " . $stmt->error;
    }
}

// Read Users (Read)
$result = $conn->query("SELECT * FROM user");

// Delete User (Delete)
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    $sql = "DELETE FROM user WHERE UserID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        echo "User deleted successfully.";
    } else {
        echo "Error deleting user: " . $stmt->error;
    }
}

// Create Movie (Create)
if (isset($_POST['create_movie'])) {
    $title = $_POST['title'];
    $releaseDate = $_POST['release_date'];
    $ageRating = $_POST['age_rating'];
    $description = $_POST['description'];
    $language = $_POST['language'];
    $runtime = $_POST['runtime'];
    $director = $_POST['director'];
    $actors = $_POST['actors'];
    // Handle file upload for movie poster (you may need to adjust this part)
    $posterData = file_get_contents($_FILES['movie_poster']['tmp_name']);

    $sql = "INSERT INTO movie (Title, Release_Date, Age_Rating, Description, Language, Runtime, Director, Actors, Movie_Poster) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssb", $title, $releaseDate, $ageRating, $description, $language, $runtime, $director, $actors, $posterData);

    if ($stmt->execute()) {
        echo "Movie created successfully.";
    } else {
        echo "Error creating movie: " . $stmt->error;
    
    }
}
if (isset($_POST['create_genre'])) {
    $genreName = $_POST['genre_name'];
    
    // Sanitize the genre name using the filter_var function
    $genreName = filter_var($genreName, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $sql = "INSERT INTO genre (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $genreName);

    if ($stmt->execute()) {
        echo "Genre created successfully.";
    } else {
        echo "Error creating genre: " . $stmt->error;
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Management</title>
</head>

<body>
    <h2>User and Movie Management</h2>
    <a href="index.php" class="nav-link">Home</a>
    <a href="create-user.php" class="nav-link">Create User</a>
  

    <!-- Display Users -->
    <h3>Users</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['UserID']; ?></td>
            <td><?php echo $row['Username']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td>
                <a href="edit-user.php?user_id=<?php echo $row['UserID']; ?>">Edit</a>
                <a href="user-management.php?delete_user=<?php echo $row['UserID']; ?>"
                    onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- Create Movie Form -->
    <h3>Create Movie</h3>
    <form action="user-management.php" method="post" enctype="multipart/form-data">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title" required>
        <label for="release_date">Release Date:</label>
        <input type="text" id="release_date" name="release_date" required>
        <label for="age_rating">Age Rating:</label>
        <input type="text" id="age_rating" name="age_rating" required>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>
        <label for="language">Language:</label>
        <input type="text" id="language" name="language" required>
        <label for="runtime">Runtime:</label>
        <input type="text" id="runtime" name="runtime" required>
        <label for="director">Director:</label>
        <input type="text" id="director" name="director" required>
        <label for="actors">Actors:</label>
        <input type="text" id="actors" name="actors" required>
        <label for="movie_poster">Movie Poster:</label>
        <input type="file" id="movie_poster" name="movie_poster" required>
        <button type="submit" name="create_movie">Create Movie</button>
    </form>
    <!-- Create Genre Form -->
    <h3>Create Genre</h3>
    <form action="user-management.php" method="post">
        <label for="genre_name">Genre Name:</label>
        <input type="text" id="genre_name" name="genre_name" required>
        <button type="submit" name="create_genre">Create Genre</button>
    </form>

    <!-- Display Genres -->
    <h3>Genres</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
        </tr>
        <?php
        $genreResult = $conn->query("SELECT * FROM genre");
        while ($genreRow = $genreResult->fetch_assoc()) {
        ?>
        <tr>
            <td><?php echo $genreRow['genre_id']; ?></td>
            <td><?php echo $genreRow['name']; ?></td>
        </tr>
        <?php } ?>
    </table>
</body>

</html>