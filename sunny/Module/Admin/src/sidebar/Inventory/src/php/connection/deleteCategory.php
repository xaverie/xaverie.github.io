<?php
include "connection.php";

if (isset($_GET["category"])) {
    $category = $_GET["category"];

    // Add appropriate validation and error handling here

    // Delete the category from the Category table
    $sql = "DELETE FROM `Category` WHERE TRIM(Category) = '$category'";
    if (mysqli_query($conn, $sql)) {
        // Optionally, you can also delete related records from the inventory table if needed.

        // Redirect back to the inventory page
        header("Location: http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php?msg=Category deleted successfully");
        exit();
    } else {
        echo "Error deleting category: " . mysqli_error($conn);
    }
}
?>