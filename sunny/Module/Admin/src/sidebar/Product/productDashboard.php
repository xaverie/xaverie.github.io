<?php
include "src\php\connection\connection.php";

$message = []; 

if (isset($_POST['update_product'])) {
  $id = $_POST['ID'];

  $product_name = $_POST['Name'];
  $product_price = $_POST['Price'];
  $product_stocks = $_POST['Stocks'];
  $product_image = $_FILES['product_image']['name'];
  $product_image_tmp_name = $_FILES['product_image']['tmp_name'];
  $product_image_folder = 'src/uploaded_img/' . $product_image;
  $product_ingridient = $_POST['Ingridients'];

  $update_data = "UPDATE menu SET Name='$product_name', Price='$product_price', Stocks='$product_stocks', Image='$product_image' WHERE ID = '$id'";
  $upload = mysqli_query($conn, $update_data);

  if ($upload) {
    move_uploaded_file($product_image_tmp_name, $product_image_folder);
    header('location:http://localhost/sunny/Module/Admin/src/sidebar/Product/productDashboard.php');
    exit;
  } else {
    $message[] = 'Failed to update the product';
  }
}

if (isset($_POST['add_product'])) {
  $product_name = $_POST['product_name'];
  $product_price = $_POST['product_price'];
  $product_image = $_FILES['product_image']['name'];
  $product_image_tmp_name = $_FILES['product_image']['tmp_name'];
  $product_image_folder = 'src/uploaded_img/' . $product_image;
  $Category = isset($_POST['Category']) ? $_POST['Category'] : '';
  $mainIngredient = $_POST['mainIngredient'];

  $product_ingredients = isset($_POST['product_ingredient']) ? $_POST['product_ingredient'] : array();

  $product_ingredients_str = implode(", ", $product_ingredients);

  if (empty($product_name) || empty($product_price) || empty($product_image) || empty($product_ingredients)) {
    $message[] = 'Please fill out all fields';
  } else {
    if (!is_dir('src/uploaded_img/')) {
      mkdir('src/uploaded_img/');
    }

    $insert = "INSERT INTO menu (name, price, image, Ingridients, Category, mainIngredient) VALUES ('$product_name', '$product_price', '$product_image', '$product_ingredients_str','$Category','$mainIngredient')";
    $upload = mysqli_query($conn, $insert);

    if ($upload) {
      move_uploaded_file($product_image_tmp_name, $product_image_folder);
      $message[] = 'New product added successfully';
    } else {
      $message[] = 'Could not add the product';
    }
  }
}
if (isset($_GET['category'])) {
  $selectedCategory = $_GET['category'];

  $sql = "SELECT IngridientName FROM Inventory WHERE Category = '$selectedCategory'";
  $ingredients = mysqli_query($conn, $sql);

  if (mysqli_num_rows($ingredients) > 0) {
    while ($row = mysqli_fetch_assoc($ingredients)) {
      echo '<option value="' . $row['IngridientName'] . '">' . $row['IngridientName'] . '</option>';
    }
  } else {
    echo 'No ingredients found for this category.';
  }
}
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  mysqli_query($conn, "DELETE FROM menu WHERE id = $id");
  header('location:productDashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sunny's Sandwich +Coffee</title>

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
  <link rel="stylesheet" href="src/style/styleProductDashboard.css">

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
              <a href="http://localhost/sunny/Module/Admin/indexDashboard.php" class="nav-link">
                <i class="nav-icon fas fa-tachometer-alt" style="color:#fcfbf4; white-space: nowrap;"></i>
                <p>
                  Dashboard
                </p>
              </a>
            </li>
            <li class="nav-item">
              <a href="http://localhost/sunny/Module/Admin/src/sidebar/Product/productDashboard.php"
                class="nav-link active">
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
              <h1 class="m-0">Products List</h1>
            </div>
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="http://localhost/sunny/Module/Admin/indexDashboard.php">Home</a>
                </li>
                <li class="breadcrumb-item active">Products</li>
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
            style="background-color: #3C91E6; width: 100px">Add New</button>
          </div>
        </div>
        <div class="row">
          <section class="col-lg-12">
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
                    $sql = "SELECT m.*, i.IngridientName, SUM(i.Stock) AS TotalStock
                FROM `menu` m
                LEFT JOIN `inventory` i ON m.mainIngredient = i.IngridientName AND m.Category = i.Category
                WHERE m.Category = '$category'
                GROUP BY m.ID";
                    $result = mysqli_query($conn, $sql);
                    ?>

                    <style>
                      .custom-table {
                        border: 1px solid #dee2e6;
                        border-collapse: collapse;
                        width: 100%;
                        max-width: 800px;
                        margin: 0 auto;
                        background-color: #fff;
                        border-radius: 5px;
                      }

                      .custom-table th {
                        background-color: #3C91E6;
                        color: white;
                        text-align: center;
                      }

                      .custom-table td {
                        text-align: center;
                        vertical-align: middle;
                      }

                      .custom-table tbody tr:nth-child(odd) {
                        background-color: #f2f2f2;
                      }

                      .custom-table tbody tr:nth-child(even) {
                        background-color: #e6e6e6;
                      }

                      .custom-table img {
                        max-width: 80px;
                        height: auto;
                        display: block;
                        margin: 0 auto;
                      }

                      .custom-table .btn-primary {
                        background-color: #3C91E6;
                        color: white;
                        border: none;
                      }

                      .custom-table .btn-primary:hover {
                        background-color: #2c7ab8;
                      }
                    </style>

                    <table class="table table-hover text-center">
                      <thead class="table">
                        <tr style="background-color:#3C91E6; color:white">
                          <th scope="col">ID</th>
                          <th scope="col">Image</th>
                          <th scope="col">Name of
                            <?php echo $category ?>
                          </th>
                          <th scope="col">Price</th>
                          <th scope="col">Stocks</th>
                          <th scope="col">Ingredients</th>
                          <th scope="col">Category</th>
                          <th scope="col">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($result)) {
                          ?>
                          <tr>
                            <td>
                              <?php echo $row['ID']; ?>
                            </td>
                            <td><img src="src/uploaded_img/<?php echo $row['Image']; ?>" height="50" alt=""></td>
                            <td>
                              <?php echo $row['Name']; ?>
                            </td>
                            <td>
                              <?php echo number_format($row['Price'], 2, '.', ','); ?>
                            </td>
                            <td>
                              <?php echo $row['TotalStock']; ?>
                            </td>
                            <td>
                              <?php echo $row['mainIngredient'] . ', ' . $row['Ingridients']; ?>
                            </td>
                            <td class="category-cell">
                              <?php echo $row["Category"] ?>
                            </td>
                            <td style="white-space: nowrap;">

                              <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#myModalEdit<?php echo $row['ID']; ?>"
                                style="background-color: transparent; border: none;"><i class="fas fa-pencil"
                                  style="color: black;"></i></button>
                              <a href="?delete=<?php echo $row["ID"] ?>" class="link-blue"><i
                                  class="fa-solid fa-trash fs-5"></i></a>

                          <div class="modal" id="myModalEdit<?php echo $row['ID']; ?>">
                            <div class="modal-dialog">
                              <div class="modal-content" style="width: 30vw;">
                                <div class="text-center mb-4">
                                  <h3>Edit Product</h3>
                                  <p class="text-muted">Complete the form below to edit this product</p>
                                </div>
                                <div class="container-xl d-flex justify-content-center">
                                  <form action="" method="post" enctype="multipart/form-data">

                                    <div class="row mb-3" style="display:none;">
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
                                          value="<?php echo $row['Name'] ?>" >
                                      </div>
                                    </div>
                                    <div class="row mb-3">
                                      <div class="col">
                                        <label class="form-label">Price:</label>
                                        <input type="text" class="form-control" name="Price"
                                          value="<?php echo $row['Price'] ?>">
                                      </div>
                                    </div>
                                    <div class="row mb-3" style="display:none;">
                                      <div class="col">
                                        <label class="form-label">Stock:</label>
                                        <input type="text" class="form-control" name="Stocks"
                                          value="<?php echo $row['Stocks'] ?>" readonly>
                                      </div>
                                    </div>
                                    <div class="row mb-3">
                                      <div class="col">
                                        <label class="form-label">Image:</label>
                                        <input type="File" accept="image/png, image/jpeg, image/jpg" id="product_image"
                                          name="product_image">
                                      </div>
                                    </div>
                                    <div class="row mb-3">
                                      <div class="modal-footer">
                                        <button type="submit" class="btn btn-success" name="update_product"
                                          style="background-color:#3C91E6; border:none">Save</button>
                                        <a href="http://localhost/sunny/Module/Admin/src/sidebar/Product/productDashboard.php"
                                          class="btn btn-danger">Cancel</a>
                                      </div>
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
    <div class="modal" id="myModal">
      <div class="modal-dialog">
        <div class="modal-content" style="width:30vw">
          <div class="text-center mb-4">
            <h3>Add a new product</h3>
            <p class="text-muted">Complete the form below to add a new product</p>
          </div>

          <div class="container-xl d-flex justify-content-center">
            <div class="row mb-3">
              <div class="col">
                <form action="" method="post" enctype="multipart/form-data">
                  <div class="row mb-3" style="display:none;">
                    <div class="col">
                      <label class="form-label">ID:</label>
                      <input type="text" class="form-control" name="ID" placeholder="System Generated" readonly>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col">
                      <label class="form-label">Name:</label>
                      <input type="text" class="form-control" placeholder="Product name" name="product_name" class="box"
                        required>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col">
                      <label class="form-label">Price:</label>
                      <input type="number" class="form-control" placeholder="Price" name="product_price" class="box"
                        required>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col">
                      <label class="form-label">Image:</label>
                      <input type="file" class="form-control" accept="image/png, image/jpeg, image/jpg"
                        name="product_image" class="box" required>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col">
                      <label class="form-label">Category:</label>
                      <select class="form-select" name="Category" id="categorySelect" required>
                        <option value="0">Select Category</option>
                        <?php
                        $sql = "SELECT DISTINCT TRIM(Category) AS Category FROM `Category`";
                        $categories = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($categories) > 0) {
                          foreach ($categories as $cat) {
                            echo '<option value="' . $cat['Category'] . '">' . $cat['Category'] . '</option>';
                          }
                        }
                        ?>
                      </select>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col">
                      <div id="ingredientTable" style="display: none;">
                        <table class="table table-bordered text-left">
                          <thead>
                            <tr>
                              <th style="color: black;">Main Product</th>
                              <th style="color: black;">Ingredients</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php
                            $sql = "SELECT DISTINCT TRIM(Category) AS Category FROM `Category`";
                            $categories = mysqli_query($conn, $sql);
                            if (mysqli_num_rows($categories) > 0) {
                              while ($cat = mysqli_fetch_assoc($categories)) {
                                $category = $cat['Category'];
                                echo '<tr class="category-group" style="display: none;" data-category="' . $category . '">';
                                echo '<td>';

                                $sqlMainProduct = "SELECT * FROM Inventory WHERE Category = '$category' AND productType = 'Main'";
                                $mainProducts = mysqli_query($conn, $sqlMainProduct);

                                if (mysqli_num_rows($mainProducts) > 0) {
                                  while ($mainProduct = mysqli_fetch_assoc($mainProducts)) {
                                    echo '<label><input type="radio" name="mainIngredient" value="' . $mainProduct['IngridientName'] . '" data-category="' . $category . '" required> ' . $mainProduct['IngridientName'] . '</label><br>';
                                  }
                                }
                                echo '</td>';
                                echo '<td>';

                                $sql = "SELECT IngridientName FROM Inventory WHERE Category = '$category' AND productType = 'Regular'";
                                $ingredients = mysqli_query($conn, $sql);
                                if (mysqli_num_rows($ingredients) > 0) {
                                  while ($row = mysqli_fetch_assoc($ingredients)) {
                                    echo '<label><input type="checkbox" name="product_ingredient[]" value="' . $row['IngridientName'] . '"> ' . $row['IngridientName'] . '</label><br>';
                                  }
                                }

                                echo '</td>';
                                echo '</tr>';
                              }
                            }
                            ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>

                  <div class="row mb-3">
                    <div class="col">
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-success" name="add_product"
                          style="background-color:#3C91E6; border:none">Save</button>
                        <a href="http://localhost/sunny/Module/Admin/src/sidebar/Product/productDashboard.php  "
                          class="btn btn-danger">Cancel</a>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
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
    document.getElementById('categorySelect').addEventListener('change', function () {
      var selectedCategory = this.value;
      var ingredientTable = document.getElementById('ingredientTable');

      ingredientTable.style.display = 'none';

      var ingredientGroups = document.querySelectorAll('.category-group');
      ingredientGroups.forEach(function (group) {
        group.style.display = 'none';
      });

      if (selectedCategory !== '0') {
        var selectedGroup = document.querySelector('.category-group[data-category="' + selectedCategory + '"]');
        if (selectedGroup) {
          selectedGroup.style.display = 'table-row';
          ingredientTable.style.display = 'table';
        }
      }
    });
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
  <script src="src\sidebar\Product\src\script\scriptProduct.js"></script>
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">
  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
  <script src="src\script\scriptProduct.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0/js/bootstrap-select.min.js"></script>

</body>
</html>