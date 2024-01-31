<?php
@include 'connection.php';
include 'transaction.php';

$message = array();
function updateInventory($conn, $ingredientQuantities)
{
   $ingredientNames = array_keys($ingredientQuantities);

   foreach ($ingredientNames as $ingredient) {
      $quantity = $ingredientQuantities[$ingredient];

      $safeIngredient = mysqli_real_escape_string($conn, $ingredient);

      $fetchServing = mysqli_query($conn, "SELECT `Serving` FROM `inventory` WHERE `IngridientName` = '$safeIngredient'");
      $row = mysqli_fetch_assoc($fetchServing);

      $Serving = isset($row['Serving']) ? $row['Serving'] : 0;

      if ($quantity <= $Serving) {
         mysqli_query($conn, "UPDATE `inventory` SET `Serving` = `Serving` - $quantity WHERE `IngridientName` = '$safeIngredient'");
      } else {
         echo "Insufficient inventory for ingredient: $safeIngredient";
      }
   }
}


if (isset($_POST['update_update_btn'])) {
   $update_value = $_POST['update_quantity'];
   $update_id = $_POST['update_quantity_id'];
   $update_quantity_query = mysqli_query($conn, "UPDATE `cart` SET quantity = '$update_value' WHERE id = '$update_id'");
   if ($update_quantity_query) {
      header('location:cart.php');
   }
}


if (isset($_GET['remove'])) {
   $remove_id = $_GET['remove'];
   mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$remove_id'");
   header('location:cart.php');
}

