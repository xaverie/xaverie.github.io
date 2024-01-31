<?php
include 'src/connection/connection.php';

session_start();
if (isset($_POST['login'])) {
    $username = $_POST['Name'];
    $password = $_POST['Password'];

    // Assuming your table is named 'accounts' and has columns 'Name' and 'Password'
    $query = "SELECT * FROM accounts WHERE Name = '$username' AND Password = '$password'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $row_count = mysqli_num_rows($result);

        if ($row_count == 1) {
            // Login successful
            $_SESSION['Name'] = $username;
            header('Location: CashierMonitor.php'); // Redirect to a dashboard page or any other page you want
            exit();
        } else {
            // Invalid credentials
            $loginError = "Invalid username or password";
        }
    } else {
        // Error in the query
        echo "Error: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <style>
        body {
            background-image: url('src/image/logo.png');
            background-size: contain;
            background-position: left center; /* Set background position to left center */
            background-repeat: no-repeat;
            margin-left: 10%; /* Adjust the margin-left value as needed */
            height: 100vh;
        }

        .login-container {
            max-width: 1000px; /* Adjusted max-width to make it a bit bigger */
            width: 120%;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="row">
            <div class="col-md-12 mt-5 text-center">
                <div class="login-container">
                    <h2 class="text-center mb-4">Login</h2>
                    <?php
                    if (isset($loginError) && !empty($loginError)) {
                        echo '<div class="alert alert-danger" role="alert">' . $loginError . '</div>';
                    }
                    ?>
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                    <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="Name" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="Password" required>
                        </div>
                        <button type="submit" class="btn btn-primary" name="login">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>

</body>

</html>
