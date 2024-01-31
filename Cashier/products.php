<?php
@include 'connection.php';

$message = array();

if (isset($_POST['add_to_cart'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['quantity'];
    $category = $_POST['category'];

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
    $totalAddOnsPrice = $_POST['hidden-total-addons-price'];
    $insert_product = mysqli_query($conn, "INSERT INTO `cart` (name, price, image, quantity, MainIngredient, Ingredient, Category, orderStatus, tableNo, totalpriceaddons) VALUES ('$product_name', '$product_price', '$product_image', '$product_quantity', '$main_ingredient', '$ingredientString', '$category', 'Placed', '1', '$totalAddOnsPrice')");

    if ($insert_product) {
        $message[] = 'Product added to cart successfully';
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


$category = isset($_GET['category']) ? $_GET['category'] : 'sandwich';
$sql = "SELECT DISTINCT TRIM(Category) AS Category FROM `Category`";
$categories = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\style.css">

</head>

<style>
    @media (max-width:768px) {

        .box {
            height: 340px !important;
            width: 100%;
        }
        .menu-container {
            width: 710px;
        }
    }
</style>

<body>
    <header class="header" style="background-color: #2980b9; width: 100%; margin-bottom: 12px;">
        <?php
        if (isset($message)) {
            foreach ($message as $messageText) {
                echo '<div class="message"><span>' . $messageText . '</span> <i class="fas fa-times" onclick="hideMessage(this.parentElement)"></i></div>';
            }
        }
        ?>

        <div class="container-fluid">
            <div class="row align-items-center" style="margin-right: 10px;">
                <div class="col-md-6">
                    <?php
                    echo '<div class="flex">';
                    foreach ($categories as $cat) {
                        $isActive = ($cat['Category'] == $category) ? 'active' : '';
                        echo '<a href="?category=' . $cat['Category'] . '" class="btn btn-primary me-2 ' . $isActive . '">' . $cat['Category'] . '</a>';
                    }
                    echo '</div>';
                    ?>
                </div>
                <div class="col-md-6 text-end">
                    <a href="cart.php" class="btn btn-primary me-2">View Order</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <section class="products">
            <h1 class="heading">Latest Products</h1>
            <div class="menu-container">
                <div class="box-container">
                    <?php
                    $select_products = mysqli_query($conn, "SELECT m.Name, m.*, i.Serving AS MainIngredientServing
        FROM `menu` m
        LEFT JOIN `inventory` i ON m.mainIngredient = i.IngridientName AND m.Category = i.Category
        WHERE m.Category = '$category' AND m.Name NOT LIKE '%Oz%'");

                    $displayedNames = array();

                    if (mysqli_num_rows($select_products) > 0) {
                        while ($fetch_product = mysqli_fetch_assoc($select_products)) {
                            $productName = $fetch_product['Name'];

                            if (!in_array($productName, $displayedNames)) {
                                $displayedNames[] = $productName;
                                $main_ingredient_serving = $fetch_product['MainIngredientServing'];

                                $imageStyle = ($main_ingredient_serving == 0) ? 'filter: grayscale(100%);' : '';
                                $isDisabled = ($main_ingredient_serving == 0) ? 'disabled' : '';
                                ?>
                                <div class="box" style="height: 380px; width: 100%; border-radius:20px; border: 1px solid black;">
                                    <img src="uploaded_img/<?php echo $fetch_product['Image']; ?>" alt="<?php echo $productName; ?>"
                                        height="100px" width="250px"
                                        style="border-radius:20px; border: 1px solid black; <?php echo $imageStyle; ?>">
                                    <h2 style="margin-top: 8px;">
                                        <?php echo $productName; ?>
                                    </h2>
                                    <input type="hidden" name="product_name" value="<?php echo $productName; ?>">
                                    <input type="hidden" name="product_price" value="<?php echo $fetch_product['Price']; ?>">
                                    <input type="hidden" name="product_image"
                                        value="uploaded_img/<?php echo $fetch_product['Image']; ?>">
                                    <button type="button" class="btn" <?php echo $isDisabled; ?> data-bs-toggle="modal"
                                        data-bs-target="#addToCartModal<?php echo $fetch_product['ID']; ?>"
                                        style="width: 100%; border-radius:20px; border: 1px solid black;">Order</button>
                                </div>

                                <div class="modal fade" id="addToCartModal<?php echo $fetch_product['ID']; ?>">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content"
                                            style="width: 100%; border-radius:20px; border: 1px solid black;">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="addToCartModalLabel<?php echo $fetch_product['ID']; ?>">
                                                    Order
                                                    <?php echo $fetch_product['Name']; ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body"
                                                style="display: flex; flex-wrap: wrap; justify-content: space-evenly;">
                                                <?php
                                                $select_same_name_products = mysqli_query($conn, "SELECT * FROM `menu` WHERE Name LIKE '%{$fetch_product['Name']}%'");

                                                while ($same_name_product = mysqli_fetch_assoc($select_same_name_products)) {
                                                    ?>
                                                    <div class="border border-2 border-dark p-3 mb-3 rounded"
                                                        style="width: 150px; display: flex; flex-direction: column; align-items: center;">
                                                        <img src="uploaded_img/<?php echo $same_name_product['Image']; ?>"
                                                            alt="<?php echo $same_name_product['Name']; ?>" height="100px" width="100px"
                                                            style="border-radius: 5px;">
                                                        <h4 class="text-center">
                                                            <?php echo $same_name_product['Name']; ?>
                                                        </h4>
                                                        <h5>₱
                                                            <?php echo $same_name_product['Price']; ?>
                                                        </h5>

                                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                            data-bs-target="#orderDetailsModal<?php echo $same_name_product['ID']; ?>"
                                                            style="width: 100%; border-radius:20px; border: 1px solid black;">Order</button>

                                                        <div class="modal fade"
                                                            id="orderDetailsModal<?php echo $same_name_product['ID']; ?>" tabindex="-1"
                                                            aria-labelledby="orderDetailsModalLabel<?php echo $same_name_product['ID']; ?>"
                                                            aria-hidden="true">
                                                            <div class="modal-dialog modal-lg">
                                                                <div class="modal-content"
                                                                    style="width: 100%; border-radius:20px; border: 1px solid black;">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title"
                                                                            id="orderDetailsModalLabel<?php echo $same_name_product['ID']; ?>"
                                                                            style="font-size: 20px;">Order Details for
                                                                            <?php echo $same_name_product['Name']; ?>
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                            aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body" style="font-size: 18px;">
                                                                        <form action="" method="post">
                                                                            <div class="row mb-4">
                                                                                <div class="col">
                                                                                    <img src="uploaded_img/<?php echo $same_name_product['Image']; ?>"
                                                                                        name="product_image"
                                                                                        alt="<?php echo $same_name_product['Name']; ?>"
                                                                                        height="100px" width="100px"
                                                                                        style="border-radius: 10px;">
                                                                                    <input type="hidden" name="product_name"
                                                                                        value="<?php echo $same_name_product['Name']; ?>">
                                                                                    <input type="hidden" name="product_price"
                                                                                        value="<?php echo $same_name_product['Price']; ?>">
                                                                                    <input type="hidden" name="product_image"
                                                                                        value="uploaded_img/<?php echo $same_name_product['Image']; ?>">
                                                                                    <label>
                                                                                        <?php echo $same_name_product['Name']; ?>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row mb-4">
                                                                                <div class="col">
                                                                                    <label for="Price">Price:</label>
                                                                                    <label class="form-label">
                                                                                        <?php echo $same_name_product['Price']; ?>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row mb-4" style="display: none;">
                                                                                <div class="col">
                                                                                    <label for="Category">Category:</label>
                                                                                    <input type="hidden" name="category"
                                                                                        value="<?php echo $category; ?>">
                                                                                    <label class="form-label">
                                                                                        <?php echo $same_name_product['Category']; ?>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="row mb-4">
                                                                                <div class="col">
                                                                                    <label for="Customize"><a href="#"
                                                                                            id="customize-toggle<?php echo $same_name_product['ID']; ?>"
                                                                                            style="text-decoration:none; color:black;">Customize:
                                                                                            ▼</a></label>
                                                                                </div>
                                                                                <br>
                                                                                <br>
                                                                                <div class="form-group customize-section"
                                                                                    id="customize-section<?php echo $same_name_product['ID']; ?>"
                                                                                    style="display: none;">
                                                                                    <label for="MainIngredient">
                                                                                        <?php echo $same_name_product['mainIngredient']; ?>
                                                                                    </label>
                                                                                    <br>

                                                                                    <?php
                                                                                    $totalAddOnsPrice = 0.0;

                                                                                    $ingredients = explode(', ', $same_name_product['Ingridients']);

                                                                                    echo '<div id="addons-container' . $same_name_product['ID'] . '">';

                                                                                    foreach ($ingredients as $ingredient) {
                                                                                        if (isset($ingredientPrices[$ingredient])) {
                                                                                            $price = $ingredientPrices[$ingredient];

                                                                                            echo '<div class="ingredient-row">';
                                                                                            echo '<label class="ingredient-label" for="ingredient_' . $ingredient . '_' . $same_name_product['ID'] . '">' . $ingredient . '</label>';
                                                                                            echo '<span class="ingredient-price">₱' . number_format($price, 2) . '</span>';
                                                                                            echo '<div class="quantity-input">';
                                                                                            echo '<button type="button" class="quantity-btn minus" onclick="updateTotal(' . $price . ', \'' . $ingredient . '\', -1, ' . $same_name_product['ID'] . ')">-</button>';
                                                                                            echo '<input type="number" id="ingredient_' . $ingredient . '_' . $same_name_product['ID'] . '" name="ingredient_' . $ingredient . '_' . $same_name_product['ID'] . '" value="1" min="-1" max="10" onchange="updateTotal(' . $price . ', \'' . $ingredient . '\', this.value, ' . $same_name_product['ID'] . ')">';
                                                                                            echo '<button type="button" class="quantity-btn plus" onclick="updateTotal(' . $price . ', \'' . $ingredient . '\', 1, ' . $same_name_product['ID'] . ')">+</button>';
                                                                                            echo '</div>';
                                                                                            echo '</div>';
                                                                                            $totalAddOnsPrice += $price;
                                                                                        } else {
                                                                                            echo '<div class="ingredient-row">';
                                                                                            echo '<label class="ingredient-label" for="ingredient_' . $ingredient . '_' . $same_name_product['ID'] . '">' . $ingredient . '</label>';
                                                                                            echo '<span class="ingredient-price">Price not available</span>';
                                                                                            echo '<div class="quantity-input">';
                                                                                            echo '<button type="button" class="quantity-btn minus" disabled>-</button>';
                                                                                            echo '<input type="number" id="ingredient_' . $ingredient . '_' . $same_name_product['ID'] . '" name="ingredient_' . $ingredient . '_' . $same_name_product['ID'] . '" value="1" min="-1" max="10" readonly>';
                                                                                            echo '<button type="button" class="quantity-btn plus" disabled>+</button>';
                                                                                            echo '</div>';
                                                                                            echo '</div>';
                                                                                        }
                                                                                    }

                                                                                    echo '</div>';
                                                                                    ?>
                                                                                    <label for="total-addons-price">Total Add-ons
                                                                                        Price:</label>
                                                                                    <?php
                                                                                    if ($totalAddOnsPrice == $totalAddOnsPrice) {
                                                                                        echo '<input type="text"
                                                                                                            id="total-addons-price' . $same_name_product['ID'] . '"
                                                                                                            name="total-addons-price"
                                                                                                            value="₱' . 0.00 . '"
                                                                                                            readonly>';
                                                                                    }
                                                                                    ?>

                                                                                    <input type="hidden"
                                                                                        id="hidden-total-addons-price<?php echo $same_name_product['ID']; ?>"
                                                                                        name="hidden-total-addons-price" value="">




                                                                                </div>
                                                                            </div>
                                                                            <div class="row mb-4">
    <div class="col">
        <div class="quantity-row" id="quantity-container">
            <label class="quantity-label" for="quantity">Quantity:</label>
            <div class="quantity-input">
                <button type="button" class="quantity-btn minus" onclick="updateQuantity(-1, <?php echo $same_name_product['ID']; ?>)">-</button>
                <input type="number" id="quantity<?php echo $same_name_product['ID']; ?>" name="quantity" value="1" min="1" max="10">
                <button type="button" class="quantity-btn plus" onclick="updateQuantity(1, <?php echo $same_name_product['ID']; ?>)">+</button>
            </div>
        </div>
    </div>
</div>

                                                                            <button type="submit" class="btn btn-primary"
                                                                                name="add_to_cart"
                                                                                style="width: 100%; border-radius:20px; border: 1px solid black;">Add
                                                                                to Order</button>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                            }
                            ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                        ;
                    }
                    ?>
                </div>
            </div>
        </section>
    </div>
</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function () {
        $('#quickOrderModal').modal('show');
    });
    function updateTotal(price, ingredient, quantityChange, productId) {
        var inputElement = document.getElementById('ingredient_' + ingredient + '_' + productId);
        var currentQuantity = parseInt(inputElement.value);
        var newQuantity = currentQuantity + quantityChange;

        if (newQuantity < 0 || newQuantity > 10) {
            return;
        }

        inputElement.value = newQuantity;
        var totalAddOnsPriceElement = document.getElementById('total-addons-price' + productId);
        var totalAddOnsPrice = parseFloat(totalAddOnsPriceElement.value.replace('₱', ''));
        var changeInTotal = price * quantityChange;

        if (newQuantity > 0 && currentQuantity !== 0) {
            var newTotal = totalAddOnsPrice + changeInTotal;
            totalAddOnsPriceElement.value = '₱' + newTotal.toFixed(2);

            var hiddenTotalElement = document.getElementById('hidden-total-addons-price' + productId);
            hiddenTotalElement.value = newTotal.toFixed(2);
        }
    }

    function updateQuantity(change, productId) {
        var quantityInput = document.getElementById('quantity' + productId);
        var currentQuantity = parseInt(quantityInput.value);
        var newQuantity = currentQuantity + change;
        newQuantity = Math.min(10, Math.max(1, newQuantity));
        quantityInput.value = newQuantity;
    }

    document.addEventListener("DOMContentLoaded", function () {
        const customizeToggles = document.querySelectorAll('[id^="customize-toggle"]');

        customizeToggles.forEach(toggle => {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                const productId = toggle.id.replace('customize-toggle', '');
                const customizeSection = document.getElementById(`customize-section${productId}`);

                if (customizeSection.style.display === 'none' || customizeSection.style.display === '') {
                    customizeSection.style.display = 'block';
                } else {
                    customizeSection.style.display = 'none';
                }
            });
        });
    });
</script>

</html>