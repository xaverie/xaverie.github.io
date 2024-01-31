<?php
include "connection/connection.php";
$ID = $_GET["id"];
$sql = "DELETE FROM `product` WHERE id = $ID";
$result = mysqli_query($conn, $sql);

if ($result) {
  header("Location: http://localhost/sunny/Module/Admin/src/sidebar/Product/productDashboard.php?msg=Data deleted successfully");
} else {
  echo "Failed: " . mysqli_error($conn);
}
