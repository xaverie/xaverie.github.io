<?php

@include 'connection.php';
include 'transaction.php';

$message = array();

if(isset($_POST['update_update_btn'])){
   $update_value = $_POST['update_quantity'];
   $update_id = $_POST['update_quantity_id'];
   $update_quantity_query = mysqli_query($conn, "UPDATE `cashierCart` SET quantity = '$update_value' WHERE id = '$update_id'");
   if($update_quantity_query){
      header('location:cashier.php');
   }
}

if(isset($_GET['remove'])){
   $remove_id = $_GET['remove'];
   mysqli_query($conn, "DELETE FROM `cashierCart` WHERE id = '$remove_id'");
   header('location:cashier.php');
}

if(isset($_GET['delete_all'])){
   mysqli_query($conn, "DELETE FROM `cashierCart`");
   header('location:cashier.php');
}
if (isset($_POST['checkout'])) {
   mysqli_begin_transaction($conn);

   try {
       $select_cart = mysqli_query($conn, "SELECT * FROM `cashierCart`");

       if (mysqli_num_rows($select_cart) > 0) {
           $mainIngredientQuantities = [];

           while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
               $mainIngredient = $fetch_cart['mainIngredient'];
               $mainIngredientQuantity = $fetch_cart['quantity'];

               if (isset($mainIngredientQuantities[$mainIngredient])) {
                   $mainIngredientQuantities[$mainIngredient] += $mainIngredientQuantity;
               } else {
                   $mainIngredientQuantities[$mainIngredient] = $mainIngredientQuantity;
               }
           }
           $capturedImage = isset($_POST['captured_image']) ? $_POST['captured_image'] : '';
           $tableNumber = mysqli_real_escape_string($conn, $_POST['tableNumber']);

           foreach ($mainIngredientQuantities as $ingredient => $quantity) {
               mysqli_query($conn, "UPDATE `inventory` SET `Serving` = `Serving` - $quantity WHERE `IngridientName` = '$ingredient'");
               mysqli_query($conn, "INSERT INTO `orders` (`tableNo`, `order_details`, `price`,`MOP`,`paymentStatus`) SELECT '$tableNumber', GROUP_CONCAT(CONCAT(name, ': ', quantity) SEPARATOR ', '), SUM(price * quantity), Cash' ,'Processing'FROM `cart`");
            }
         
           $fetch_ingredient_query = mysqli_query($conn, "SELECT Ingredient FROM `cashierCart`");
           $fetch_ingredient = mysqli_fetch_assoc($fetch_ingredient_query);

           if ($fetch_ingredient) {
               $removedIngredients = [];
               $ingredientPairs = explode(', ', $fetch_ingredient['Ingredient']);
               foreach ($ingredientPairs as $pair) {
                   list($ingredient, $quantity) = explode(': ', $pair);
                   $removedIngredients[$ingredient] = (int)$quantity;
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
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sunny's Sandwich+ Coffee Cart</title>

   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="src/style/style.css">

</head>
<body>
<header class="header">
   <div class="flex">
      <a href="cashier.php" class="logo">Sunny's Sandwich+ Coffee</a>
      <?php
      $select_rows = mysqli_query($conn, "SELECT * FROM `cashierCart`") or die('query failed');
      $row_count = mysqli_num_rows($select_rows);
      ?>
      <a href="cashier.php" class="cart">cart <span><?php echo $row_count; ?></span> </a>
      <div id="menu-btn" class="fas fa-bars"></div>
   </div>
</header>
<div class="container">
<section class="shopping-cart">
<h1 class="heading">List of Orders</h1>
<h1 class="text-center">Table 1</h1>
<br>

   <table class="table table-hover text-center">
      <thead class="table" style="background-color:red; color: white;">
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
         $select_cart = mysqli_query($conn, "SELECT * FROM `cashierCart`");
         $grand_total = 0;
         $sub_total; 
         $tax_rate = 0.12; 
         $tax_amount = 0; 

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
               <a href="cashier.php?remove=<?php echo $fetch_cart['id']; ?>" onclick="return confirm('remove item from cart?')" class="delete-btn"> <i class="fas fa-trash" style="text-decoration:none"></i>remove</a>
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
                                    <div class="col-auto"> 
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
            
            $tax_amount = $grand_total * $tax_rate;
               };
            };
            ?>
            <tr class="table-bottom">
               <td><a href="cashier.php" class="option-btn" style="margin-top: 0;">back to menu</a></td>
               <td colspan="4">Sub Total of all products</td>
               <td>₱<?php echo  number_format($grand_total, 2, '.', ','); ?></td>
               <td></td>

            </tr>
            <tr class="table-bottom">
               <td></td>
               <td colspan="4">Tax 12%</td>
               <td>₱<?php echo  number_format($tax_amount, 2, '.', ','); ?></td>
               <td></td>
            </tr>
            <tr class="table-bottom">
               <td></td>
               <td colspan="4">Total Amount</td>
               <td>₱<?php echo  number_format($grand_total, 2, '.', ','); ?></td>
               <td><a href="cashier.php?delete_all" onclick="return confirm('are you sure you want to delete all?');" class="delete-btn"> <i class="fas fa-trash"></i> Remove all </a></td>
            </tr>
      </tbody>
   </table>
            <div class="checkout-btn d-flex justify-content-center align-items-center">
               <button type="button" data-bs-toggle="modal" data-bs-target="#PaymentModal"   class="btn <?= ($grand_total > 1)?'':'disabled'; ?>" style = "width:150px; margin:">Check Out</button>
               <a href="cashier.php" class="btn" style="height:75px ;width: 150px; margin-left: 30px; background-color: red; text-align: center; text-decoration: none; color: white;">Menu</a>

            </div>
            <div class="modal" id="PaymentModal">
            <div class="modal-dialog modal-dialog-centered" id="customModalDialog">
            <div class="modal-content" style="width: 35vw;">
            <div class="text-center mb-4">
                <br>
                <h3>Payment Method</h3>
            </div>

            <div class="container-xl d-flex justify-content-center">
                <form method="post" style="width: 70vw; min-width: 300px;">
                    <div class="row mb-4">
                        <div class="col">
                            <h1 style="margin-bottom: 10px;">Bill: ₱<?php echo number_format($grand_total, 2, '.', ','); ?></h1>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label" style="font-size: 18px; margin-bottom: 5px;">Enter Amount:</label>
                            <input type="text" class="form-control" id="amount" name="amount" oninput="calculateChange()" style="height: 40px; font-size: 16px;" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="tableNumber" style="font-size: 18px; margin-bottom: 5px;">Table Number:</label>
                            <input type="text" class="form-control" id="tableNumber" name="tableNumber" style="height: 40px; font-size: 16px;" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col d-flex align-items-center">
                            <input type="checkbox" id="idCheckbox" onclick="showIdInput()" style="font-size: 20px; width: 20px; height: 20px; margin-right: 10px;" >
                            <label for="idCheckbox" style="font-size: 18px;">Include ID Number</label>
                        </div>
                    </div>

            
                    <div class="row mb-3" id="idInputRow" style="display: none;">
                        <div class="col">
                            <label for="idNumber" style="font-size: 18px; margin-bottom: 5px;">ID Number:</label>
                            <input type="text" class="form-control" id="idNumber" name="idNumber" style="height: 40px; font-size: 16px;" required>
                        </div>
                    </div>

                    
                     <div class="row mb-3" id="discountedBillRow" style="display: none;">
                        <div class="col">
                           <h4 style="margin-bottom: 10px;">Discounted Bill: ₱<span id="discountedBill">0.00</span></h4>
                        </div>
                     </div>

                   
                    <div class="row mb-3">
                        <div class="col">
                            <h4 style="margin-bottom: 10px;">Change: ₱<span id="change">0.00</span></h4>
                        </div>
                    </div>

                    
                    <div class="row mt-3">
                        <div class="col">
                           <button type="submit" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>" onclick="checkout()" name="checkout" style="width:150px; margin-top:5%;">Check Out</button>
                        <br>
                        </div>
                     </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</html>