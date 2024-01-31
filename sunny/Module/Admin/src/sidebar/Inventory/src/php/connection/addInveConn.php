<?php
include "connection.php";


if (isset($_POST["submit"])) {
  $ID = $_POST['ID'];
	$IngridientName = $_POST['IngridientName'];
	$Stock = $_POST['Stock'];
	$Price = $_POST['Price'];
  $NumServing = $_POST['NumServing'];
  $Serving = $_POST['Serving'];


  $Serving = $Stock * $NumServing;

  // Check if the ID exists to determine whether to INSERT or UPDATE
  $sql = "SELECT * FROM `inventory` WHERE `ID` = '$ID'";
  $result = mysqli_query($conn, $sql);
  

  // Update the existing record
  $updateSql = "UPDATE `inventory` SET `IngridientName`='$IngridientName',`Stock`='$Stock',`Price`='$Price',`NumServing`='$NumServing',`Serving`='$Serving', `hns`='$NumServing'WHERE id  = $ID";
  if (mysqli_query($conn, $updateSql)) {
    // Fetch the updated values from the database
    $fetchSql = "SELECT * FROM `inventory` WHERE `ID` = '$ID'";
    $fetchResult = mysqli_query($conn, $fetchSql);
    $updatedRow = mysqli_fetch_assoc($fetchResult);

    // Redirect with a success message
    header("Location: http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php?msg=Record updated successfully");
  }
   else {
    echo "Failed: " . mysqli_error($conn);
  }
}

if (isset($_POST["add"])) {
  $ID = $_POST['ID'];
  $IngridientName = $_POST['IngridientName'];
  $IngridientName = ucfirst($IngridientName);
  $Stock = $_POST['Stock'];
  $Price = $_POST['Price'];
  $Category = $_POST['Category'];
  $ProductType = $_POST['productType'];
  $ProductType = ucfirst($ProductType);
  $NumServing = $_POST['NumServing'];

  $Serving = $Stock * $NumServing;

  // Check if the IngridientName already exists in the database
  $checkSql = "SELECT * FROM `inventory` WHERE `IngridientName` = '$IngridientName'";
  $checkResult = mysqli_query($conn, $checkSql);

  if (mysqli_num_rows($checkResult) > 0) {
    // IngridientName already exists, display an error message
    header("Location: http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php?msg=The ingredient is already exists in the database.");
  } else {
    // IngridientName is not a duplicate, proceed with insertion
    $sql = "INSERT INTO `inventory` (`ID`, `IngridientName`, `Stock`, `Price`, `Category`, `productType`,`NumServing`, `Serving`,`hns`) VALUES ('$ID', '$IngridientName', '$Stock', '$Price', '$Category', '$ProductType','$NumServing', '$Serving','$NumServing')";
    $result = mysqli_query($conn, $sql);

    if ($result) {
      header("Location: http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php?msg=New record created successfully");
    } else {
      echo "Failed: " . mysqli_error($conn);
    }
  }
}
if (isset($_POST["addCategory"])) {
  $Category = $_POST['Category'];
  $Category = ucfirst($Category);

  // Check if the category already exists in the database
  $checkSql = "SELECT * FROM `Category` WHERE `Category` = '$Category'";
  $checkResult = mysqli_query($conn, $checkSql);

  if (mysqli_num_rows($checkResult) > 0) {
    // Category already exists, display an error message
    header("Location: http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php?msg=Category already exists in the database.");

  } else {
    // Category is not a duplicate, proceed with insertion
    $sql = "INSERT INTO `Category` (`Category`) VALUES ('$Category')";
    $result = mysqli_query($conn, $sql);

    if ($result) {
      header("Location: http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php?msg=New record created successfully");
    } else {
      echo "Failed: " . mysqli_error($conn);
    }
  }
}

if (isset($_GET['newCategory'])) {
  // Add code to handle the new category here
  // For this example, let's assume a category named 'NewCategory' is added
  $newCategory = 'NewCategory';

  // Update the SQL query to fetch data for the newly added category
  $sql = "SELECT * FROM `inventory` WHERE Category = '$newCategory'";
  $result = mysqli_query($conn, $sql);
}


?>


