<?php
@include 'connection.php';

$sqlPreparing = "SELECT order_id, tableNo FROM orders WHERE queueStatus = 'Preparing'";
$resultPreparing = mysqli_query($conn, $sqlPreparing);
$dataPreparing = mysqli_fetch_all($resultPreparing, MYSQLI_ASSOC);

$sqlServing = "SELECT order_id, tableNo FROM orders WHERE queueStatus = 'Serving'";
$resultServing = mysqli_query($conn, $sqlServing);
$dataServing = mysqli_fetch_all($resultServing, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screensaver</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            overflow: hidden;
        }

        #fullscreenOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #textTable {
            border-spacing: 10px;
            border: 2px solid black;

        }

        #leftText,
        #rightText {
            font-size: 24px;
            border: 1px solid white;
            padding: 10px;
            border-radius: 5px;
        }

        #fullscreenImage {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
        }

        @media (max-width: 768px) {
            .container {
                margin-bottom: 90px;
            }

            #fullscreenOverlay {
                margin-bottom: 10px;
                flex-direction: column;
                /* Change to a column layout for smaller screens */
                text-align: center;
                /* Center-align text in the column layout */
            }

            #textTable {
                border-spacing: 5px;
                /* Adjust spacing for smaller screens */
            }

            #leftText,
            #rightText {
                font-size: 15px;
                /* Adjust font size for smaller screens */
            }

            #fullscreenImage {
                max-width: 20%;
                max-height: 15%;
                width: auto;
                height: auto;
            }


        }
    </style>
</head>

<body>
    <!-- <a href="products.php"> -->
    <div id="fullscreenOverlay">
        <img id="fullscreenImage" src="uploaded_img/logo.png" alt="Fullscreen Image">
        <div class="container mt-5">
            <div class="container mt-5">
                <h2>Order Status</h2>

                <!-- Row to contain both tables -->
                <main class="row" id="orders-container">

                    <div class="row">

                        <!-- Table for 'Preparing' status -->
                        <div class="col">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">Preparing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dataPreparing as $row): ?>
                                        <tr>
                                            <td>
                                                <?= $row['order_id']; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="col">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">Serving</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dataServing as $row): ?>
                                        <tr>
                                            <td>
                                                <?= $row['order_id']; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="container mt-3">
                            <a href="http://localhost/Cashier/products.php" class="btn btn-primary">Go Back to Menu</a>
                            <button type="button" class="btn btn-success"
                                onclick="completeOrder(<?php echo $dataServing[0]['order_id']; ?>)">Complete
                                Order</button>

                            <form id="completeForm" action="complete_order.php" method="post">
                                <input type="hidden" name="order_id" value="<?php echo $dataServing[0]['order_id']; ?>">
                            </form>

                        </div>
                </main>
            </div>
        </div>
    </div>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function redirectToProducts() {
        }

        function completeOrder(orderId) {
            $.ajax({
                type: 'POST',
                url: 'complete_order.php',
                data: { order_id: orderId },
                success: function (response) {
                    console.log(response);

                    refreshOrders();
                },
                error: function (error) {
                    console.error('Error completing order:', error);
                }
            });
        }

        $(document).ready(function () {
            function refreshOrders() {
                $.ajax({
                    url: 'screensaver.php',
                    type: 'GET',
                    success: function (response) {
                        $('#orders-container').html($(response).find('#orders-container').html());
                    },
                    error: function (error) {
                        console.error('Error fetching orders:', error);
                    }
                });
            }

            $('#completeForm button').on('click', function () {
                var orderId = $('#completeForm input[name="order_id"]').val();
                completeOrder(orderId);
            });
            setInterval(function () {
                refreshOrders();
            }, 500);
        });
    </script>
</body>

</html>