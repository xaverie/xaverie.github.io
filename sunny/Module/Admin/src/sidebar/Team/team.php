<?php
include "src/php/connection/connection.php";

session_start();

if (isset($_SESSION['Name']) && isset($_SESSION['Password'])) {
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
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="src/style/styleTeam.css">

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
                <a href="http://localhost/sunny/Module/Admin/indexDashboard.php" class="nav-link">
                  <i class="nav-icon fas fa-tachometer-alt" style="color:#fcfbf4; white-space: nowrap;"></i>
                  <p>
                    Dashboard
                  </p>
                </a>
              </li>
              <li class="nav-item">
                <a href="http://localhost/sunny/Module/Admin/src/sidebar/Product/productDashboard.php" class="nav-link">
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
                <a href="" class="nav-link active">
                  <i class="nav-icon fas fa-users" style="color:#fcfbf4; white-space: nowrap;"></i>
                  <p>
                    Employee
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
                <h1 class="m-0">Employee List</h1>
              </div>
              <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                  <li class="breadcrumb-item"><a href="http://localhost/sunny/Module/Admin/indexDashboard.php">Home</a></li>
                  <li class="breadcrumb-item active">Team</li>
                </ol>
              </div>
            </div>
          </div>
        </div>

        <section class="content">
          <div class="container-fluid">
            <?php
            if (isset($_GET["msg"])) {
              $msg = $_GET["msg"];
              echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
		' . $msg . '
		<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>';
            }
            ?>
          <div class="row justify-content-end">
            <button type="button" data-bs-toggle="modal" data-bs-target="#myModal" class="btn btn-dark"
            style="background-color: #3C91E6; width: 100px;">Add New</button>
          </div>
            <div class="row">

              <section class="col-lg-12 connectedSortable">
                <div class="card">
                  <div class="card-header">
                    <table class="table table-hover text-center" style="margin-top: 6px">
                      <thead class="table" style="background-color:#3C91E6; color: white;">
                        <tr>
                          <th scope="col">ID</th>
                          <th scope="col">Name</th>
                          <th scope="col">Email</th>
                          <th scope="col">Address</th>
                          <th scope="col">Phone</th>
                          <th scope="col">Action</th>
                        </tr>
                      </thead>
                      <tbody>

                        <?php
                        $sql = "SELECT * FROM `employeelist`";
                        $result = mysqli_query($conn, $sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                          ?>
                          <tr>
                            <td>
                              <?php echo $row["ID"] ?>
                            </td>
                            <td>
                              <?php echo $row["Name"] ?>
                            </td>
                            <td>
                              <?php echo $row["Email"] ?>
                            </td>
                            <td>
                              <?php echo $row["Address"] ?>
                            </td>
                            <td>
                              <?php echo $row["Phone"] ?>
                            </td>
                            <td>
                              <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#myModalEdit<?php echo $row['ID']; ?>"
                                style="background-color: transparent; border: none;"><i class="fas fa-pencil"
                                  style="color: black;"></i></button>
                              <a href="src/php/delete.php?id=<?php echo $row["ID"] ?>" class="link-blue"><i
                                  class="fa-solid fa-trash fs-5"></i></a>

                              <div class="modal" id="myModalEdit<?php echo $row['ID']; ?>">
                                <div class="modal-dialog">
                                  <div class="modal-content" style="width: 30vw;">
                                    <div class="text-center mb-4">
                                      <h3>Edit Employee</h3>
                                      <p class="text-muted">Complete the form below to edit this employee</p>
                                    </div>
                                    <div class="container-xl d-flex justify-content-center">
                                      <form action="src/php/connection/addNewConn.php" method="post"
                                        style="width: 70vw; min-width: 300px;">

                                        <div class="row mb-3">
                                          <div class="col">
                                            <label class="form-label">ID:</label>
                                            <input type="text" class="form-control" name="ID"
                                              value="<?php echo $row['ID'] ?>" readonly>
                                          </div>
                                        </div>
                                        <div class="row mb-3">
                                          <div class="col">
                                            <label class="form-label">Name:</label>
                                            <input type="text" class="form-control" name="Name"
                                              value="<?php echo $row['Name'] ?>">
                                          </div>
                                        </div>
                                        <div class="row mb-3">
                                          <div class="col">
                                            <label class="form-label">Email:</label>
                                            <input type="email" class="form-control" name="Email"
                                              value="<?php echo $row['Email'] ?>">
                                          </div>
                                        </div>
                                        <div class="mb-2">
                                          <label class="form-label">Address:</label>
                                          <input type="text" class="form-control" name="Address"
                                            value="<?php echo $row['Address'] ?>">
                                        </div>
                                        <div class="row mb-4">
                                          <div class="col">
                                            <label class="form-label">Phone:</label>
                                            <input type="text" class="form-control" name="Phone"
                                              value="<?php echo $row['Phone'] ?>">
                                          </div>
                                          <div class="modal-footer">
                                            <a href="http://localhost/sunny/Module/Admin/src/sidebar/Team/team.php"
                                              style="text-decoration: none; color: white;">
                                              <button type="submit" class="btn btn-success" name="submit"
                                                style="background-color:#3C91E6; border:none">Save</button>
                                            </a>
                                            <a href="http://localhost/sunny/Module/Admin/src/sidebar/Team/team.php"
                                              class="btn btn-danger">Cancel</a>
                                          </div>
                                      </form>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </td>
                          </tr>
                          <?php
                        }
                        ?>

                      </tbody>
                    </table>
                  </div>

                </div>
              </section>

              <div class="modal" id="myModal">
                <div class="modal-dialog">
                  <div class="modal-content" style="width:30vw">
                    <div class="text-center mb-4">
                      <h3>Add New Employee</h3>
                      <p class="text-muted">Complete the form below to add a new employee</p>
                    </div>

                    <div class="container-xl d-flex justify-content-center">
                      <form action="src/php/connection/addNewConn.php" method="post" style="width:70vw; min-width:300px;">
                        <div class="row mb-3">
                          <div class="col">
                            <label class="form-label">ID:</label>
                            <input type="text" class="form-control" name="ID" placeholder="System Generated" readonly>
                          </div>
                        </div>
                        <div class="row mb-3">
                          <div class="col">
                            <label class="form-label">Name:</label>
                            <input type="text" class="form-control" name="Name" placeholder="Name">
                          </div>
                        </div>
                        <div class="row mb-3">
                          <div class="col">
                            <label class="form-label">Email:</label>
                            <input type="email" class="form-control" name="Email" placeholder="Email">
                          </div>
                        </div>

                        <div class="mb-3">
                          <label class="form-label">Address:</label>
                          <input type="text" class="form-control" name="Address"
                            placeholder="Address">
                        </div>
                        <div class="row mb-4">
                          <div class="col">
                            <label class="form-label">Phone:</label>
                            <input type="text" class="form-control" name="Phone" placeholder="Phone Number">
                          </div>
                        </div>

                        <div class="modal-footer">
                          <a href="http://localhost/sunny/Module/Admin/src/sidebar/Team/team.php"
                            style="text-decoration: none; color: white;"> <button type="submit" class="btn btn-success"
                              name="add" style="background-color:#3C91E6; border:none">Save</button></a>

                          <a href="http://localhost/sunny/Module/Admin/src/sidebar/Team/team.php"
                            class="btn btn-danger">Cancel</a>
                        </div>

                      </form>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="src/script/scriptTeam.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0/js/bootstrap-select.min.js"></script>

  </body>

  </html>
  <?php
} else {
  header("Location: index.php");
  exit;
}

?>