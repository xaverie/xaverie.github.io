<?php
include 'src/connection/connection.php';

$fetch_cart_query = mysqli_query($conn, "SELECT * FROM `cashierCart`");

$cartContent = '<ul>';
while ($row = mysqli_fetch_assoc($fetch_cart_query)) {
    $cartContent .= '<li>' . $row['name'] . ' - ' . $row['quantity'] . '</li>';
}
$cartContent .= '</ul>';

echo $cartContent;
?>