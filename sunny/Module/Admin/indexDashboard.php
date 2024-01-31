<?php
session_start();
if (isset($_SESSION['Name']) && isset($_SESSION['Password'])) {
  @include 'src\connection\connection.php'
    ?>

  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sunny's Sandwich + Coffee</title>
    <link rel="stylesheet"
      href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="src\plugins\fontawesome-free\css\all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="src\plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <link rel="stylesheet" href="src\plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <link rel="stylesheet" href="src\plugins/jqvmap/jqvmap.min.css">
    <link rel="stylesheet" href="src\dist/css/adminlte.min.css">
    <link rel="stylesheet" href="src\plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="src\plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="src\plugins/summernote/summernote-bs4.min.css">
    <link rel="stylesheet" href="src\style\styleIndexDash.css">
    <link href="src\img\logo.png" rel="icon">

  </head>

  <body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

      <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="src/img/logo.png" alt="AdminLTELogo" height="60" width="60">
      </div>

      <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
          </li>
          <li class="nav-item d-none d-sm-inline-block">
            <a href="http://localhost/sunny/Module/Admin/indexDashboard.php" class="nav-link">Home</a>
          </li>
        </ul>

        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
              <i class="fas fa-expand-arrows-alt"></i>
            </a>
          </li>
        </ul>
      </nav>

      <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <div class="sidebar">
          <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
              <img src="src/img/logo.png" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
              <a href="http://localhost/sunny/Module/Admin/indexDashboard.php" class="d-block">Sunny's Sandwich
                + Coffee</a>
            </div>
          </div>

          <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
              <li class="nav-item">
                <a href="http://localhost/sunny/Module/Admin/indexDashboard.php" class="nav-link active">
                  <i class="nav-icon fas fa-tachometer-alt" style="color:#fcfbf4; white-space: nowrap;"></i>
                  <p>
                    Dashboard
                  </p>
                </a>
              </li>
              <li class="nav-item ">
                <a href="http://localhost/sunny/module/admin/src/sidebar/Product/productDashboard.php" class="nav-link">
                  <i class="nav-icon fa fa-shopping-bag" style="color:#fcfbf4; white-space: nowrap;"></i>
                  <p>
                    My Products
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php" class="nav-link">
                  <i class="nav-icon fas fa-copy" style="color:#fcfbf4; white-space: nowrap;"></i>
                  <p>
                    Inventory
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="http://localhost/sunny/Module/Admin/src/sidebar/Team/team.php" class="nav-link">
                  <i class="nav-icon fas fa-users" style="color:#fcfbf4; white-space: nowrap;"></i>
                  <p>
                    Team
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="http://localhost/sunny/module/admin/src/sidebar/Transaction/transaction.php" class="nav-link">
                  <i class="nav-icon fas fa-dollar-sign" style="color:#fcfbf4; white-space: nowrap;"></i>
                  <p>
                    Transactions
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="http://localhost/sunny/module/admin/src/sidebar/Report/report.php" class="nav-link">
                  <i class="nav-icon fas fa-file-alt" style="color:#fcfbf4; white-space: nowrap;"></i>
                  <p>
                    Report
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="http://localhost/sunny/Module/index.php" class="nav-link">
                  <i class="nav-icon fas fa-sign-out-alt" style="color:#FF0000; white-space: nowrap;"></i>
                  <p>
                    Logout
                  </p>
                </a>
              </li>
            </ul>
          </nav>
        </div>
      </aside>

      <div class="content-wrapper">
        <div class="content-header">
          <div class="container-fluid">
            <div class="row mb-2">
              <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="http://localhost/sunny/Module/Admin/indexDashboard.php">Home</a>
                  </li>
                  <li class="breadcrumb-item active">Dashboard</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <section class="content">
          <div class="container-fluid">
            <div class="row" style="flex-wrap: wrap; justify-content: center; display:inline-flex; gap: 30px;">
              <div style="width:400px;">
                <div class="small-box bg-info">
                  <div class="inner">
                    <span class="text">
                      <?php
                      $currentDate = date("Y-m-d");

                      $sql = "SELECT COUNT(*) AS total_orders FROM `orders` WHERE DATE(order_date) = '$currentDate'";
                      $result = mysqli_query($conn, $sql);
                      if (!$result) {
                        die("Query failed: " . mysqli_error($conn));
                      }

                      $row = mysqli_fetch_assoc($result);
                      $totalOrders = $row['total_orders'];

                      ?>
                      <h3 style="margin-left: 90px">
                        <?php echo $totalOrders; ?>
                      </h3>

                      <p style="margin-left: 90px">New Order</p>
                    </span>
                  </div>
                  <div class="icon">
                    <i class="ion ion-bag" style="margin-right: 300px;"></i>
                  </div>
                  <a href="#" class="small-box-footer">.</a>
                </div>
              </div>

              <div style="width:400px;">
                <div class="small-box bg-success">
                  <div class="inner">
                    <span class="text">
                      <?php
                      $sql = "SELECT SUM(price) AS total_price FROM orders";
                      $result = mysqli_query($conn, $sql);
                      while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <h3 style="margin-left: 100px">
                          <?php echo $row['total_price']; ?>
                        </h3>
                      <?php } ?>
                      <p style="margin-left: 100px">Total Sales</p>
                      <div class="icon">
                        <i class="fas fa-donate" style="margin-right: 290px;"></i>
                      </div>

                    </span>
                  </div>
                  <a href="#" class="small-box-footer">.</a>
                </div>
              </div>

              <div class="row" style="flex-wrap: wrap; justify-content: center; display:inline-flex; gap: 30px;">
                <div style="width:400px;">
                  <div class="card">
                    <div class="card-header">
                      <h3 class="card-title">
                        <i class="far fa-file-alt"></i>
                        Recent Orders
                      </h3>
                    </div>
                    <div class="card-body">
                      <div class="table-data">
                        <div class="order">
                          <div class="head">
                            <table>
                              <thead>
                                
                              </thead>
                            </table>
                          </div>
                          <div class="table-container">
                            <table>
                              <thead> 
                                <tr>
                                  <th>Table No.</th>
                                  <th>Date Order And Time</th>
                                  <th>Status</th>
                                </tr>
                              </thead>
                              <tbody>
                                <?php
                                $sql = "SELECT * FROM orders ORDER BY order_date DESC";
                                $result = mysqli_query($conn, $sql);
                                while ($row = mysqli_fetch_assoc($result)) {
                                  ?>
                                  <tr>
                                    <td>
                                      <p>
                                        <?php echo $row['tableNo'] ?>
                                      </p>
                                    </td>
                                    <td>
                                      <?php echo $row['order_date'] ?>
                                    </td>
                                    <td><span class="status <?php echo isset($row['status']) ? $row['status'] : ''; ?>">
                                        <?php echo isset($row['status']) ? $row['status'] : ''; ?>
                                      </span></td>
                                  </tr>
                                <?php } ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        
                        <style>
                          .table-container{
                            max-height: 400px;
                            overflow-y: auto;
                          }

                          .table-container::-webkit-scrollbar {
                            width: 6px;
                          }

                          .table-container::-webkit-scrollbar-thumb {
                            background-color: #888;
                            border-radius: 6px;
                          }

                          .table-container::-webkit-scrollbar-track {
                            background-color: #f1f1f1;
                            border-radius: 6px;
                          }

                          .table-container::-webkit-scrollbar-button {
                            display: none;
                          }

                          table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 10px;
                          }

                          th,
                          td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: left;
                          }

                          th {
                            background-color: #f2f2f2;
                          }

                          .status {
                            padding: 4px 8px;
                            border-radius: 4px;
                            font-weight: bold;
                            text-transform: uppercase;
                            color: #fff;
                            display: inline-block;
                          }

                          .status.pending {
                            background-color: #ffcc00;
                          }

                          .status.completed {
                            background-color: #4CAF50;
                          }

                          .status.cancelled {
                            background-color: #ff0000;
                          }
                        </style>
                      </div>

                    </div>
                  </div>
                </div>
                <div style="width:400px;">

                  <div class="card direct-chat direct-chat-primary">
                    <div class="card-header">
                      <h3 class="card-title">Inventory Updates</h3>
                    </div>
                    <div class="card-body">
                      <div class="todo-list">
                        <style>
                          .todo-list {
                            max-height: 490px;
                            width: 100%;
                            max-width: 800px;
                            margin: 0 auto;
                          }


                          .todo-list {
                            background-color: #f2f2f2;
                            padding: 10px;
                          }

                          .critical-table {
                            background-color: #ffa07a !important;
                          }

                          .recovery-table {
                            background-color: #add8e6 !important;
                          }

                          .todo-list {
                            background-color: none;
                            list-style-type: none;
                            padding: 0;
                            margin: 0;
                          }

                          .todo-list li {
                            border-bottom: 1px solid #ccc;
                          }

                          .container {
                            padding: 10px;
                          }

                          .container p {
                            margin: 5px 0;
                          }

                          .container p:first-child {
                            font-weight: bold;
                          }

                          .todo-list::-webkit-scrollbar {
                            width: 6px;
                          }

                          .todo-list::-webkit-scrollbar-thumb {
                            background-color: #888;
                            border-radius: 6px;
                          }

                          .todo-list::-webkit-scrollbar-track {
                            background-color: #f1f1f1;
                            border-radius: 6px;

                          }

                          .todo-list::-webkit-scrollbar-button {
                            display: none;
                          }
                        </style>
                        <div class="todo">
                          <ul class="todo-list">
                            <?php
                            $sql = "SELECT * FROM notification ORDER BY State='critical' DESC, State ASC";
                            $result = mysqli_query($conn, $sql);

                            while ($row = mysqli_fetch_assoc($result)) {
                              $class = ($row['State'] === 'critical') ? 'critical-table' : 'recovery-table';
                              ?>
                              <li class="<?php echo $class; ?>">
                                <div class="container" style="height:60px">
                                  <p>
                                    <?php echo $row['product']; ?>
                                  </p>
                                  <p>Status:
                                    <?php echo $row['State']; ?>
                                  </p>
                                </div>
                              </li>
                            <?php } ?>
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>




                </div>
              </div>
            </div>
        </section>
      </div>
      <footer class="main-footer" style="white-space: nowrap; text-align: center;">
        <strong style="display: inline-block; margin: 0 auto;">Copyright &copy; 2024 Sunny's Sandwich + Coffee. All rights
          reserved.</strong>
      </footer>

      <aside class="control-sidebar control-sidebar-dark">
      </aside>
    </div>

    <script src="src\plugins/jquery/jquery.min.js"></script>
    <script src="src\plugins/jquery-ui/jquery-ui.min.js"></script>
    <script>
      $.widget.bridge('uibutton', $.ui.button)
    </script>
    <script src="src\plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="src\plugins/chart.js/Chart.min.js"></script>
    <script src="src\plugins/sparklines/sparkline.js"></script>
    <script src="src\plugins/jqvmap/jquery.vmap.min.js"></script>
    <script src="src\plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
    <script src="src\plugins/jquery-knob/jquery.knob.min.js"></script>
    <script src="src\plugins/moment/moment.min.js"></script>
    <script src="src\plugins/daterangepicker/daterangepicker.js"></script>
    <script src="src\plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
    <script src="src\plugins/summernote/summernote-bs4.min.js"></script>
    <script src="src\plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <script src="src\dist/js/adminlte.js"></script>
    <script src="src\dist/js/demo.js"></script>
    <script src="src\dist/js/pages/dashboard.js"></script>
  </body>

  </html>
  <?php
} else {
  header("Location: index.php");
  exit;
}
?>