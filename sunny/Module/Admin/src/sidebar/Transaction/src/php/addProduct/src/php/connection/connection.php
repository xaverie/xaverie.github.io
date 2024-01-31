<?php

$sname ="localhost";
$name = "root";
$pass = "";

$db_name = "sunnyssandwichcoffee";

$conn = mysqli_connect($sname, $name, $pass, $db_name);

if(!$conn){
    echo "failed";
}

?>