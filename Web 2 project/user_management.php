<?php

// Include authentication script
require('authenticate.php');

// Database connection parameters
$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create User (Create)
if (isset($_POST['create_user'])) {
    // Process user creation form submission
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

// Create Category (Create)
if (isset($_POST['create_category'])) {
    // Process category creation form submission
    $categoryName = $_POST['category_name'];

    // Check if the category already exists
    $checkDuplicateSql = "SELECT * FROM categories WHERE category_name = ?";
    $checkDuplicateStmt = $conn->prepare($checkDuplicateSql);
    $checkDuplicateStmt->bind_param("s", $categoryName);
    $checkDuplicateStmt->execute();
    $checkDuplicateResult = $checkDuplicateStmt->get_result();

    if ($checkDuplicateResult->num_rows > 0) {
        echo "Error creating category: Category already exists.";
    } else {
        // Insert the category if it doesn't exist
        $insertSql = "INSERT INTO categories (category_name) VALUES (?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("s", $categoryName);

        if ($insertStmt->execute()) {
            echo "Category created successfully.";
        } else {
            echo "Error creating category: " . $insertStmt->error;
        }
    }
}

// Delete User (Delete)
if (isset($_GET['delete_user'])) {
    // Process user deletion
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
    // Process movie creation form submission
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

// Create Genre (Create)
if (isset($_POST['create_genre'])) {
    // Process genre creation form submission
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

// Fetch existing posts from the database with only title and picture
$postsResult = $conn->query("SELECT * FROM movie");

// Fetch all rows into an array
$posts = [];
while ($post = $postsResult->fetch_assoc()) {
    $posts[] = $post;
}

// Process Category Deletion
if (isset($_GET['delete_category'])) {
    // Process category deletion
    $categoryId = $_GET['delete_category'];

    $sql = "DELETE FROM categories WHERE category_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $categoryId);

    if ($stmt->execute()) {
        echo "Category deleted successfully.";
    } else {
        echo "Error deleting category: " . $stmt->error;
    }
}

// Re-fetch categories after deletion
$categoriesResult = $conn->query("SELECT * FROM categories");

// Handle form submission to update category
if (isset($_POST['update_category'])) {
    // Process category update form submission
    $categoryId = $_POST['category_id'];
    $newCategoryName = $_POST['new_category_name'];

    // Update the category
    $updateSql = "UPDATE categories SET category_name = ? WHERE category_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $newCategoryName, $categoryId);

    if ($updateStmt->execute()) {
        echo "Category updated successfully.";
    } else {
        echo "Error updating category: " . $updateStmt->error;
    }
}

// Fetch Comments (Read)
$commentsResult = $conn->query("SELECT * FROM comments");
$comments = [];
while ($comment = $commentsResult->fetch_assoc()) {
    $comments[] = $comment;
}

// Delete Comment (Delete)
if (isset($_GET['delete_comment'])) {
    // Process comment deletion
    $comment_id = $_GET['delete_comment'];

    $sql = "DELETE FROM comments WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $comment_id);

    if ($stmt->execute()) {
        echo "Comment deleted successfully.";
    } else {
        echo "Error deleting comment: " . $stmt->error;
    }
}

if (isset($_GET['toggle_moderation'])) {
    // Process comment moderation toggle
    $commentId = $_GET['toggle_moderation'];

    $sql = "UPDATE comments SET moderation_status = CASE WHEN moderation_status='approved' THEN 'hidden' ELSE 'approved' END WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $commentId);

    if ($stmt->execute()) {
        echo "Comment moderation toggled successfully.";
    } else {
        echo "Error toggling comment moderation: " . $stmt->error;
    }
}


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Management</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <h2>User and Content Management</h2>
    <nav>
        <div>
            <!-- Search bar -->
            <form action="search.php" method="GET">
                <input type="text" name="q" placeholder="Search movies...">
                <button type="submit">Search</button>
            </form>
        </div>
    </nav>

    <!-- Navigation Links -->
    <a href="index.php" class="nav-link">Home</a>
    <a href="create-user.php" class="nav-link">Create User</a>
    <a href="post.php" class="nav-link">Go to Post</a>

    <!-- Display Users -->
    <h3>Users</h3>
    <table>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($result as $row) { ?>
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

    <!-- Add Category Form -->
    <h3>Add Category</h3>
    <form action="user_management.php" method="post">
        <label for="category_name">Category Name:</label>
        <input type="text" name="category_name" id="category_name" required>
        <button type="submit" name="create_category">Create Category</button>
    </form>

    <!-- Edit Category Form -->
    <h3>Edit Category</h3>
    <form action="user_management.php" method="post">
        <label for="category_id">Category ID:</label>
        <input type="text" name="category_id" id="category_id" required>
        <label for="new_category_name">New Category Name:</label>
        <input type="text" name="new_category_name" id="new_category_name" required>
        <button type="submit" name="update_category">Update Category</button>
    </form>


    <!-- Display Categories -->
    <h3>Categories</h3>
    <table>
        <tr>
            <th>Category ID</th>
            <th>Category Name</th>
            <th>Actions</th>
        </tr>
        <?php
        foreach ($categoriesResult as $category) {
            echo "<tr>";
            echo "<td>{$category['category_id']}</td>";
            echo "<td>{$category['category_name']}</td>";
            // Add links for edit and delete actions
            echo "<td><a href='user_management.php?category_id={$category['category_id']}'>Edit</a>";
            echo "<a href='user_management.php?delete_category={$category['category_id']}' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>";
            echo "</tr>";
        }
        ?>
    </table>

    <!-- Display Existing Posts -->
    <h3>Existing Posts</h3>
    <div class="mini-posts-container">
        <?php foreach ($posts as $post) { ?>
        <div class="mini-post">
            <a href="show.php?id=<?php echo $post['MovieID']; ?>">
                <h4><?php echo $post['Title']; ?></h4>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($post['Movie_Poster']); ?>"
                    alt="<?php echo $post['Title']; ?> Poster" width="100">
            </a>
            <a href="edit.php?id=<?php echo $post['MovieID']; ?>">Edit Movie</a>
        </div>
        <?php } ?>
    </div>


    <!-- Display Comments -->
    <h3>Comments</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Comment</th>
            <th>Created At</th>
            <th>Movie ID</th>
            <th>Moderation Status</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($comments as $comment) { ?>
        <tr>
            <td><?php echo $comment['id']; ?></td>
            <td><?php echo $comment['name'] ? $comment['name'] : 'Anonymous'; ?></td>
            <td><?php echo $comment['comment']; ?></td>
            <td><?php echo $comment['created_at']; ?></td>
            <td><?php echo $comment['movie_id']; ?></td>
            <td><?php echo $comment['moderation_status']; ?></td>
            <td>
                <a href="user_management.php?delete_comment=<?php echo $comment['id']; ?>"
                    onclick="return confirm('Are you sure you want to delete this comment?')">Delete</a>
                <a href="user_management.php?toggle_moderation=<?php echo $comment['id']; ?>">
                    <?php echo $comment['moderation_status'] === 'approved' ? 'Hide' : 'Approve'; ?>
                </a>
            </td>
        </tr>
        <?php } ?>
    </table>



</body>

</html>