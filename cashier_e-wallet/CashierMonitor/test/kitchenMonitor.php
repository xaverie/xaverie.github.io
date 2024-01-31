<?php
session_start(); // Start the session at the beginning of the file

include 'src/connection/connection.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['done'])) {
        $orderId = mysqli_real_escape_string($conn, $_POST['order_id']);

        // Update the productStatus column in ordersdetails table to 'completed'
        $updateProductStatusSql = "UPDATE `ordersdetails` 
        SET productStatus = 'Completed', status = 'Completed' 
        WHERE orderID = '$orderId'";        mysqli_query($conn, $updateProductStatusSql);
    }

    // Store the selected categories in the session
    if (isset($_POST['categories'])) {
        $_SESSION['selected_categories'] = $_POST['categories'];
    }
}

// Fetch new orders based on selected categories
$searchKeywords = isset($_SESSION['selected_categories']) ? $_SESSION['selected_categories'] : [];
$categoryConditions = [];

if (!empty($searchKeywords)) {
    foreach ($searchKeywords as $searchKeyword) {
        $categoryConditions[] = "category LIKE '%$searchKeyword%'";
    }
}

// Combine category conditions with OR
$categoryCondition = (!empty($categoryConditions)) ? implode(' OR ', $categoryConditions) : "1";

// Fetch orders based on selected categories
$sql = "SELECT * FROM `ordersdetails` WHERE  Status = 'Processing' AND ($categoryCondition)";
$result = mysqli_query($conn, $sql);

if (!$result) {
    // Handle the error, print the SQL query, and exit the script
    echo "Error: " . mysqli_error($conn);
    echo "SQL Query: $sql";
    exit();
}
$orders = [];

while ($row = mysqli_fetch_assoc($result)) {
    $orderId = $row['orderID'];

    // Check if the order is marked as "Done" in local storage
    $isDone = (isset($_COOKIE['done_orders']) && in_array($orderId, explode(",", $_COOKIE['done_orders'])));

    // Skip rendering if the order is marked as "Done"
    if ($isDone) {
        continue;
    }

    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitchen/Barista Monitor</title>
    <link rel="stylesheet" href="src/style/index.css"> <!-- Link to your custom CSS file -->
</head>

<body>

<form method="post" action="">
    <label class="form-label">Category:</label>
    <div class="row">
        <?php
        $sql = "SELECT DISTINCT TRIM(Category) AS Category FROM `Category`";
        $categories = mysqli_query($conn, $sql);

        if (mysqli_num_rows($categories) > 0) {
            while ($row = mysqli_fetch_assoc($categories)) {
                // Check if the category is in the selected categories stored in $_SESSION
                $checked = (isset($_SESSION['selected_categories']) && in_array($row['Category'], $_SESSION['selected_categories'])) ? 'checked' : '';
        ?>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="categories[]" value="<?= $row['Category'] ?>" <?= $checked ?>>
                        <label class="form-check-label"><?= $row['Category'] ?></label>
                    </div>
                </div>
        <?php
            }
        }
        ?>
    </div>
    <button type="submit" class="btn btn-primary" style="margin-top: 15%;">Configure</button>
</form>
<div class="logo-container my-5">
        <img src="src/image/logo.png" alt="Logo">
    </div>
<div class="container">
   
    <main class="row" id="orders-container">
        <?php
        // Display orders fetched from the database
        $prevOrderId = null;
        foreach ($orders as $row) {
            $orderId = $row['orderID'];

            // Check if the orderID is different from the previous one
            if ($orderId !== $prevOrderId) {
                // If different, close the previous container and start a new one
                if ($prevOrderId !== null) {
                    echo '</ul>';
                    echo '<form method="post" action="" class="d-flex justify-content-center mt-3">';
                    echo '<input type="hidden" name="order_id" value="' . $prevOrderId . '">';
                    echo '<button type="submit" class="btn btn-success done-button" name="done">Done</button>';
                    echo '</form>';
                    echo '</div>';
                }

                // Start a new container for the current orderID
                echo '<div class="container-fluid" style="background-color: #B68D40; width: 250px; border-radius: 20px;">';
                echo '<h2>Order ID: ' . $orderId . '</h2>';
                echo '<p>Table No.' . $row['tableNo'] . '</p>';
                echo '<ul class="list-unstyled">';
            }

            // Display item details
            echo '<br>';
            echo '<li style="text-align: left; margin-bottom: 5px; color: black; margin-left: 20px;">' . $row['quantity']  .' '. $row['name'] . '</li>';
            foreach (explode(', ', $row['ingredient']) as $ingre) {
                // Split quantity and ingredient
                $parts = explode(':', $ingre, 2);

                if (count($parts) === 2) {
                    $quantity = trim($parts[1]);
                    $ingredient = trim($parts[0]);

                    // Check if the quantity exceeds 1
                    if ((int)$quantity > 1) {
                        echo '<li style="text-align: left; margin-left: 30px; color: black;">↳ ' . $quantity . ' ' . $ingredient . '</li>';
                    }
                }
            }

            // Update the previous orderID
            $prevOrderId = $orderId;
        }

        // Close the last container if there are orders
        if (!empty($orders)) {
            echo '</ul>';
            echo '<form method="post" action="" class="d-flex justify-content-center mt-3">';
            echo '<input type="hidden" name="order_id" value="' . $prevOrderId . '">';
            echo '<button type="submit" class="btn btn-success done-button" name="done" >Done</button>';
            echo '</form>';
            echo '</div>';
        }
        ?>
    </main>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/js/bootstrap.bundle.min.js"></script>


<!-- Add this script tag to include the JavaScript code for refreshing orders -->
<script>
   $(document).ready(function () {
            // Function to dynamically set the height based on content
            function setContainerHeight(containerId) {
                var container = $("#" + containerId);
                var contentHeight = container.find('.content-wrapper').outerHeight(); // Adjust the class name accordingly

                container.css('height', contentHeight + 'px');
            }

            // Call the function on document ready and when the window is resized
            setContainerHeight("<?php echo 'order-' . $orderId; ?>");
            $(window).resize(function () {
                setContainerHeight("<?php echo 'order-' . $orderId; ?>");
            });
        });

    // Function to refresh orders using AJAX
    function refreshOrders() {
        $.ajax({
            url: 'kitchenMonitor.php',  
            type: 'GET',
            success: function (response) {
                // Replace the existing orders with the updated ones
                $('#orders-container').html($(response).find('#orders-container').html());
            },
            error: function (error) {
                console.error('Error fetching orders:', error);
            }
        });
    }

    // Periodically refresh orders every 10 seconds (adjust as needed)
    setInterval(function () {
        refreshOrders();
    }, 500); // 10 seconds
</script>

</body>




</html>