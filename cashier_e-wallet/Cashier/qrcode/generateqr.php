<?php
@include 'products.php';
include "meRaviQr/qrlib.php";

$sname ="localhost";
$name = "root";
$pass = "";

$db_name = "sunnyssandwichcoffee";

$conn = mysqli_connect($sname, $name, $pass, $db_name);

if(!$conn){
    echo "failed";
}

$select_cart = mysqli_query($conn, "SELECT * FROM `cart`");
$grand_total = 0;

if (mysqli_num_rows($select_cart) > 0) {
    while ($fetch_cart = mysqli_fetch_assoc($select_cart)) {
        $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
        $grand_total += $sub_total;
    }
}

$message = array();

// Assuming you have data to generate the QR code, replace this line with your data.
$data = "Total Amount: ₱" . number_format($grand_total, 2, '.', ',');

$dev = "                                                                           
                                                                                     
                                ";
$final = $data . $dev;

// Generate the QR code and output it directly as an image
QRcode::png($final);
QRcode::png($customization_json, $qr_image_file, 'h', 8, 2);

exit; // Terminate the script after generating the image
?>


