<?php
session_start();
include "database/dbAccounts.php";
if(isset($_POST['empName']) && isset($_POST['empPass'])){
    function validate($data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    $name = validate($_POST['empName']);
    $pass = validate($_POST['empPass']);

    if(empty($name)){
        header("Location: Module/index.php?error=Name is required");
        exit;

    }elseif(empty($pass)){
        header("Location: Module/index.php?error= Password is required");
        exit;
    }
    elseif($name == "ADMIN"){
        $sql = "select * from accounts where Name = '$name' AND Password = '$pass'
        ";
        $result = mysqli_query($conn, $sql);
        if(mysqli_num_rows($result) === 1){
            session_start();
            $row = mysqli_fetch_assoc($result);
            if($row['Name'] === $name && $row['Password'] === $pass){
                $_SESSION['Name'] =  $row['Name'];
                $_SESSION['Password'] =  $row['Password'];
                header("Location: http://localhost/sunny/Module/Admin/indexDashboard.php");
                exit;
            }
        }else{
            header("Location: Module/index.php?error= Password and Name are incorrect!.");
            exit;
        }
    }else{
        header("Location: Module/index.php?error= Password and Name are incorrect!.");
    }
}
else{
    header("Location: Module/index.php");
    exit;
}
