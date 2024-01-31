<?php
// Include your database connection or any necessary PHP code here
@include 'connection.php';

$message = array();

if (isset($_POST['add_to_cart'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];
    $product_quantity = $_POST['quantity'];
    $main_ingredient = ''; // Initialize the main ingredient variable

  // Fetch the main ingredient from the database
$fetch_product_query = mysqli_query($conn, "SELECT mainIngredient FROM `menu` WHERE Name = '$product_name'");
$fetch_product = mysqli_fetch_assoc($fetch_product_query);

if ($fetch_product) {
    $main_ingredient = $fetch_product['mainIngredient'];
}

// Initialize an array to store the ingredient and quantity pairs
$ingredientQuantities = [];

// Loop through the POST data to capture ingredient quantities
foreach ($_POST as $key => $value) {
    if (strpos($key, 'ingredient_') === 0) {
        // Extract the ingredient name and quantity
        $ingredientName = substr($key, 11);
        $ingredientQuantities[$ingredientName] = $value;
    }
}

// Convert the ingredient and quantity pairs to a comma-separated string
$ingredientString = implode(
    ', ',
    array_map(
        function ($key, $value) {
            return "$key:$value";
        },
        array_keys($ingredientQuantities),
        $ingredientQuantities
    )
);

// Add the main ingredient to the ingredient string if it's not empty
if (!empty($main_ingredient)) {
    $ingredientString = $main_ingredient . ($ingredientString ? ', ' : '') . $ingredientString;
}
    $select_cart = mysqli_query($conn, "SELECT * FROM `cart` WHERE name = '$product_name'");

    if (mysqli_num_rows($select_cart) > 0) {
        $message[] = 'Product already added to cart';
    } else {
        // Insert the product into the cart table
        $insert_product = mysqli_query($conn, "INSERT INTO `cart`(name, price, image, quantity, ingredient) VALUES('$product_name', '$product_price', '$product_image', '$product_quantity', '$ingredientString')");

        $message[] = 'Product added to cart successfully';
    }
}


// Query to fetch ingredient prices from the inventory
$ingredientPrices = array();

$sql = "SELECT IngridientName, Price FROM inventory";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $ingredientPrices[$row['IngridientName']] = $row['Price'];
    }
}

// Close the database connection
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu</title>
    <!-- Add this in your <head> section to include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    
</head>

