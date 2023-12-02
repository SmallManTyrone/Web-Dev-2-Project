<?php

require('authenticate.php');

$servername = "localhost";
$username = "serveruser";
$password = "gorgonzola7!";
$dbname = "serverside";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add this block to check if the category already exists before processing the form
if (isset($_POST['newCategory'])) {
    $newCategory = trim(filter_input(INPUT_POST, 'newCategory', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    // Check if the category name is not empty
    if (!empty($newCategory)) {
        // Check if a category with the same name (case-insensitive) already exists
        $checkCategorySql = "SELECT category_id FROM categories WHERE LOWER(category_name) = LOWER(?)";
        $checkCategoryStmt = $conn->prepare($checkCategorySql);
        $checkCategoryStmt->bind_param("s", $newCategory);
        $checkCategoryStmt->execute();
        $checkCategoryStmt->store_result();

        if ($checkCategoryStmt->num_rows > 0) {
            // Category already exists, show a custom error message
            echo "<p>Error: This category already exists.</p>";
        } else {
            // Continue with the form processing logic
            $insertCategorySql = "INSERT INTO categories (category_name) VALUES (?)";
            $insertCategoryStmt = $conn->prepare($insertCategorySql);
            $insertCategoryStmt->bind_param("s", $newCategory);

            if ($insertCategoryStmt->execute()) {
                echo "<p>Category added successfully!</p>";
            } else {
                // Check for unique constraint violation
                $errorMessage = $conn->error;
                if (strpos($errorMessage, 'Duplicate entry') !== false) {
                    echo "<p>Error: This category already exists.</p>";
                } else {
                    echo "<p>Error adding category: " . $errorMessage . "</p>";
                    echo "<p>SQL: $insertCategorySql</p>";
                    echo "<p>Category: $newCategory</p>";
                }
            }
        }
    } else {
        echo "<p>Error: Category name cannot be empty.</p>";
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
    <?php
    if (isset($_GET['error']) && $_GET['error'] === 'categoryExists') {
        echo "<p>Error: Category already exists.</p>";
    }
    ?>
    <form action="CRUDcategory.php" method="post">
        <label for="newCategory">Category Name:</label>
        <input type="text" id="newCategory" name="newCategory" required>
        <button type="submit">Add Category</button>
    </form>

    <!-- Form for updating existing categories -->
    <h2>Update Existing Categories</h2>
    <?php
    if (isset($_GET['error']) && $_GET['error'] === 'updatedCategoryExists') {
        echo "<p>Error: Updated category name already exists.</p>";
    }
    ?>
    <form action="process_category.php" method="post">
        <label for="existingCategory">Select Category to Update:</label>
        <select id="existingCategory" name="existingCategory" required>
            <option value="" disabled selected>Select Category</option>
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
    </form>

    <!-- Form for deleting a category -->
    <h2>Delete Existing Categories</h2>
    <form action="process_category.php" method="post">
        <label for="deleteCategory">Select Category to Delete:</label>
        <select id="deleteCategory" name="deleteCategory" required>
            <option value="" disabled selected>Select Category</option>
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
    </form>

    <!-- Form for viewing category details -->
    <h2>View Current Categories</h2>
    <form action="index.php" method="post">
        <label for="viewCategory">Select Category to View:</label>
        <select id="viewCategory" name="viewCategory">
            <option value="" disabled selected>Select Category</option>
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