if (isset($_GET['delete_all'])) {
   mysqli_query($conn, "DELETE FROM `cart` Where orderStatus = 'Placed'");
   header('location:cart.php');
}
if (isset($_POST['checkout'])) {
   mysqli_begin_transaction($conn);

   try {
      $select_cart = mysqli_query($conn, "SELECT * FROM `cart`");

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
         mysqli_query($conn, "INSERT INTO `orders` (`tableNo`, `order_details`, `price`, `MOP`, `paymentStatus`, `ingredient`)
               SELECT '1', GROUP_CONCAT(CONCAT(name, ': ', quantity, ' ', category ) SEPARATOR ', '), SUM(price * quantity), 'Cash', 'Processing', GROUP_CONCAT(CONCAT(ingredient, ': ', quantity) SEPARATOR ', ')
               FROM `cart`");

         $orderID = mysqli_insert_id($conn);

         mysqli_data_seek($select_cart, 0); 
         while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
            $name = $fetch_cart['name'];
            $quantity = $fetch_cart['quantity'];
            $category = $fetch_cart['category'];
            $ingredient = $fetch_cart['ingredient'];
            $tableNo = '1';

            mysqli_query($conn, "INSERT INTO `ordersdetails` (orderID, name, quantity, category, Ingredient, tableNo,status,queueStatus,productStatus) 
               VALUES ('$orderID', '$name', '$quantity', '$category', '$ingredient', '$tableNumber','Paid','Preparing','Preparing')");
         }

         $fetch_ingredient_query = mysqli_query($conn, "SELECT Ingredient FROM `cart`");
         $fetch_ingredient = mysqli_fetch_assoc($fetch_ingredient_query);

         mysqli_commit($conn);
      }
   } catch (Exception $e) {
      mysqli_rollback($conn);

      echo "Checkout failed: " . $e->getMessage();
   }
   header('Location: cashier.php');
   exit();
}
if (isset($_POST['Ewallet'])) {
   mysqli_begin_transaction($conn);

   try {
      $select_cart = mysqli_query($conn, "SELECT * FROM `cart`");

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
         $totalpriceaddons = $fetch_cart['totalpriceaddons'];

         mysqli_query($conn, "INSERT INTO `orders` (`tableNo`, `order_details`, `price`, `MOP`, `paymentStatus`, `ingredient`)
                  SELECT '1', GROUP_CONCAT(CONCAT(name, ': ', quantity, ' ', category) SEPARATOR ', '), SUM(price * quantity +$totalpriceaddons), 'EWallet', 'Processing', GROUP_CONCAT(CONCAT(ingredient, ': ', quantity) SEPARATOR ', ')
                  FROM `cart`");

         $orderID = mysqli_insert_id($conn);

         foreach ($cartData as $fetch_cart) {
            $name = $fetch_cart['name'];
            $quantity = $fetch_cart['quantity'];
            $category = $fetch_cart['category'];
            $ingredient = $fetch_cart['ingredient'];
            $tableNo = '1';

            mysqli_query($conn, "INSERT INTO `ordersdetails` (orderID, name, quantity, category, Ingredient, tableNo, status)
                      VALUES ('$orderID', '$name', '$quantity', '$category', '$ingredient', '$tableNo', 'Processing')");
         }
         $fetch_ingredient_query = mysqli_query($conn, "SELECT Ingredient FROM `cart`");
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

         mysqli_query($conn, "DELETE FROM `cart`");

         header('Location: screensaver.php');
         exit();
      }
   } catch (Exception $e) {
      mysqli_rollback($conn);

      echo "Checkout failed: " . $e->getMessage();
   }
}

if (isset($_POST['cash'])) {
   mysqli_begin_transaction($conn);

   try {
      $select_cart = mysqli_query($conn, "SELECT * FROM `cart`");

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
         $totalpriceaddons = $fetch_cart['totalpriceaddons'];

         mysqli_query($conn, "INSERT INTO `orders` (`tableNo`, `order_details`, `price`, `MOP`, `paymentStatus`, `ingredient`)
                  SELECT '1', GROUP_CONCAT(CONCAT(name, ': ', quantity, ' ', category) SEPARATOR ', '), SUM(price * quantity +  $totalpriceaddons), 'Cash', 'Processing', GROUP_CONCAT(CONCAT(ingredient, ': ', quantity) SEPARATOR ', ')
                  FROM `cart`");

         $orderID = mysqli_insert_id($conn);

         foreach ($cartData as $fetch_cart) {
            $name = $fetch_cart['name'];
            $quantity = $fetch_cart['quantity'];
            $category = $fetch_cart['category'];
            $ingredient = $fetch_cart['ingredient'];
            $tableNo = '1';

            mysqli_query($conn, "INSERT INTO `ordersdetails` (orderID, name, quantity, category, Ingredient, tableNo, status)
                      VALUES ('$orderID', '$name', '$quantity', '$category', '$ingredient', '$tableNo', 'Processing')");
         }

         $fetch_ingredient_query = mysqli_query($conn, "SELECT Ingredient FROM `cart`");
         $fetch_ingredient = mysqli_fetch_assoc($fetch_ingredient_query);

         mysqli_commit($conn);

         mysqli_query($conn, "DELETE FROM `cart`");

         header('Location: screensaver.php');
         exit();
      }
   } catch (Exception $e) {
      mysqli_rollback($conn);

      echo "Checkout failed: " . $e->getMessage();
   }
}


if (isset($_POST['discount'])) {
   mysqli_begin_transaction($conn);

   try {
      $select_cart = mysqli_query($conn, "SELECT * FROM `cart`");

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
         $totalpriceaddons = $fetch_cart['totalpriceaddons'];

         mysqli_query($conn, "INSERT INTO `orders` (`tableNo`, `order_details`, `price`, `MOP`, `paymentStatus`, `ingredient`)
               SELECT '1', GROUP_CONCAT(CONCAT(name, ': ', quantity, ' ', category) SEPARATOR ', '), SUM(price * quantity +$totalpriceaddons ), 'Discount', 'Processing', GROUP_CONCAT(CONCAT(ingredient, ': ', quantity) SEPARATOR ', ')
               FROM `cart`");

         $orderID = mysqli_insert_id($conn);

         foreach ($cartData as $fetch_cart) {
            $name = $fetch_cart['name'];
            $quantity = $fetch_cart['quantity'];
            $category = $fetch_cart['category'];
            $ingredient = $fetch_cart['ingredient'];
            $tableNo = '1';

            mysqli_query($conn, "INSERT INTO `ordersdetails` (orderID, name, quantity, category, Ingredient, tableNo, status)
                   VALUES ('$orderID', '$name', '$quantity', '$category', '$ingredient', '$tableNo', 'Processing')");
         }

         $fetch_ingredient_query = mysqli_query($conn, "SELECT Ingredient FROM `cart`");
         $fetch_ingredient = mysqli_fetch_assoc($fetch_ingredient_query);

         mysqli_commit($conn);

         mysqli_query($conn, "DELETE FROM `cart`");

         header('Location: screensaver.php');
         exit();
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
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sunny's Sandwich + Coffee Cart</title>

   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
   <link rel="stylesheet" href="css/style1.css">
</head>

<body>
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js">
   </script>
   <script src="js/script.js"></script>

   <div class="container">
      <section class="shopping-cart">
         <h1 class="heading">List of Orders</h1>
         <h1 class="text-center">Table 1</h1>
         <br>

         <div class="table-container" style="max-height: 400px; overflow-y: auto; ">

            <table class="table table-hover text-center">
               <thead class="table" style="background-color:red; color: white;">
                  <th>image</th>
                  <th>name</th>
                  <th>ingredient</th>
                  <th>quantity</th>
                  <th>total price</th>
                  <th>Action</th>
               </thead>
               <tbody>
                  <?php
                  $select_cart = mysqli_query($conn, "SELECT * FROM `cart` where orderStatus = 'Placed'");
                  $grand_total = 0;
                  $sub_total;
                  $tax_rate = 0.12; 
                  $tax_amount = 0; 
                  
                  if (mysqli_num_rows($select_cart) > 0) {
                     while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
                        ?>

                        <tr>
                           <td><img src="<?php echo $fetch_cart['image']; ?>" alt="" height="100px" width="100px"
                                 style="border-radius:20px; border: 1px solid black;"></td>
                           <td>
                              <?php echo $fetch_cart['name']; ?>
                           </td>
                           <td>
                              <ul style="list-style-type: none;">
                                 <?php
                                 $ingredientPairs = explode(', ', $fetch_cart['ingredient']);
                                 foreach ($ingredientPairs as $pair) {
                                    list($ingredient, $quantity) = explode(': ', $pair);
                                    echo '<li>' . $ingredient . ': ' . $quantity . '</li>';
                                 }
                                 ?>
                              </ul>
                           </td>

                           <td style="display:none;">
                              <?php echo $fetch_cart['category']; ?>
                           </td>

                           <td>
                              <form action="" method="post">
                                 <input type="hidden" name="update_quantity_id" value="<?php echo $fetch_cart['id']; ?>">
                                 <label>
                                    <?php echo $fetch_cart['quantity']; ?>
                                 </label>
                              </form>
                           </td>
                           <?php $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'] + $fetch_cart['totalpriceaddons']; ?>
                           <td>₱
                              <?php echo number_format($sub_total, 2, '.', ','); ?>
                           </td>

                           <td>
                              <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModalEdit<?php echo $fetch_cart['id']; ?>" style="background-color: transparent; border: none;"><i class="fas fa-pencil" style="color: black;"></i></button> -->
                              <a href="cart.php?remove=<?php echo $fetch_cart['id']; ?>"
                                 onclick="return confirm('Remove item from cart?')"
                                 style="color: red; width: 70px; height: 70px; text-decoration: none; display: inline-block; font-size: 30px; padding: 10px;">
                                 <i class="fas fa-trash"></i>
                              </a><!-- 
                     <div class="modal" id="myModalEdit<?php echo $fetch_cart['id']; ?>">
                           <div class="modal-dialog">
                              <div class="modal-content">
                                 <div class="modal-header">
                                    <h5 class="modal-title">Customize Your Order</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                 </div>

                                 <div class="modal-body" style="font-size: 18px">
                                 <form action="" method="post">
                                    <div class="row mb-4">
                                       <div class="col-auto"> <
                                          <img src="<?php echo $fetch_cart['image']; ?>" alt="<?php echo $fetch_cart['name']; ?>" height="100px" width="100px" style="border-radius: 10px;">
                                          <label class="form-label"><?php echo $fetch_cart['name']; ?></label>
                                    </div>

                                    <div class="row mb-4">
                                       <div class="col-auto">
                                          <label for="Price">Price:</label>
                                          <label class="form-label"><?php echo $fetch_cart['price']; ?></label>
                                       </div>
                                    </div>
                                    
                        <div class="row mb-4">
                           <div class="col-auto">
                              <label for="Customize"><a href="#" id="customize-toggle<?php echo $fetch_cart['id']; ?>" style="text-decoration:none; color:black;">Customize: ▼</a></label>
                           </div>

                           <div class="form-group customize-section" id="customize-section<?php echo $fetch_cart['id']; ?>" style="display: none;">
                           
                              <?php
                              $ingredients = explode(', ', $fetch_cart['ingredient']);
                              foreach ($ingredients as $ingredient) {
                                 if (isset($ingredientPrices[$ingredient])) {
                                    $price = $ingredientPrices[$ingredient];

                                    echo '<div class="ingredient-row">';
                                    echo '<label class="ingredient-label" for="ingredient_' . $ingredient . '">' . $ingredient . '</label>';
                                    echo '<span class="ingredient-price">₱' . number_format($price, 2) . '</span>';
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
                                    echo '<input type="number" id="ingredient_' . $ingredient . '" name="ingredient_' . $ingredient . '" value="1" min="1" max="10">';
                                    echo '<button type="button" class="quantity-btn plus">+</button>';
                                    echo '</div>';
                                    echo '</div>';
                                 }
                              }

                              ?>
                           </div>
                     </div> -->


               </div>
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
   <tr class="table-bottom">
      <td colspan="4">Sub Total of all products</td>
      <td>₱
         <?php echo number_format($grand_total, 2, '.', ','); ?>
      </td>
      <td><a href="cart.php?delete_all" onclick="return confirm('are you sure you want to delete all?');"
            class="delete-btn"> <i class="fas fa-trash"></i> Remove all </a></td>

   </tr>
   <!-- <tr class="table-bottom">
                  <td></td>
                  <td colspan="4">Tax 12%</td>
                  <td>₱<?php echo number_format($tax_amount, 2, '.', ','); ?></td>
                  <td></td>
               </tr> -->
   <!-- <tr class="table-bottom">
                  <td></td>
                  <td colspan="4">Total Amount</td>
                  <td>₱<?php echo number_format($grand_total, 2, '.', ','); ?></td>
                  <td><a href="cart.php?delete_all" onclick="return confirm('are you sure you want to delete all?');" class="delete-btn"> <i class="fas fa-trash"></i> Remove all </a></td>
               </tr> -->
   </tbody>
   </table>
   </div>

   <div class="checkout-btn d-flex justify-content-center align-items-center">
      <button type="button" data-bs-toggle="modal" data-bs-target="#PaymentModal"
         class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>"
         style="width: 150px; height: 75px; margin-left: 5px; text-align: center; display: flex; align-items: center; justify-content: center; border-radius:20px; border: 1px solid black;">Check Out</button>
      <button onclick="window.location.href='products.php'" class="btn"
         style="width: 150px; height: 75px; margin-left: 5px; text-align: center; display: flex; align-items: center; justify-content: center; border-radius:20px; border: 1px solid black;">
         Back to Menu
      </button>
      <button onclick="window.location.href='screensaver.php'" class="btn"
         style="width: 150px; height: 75px; margin-left: 5px; text-align: center; display: flex; align-items: center; justify-content: center; border-radius:20px; border: 1px solid black;">
         View Order Status
      </button>

   </div>
   <div class="modal" id="PaymentModal">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content" style="width: 100%; height: 15vw; border-radius:20px; border: 1px solid black;">
            <div class="modal-header">
               <h5 class="modal-title" id="EwalletQRModalLabel">Payment</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="text-center mb-4">
               <br>
               <h3>Payment Method</h3>
               <h5 class="text-muted">How would like to pay?</h5>
            </div>

            <div class="container-xl d-flex justify-content-center">
               <form method="post" style="width:70vw; min-width:300px;">
                  <div class="row">
                     <div class="col">
                        <div class="checkout-btn d-flex justify-content-center align-items-center">
                           <a href="EwalletModal" class="btn btn-success payment-method-btn text" name="add"
                              style="background-color: #3C91E6; width:150px; margin-right: 10px; text-align: center; border-radius:20px; border: 1px solid black;"
                              data-bs-toggle="modal" data-bs-target="#EwalletQRModal">E-Wallet</a>
                           <button type="button"
                              class="btn btn-success payment-method-btn <?= ($grand_total > 1) ? '' : 'disabled'; ?> mx-auto"
                              data-bs-toggle="modal" data-bs-target="#cashmodal" name="cash"
                              style="background-color: #3C91E6; width:150px; border-radius:20px; border: 1px solid black;">Cash</button>
                           <a href="http://localhost/cashier/camera.php" class="btn btn-success payment-method-btn "
                              name="add"
                              style="background-color: #3C91E6; width:150px; height:50px; margin-right: 10px;margin-left: 10px;font-size:12px; border-radius:20px; border: 1px solid black;"
                              data-bs-toggle="modal" data-bs-target="#discount">PWD/Senior Citizen</a>

                        </div>
                     </div>
                  </div>
               </form>
            </div>
         </div>
      </div>
   </div>

   <div class="modal" id="cashmodal">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content" style="border-radius:20px; border: 1px solid black;">
            <div class="modal-header">
               <h5 class="modal-title" id="EwalletQRModalLabel">Cash Payment</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="text-center mb-4">
               <br>
               <h2>Thank You!</h2>
               <h3 class="text-muted">Please Proceed to the cashier for payment please state your table number and order
                  id.</h3>
               <?php
               if (isset($_POST['cash'])) {
                  $orderID = $_POST['orderID'];
                  echo '<p>Order ID: ' . $orderID . '</p>';
               }
               ?>

               <form method="post">
                  <button type="submit" class="btn btn-success <?= ($grand_total > 1) ? '' : 'disabled'; ?> mx-auto"
                     data-bs-toggle="modal" data-bs-target="#thankYouModal" name="cash"
                     style="width: 150px; border-radius:20px; border: 1px solid black;">Proceed</button>
               </form>
            </div>
         </div>
      </div>
   </div>
   </section>
   <div class="modal fade" id="EwalletQRModal" tabindex="-1" aria-labelledby="EwalletQRModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content" style="border-radius:20px; border: 1px solid black;">
            <div class="modal-header">
               <h5 class="modal-title" id="EwalletQRModalLabel">E-Wallet QR Code</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
               <img id="checkoutQRCode" src="qrcode/generateqr.php" alt="Checkout QR Code" width="200" height="200">
               <form action="" method="post">
                  <button type="submit" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?> mx-auto" name="Ewallet"
                     style="width: 150px; border-radius:20px; border: 1px solid black;">Check Out</button>
               </form>

            </div>
         </div>
      </div>
   </div>

   <div class="modal fade" id="discount" tabindex="-1" aria-labelledby="discountLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content" style="border-radius:20px; border: 1px solid black;">
            <div class="modal-header">
               <h5 class="modal-title" id="discountLabel">PWD/Senior Discount</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="text-center mb-4">
               <br>
               <h2>Thank You!</h2>
               <h3 class="text-muted">Please Proceed to the cashier for payment, please state your table number and
                  order id and present the ID to our cashier.</h3>
               <form method="post">
                  <button type="submit" class="btn btn-success <?= ($grand_total > 1) ? '' : 'disabled'; ?> mx-auto"
                     data-bs-toggle="modal" data-bs-target="#thankYouModal" name="discount"
                     style="width: 150px; border-radius:20px; border: 1px solid black;">Proceed</button>
               </form>
            </div>
         </div>
      </div>
   </div>
</body>

</html>
<script>
   $(document).ready(function () {
      $('.payment-method-btn').on('click', function () {
         $('#PaymentModal').modal('hide');
      });
   });
</script>