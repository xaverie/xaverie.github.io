<?php
include "src/php/connection/connection.php";

$sql = "SELECT IngridientName, Stock FROM inventory";
$result = mysqli_query($conn, $sql);

if (!$result) {
  die("Query failed: " . mysqli_error($conn));
}
$criticalThreshold = 10;
$recoveryThreshold = 20;
$noservingThreshold = 0;

while ($row = mysqli_fetch_assoc($result)) {
  $ingredientName = $row["IngridientName"];
  $stock = $row["Stock"];

  if ($stock <= $criticalThreshold) {
    $checkNotificationSQL = "SELECT * FROM notification WHERE product = '$ingredientName' AND State = 'critical'";
    $existingNotificationResult = mysqli_query($conn, $checkNotificationSQL);

    if (mysqli_num_rows($existingNotificationResult) == 0) {
      $notificationMessage = "$ingredientName";
      $notificationState = "critical";
      $notificationTimestamp = date("Y-m-d H:i:s"); 

      $insertNotificationSQL = "INSERT INTO notification (product, DTStamp, State) VALUES ('$notificationMessage', '$notificationTimestamp', '$notificationState')";

      if (mysqli_query($conn, $insertNotificationSQL)) {
      } else {
        die("Error: " . mysqli_error($conn));
      }
    }
  } elseif ($stock <= $recoveryThreshold) {
    $checkNotificationSQL = "SELECT * FROM notification WHERE product = '$ingredientName' AND State = 'recovery'";
    $existingNotificationResult = mysqli_query($conn, $checkNotificationSQL);

    if (mysqli_num_rows($existingNotificationResult) == 0) {
      $notificationMessage = "$ingredientName";
      $notificationState = "recovery";
      $notificationTimestamp = date("Y-m-d H:i:s"); 

      $insertNotificationSQL = "INSERT INTO notification (product, DTStamp, State) VALUES ('$notificationMessage', '$notificationTimestamp', '$notificationState')";

      if (mysqli_query($conn, $insertNotificationSQL)) {
      } else {
        die("Error: " . mysqli_error($conn));
      }
    }
  }

  if ($stock > $recoveryThreshold) {
    $deleteSQL = "DELETE FROM notification WHERE product = '$ingredientName'";
    if (mysqli_query($conn, $deleteSQL)) {
    } else {
      die("Error: " . mysqli_error($conn));
    }
  }
}
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="src\sidebar\Inventory\src\style\styleInventory.css">
  <link href="src\img\logo.png" rel="icon">

  <script>
    function hideShowDiv(select) {
      var selectedValue = select.value;
      var div_sandwich = document.getElementById('div_sandwich');
      if (select === "0") {
        div_sandwich.style.display = 'none';
      } else {
        div_sandwich.style.display = 'block';
      }
    }
    function ProductType(productType) {
      if (productType == "0") {
        document.getElementById('div_index').style.display = 'none';
        document.getElementById('div_reg').style.display = 'none';
      }
      else if (productType == "Main") {
        document.getElementById('div_reg').style.display = 'none';
        document.getElementById('div_index').style.display = 'block';
      }
      else if (productType == "Regular") {
        document.getElementById('div_reg').style.display = 'block';
        document.getElementById('div_index').style.display = 'block';
      }
    };
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
  </script>

