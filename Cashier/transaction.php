<?php
// Include your database connection or any necessary PHP code here
@include 'connection.php';

$message = array();

if (isset($_POST['add_to_cart'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['quantity'];
    $main_ingredient = isset($_POST['main_ingredient']) ? $_POST['main_ingredient'] : ''; // Initialize the main ingredient variable

    $ingredientQuantities = [];

    // Loop through the POST data to capture ingredient quantities
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'ingredient_') === 0) {
            // Extract the ingredient name and quantity
            $ingredientName = substr($key, 11);
            $ingredientQuantities[$ingredientName] = $value;
        } elseif ($key === 'main_ingredient') {
            // Set the main ingredient if provided
            $main_ingredient = $value;
        }
    }

    // Add the main ingredient to the ingredient array if it's not empty
    if (!empty($main_ingredient)) {
        $ingredientQuantities[$main_ingredient] = 1; // Assuming a default quantity of 1 for the main ingredient
    }

    // Convert the ingredient and quantity pairs to a formatted string
    $ingredientString = '';

    foreach ($ingredientQuantities as $ingredient => $quantity) {
        $ingredientString .= "$ingredient: $quantity, ";
    }

    // Remove trailing comma and space
    $ingredientString = rtrim($ingredientString, ', ');

    // Insert the product into the cart table
    $insert_product = mysqli_query($conn, "INSERT INTO `cart`(name, price, image, quantity, MainIngredient, Ingredient) VALUES('$product_name', '$product_price', '$product_image', '$product_quantity', '$main_ingredient', '$ingredientString')");

    if ($insert_product) {
        $message[] = 'Product added to cart successfully';

        // Update the inventory after successful insertion into the cart
    } else {
        $message[] = 'Failed to add product to cart';
    }

    $ingredientPrices = array();

    $sql = "SELECT IngridientName, Price FROM inventory";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $ingredientPrices[$row['IngridientName']] = $row['Price'];
        }
    }

    // Get the selected category from the URL parameter
    $category = isset($_GET['category']) ? $_GET['category'] : '';

    // Fetch distinct categories for the header
    $sql = "SELECT DISTINCT TRIM(Category) AS Category FROM `Category`";
    $categories = mysqli_query($conn, $sql);

    // ... (remaining code)
}
?>