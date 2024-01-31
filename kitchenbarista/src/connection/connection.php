<?php

$sname ="sql6.freesqldatabase.com";
$name = "sql6680751";
$pass = "ZJeCDdRZd1";

$db_name = "sql6680751";

$conn = mysqli_connect($sname, $name, $pass, $db_name);

if(!$conn){
    echo "failed";
}

?>