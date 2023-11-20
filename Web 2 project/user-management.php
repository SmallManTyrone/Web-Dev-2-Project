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
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <h2>User Management</h2>
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

    <p>
        <a href="post.php">Go to Post</a> |
        <a href="CRUDcategory.php">Go to CRUDcategory</a>
    </p>

</body>

</html>