<body>
    <header class="header">
        <div class="flex">
            <a href="#" class="logo">Sandwich/Extra's Sunny Fries</a>
            <a href="#" class="logo">Espresso Series</a>
            <a href="#" class="logo">Non-Coffee Series</a>
            <a href="#" class="logo">Yogurt Series</a>
            <?php
            $select_rows = mysqli_query($conn, "SELECT * FROM `menu`") or die('query failed');
            $row_count = mysqli_num_rows($select_rows);
            ?>
            <a href="cart.php" class="cart">view order<span></a>
            <div id="menu-btn" class="fas fa-bars"></div>
        </div>
        <?php
        
        if (isset($message)) {
            foreach ($message as $messageText) {
                echo '<div class="message"><span>' . $messageText . '</span> <i class="fas fa-times" onclick="hideMessage(this.parentElement)"></i></div>';
            }
        }

    ?>
    </header>

    <div class="container">
        <section class="products">
            <h1 class="heading">Latest Products</h1>
            <div class="menu-container">
                <div class="box-container">
                    <?php
                    $select_products = mysqli_query($conn, "SELECT * FROM `menu` ");
                    if (mysqli_num_rows($select_products) > 0) {
                        while ($fetch_product = mysqli_fetch_assoc($select_products)) {
                            ?>
                            <form action="" method="post">
                                <div class="box">
                                    <img src="uploaded_img/<?php echo $fetch_product['Image']; ?>"
                                        alt="<?php echo $fetch_product['Name']; ?>" height="100px" width="250px"
                                        style="border-radius: 5px;">
                                    <h3>
                                        <?php echo $fetch_product['Name']; ?>
                                    </h3>
                                    <h4>₱
                                        <?php echo $fetch_product['Price']; ?>
                                    </h4>
                                    <input type="hidden" name="product_name" value="<?php echo $fetch_product['Name']; ?>">
                                    <input type="hidden" name="product_price" value="<?php echo $fetch_product['Price']; ?>">
                                    <input type="hidden" name="product_image"
                                        value="uploaded_img/<?php echo $fetch_product['Image']; ?>">
                                    <button type="button" class="btn" data-bs-toggle="modal"
                                        data-bs-target="#addToCartModal<?php echo $fetch_product['ID']; ?>">Order</button>
                                </div>
                            </form>

                            <!-- Add to Cart Modal -->
                            <div class="modal" id="addToCartModal<?php echo $fetch_product['ID']; ?>">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Customize Your Order</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body" style="font-size: 18px">
                                            <form action="" method="post">
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <img src="uploaded_img/<?php echo $fetch_product['Image']; ?>"
                                                            name="product_image" alt="<?php echo $fetch_product['Name']; ?>"
                                                            height="100px" width="100px" style="border-radius: 10px;">
                                                        <input type="hidden" name="product_name"
                                                            value="<?php echo $fetch_product['Name']; ?>">
                                                        <input type="hidden" name="product_price"
                                                            value="<?php echo $fetch_product['Price']; ?>">
                                                        <input type="hidden" name="product_image"
                                                            value="uploaded_img/<?php echo $fetch_product['Image']; ?>">
                                                        <label>
                                                            <?php echo $fetch_product['Name']; ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <label for="Price">Price:</label>
                                                        <label class="form-label">
                                                            <?php echo $fetch_product['Price']; ?>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <label for="Customize"><a href="#"
                                                                id="customize-toggle<?php echo $fetch_product['ID']; ?>"
                                                                style="text-decoration:none; color:black;">Customize:
                                                                ▼</a></label>
                                                    </div>
                                                    <br>
                                                    <br>
                                                    <div class="form-group customize-section"
                                                        id="customize-section<?php echo $fetch_product['ID']; ?>"
                                                        style="display: none;">
                                                        <label for="MainIngredient">
                                                            <?php echo $fetch_product['mainIngredient']; ?>
                                                        </label>

                                                        <br>

                                                        <?php
                                                        // Initialize a variable to store the total add-ons price
                                                        $totalAddOnsPrice = 0.0;

                                                        // Split the ingredients into an array
                                                        $ingredients = explode(', ', $fetch_product['Ingridients']);

                                                        foreach ($ingredients as $ingredient) {
                                                            if (isset($ingredientPrices[$ingredient])) {
                                                                $price = $ingredientPrices[$ingredient];

                                                                echo '<div class="ingredient-row">';
                                                                echo '<label class="ingredient-label" for="ingredient_' . $ingredient . '">' . $ingredient . '</label>';
                                                                echo '<span class="ingredient-price">$' . number_format($price, 2) . '</span>';
                                                                echo '<div class="quantity-input">';
                                                                echo '<button type="button" class="quantity-btn minus">-</button>';
                                                                echo '<input type="number" id="ingredient_' . $ingredient . '" name="ingredient_' . $ingredient . '" value="1" min="1" max="10">';
                                                                echo '<button type="button" class="quantity-btn plus">+</button>';
                                                                echo '</div>';
                                                                echo '</div>';


                                                                // Calculate and accumulate the total add-ons price
                                                                $quantity = isset($_POST['ingredient_' . $ingredient]) ? (int) $_POST['ingredient_' . $ingredient] : 0;
                                                                $totalAddOnsPrice += $price * $quantity;


                                                            } else {
                                                                echo '<div class="ingredient-row">';
                                                                echo '<label class="ingredient-label" for="ingredient_' . $ingredient . '">' . $ingredient . '</label>';
                                                                echo '<span class="ingredient-price">Price not available</span>';
                                                                echo '<div class="quantity-input">';
                                                                echo '<button type="button" class="quantity-btn minus">-</button>';
                                                                echo '<input type="number" id="ingredient_' . $ingredient . '" name="ingredient_' . $ingredient . '" value="1" min="1" max="10" readonly data-product-id="' . $fetch_product['ID'] . '">';
                                                                echo '<button type="button" class="quantity-btn plus">+</button>';
                                                                echo '</div>';
                                                                echo '</div>';
                                                            }
                                                            //   echo '<div class="total-add-ons-price">';
                                                            //     echo 'Total Add-ons Price: $<span id="totalAddOnsPriceLabel' . $fetch_product['ID'] . '" name="totalAddOnsPrice">' . number_format($totalAddOnsPrice, 2) . '</span>';
                                                            //     echo '</div>';  
                                                        }

                                                        ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-4">
                                                    <div class="col">
                                                        <div class="quantity-row">
                                                            <label class="quantity-label" for="quantity">Quantity:</label>
                                                            <div class="quantity-input">
                                                                <button type="button" class="quantity-btn minus">-</button>
                                                                <input type="number" id="quantity" name="quantity" value="1"
                                                                    min="1" max="10">
                                                                <button type="button" class="quantity-btn plus">+</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary" name="add_to_cart">Add to
                                                    Order</button>
                                            </form>
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


    <!-- Include your JavaScript libraries and custom script.js here -->
    <script src="js/script.js"></script>
    <script>
        function hideMessage(element) {
                // Set a timeout of 3000 milliseconds (3 seconds) before hiding the element
                setTimeout(function() {
                    element.style.display = 'none';
                }, 3000);
            }
        document.addEventListener("DOMContentLoaded", function () {
            const quantityInputs = document.querySelectorAll(".quantity-input input");
            const plusButtons = document.querySelectorAll(".quantity-btn.plus");
            const minusButtons = document.querySelectorAll(".quantity-btn.minus");

            plusButtons.forEach((plusButton, index) => {
                plusButton.addEventListener("click", function () {
                    const input = quantityInputs[index];
                    let currentValue = parseInt(input.value);
                    if (!isNaN(currentValue) && currentValue < 10) {
                        input.value = currentValue + 1;
                        const ingredientName = input.name.replace("ingredient_", "");
                        const price = parseFloat(<?php echo json_encode($ingredientPrices); ?>[ingredientName]);
                        if (!isNaN(price)) {
                            const productId = input.getAttribute("data-product-id");
                            const totalAddOnsPriceLabel = document.getElementById("totalAddOnsPriceLabel" + productId);
                            totalAddOnsPrice += price;
                            totalAddOnsPriceLabel.textContent = "$" + totalAddOnsPrice.toFixed(2);
                        }
                    }
                });
            });

            minusButtons.forEach((minusButton, index) => {
                minusButton.addEventListener("click", function () {
                    const input = quantityInputs[index];
                    let currentValue = parseInt(input.value);
                    if (!isNaN(currentValue) && currentValue > 1) {
                        input.value = currentValue - 1;
                        const ingredientName = input.name.replace("ingredient_", "");
                        const price = parseFloat(<?php echo json_encode($ingredientPrices); ?>[ingredientName]);
                        if (!isNaN(price)) {
                            const productId = input.getAttribute("data-product-id");
                            const totalAddOnsPriceLabel = document.getElementById("totalAddOnsPriceLabel" + productId);
                            totalAddOnsPrice -= price;
                            totalAddOnsPriceLabel.textContent = "$" + totalAddOnsPrice.toFixed(2);
                        }
                    }
                });
            });
        });
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

    <!-- Add this at the end of your <body> section to include Bootstrap JavaScript and jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>