<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="src\style\index.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="wrapper">
        <img src="src\image\logo.png" alt="Login Image">
        <form action="src/php/Login.php" method="post">
            <h1>Login</h1>

            <?php if(isset($_GET['error'])){?>
            <p class="error">
                <?php echo $_GET['error'];?>
            </p>

            <?php }?>

            <div class="input-box">
                <input type="text" name="empName" placeholder="Username" required>
                <i class='bx bxs-user'></i>
            </div>

            <div class="input-box">
                <input type="password" name="empPass" placeholder="Password" required>
                <i class='bx bxs-lock-alt'></i>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>

</html>