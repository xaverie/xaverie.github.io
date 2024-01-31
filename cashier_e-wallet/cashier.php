<?php
include 'connection.php';
include 'transaction.php';

$message = array();

if (isset($_POST['proceedPayment'])) {
    $order_id = $_POST['order_id'];
    mysqli_query($conn, "UPDATE `orders` SET paymentStatus = 'Paid',queueStatus = 'Preparing' WHERE order_id = '$order_id'");
    $updateOrdersDetailsSql = "UPDATE ordersdetails SET queueStatus = 'Preparing',productStatus = 'Preparing' WHERE orderID = $order_id";

    if (mysqli_query($conn, $updateOrdersDetailsSql)) {
    } else {
        echo "Error updating order details status: " . mysqli_error($conn);
    }
}
if (isset($_POST['proceedPaymentEwallet'])) {
    $order_id = $_POST['order_id'];
    mysqli_query($conn, "UPDATE `orders` SET MOP = 'E-Wallet',queueStatus = 'Preparing' WHERE order_id = '$order_id'");
    $updateOrdersDetailsSql = "UPDATE ordersdetails SET queueStatus = 'Preparing',productStatus = 'Preparing' WHERE orderID = $order_id";

    if (mysqli_query($conn, $updateOrdersDetailsSql)) {
    } else {
        echo "Error updating order details status: " . mysqli_error($conn);
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



if (isset($_POST['update_update_btn'])) {
    $update_value = $_POST['update_quantity'];
    $update_id = $_POST['update_quantity_id'];
    $update_quantity_query = mysqli_query($conn, "UPDATE `cashierCart` SET quantity = '$update_value' WHERE id = '$update_id'");
    if ($update_quantity_query) {
        header('location:cashier.php');
    }
}

if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM `cashierCart` WHERE id = '$remove_id'");
    header('location:cashier.php');
}

if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM `cashierCart`");
    header('location:cashier.php');
}
if (isset($_POST['checkout'])) {
    mysqli_begin_transaction($conn);

    try {
        if (mysqli_num_rows($select_cart) > 0) {
            $mainIngredientQuantities = [];
            $cartData = [];
            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                $cartData[] = $fetch_cart;
            }

            foreach ($cartData as $fetch_cart) {
                $mainIngredient = $fetch_cart['mainIngredient'];
                $mainIngredientQuantity = $fetch_cart['quantity'];

                if (isset($mainIngredientQuantities[$mainIngredient])) {
                    $mainIngredientQuantities[$mainIngredient] += $mainIngredientQuantity;
                } else {
                    $mainIngredientQuantities[$mainIngredient] = $mainIngredientQuantity;
                }
            }

            foreach ($mainIngredientQuantities as $ingredient => $quantity) {
                $fetchNumServing = mysqli_query($conn, "SELECT `NumServing` FROM `inventory` WHERE `IngridientName` = '$ingredient'");
                $row = mysqli_fetch_assoc($fetchNumServing);

                $NumServing = isset($row['NumServing']) ? $row['NumServing'] : 0;

                mysqli_query($conn, "UPDATE `inventory` SET 
                    `Serving` = `Serving` - $quantity, 
                    `hns` = IF(`hns` = 0, $NumServing, `hns` - $quantity), 
                    `Stock` = IF(`hns` = 0, `Stock` - 1, `Stock`)
                WHERE `IngridientName` = '$ingredient'");

                mysqli_query($conn, "UPDATE `inventory` SET `hns` = $NumServing WHERE `IngridientName` = '$ingredient' AND `hns` = 0");
            }
            mysqli_query($conn, "INSERT INTO `orders` (`tableNo`, `order_details`, `price`, `MOP`, `paymentStatus`, `ingredient`,queueStatus)
            SELECT '1', GROUP_CONCAT(CONCAT(name, ': ', quantity, ' ', category ) SEPARATOR ', '), SUM(price * quantity), 'Cash', 'Processing', GROUP_CONCAT(CONCAT(ingredient, ': ', quantity) SEPARATOR ', '),'Preparing'
            FROM `cashierCart`");
            $fetch_ingredient_query = mysqli_query($conn, "SELECT Ingredient FROM `cashierCart`");
            $fetch_ingredient = mysqli_fetch_assoc($fetch_ingredient_query);

            if ($fetch_ingredient) {
                $removedIngredients = [];
                $ingredientPairs = explode(', ', $fetch_ingredient['Ingredient']);
                foreach ($ingredientPairs as $pair) {
                    list($ingredient, $quantity) = explode(': ', $pair);
                    $removedIngredients[$ingredient] = (int) $quantity;
                }

                updateInventory($conn, $removedIngredients);
            }
            mysqli_commit($conn);

            mysqli_query($conn, "DELETE FROM `cashierCart`");
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "Checkout failed: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="src/style/style.css">
    <link rel="stylesheet" href="src/style/style1.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
    <style>
        .box-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin: 0;
            border: 1px solid black;
            padding: 6px;
            border-radius: 5px;
            width: 150px;
            height: 160px !important;
        }

        .menu-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between !important;
        }

        img {
            border-radius: 5px;
            margin-bottom: 10px;
            width: 90px;
            height: 90px;
        }

        .contain {
            display: flex;
            flex-direction: column;
        }

        .col-md-3 {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            border-radius: 20px;
            border: 1px solid black;
            height: 300px;
            width: 320px;
            overflow-y: auto;
            overflow-x: hidden;
            margin-bottom: 10px;
        }

        .col-md-8 {
            height: 30px;
            width: 800px;
        }

        .item2 {
            overflow-y: auto;
            overflow-x: hidden;
            grid-area: menu;
            width: 330px;
            margin: 0;
        }

        .item3 {
            display: flex;
            overflow-y: auto;
            overflow-x: hidden;
            grid-area: main;
            margin-left: 20px;
        }

        .btn2 {
            position: sticky;
            text-wrap: nowrap;
        }


        .grid-container {
            display: grid;
            grid-template-areas:
                'menu main main main main main'
                'menu main main main main main ';
        }

        .grid-container>div {
            font-size: 10px;
        }

        @media only screen and (max-width: 768px) {

            .grid-container>div {
                font-size: 10px;
            }

            .box-container {
                width: 80%;
                margin-bottom: 10px;

            }

            .col-md-3 {
                margin-top: 5px;
                width: 100%;
                height: 260px;
            }

            .col-md-8 {
                width: 100%;
                height: 260px;
            }

            .item2 {
                width: 250px;

            }

            .item3 {

                margin-left: 20px;
            }

            .grid-container {
                gap: 1px;
                display: grid;
                grid-template-areas:
                    'menu main ';
            }


            .btn2 {
                position: sticky;
                text-wrap: nowrap;
            }

            img {
                width: 50px;
                height: 50px;
            }

            .box-container {
                padding: -9px !important;
                border-radius: 5px;
                width: 100px;
                height: 130px !important;
            }

        }
    </style>
    <title>Cashier</title>
</head>

<body>

    <div class="container-fluid">
        <div class="grid-container">
            <div class="item2">
                <div class="contain">
                    <div class="col-md-3 sidebar p-0">
                        <h2 class="head text-center">Discounts/Cash Payment</h2>
                        <main class="row justify-content-center" id="orders-container">
                            <?php
                            $sql = "SELECT *, MOP FROM orders WHERE paymentStatus = 'Processing' AND MOP IN ('Cash', 'Discount')";
                            $result = mysqli_query($conn, $sql);

                            $ordersData = [];
                            while ($row = mysqli_fetch_assoc($result)) {
                                $ordersData[] = $row;
                            }

                            foreach ($ordersData as $row) {
                                ?>
                                <div class="discount-container mx-auto mb-3" data-bs-toggle="modal"
                                    data-bs-target="#discountModal<?php echo $row['order_id']; ?>"
                                    style="border-radius:20px; border: 1px solid black; font-size: 13px; padding: 10px; width: 90%; border-radius: 10px;">
                                    <p class="mt-0 text-center" style="margin: 0;">Table
                                        <?php echo $row['tableNo'] ?>
                                    </p>
                                    <p class="mt-0 text-center" style="margin: 0;">Table
                                        <?php echo $row['order_id'] ?>
                                    </p>
                                    <p class="text-center mb-0">
                                        <?php echo $row['MOP'] ?>
                                    </p>
                                </div>
                            <?php } ?>
                            <div id="discountModalContent">
                                <?php foreach ($ordersData as $row) { ?>
                                    <div class="modal fade" id="discountModal<?php echo $row['order_id']; ?>" tabindex="-1"
                                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-sm">
                                            <div class="modal-content"
                                                style="width: 100%; border-radius:20px; border: 1px solid black;">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fs-3">Order
                                                        <?php echo $row['order_id']; ?>
                                                    </h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body" style="height: auto; overflow-y: auto;">
                                                    <h3 class="text-center fs-4">Table
                                                        <?php echo $row['tableNo']; ?>
                                                    </h3>
                                                    <h3 class="text-center fs-4">Order No.
                                                        <?php echo $row['order_id']; ?>
                                                    </h3>
                                                    <br>
                                                    <br>
                                                    <h6 class="fs-5">Order Details: </h6>
                                                    <ul class="list-unstyled">
                                                        <?php
                                                        $orderDetailsList = explode(',', $row['order_details']);

                                                        foreach ($orderDetailsList as $orderDetail) {
                                                            echo '<li class="fs-5">' . trim($orderDetail) . '</li>';
                                                        }
                                                        ?>
                                                    </ul>
                                                    <p class="fs-5">
                                                        <?php echo $row['price']; ?>
                                                    </p>

                                                    <form method="post">
                                                        <input type="hidden" name="order_id"
                                                            value="<?php echo $row['order_id']; ?>">
                                                        <button type="submit" class="btn btn-primary fs-5"
                                                            name="proceedPayment"
                                                            style="width: 100%; border-radius:20px; border: 1px solid black;">Proceed</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </main>
                    </div>

                </div>
                <div class="col-md-3 sidebar p-0">
                    <h2 class="head text-center">E-Wallet</h2>
                    <main class="row justify-content-center" id="ewallet-orders-container">
                        <?php
                        $sql = "SELECT * FROM orders WHERE MOP = 'EWallet' ORDER BY order_id DESC";
                        $result = mysqli_query($conn, $sql);

                        $ordersData = [];
                        while ($row = mysqli_fetch_assoc($result)) {
                            $ordersData[] = $row;
                        }

                        foreach ($ordersData as $row) {
                            ?>
                            <div class="discount-container mx-auto mb-3" data-bs-toggle="modal"
                                data-bs-target="#ewalletDiscountModal<?php echo $row['order_id']; ?>"
                                style="border-radius:20px; border: 1px solid black; font-size: 13px; padding: 10px; width: 90%; border-radius: 10px;">
                                <p class="mt-0 text-center" style="margin: 0;">Table
                                    <?php echo $row['tableNo'] ?>
                                </p>
                                <p class="mt-0 text-center" style="margin: 0;">Table
                                    <?php echo $row['order_id'] ?>
                                </p>
                                <p class="text-center mb-0">
                                    <?php echo $row['MOP'] ?>
                                </p>
                            </div>
                        <?php } ?>
                        <div id="ewalletDiscountModalContent">
                            <?php foreach ($ordersData as $row) { ?>
                                <div class="modal" id="ewalletDiscountModal<?php echo $row['order_id']; ?>">
                                    <div class="modal-dialog modal-sm">
                                        <div class="modal-content"
                                            style="width: 100%; border-radius:20px; border: 1px solid black;">
                                            <div class="modal-header">
                                                <h5 class="modal-title fs-3">Order
                                                    <?php echo $row['order_id']; ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body" style="height: auto; overflow-y: auto;">
                                                <h3 class="text-center fs-4">Table
                                                    <?php echo $row['tableNo']; ?>
                                                </h3>
                                                <h3 class="text-center fs-4">Order No.
                                                    <?php echo $row['order_id']; ?>
                                                </h3>
                                                <br>
                                                <br>
                                                <h6 class="fs-5">Order Details: </h6>
                                                <ul class="list-unstyled">
                                                    <?php
                                                    $orderDetailsList = explode(',', $row['order_details']);

                                                    foreach ($orderDetailsList as $orderDetail) {
                                                        echo '<li class="fs-5">' . trim($orderDetail) . '</li>';
                                                    }
                                                    ?>
                                                </ul>
                                                <p class="fs-5">
                                                    <?php echo $row['price']; ?>
                                                </p>

                                                <form method="post">
                                                    <input type="hidden" name="order_id"
                                                        value="<?php echo $row['order_id']; ?>">
                                                    <button type="submit" class="btn btn-primary fs-5"
                                                        name="proceedPaymentEwallet"
                                                        style="width: 100%; border-radius:20px; border: 1px solid black;">Proceed</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </main>
                </div>
            </div>
            <div class="item3">
                <div class="col-md-8">
                    <?php

                    echo '<div class="d-flex">';
                    foreach ($categories as $cat) {
                        $isActive = ($cat['Category'] == $category) ? 'active' : '';
                        echo '<a href="?category=' . $cat['Category'] . '" class="btn btn-primary me-2 btn2' . $isActive . '" style="border-radius:20px; border: 1px solid black;">' . $cat['Category'] . '</a>';
                    }
                    echo '<button type="button" class="btn btn-primary me-2 btn2" data-bs-toggle="modal" data-bs-target="#viewOrderModal" style=" border-radius:20px; border: 1px solid black;">View Order</button>';

                    echo '</div>';
                    ?>
                    <div class="modalCart">
                        <div class="modal" id="viewOrderModal">
                            <div class="modal-dialog modal-xl">
                                <div class="sample modal-content"
                                    style="width: 100%; border-radius:20px; border: 1px solid black;">
                                    <div class="modal-header">
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body" style="overflow-y: auto;">

                                        <div class="container">
                                            <section class="shopping-cart">
                                                <h1 class="heading">List of Orders</h1>
                                                <br>

                                                <table class="table table-hover text-center">
                                                    <thead class="table" style="color: white;">
                                                        <th style="font-size: 14px">image</th>
                                                        <th style="font-size: 14px">name</th>
                                                        <th style="font-size: 14px">ingredient</th>
                                                        <th style="font-size: 14px">price</th>
                                                        <th style="font-size: 14px">quantity</th>
                                                        <th style="font-size: 14px">total price</th>
                                                        <th style="font-size: 14px">Action</th>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $select_cart = mysqli_query($conn, "SELECT * FROM `cashierCart`");

                                                        $grand_total = 0;
                                                        $sub_total;
                                                        $tax_rate = 0.12;
                                                        $tax_amount = 0;

                                                        if (mysqli_num_rows($select_cart) > 0) {
                                                            while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                                                                ?>
                                                                <tr>
                                                                    <td><img src="<?php echo $fetch_cart['image']; ?>" alt=""
                                                                            height="10x" width="50px"
                                                                            style="border-radius: 5px;"></td>
                                                                    <td style="font-size: 14px">
                                                                        <?php echo $fetch_cart['name']; ?>
                                                                    </td>
                                                                    <td>
                                                                        <ul style="list-style-type: none;font-size: 14px ">

                                                                            <?php
                                                                            $ingredient = explode(', ', $fetch_cart['ingredient']);
                                                                            foreach ($ingredient as $ingredients) {
                                                                                echo '<li>' . $ingredients . ' </li>';
                                                                            }
                                                                            ?>
                                                                        </ul>
                                                                    </td>


                                                                    <td style="font-size: 14px">₱
                                                                        <?php echo number_format($fetch_cart['price'], 2, '.', ','); ?>
                                                                    </td>
                                                                    <td>
                                                                        <form action="" method="post">
                                                                            <input type="hidden" name="update_quantity_id"
                                                                                value="<?php echo $fetch_cart['id']; ?>">
                                                                            <label style="font-size: 14px">
                                                                                <?php echo $fetch_cart['quantity']; ?>
                                                                            </label>
                                                                        </form>
                                                                    </td style="font-size: 14px">
                                                                    <?php $sub_total = $fetch_cart['price'] * $fetch_cart['quantity']; ?>
                                                                    <td style="font-size: 14px">₱
                                                                        <?php echo number_format($sub_total, 2, '.', ','); ?>
                                                                    </td>
                                                                    <td style="font-size: 14px">
                                                                        <button type="button" class="btn btn-primary"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#myModalEdit<?php echo $fetch_cart['id']; ?>">Edit</button>
                                                                        <a href="cashier.php?remove=<?php echo $fetch_cart['id']; ?>"
                                                                            onclick="return confirm('remove item from cart?')"
                                                                            class="delete-btn"> <i class="fas fa-trash"
                                                                                style="text-decoration:none"></i>remove</a>

                                                                        <div class="modal"
                                                                            id="myModalEdit<?php echo $fetch_cart['id']; ?>">
                                                                            <div class="modal-dialog">
                                                                                <div class="modal-content">
                                                                                    <div class="modal-header">
                                                                                        <h5 class="modal-title">Customize Your
                                                                                            Order
                                                                                        </h5>
                                                                                        <button type="button" class="btn-close"
                                                                                            data-bs-dismiss="modal"></button>
                                                                                    </div>
                                                                                    <div class="modal-body"
                                                                                        style="font-size: 18px">
                                                                                        <form action="" method="post">
                                                                                            <div class="row mb-4">
                                                                                                <div class="col-auto">
                                                                                                    <img src="<?php echo $fetch_cart['image']; ?>"
                                                                                                        alt="<?php echo $fetch_cart['name']; ?>"
                                                                                                        height="100px"
                                                                                                        width="100px"
                                                                                                        style="border-radius: 10px;">
                                                                                                    <label class="form-label">
                                                                                                        <?php echo $fetch_cart['name']; ?>
                                                                                                    </label>
                                                                                                </div>
                                                                                                <div class="row mb-4">
                                                                                                    <div class="col-auto">
                                                                                                        <label
                                                                                                            for="Price">Price:</label>
                                                                                                        <label
                                                                                                            class="form-label">
                                                                                                            <?php echo $fetch_cart['price']; ?>
                                                                                                        </label>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="row mb-4">
                                                                                                    <div class="col text-start">
                                                                                                        <label for="Customize">
                                                                                                            <a href="#"
                                                                                                                id="customize-toggle<?php echo $fetch_cart['id']; ?>"
                                                                                                                style="text-decoration:none; color:black;">Customize:
                                                                                                                ▼</a>
                                                                                                        </label>
                                                                                                    </div>
                                                                                                    <div class="form-group customize-section text-start"
                                                                                                        id="customize-section<?php echo $fetch_cart['id']; ?>">
                                                                                                        <br>
                                                                                                        <br>
                                                                                                        <label
                                                                                                            for="MainIngredient">
                                                                                                            <?php echo $fetch_cart['mainIngredient']; ?>
                                                                                                        </label>
                                                                                                        <?php
                                                                                                        $totalAddOnsPrice = 0.0;

                                                                                                        $ingredients = explode(', ', $fetch_cart['ingredient']);

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
                                                                                                                $quantity = isset($_POST['ingredient_' . $ingredient]) ? (int) $_POST['ingredient_' . $ingredient] : 0;
                                                                                                                $totalAddOnsPrice += $price * $quantity;


                                                                                                            } else {
                                                                                                                echo '<div class="ingredient-row">';
                                                                                                                echo '<label class="ingredient-label" for="ingredient_' . $ingredient . '">' . $ingredient . '</label>';
                                                                                                                echo '<span class="ingredient-price">Price not available</span>';
                                                                                                                echo '<div class="quantity-input">';
                                                                                                                echo '<button type="button" class="quantity-btn minus">-</button>';
                                                                                                                echo '<input type="number" id="ingredient_' . $ingredient . '" name="ingredient_' . $ingredient . '" value="1" min="1" max="10" readonly data-product-id="' . $fetch_cart['id'] . '">';
                                                                                                                echo '<button type="button" class="quantity-btn plus">+</button>';
                                                                                                                echo '</div>';
                                                                                                                echo '</div>';
                                                                                                            }

                                                                                                        }

                                                                                                        ?>

                                                                                                    </div>
                                                                                                </div>
                                                                                        </form>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php
                                                                $grand_total += $sub_total;
                                                                $tax_amount = $grand_total * $tax_rate;
                                                            }
                                                            ;
                                                        }
                                                        ;
                                                        ?>

                                                        <tr class="table-bottom" style="font-size: 14px">
                                                            <td></td>
                                                            <td colspan="4" style="font-size: 14px">Total Amount</td>
                                                            <td style="font-size: 14px">₱
                                                                <?php echo number_format($grand_total, 2, '.', ','); ?>
                                                            </td>
                                                            <td><a href="cashier.php?delete_all"
                                                                    onclick="return confirm('are you sure you want to delete all?');"
                                                                    class="delete-btn"> <i class="fas fa-trash"></i>
                                                                    Remove all
                                                                </a></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <div
                                                    class="checkout-btn d-flex justify-content-center align-items-center">
                                                    <button type="button btn1" data-bs-toggle="modal"
                                                        data-bs-target="#PaymentModal"
                                                        class="btn1 <?= ($grand_total > 1) ? '' : 'disabled'; ?>"
                                                        style="height:75px; width:150px;  border-radius:20px; border: 1px solid black;">Check
                                                        Out</button>
                                                        <a href="cashier.php" class="btn1" style="height: 75px; width: 150px; margin-left: 30px; background-color: red; text-align: center; text-decoration: none; color: white; border-radius: 20px; border: 1px solid black; display: flex; align-items: center; justify-content: center;">
   Menu
</a>

                                                </div>
                                                <div class="modal" id="PaymentModal">
                                                    <div class="modal-dialog modal-dialog-centered"
                                                        id="customModalDialog">
                                                        <div class="modal-content"
                                                            style="width: 100%; border-radius:20px; border: 1px solid black;">
                                                            <div class="text-center mb-4">
                                                                <br>
                                                                <h3>Payment Method</h3>
                                                            </div>

                                                            <div class="container-xl d-flex justify-content-center">
                                                                <form method="post"
                                                                    style="width: 70vw; min-width: 300px;">
                                                                    <div class="row mb-4">
                                                                        <div class="col">
                                                                            <h1 style="margin-bottom: 10px;">Bill: ₱
                                                                                <?php echo number_format($grand_total, 2, '.', ','); ?>
                                                                            </h1>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-3">
                                                                        <div class="col">
                                                                            <label class="form-label"
                                                                                style="font-size: 18px; margin-bottom: 5px;">Enter
                                                                                Amount:</label>
                                                                            <input type="text" class="form-control"
                                                                                id="amount" name="amount"
                                                                                oninput="calculateChange()"
                                                                                style="height: 40px; font-size: 16px; border: 1px solid black;"
                                                                                required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-3">
                                                                        <div class="col">
                                                                            <label for="tableNumber"
                                                                                style="font-size: 18px; margin-bottom: 5px;">Table
                                                                                Number:</label>
                                                                            <input type="text" class="form-control"
                                                                                id="tableNumber" name="tableNumber"
                                                                                style="height: 40px; font-size: 16px; border: 1px solid black;"
                                                                                required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-3">
                                                                        <div class="col d-flex align-items-center">
                                                                            <input type="checkbox" id="idCheckbox"
                                                                                onclick="showIdInput()"
                                                                                style="font-size: 20px; width: 20px; height: 20px; margin-right: 10px; ">
                                                                            <label for="idCheckbox"
                                                                                style="font-size: 18px;">Include ID
                                                                                Number</label>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-3" id="idInputRow"
                                                                        style="display: none;">
                                                                        <div class="col">
                                                                            <label for="idNumber"
                                                                                style="font-size: 18px; margin-bottom: 5px;">ID
                                                                                Number:</label>
                                                                            <input type="text" class="form-control"
                                                                                id="idNumber" name="idNumber"
                                                                                style="height: 40px; font-size: 16px; border: 1px solid black;"
                                                                                required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-3" id="discountedBillRow"
                                                                        style="display: none;">
                                                                        <div class="col">
                                                                            <h4 style="margin-bottom: 10px;">Discounted
                                                                                Bill:
                                                                                ₱<span id="discountedBill">0.00</span>
                                                                            </h4>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-3">
                                                                        <div class="col">
                                                                            <h4 style="margin-bottom: 10px;">Change:
                                                                                ₱<span id="change">0.00</span></h4>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mt-3">
                                                                        <div class="col">
                                                                            <button type="submit"
                                                                                class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>"
                                                                                onclick="checkout()" name="checkout"
                                                                                style="width:150px; margin-top:5%; border-radius:20px; border: 1px solid black;">Check
                                                                                Out</button>
                                                                            <br>
                                                                        </div>
                                                                    </div>

                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="menu-container" style="max-height: 600px; overflow-y: auto;">
                            <?php
                            $select_products = mysqli_query($conn, "SELECT m.*, i.Serving AS MainIngredientServing
                FROM `menu` m
                LEFT JOIN `inventory` i ON m.mainIngredient = i.IngridientName AND m.Category = i.Category
                WHERE m.Category = '$category'");

                            if (mysqli_num_rows($select_products) > 0) {
                                while ($fetch_product = mysqli_fetch_assoc($select_products)) {
                                    $main_ingredient_serving = $fetch_product['MainIngredientServing'];

                                    $imageStyle = ($main_ingredient_serving == 0) ? 'filter: grayscale(100%);' : '';
                                    $isDisabled = ($main_ingredient_serving == 0) ? 'disabled' : '';
                                    ?>
                                    <div class="col">
                                        <button type="button" class="btn btn-link" data-bs-toggle="modal"
                                            data-bs-target="#addToCartModal<?php echo $fetch_product['ID']; ?>"
                                            style="margin: 0; padding: 0; text-decoration: none; border: none; background-color: transparent;"
                                            <?php echo $isDisabled; ?>>
                                            <div class="box-container" style="height: 170px; margin: 0;">
                                                <div>
                                                    <img src="uploaded_img/<?php echo $fetch_product['Image']; ?>"
                                                        alt="<?php echo $fetch_product['Name']; ?>" height="100px" width="100px"
                                                        style="border-radius: 5px; <?php echo $imageStyle; ?>">
                                                </div>
                                                <h6 style="text-decoration: none; font-size: 12px; color: blue;">
                                                    <?php echo $fetch_product['Name']; ?>
                                                </h6>
                                            </div>
                                        </button>
                                    </div>
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
                                                                    name="product_image"
                                                                    alt="<?php echo $fetch_product['Name']; ?>" height="100px"
                                                                    width="100px" style="border-radius: 10px;">
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
                                                                $totalAddOnsPrice = 0.0;

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

                                                                }

                                                                ?>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-4">
                                                            <div class="col">
                                                                <div class="quantity-row">
                                                                    <label class="quantity-label"
                                                                        for="quantity">Quantity:</label>
                                                                    <div class="quantity-input">
                                                                        <button type="button"
                                                                            class="quantity-btn minus">-</button>
                                                                        <input type="number" id="quantity" name="quantity"
                                                                            value="1" min="1" max="10">
                                                                        <button type="button"
                                                                            class="quantity-btn plus">+</button>
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
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        var isModalOpen = false;

        function checkModalOpen() {
            isModalOpen = $('#discountModalContent .modal.show').length > 0;
        }

        function refreshOrders() {
            checkModalOpen();

            if (!isModalOpen) {
                $.ajax({
                    url: 'cashier.php',
                    type: 'GET',
                    success: function (response) {
                        $('#orders-container').html($(response).find('#orders-container').html());
                    },
                    error: function (error) {
                        console.error('Error fetching orders:', error);
                    }
                });
            }
        }

        setInterval(function () {
            refreshOrders();
        }, 500);

        $('#discountModalContent .modal').on('shown.bs.modal', function () {
            isModalOpen = true;
        });

        $('#discountModalContent .modal').on('hidden.bs.modal', function () {
            isModalOpen = false;
        });

        checkModalOpen();

        var isEWalletModalOpen = false;

        function checkEWalletModalOpen() {
            isEWalletModalOpen = $('#ewalletDiscountModalContent .modal.show').length > 0;
        }

        function refreshEWalletOrders() {
            checkEWalletModalOpen();

            if (!isEWalletModalOpen) {
                $.ajax({
                    url: 'cashier.php',
                    type: 'GET',
                    success: function (response) {
                        $('#ewallet-orders-container').html($(response).find('#ewallet-orders-container').html());
                    },
                    error: function (error) {
                        console.error('Error fetching orders:', error);
                    }
                });
            }
        }

        setInterval(function () {
            refreshEWalletOrders();
        }, 500);

        $('#ewalletDiscountModalContent .modal').on('shown.bs.modal', function () {
            isEWalletModalOpen = true;
        });

        $('#ewalletDiscountModalContent .modal').on('hidden.bs.modal', function () {
            isEWalletModalOpen = false;
        });

        checkEWalletModalOpen();

        $(document).on('click', '[data-bs-target="#discountModal"]', function () {
            var orderId = $(this).data('order-id');
            showModal(orderId);
        });

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
        function setModalHeight() {
            var modalDialog = document.getElementById('customModalDialog');
            var modalContent = document.querySelector('.modal-content');
            modalDialog.style.height = (modalContent.offsetHeight + 20) + 'px';
        }

        $('#PaymentModal').on('shown.bs.modal', function () {
            setModalHeight();
        });

        window.addEventListener('resize', function () {
            setModalHeight();
        });

        function calculateChange() {
            var billAmount = parseFloat('<?php echo $grand_total; ?>');
            var enteredAmount = parseFloat(document.getElementById('amount').value);
            var discountCheckbox = document.getElementById('idCheckbox');
            var discount = discountCheckbox.checked ? 0.2 : 0;
            var discountedBillRow = document.getElementById('discountedBillRow');

            discountedBillRow.style.display = discountCheckbox.checked ? 'block' : 'none';

            var idNumberInput = document.getElementById('idNumber');
            idNumberInput.required = discountCheckbox.checked;

            if (!isNaN(enteredAmount)) {
                var discountedBill = billAmount - (billAmount * discount);
                var discountedAmount = enteredAmount - (enteredAmount * discount);
                var change = discountedAmount - discountedBill;
                document.getElementById('change').innerHTML = change.toFixed(2);

                var checkoutButton = document.querySelector('.btn');
                checkoutButton.disabled = (discountedAmount < discountedBill) || (enteredAmount < billAmount);

                document.getElementById('discountedBill').innerHTML = discountedBill.toFixed(2);
            } else {
                document.getElementById('change').innerHTML = '0.00';

                var checkoutButton = document.querySelector('.btn');
                checkoutButton.disabled = true;

                document.getElementById('discountedBill').innerHTML = '0.00';
            }
        }

        function showIdInput() {
            var idCheckbox = document.getElementById('idCheckbox');
            var idInputRow = document.getElementById('idInputRow');

            idInputRow.style.display = idCheckbox.checked ? 'block' : 'none';
            calculateChange();
        }

        function checkout() {
            var enteredAmount = parseFloat(document.getElementById('amount').value);
            var change = parseFloat(document.getElementById('change').innerText);

            if (isNaN(enteredAmount) || enteredAmount < <?= $grand_total ?>) {
                alert('Please enter a valid amount greater than or equal to the discounted bill amount.');
            } else if (change < 0) {
                alert('Change cannot be negative. Please enter a sufficient amount.');
            } else {
                document.forms[0].submit();
            }
        }

    </script>
</body>

</html>