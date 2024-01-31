<?php

@include 'connection.php';

if(isset($_POST['update_update_btn'])){
   $update_value = $_POST['update_quantity'];
   $update_id = $_POST['update_quantity_id'];
   $update_quantity_query = mysqli_query($conn, "UPDATE `cart` SET quantity = '$update_value' WHERE id = '$update_id'");
   if($update_quantity_query){
      header('location:cart.php');
   }
}

if(isset($_GET['remove'])){
   $remove_id = $_GET['remove'];
   mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$remove_id'");
   header('location:cart.php');
}

if(isset($_GET['delete_all'])){
   // Clear the cart
   mysqli_query($conn, "DELETE FROM `cart`");
   header('location:cart.php');
}
if (isset($_POST['checkout'])) {
   // Start a transaction
   mysqli_begin_transaction($conn);

   try {
       $select_cart = mysqli_query($conn, "SELECT * FROM `cart`");

       if (mysqli_num_rows($select_cart) > 0) {
           $updateClauses = [];
           $allIngredients = []; // Initialize an array to store all ingredients

           while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
               // Get the list of ingredients and their quantities
               $ingredients = explode(', ', $fetch_cart['ingredient']);
               $quantities = explode(', ', $fetch_cart['quantity']);

               // Combine ingredients from all carts
               $allIngredients = array_merge($allIngredients, $ingredients);

               // Loop through each ingredient and prepare an update clause
               for ($i = 0; $i < count($ingredients); $i++) {
                   $ingredient = isset($ingredients[$i]) ? $ingredients[$i] : '';
                   $quantity = isset($quantities[$i]) ? $quantities[$i] : '';

                   if ($ingredient !== '' && $quantity !== '') {
                       // Prepare the update clause
                       $updateClauses[] = "`Stock` = `Stock` - $quantity";
                   }
               }
           }

           // Implode the update clauses to form a single update query
           $updateQuery = "UPDATE `inventory` SET " . implode(', ', $updateClauses) . " WHERE `IngridientName` IN ('" . implode("', '", $allIngredients) . "')";

           // Execute the update query
           $result = mysqli_query($conn, $updateQuery);

           // Check for errors in the query execution
           if (!$result) {
               throw new Exception("Error updating inventory");
           }

           // Move cart data to the 'orders' table
           mysqli_query($conn, "INSERT INTO `orders` (user_id, order_details, price) SELECT '1', GROUP_CONCAT(CONCAT(name, ': ', quantity) SEPARATOR ', '), SUM(price * quantity) FROM `cart`");

           // Clear the cart
           mysqli_query($conn, "DELETE FROM `cart`");

           // Commit the transaction if everything is successful
           mysqli_commit($conn);

           header('location: cart.php');
       }
   } catch (Exception $e) {
       // An error occurred, rollback the transaction
       mysqli_rollback($conn);

       // You can log the error or display a user-friendly message
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
   <title>Sunny's Sandwich+ Coffee Cart</title>

   <!-- Bootstrap -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


   

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
<header class="header">
   <div class="flex">
      <a href="#" class="logo">Sunny's Sandwich+ Coffee</a>
      <?php
      $select_rows = mysqli_query($conn, "SELECT * FROM `cart`") or die('query failed');
      $row_count = mysqli_num_rows($select_rows);
      ?>
      <a href="cart.php" class="cart">cart <span><?php echo $row_count; ?></span> </a>
      <div id="menu-btn" class="fas fa-bars"></div>
   </div>
</header>
<div class="container">

<section class="shopping-cart">
   <h1 class="heading">List of Orders</h1>
   <table class="table table-hover text-center">
      <thead class="table" style="background-color:#3C91E6; color: white;">
         <th>image</th>
         <th>name</th>
         <th>ingredient</th>
         <th>price</th>
         <th>quantity</th>
         <th>total price</th>
         <th>Action</th>
      </thead>
      <tbody>
         <?php 
         $select_cart = mysqli_query($conn, "SELECT * FROM `cart`");
         $grand_total = 0;
         $sub_total; 
         if(mysqli_num_rows($select_cart) > 0){
            while($fetch_cart = mysqli_fetch_assoc($select_cart)){
         ?>
         <tr>
            <td><img src="<?php echo $fetch_cart['image']; ?>" alt="" height="100px" width="100px" style="border-radius: 5px;"></td>
            <td><?php echo $fetch_cart['name']; ?></td>
            <td><ul style="list-style-type: none;">
            
               <?php 
                     $ingredient = explode(', ', $fetch_cart['ingredient']);
                     foreach ($ingredient as $ingredients) {
                        echo '<li>' . $ingredients . ' </li>';
                     }
               ?>
            </ul></td>
            

            <td>₱<?php echo number_format($fetch_cart['price'], 2, '.', ','); ?></td>
            <td>
               <form action="" method="post">
                  <input type="hidden" name="update_quantity_id"  value="<?php echo $fetch_cart['id']; ?>" >
                  <label><?php echo $fetch_cart['quantity']; ?></label>
               </form>   
            </td>
               <?php $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];?>
               <td>₱<?php echo number_format($sub_total, 2, '.', ','); ?></td>
            <td>
               <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#myModalEdit<?php echo $fetch_cart['id']; ?>">Edit</button>
               <a href="cart.php?remove=<?php echo $fetch_cart['id']; ?>" onclick="return confirm('remove item from cart?')" class="delete-btn"> <i class="fas fa-trash" style="text-decoration:none"></i>remove</a>
                  <div class="modal" id="myModalEdit<?php echo $fetch_cart['id']; ?>">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Customize Your Order</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                        <div class="modal-body" style="font-size: 18px">
                              <!-- Edit quantity form -->
                              <form action="" method="post">
                                 <div class="row mb-4">
                                    <div class="col-auto"> <!-- Use col-auto to make the column size as small as possible -->
                                       <img src="<?php echo $fetch_cart['image']; ?>" alt="<?php echo $fetch_cart['name']; ?>" height="100px" width="100px" style="border-radius: 10px;">
                                       <label class="form-label"><?php echo $fetch_cart['name']; ?></label>
                                 </div>
                                 <div class="row mb-4">
                                    <div class="col-auto">
                                       <label for="Price">Price:</label>
                                       <label class="form-label"><?php echo $fetch_cart['price']; ?></label>
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
               };
            };
            ?>
            <tr class="table-bottom">
               <td><a href="products.php" class="option-btn" style="margin-top: 0;">back to menu</a></td>
               <td colspan="4">total of all products</td>
               <td>₱<?php echo  number_format($grand_total, 2, '.', ','); ?></td>
               <td><a href="cart.php?delete_all" onclick="return confirm('are you sure you want to delete all?');" class="delete-btn"> <i class="fas fa-trash"></i> Remove all </a></td>
            </tr>
            
      </tbody>
   </table>
   <div class="checkout-btn d-flex justify-content-center align-items-center">
      <button type="button" data-bs-toggle="modal" data-bs-target="#PaymentModal"   class="btn <?= ($grand_total > 1)?'':'disabled'; ?>" style = "width:150px; margin:">Check Out</button>
   </div>
   <div class="modal" id="PaymentModal">
      <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content" style ="width:35vw; height:15vw;">
            	<div class="text-center mb-4">
               <br>
                <h3>Payment Method</h3>
                <h5 class="text-muted">How would like to pay?</h5>
            </div>

            <div class="container-xl d-flex justify-content-center">
                <form method="post" style="width:70vw; min-width:300px;">
                     <div class = "row">
                       <div class="col">
                       <a href="EwalletModal" class="btn btn-success" name="add" style="background-color: #3C91E6; border: none" data-bs-toggle="modal" data-bs-target="#EwalletModal">E-Wallet</a>
                           <a href="http://localhost/cashier/cash/cash.php" class="btn btn-danger" name="add" style="background-color:#3C91E6; border:none">Cash</a>
                        </div>
                    </div>
                </form>
            </div>
         </div>

   <div class="modal" id="EwalletModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="width: 35vw; height: 18vw;">
            <div class="text-center mb-4">
               <br>
                <h3>E-Wallet</h3>
                <h5 class="text-muted">Availability Options:</h5>
            </div>

            <div class="container-xl d-flex justify-content-center">
               <form method="post" style="width:70vw; min-width:150px;">
                    <div class="row">
                    <div class="col">
                        <a href="http://localhost/cashier/camera.php" class="btn btn-success" name="add" style="background-color: #3C91E6; border: none">PWD/Senior Citizen</a>
                        <a href="http://localhost/cashier/qrcode/generateqr.php" class="btn btn-success" name="ewalletChoice2" style="background-color: #3C91E6; border: none" data-bs-toggle="modal" data-bs-target="#EwalletQRModal">Regular</a>
                        <button class="btn btn-primary" onclick="backToPaymentModal()" style="background-color: #007BFF; border: none">
                           <i class="fa fa-arrow-left"></i>Back
                        </button>
                     </div>
                    </div>
                </form>
            </div>
         </div>
      </div>
    </div>
</div>
</section>

</div>
<div class="modal fade" id="EwalletQRModal" tabindex="-1" aria-labelledby="EwalletQRModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="EwalletQRModalLabel">E-Wallet QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Here, display the QR code image -->
                <img id="checkoutQRCode" src="qrcode/generateqr.php" alt="Checkout QR Code" width="200" height="200">
                <form action="" method="post">
                  <button type="submit" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" name="checkout" style="width:150px; margin">Check Out</button>
                </form>   

            </div>
        </div>
    </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js">
</script><script src="js/script.js"></script>
</body>
</html>