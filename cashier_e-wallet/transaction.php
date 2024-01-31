<?php
@include 'connection.php';

$message = array();

if (isset($_POST['add_to_cart'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['quantity'];
    $main_ingredient = ''; 

    $fetch_product_query = mysqli_query($conn, "SELECT mainIngredient FROM `menu` WHERE Name = '$product_name'");
    $fetch_product = mysqli_fetch_assoc($fetch_product_query);

    if ($fetch_product) {
        $main_ingredient = $fetch_product['mainIngredient'];
    }

    $ingredientQuantities = [];

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'ingredient_') === 0) {
            $ingredientName = substr($key, 11);
            $ingredientQuantities[$ingredientName] = $value;
        }
    }

    if (!empty($main_ingredient)) {
        $ingredientQuantities[$main_ingredient] = 1; 
    }

    $ingredientString = '';

    foreach ($ingredientQuantities as $ingredient => $quantity) {
        $ingredientString .= "$ingredient: $quantity, ";
    }

    $ingredientString = rtrim($ingredientString, ', ');

    $insert_product = mysqli_query($conn, "INSERT INTO `cashierCart`(name, price, image, quantity, MainIngredient, Ingredient) VALUES('$product_name', '$product_price', '$product_image', '$product_quantity', '$main_ingredient', '$ingredientString')");

    if ($insert_product) {
        $message[] = 'Product added to cart successfully';
        header("Location: cashier.php");

        exit();
    } else {
        $message[] = 'Failed to add product to cart';
    }
}

$ingredientPrices = array();

$sql = "SELECT IngridientName, Price FROM inventory";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ingredientPrices[$row['IngridientName']] = $row['Price'];
    }
}

$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT DISTINCT TRIM(Category) AS Category FROM `Category`";
$categories = mysqli_query($conn, $sql);

function updateInventory($conn, $ingredientQuantities) {
    $ingredientNames = array_keys($ingredientQuantities);

    foreach ($ingredientNames as $ingredient) {
        $quantity = $ingredientQuantities[$ingredient];

        $safeIngredient = mysqli_real_escape_string($conn, $ingredient);

        $updateQuery = "UPDATE `inventory` SET `Serving` = `Serving` - $quantity WHERE `IngridientName` = '$safeIngredient'";

        mysqli_query($conn, $updateQuery);
    }
}
?>