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


$errors = []; // Initialize an array to store errors
$count = 0; // Initialize $count as a global variable

function categoryExists($conn, $categoryName, $excludeCategoryId = null)
{
    global $errors, $count;

    // Convert category name to lowercase for case-insensitive comparison
    $lowerCategoryName = strtolower($categoryName);

    $sql = "SELECT COUNT(*) as count FROM categories WHERE LOWER(category_name) = ?";

    // Exclude the current category when updating
    if ($excludeCategoryId !== null) {
        $sql .= " AND category_id <> ?";
    }

    $stmt = $conn->prepare($sql);

    if ($excludeCategoryId !== null) {
        $stmt->bind_param("si", $lowerCategoryName, $excludeCategoryId);
    } else {
        $stmt->bind_param("s", $lowerCategoryName);
    }

    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $errors[] = "Error: This category already exists.";
        return true;
    }

    return false;
}








function addCategory($conn, $categoryName)
{
    // Check if the category already exists
    $lowerCategoryName = strtolower($categoryName); // Convert to lowercase
    if (categoryExists($conn, $lowerCategoryName)) {
        echo "<p>Error: This category already exists.</p>";
    } else {
        $insertCategorySql = "INSERT INTO categories (category_name) VALUES (?)";
        $insertCategoryStmt = $conn->prepare($insertCategorySql);
        $insertCategoryStmt->bind_param("s", $lowerCategoryName); // Use the lowercase name

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
                echo "<p>Category: $categoryName</p>";
            }
        }
    }
}

function updateCategory($conn, $categoryId, $updatedCategoryName)
{
    // Use a case-insensitive comparison for uniqueness check
    $lowerUpdatedCategoryName = strtolower($updatedCategoryName);

    if (categoryExists($conn, $lowerUpdatedCategoryName, $categoryId)) {
        echo "<p>Error: Updated category name already exists. Please choose a different name.</p>";
    } else {
        $sql = "UPDATE categories SET category_name = ? WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $lowerUpdatedCategoryName, $categoryId); // Use the lowercase name

        if ($stmt->execute()) {
            echo "<p>Category updated successfully!</p>";
        } else {
            echo "Error updating category: " . $stmt->error;
        }
    }
}




// Function to handle deleting an existing category
function deleteCategory($conn, $categoryId)
{
    // Delete associated records in movie_category first
    $deleteMovieCategorySql = "DELETE FROM movie_category WHERE category_id = ?";
    $deleteMovieCategoryStmt = $conn->prepare($deleteMovieCategorySql);
    $deleteMovieCategoryStmt->bind_param("i", $categoryId);

    if ($deleteMovieCategoryStmt->execute()) {
        // Now delete the category
        $sql = "DELETE FROM categories WHERE category_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $categoryId);

        if ($stmt->execute()) {
            echo "<p>Category deleted successfully!</p>";
        } else {
            echo "Error deleting category: " . $stmt->error;
        }
    } else {
        echo "Error deleting associated movie_category records: " . $deleteMovieCategoryStmt->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check the value of the "action" field
    if (isset($_POST['action'])) {
        // Add new category
        if ($_POST['action'] === 'addCategory' && isset($_POST['newCategory'])) {
            $newCategory = trim(filter_input(INPUT_POST, 'newCategory', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
            if (!empty($newCategory)) {
                if (!categoryExists($conn, $newCategory)) {
                    addCategory($conn, $newCategory);
                }
            } else {
                $errors[] = "Error: Category name cannot be empty.";
            }
        }

    // Update existing category
    if (isset($_POST['existingCategory'], $_POST['updatedCategory'])) {
        updateCategory($conn, $_POST['existingCategory'], $_POST['updatedCategory']);
    }

    // Delete existing category
    if (isset($_POST['deleteCategory'])) {
        deleteCategory($conn, $_POST['deleteCategory']);
    }
}

foreach ($errors as $error) {
    echo "<p>$error</p>";
}
} // Add this closing brace
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="categoryCRUD.css">

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
    <h2>Add New Category</h2>
    <?php
    if (isset($_GET['error']) && $_GET['error'] === 'categoryExists') {
        echo "<p>Error: Category already exists.</p>";
    }
    ?>
    <form action="CRUDcategory.php" method="post">
    <input type="hidden" name="action" value="addCategory"> <!-- Added hidden input field -->
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
    <form action="CRUDcategory.php" method="post">
    <input type="hidden" name="action" value="updateCategory"> 
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
    <form action="CRUDcategory.php" method="post">
    <input type="hidden" name="action" value="deleteCategory"> <!-- Added hidden input field -->
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
    <form action="CRUDcategory.php" method="post">
    <input type="hidden" name="action" value="viewCategory"> 
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