</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <?php
  // if (isset($_GET["msg"])) {
  //   $msg = $_GET["msg"];
  //   echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">
  //           ' . $msg . '
  //           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  //           </div>';
  // } ?>

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
              <a href="http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php" class="nav-link active">
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
              <h1 class="m-0">Inventory List</h1>
              <div class="row">
              </div>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="http://localhost/sunny/Module/Admin/indexDashboard.php">Home</a>
                </li>
                <li class="breadcrumb-item active">Inventory</li>
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
          <div class="container">
            <div class="row " style="column-gap:150px;">
              <div class="col-md-2 mb-3 text-center exclude-in-print">
                <button type="button" onclick="window.print()" class="btn btn-dark"
                  style="background-color: #3C91E6; "><i class="fa fa-print"></i> Print
                  Preview</button>
              </div>

              <div class="col-md-8 mb-3 text-center">
                <button type="button" data-bs-toggle="modal" data-bs-target="#addCategoryModal" class="btn btn-dark"
                  style="background-color: #3C91E6; margin-left: 453px">Add New Category</button>
                <button type="button" data-bs-toggle="modal" data-bs-target="#myModal" class="btn btn-dark"
                  style="background-color: #3C91E6; margin-left: 5px">Add New</button>
              </div>
            </div>
          </div>

          <div class="row">
            <section class="col-lg-12 connectedSortable">
              <div class="card">
                <div class="card-header">
                  <div class="card-tools">
                    <ul class="nav nav-tabs" id="categoryTabs">
                      <?php
                      $categories = mysqli_query($conn, "SELECT DISTINCT TRIM(Category) AS Category FROM `Category`");
                      while ($cat = mysqli_fetch_assoc($categories)) {
                        $tabId = $cat['Category'] . '-tab';
                        $tabPaneId = $cat['Category'];

                        $isActive = ($cat['Category'] == 'Sandwich') ? 'active' : '';

                        echo '<li class="nav-item">
                    <a class="nav-link ' . $isActive . '" id="' . $tabId . '" data-bs-toggle="tab" href="#' . $tabPaneId . '" role="tab">' . $cat['Category'] . '</a>
                  </li>';
                      }
                      ?>
                    </ul>
                  </div>
                </div>
                <div class="card-body">
                  <div class="tab-content p-0">
                    <?php
                    $categories = mysqli_query($conn, "SELECT DISTINCT TRIM(Category) AS Category FROM `Category`");
                    while ($cat = mysqli_fetch_assoc($categories)) {
                      $tabPaneId = $cat['Category'];
                      $isActive = ($cat['Category'] == 'Sandwich') ? 'active show' : '';

                      echo '<div class="tab-pane fade ' . $isActive . '" id="' . $tabPaneId . '" role="tabpanel">';
                      generateCategoryTable($cat['Category'], $conn);
                      echo '</div>';
                    }
                    ?>

                    <?php
                    $sql = "SELECT DISTINCT TRIM(Category) AS Category FROM `Category`";
                    $categories = mysqli_query($conn, $sql);

                    function generateCategoryTable($category, $conn)
                    {
                      $sql = "SELECT * FROM `inventory` WHERE Category = '$category'";
                      $result = mysqli_query($conn, $sql);
                      ?>
                      <table class="table table-hover text-center">
                        <thead class="table" style="background-color: #3C91E6; color: white;">
                          <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Ingredients for
                              <?php echo $category ?>
                            </th>
                            <th scope="col">Serving</th>
                            <th scope="col">Price</th>
                            <th scope="col">Category</th>
                            <th scope="col">Product Type</th>
                            <th scope="col">Action
                              <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#deleteCategoryModal<?php echo $category; ?>" style="border:none">
                                <i class="fas fa-times" style="font-size: 18px;"></i>
                              </button>

                            </th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          while ($row = mysqli_fetch_assoc($result)) {
                            ?>
                            <tr>
                              <td>
                                <?php echo $row["ID"] ?>
                              </td>
                              <td>
                                <?php echo $row["IngridientName"] ?>
                              </td>
                              <td>
                                <?php echo $row["Serving"] ?>
                              </td>
                              <td>
                                <?php echo $row["Price"] ?>
                              </td>
                              <td class="category-cell">
                                <?php echo $row["Category"] ?>
                              </td>
                              <td>
                                <?php echo $row["productType"] ?>
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
                                        <h3>Edit Sandwich</h3>
                                        <p class="text-muted">Complete the form below to edit this Sandwich</p>
                                      </div>
                                      <div class="container-xl d-flex justify-content-center">

                                        <form action="src/php/connection/addInveConn.php" method="post"
                                          style="width: 70vw; min-width: 300px;">

                                          <div class="row mb-3" style="display:none;">
                                            <div class="col">
                                              <label class="form-label">ID:</label>
                                              <input type="text" class="form-control" name="ID"
                                                value="<?php echo $row['ID'] ?>" readonly>
                                            </div>
                                          </div>

                                          <div class="row mb-3">
                                            <div class="col">
                                              <label class="form-label">Product Name:</label>
                                              <input type="text" class="form-control" name="IngridientName"
                                                value="<?php echo $row['IngridientName'] ?>">
                                            </div>
                                          </div>

                                          <div class="row mb-3">
                                            <div class="col">
                                              <label class="form-label">Stock:</label>
                                              <input type="text" class="form-control" name="Stock"
                                                value="<?php echo $row['Stock'] ?>">
                                            </div>
                                          </div>
                                          <div class="row mb-3">
                                            <div class="col">
                                              <label class="form-label">Number Serving:</label>
                                              <input type="text" class="form-control" name="NumServing"
                                                value="<?php echo $row['NumServing'] ?>">
                                            </div>
                                          </div>
                                          <div class="mb-2">
                                            <label class="form-label">Price:</label>
                                            <input type="text" class="form-control" name="Price"
                                              value="<?php echo $row['Price'] ?>">
                                          </div>

                                          <div class="modal-footer">
                                            <a href="http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php"
                                              style="text-decoration: none; color: white;">
                                              <button type="submit" class="btn btn-success" name="submit"
                                                style="background-color:#3C91E6; border:none">Save</button>
                                            </a>
                                            <a href="http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php"
                                              class="btn btn-danger">Cancel</a>
                                          </div>
                                        </form>
                                      </div>
                                    </div>
                              </td>
                            </tr>
                            <?php
                          }
                          ?>
                        </tbody>
                      </table>

                      <div class="modal" id="deleteCategoryModal<?php echo $category; ?>">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title">Delete Category</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              Are you sure you want to delete the category "
                              <?php echo $category; ?>"?
                            </div>
                            <div class="modal-footer">
                              <a href="src/php/connection/deleteCategory.php?category=<?php echo $category; ?>"
                                class="btn btn-danger">Delete Category</a>
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <?php
                    }
                    ?>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>
      </section>
      <section>
        <div class="modal" id="myModal" style="">
          <div class="modal-dialog d-flex align-items-center">
            <div class="modal-content" style="width:30vw">
              <div class="text-center mb-4">
                <h3>Add New Stock</h3>
                <p class="text-muted">Complete the form below to add a new Stock</p>
              </div>

              <div class="container-xl d-flex justify-content-center">
                <form action="src/php/connection/addInveConn.php" method="post" style="width:70vw; min-width:300px;">
                  <div class="row mb-3" style="display:none;">
                    <div class="col">
                      <label class="form-label">ID:</label>
                      <input type="text" class="form-control" name="ID" placeholder="System Generated" readonly>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col">
                      <label class="form-label">Category:</label>
                      <select class="form-select" name="Category" onchange="hideShowDiv(this.value)">
                        <option value="0">Select Category</option>
                        <?php
                        $sql = "SELECT DISTINCT TRIM(Category) AS Category FROM `Category`";
                        $select = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($select) > 0) {
                          foreach ($select as $cat) {
                            ?>
                            <option value="<?php echo $cat['Category'] ?>">
                              <?php echo $cat['Category'] ?>
                            </option>
                            <?php
                          }
                          mysqli_close($conn);

                        } ?>
                      </select>
                    </div>
                  </div>

                  <div id="div_sandwich" class="div_beverage" style="display: none;">
                    <div class="row mb-3">
                      <div class="col">
                        <label class="form-label">Product Type:</label>
                        <select class="form-select" name="productType" id="productTypeSelect"
                          onchange="ProductType(this.value)">
                          <option value="0">Select Product Type:</option>
                          <option value="Regular">Regular</option>
                          <option value="Main">Main</option>
                        </select>
                      </div>
                    </div>
                    <div id="div_index" style="display: none;">
                      <div class="row mb-3">
                        <div class="col">
                          <label class="form-label">Ingredient Name:</label>
                          <input type="text" class="form-control" name="IngridientName">
                        </div>
                      </div>
                      <div class="row mb-3">
                        <div class="col">
                          <label class="form-label">Stock:</label>
                          <input type="text" class="form-control" name="Stock">
                        </div>
                        <div class="col">
                          <label class="form-label">Number of Serving:</label>
                          <input type="text" class="form-control" name="NumServing">
                        </div>
                      </div>
                      <div id="div_reg" style="display: none;">
                        <div class="row mb-3">
                          <div class="col">
                            <label class="form-label">Price:</label>
                            <input type="text" class="form-control" name="Price">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="modal-footer">
                    <a href="http://localhost/sunny/Module/Admin/src/sidebar/Inventory/inventory.php"
                      style="text-decoration: none; color: white;"> <button type="submit" class="btn btn-success"
                        name="add" style="background-color:#3C91E6; border:none">Save</button></a>
                    <a href="http://localhost/sunny/Module/Admin/src/sidebar/Inventory/Inventory.php"
                      class="btn btn-danger">Cancel</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
    <section>
      <div class="modal" id="addCategoryModal">
        <div class="modal-dialog d-flex align-items-center">
          <div class="modal-content" style="width: 30vw;">
            <div class="text-center mb-4">
              <h3>Add New Category</h3>
              <p class="text-muted">Enter the new category name</p>
            </div>
            <div class="container-xl d-flex justify-content-center">
              <form action="src/php/connection/addInveConn.php" method="post" style="width: 70vw; min-width: 300px;">
                <div class="row mb-3">
                  <div class="col">
                    <label class="form-label">Category Name:</label>
                    <input type="text" class="form-control" name="Category" placeholder="Enter Category Name" required>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success" name="addCategory"
                    style="background-color: #3C91E6; border: none">Add Category</button>
                  <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
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
  <script src="src/script/scriptInventory.js"></script>
</body>
</html>