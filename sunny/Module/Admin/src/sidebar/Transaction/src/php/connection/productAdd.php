<?php
include "connection.php";


if(isset($_POST['update_product'])){
    $id = $_GET['edit'];

   $product_name = $_POST['product_name'];
   $product_price = $_POST['product_price'];
   $product_image = $_FILES['product_image']['name'];
   $product_image_tmp_name = $_FILES['product_image']['tmp_name'];
   $product_image_folder = 'src/uploaded_img/'.$product_image;

   if(empty($product_name) || empty($product_price) || empty($product_image)){
      $message[] = 'please fill out all!';    
   }else{

      $update_data = "UPDATE menu SET name='$product_name', price='$product_price', image='$product_image'  WHERE id = '$id'";
      $upload = mysqli_query($conn, $update_data);

      if($upload){
         move_uploaded_file($product_image_tmp_name, $product_image_folder);
         header('location:http://localhost/sunny/Module/Admin/src/sidebar/Product/productDashboard.php');
         exit;
      }else{
         $$message[] = 'please fill out all!'; 
      }

   }
}; 
?>