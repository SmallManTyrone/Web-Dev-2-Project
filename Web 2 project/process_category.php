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

// Function to handle adding a new category
function addCategory($conn, $categoryName)
{
    $sql = "INSERT INTO categories (category_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $categoryName);

    if ($stmt->execute()) {
        header("Location: index.php?success=newCategory");
        exit();
    } else {
        echo "Error adding category: " . $stmt->error;
    }

    $stmt->close();
}

// Function to handle updating an existing category
function updateCategory($conn, $categoryId, $updatedCategoryName)
{
    $sql = "UPDATE categories SET category_name = ? WHERE category_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $updatedCategoryName, $categoryId);

    if ($stmt->execute()) {
        header("Location: index.php?success=updatedCategory");
        exit();
    } else {
        echo "Error updating category: " . $stmt->error;
    }

    $stmt->close();
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
            header("Location: index.php?success=deleteCategory");
            exit();
        } else {
            echo "Error deleting category: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error deleting associated movie_category records: " . $deleteMovieCategoryStmt->error;
    }

    $deleteMovieCategoryStmt->close();
}


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new category
    if (isset($_POST['newCategory'])) {
        addCategory($conn, $_POST['newCategory']);
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

// Close the database connection
$conn->close();
?>
