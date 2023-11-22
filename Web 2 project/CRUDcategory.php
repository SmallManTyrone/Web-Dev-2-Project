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

if (isset($_POST['viewCategory'])) {
    $selectedCategoryId = $_POST['viewCategory'];

    // Fetch category details from the database
    $categoryDetailsSql = "SELECT * FROM categories WHERE category_id = ?";
    $categoryDetailsStmt = $conn->prepare($categoryDetailsSql);
    $categoryDetailsStmt->bind_param("i", $selectedCategoryId);
    $categoryDetailsStmt->execute();
    $categoryDetailsResult = $categoryDetailsStmt->get_result();

    if ($categoryDetailsResult->num_rows > 0) {
        $categoryDetailsRow = $categoryDetailsResult->fetch_assoc();
        echo "<h3>Category Details</h3>";
        echo "<p>Category ID: " . $categoryDetailsRow['category_id'] . "</p>";
        echo "<p>Category Name: " . $categoryDetailsRow['category_name'] . "</p>";
    } else {
        echo "<p>No category found for the selected ID.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="poststyles.css">

</head>

<body>
<nav>
    <div>

        <!-- Search bar -->
        <form action="search.php" method="GET">
            <input type="text" name="q" placeholder="Search movies...">
            <button type="submit">Search</button>
        </form>
    </div>
</nav>
    <h1>Manage Categories</h1>
    <ul>
    <?php
      if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'user_management.php') !== false) {
        echo '<li><a href="user_management.php">Go back to manage users</a></li>';
    }
            ?>
    <li><a href="index.php">Home</a></li>
        </ul>
    <!-- Form for creating a new category -->
    <h2>Add New Category</h2>
    <form action="process_category.php" method="post">
        <label for="newCategory">Category Name:</label>
        <input type="text" id="newCategory" name="newCategory" required>
        <button type="submit">Add Category</button>
        <?php
        if (isset($_GET['success']) && $_GET['success'] === 'newCategory') {
            echo "<p>Category added successfully!</p>";
        }
        ?>
    </form>

    <!-- Form for updating existing categories -->
    <h2>Update Existing Categories</h2>
    <form action="process_category.php" method="post">
        <label for="existingCategory">Select Category to Update:</label>
        <select id="existingCategory" name="existingCategory" required>
            <?php
            // Fetch categories from the database and populate the dropdown
            $sql = "SELECT * FROM categories";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['category_id'] . "'>" . $row['category_name'] . "</option>";
                }
            }
            ?>
        </select>
        <label for="updatedCategory">New Category Name:</label>
        <input type="text" id="updatedCategory" name="updatedCategory" required>
        <button type="submit">Update Category</button>
        <?php
        if (isset($_GET['success']) && $_GET['success'] === 'updatedCategory') {
            echo "<p>Category updated successfully!</p>";
        }
        ?>
    </form>

    <!-- Form for deleting a category -->
    <h2>Delete Existing Categories</h2>
    <form action="process_category.php" method="post">
        <label for="deleteCategory">Select Category to Delete:</label>
        <select id="deleteCategory" name="deleteCategory" required>
            <?php
            // Fetch categories from the database and populate the dropdown for deletion
            $sql = "SELECT * FROM categories";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['category_id'] . "'>" . $row['category_name'] . "</option>";
                }
            }
            ?>
        </select>
        <button type="submit" name="delete">Delete Category</button>
        <?php
        if (isset($_GET['success']) && $_GET['success'] === 'deleteCategory') {
            echo "<p>Category deleted successfully!</p>";
        }
        ?>
    </form>

    <!-- Form for viewing category details -->
    <h2>View Current Categories</h2>
    <form action="index.php" method="post">
        <label for="viewCategory">Select Category to View:</label>
        <select id="viewCategory" name="viewCategory">
            <?php
            // Fetch categories from the database and populate the dropdown for viewing
            $sql = "SELECT * FROM categories";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['category_id'] . "'>" . $row['category_name'] . "</option>";
                }
            }
            ?>
        </select>
    </form>

</body>

</html>
