<?php
include "connection.php";

if (isset($_POST["submit"])) {
    $ID = $_POST['ID'];
    $Name = $_POST['Name'];
    $Email = $_POST['Email'];
    $Address = $_POST['Address'];
    $Phone = $_POST['Phone'];
  
    // Check if the ID exists to determine whether to INSERT or UPDATE
    $sql = "SELECT * FROM `employeelist` WHERE `ID` = '$ID'";
    $result = mysqli_query($conn, $sql);
    
  
    // Update the existing record
    $updateSql = "UPDATE `employeelist` SET `Name`='$Name', `Email`='$Email', `Address`='$Address', `Phone`='$Phone' WHERE `ID`='$ID'";
    if (mysqli_query($conn, $updateSql)) {
      // Fetch the updated values from the database
      $fetchSql = "SELECT * FROM `employeelist` WHERE `ID` = '$ID'";
      $fetchResult = mysqli_query($conn, $fetchSql);
      $updatedRow = mysqli_fetch_assoc($fetchResult);
  
      // Redirect with a success message
      header("Location: http://localhost/sunny/Module/Admin/src/sidebar/Team/team.php?msg=Record updated successfully");
    }
     else {
      echo "Failed: " . mysqli_error($conn);
    }
  }
  if (isset($_POST["add"])) {
    $ID = $_POST['ID'];
    $Name = $_POST['Name'];
    $Email = $_POST['Email'];
    $Address = $_POST['Address'];
    $Phone = $_POST['Phone'];
 
    $sql ="INSERT INTO `employeelist` (`ID`, `Name`, `Email`, `Address`, `Phone`) VALUES ('$ID', '$Name', '$Email', '$Address', '$Phone')";
 
    $result = mysqli_query($conn, $sql);
 
    if ($result) {
       header("Location: http://localhost/sunny/Module/Admin/src/sidebar/Team/team.php?msg=New record created successfully");
    } else {
       echo "Failed: " . mysqli_error($conn);
    }
 }
?>