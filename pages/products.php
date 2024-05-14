<!-- FOR LOGIN -->
<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
}

include ("database_login.php");
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = "SELECT * FROM admin WHERE email = '$email' limit 1";

    $result = mysqli_query($con_login, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
    }
}
?>

<!-- ADD PRODUCT -->
<?php
include 'cus_db.php'; // Include your database connection for suppliers

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['productName'])) {
    
    $supplierID = $_POST['Sup_Id'];
    $companyName = $_POST['companyName'];
    $productName = $_POST['productName'];

    echo "Product Name: " . $productName; // Add debugging statement

    $costPrice = $_POST['costPrice'];
    $retailPrice = $_POST['retailPrice'];
    $quantity = $_POST['quantity'];

    $totalCostPrice = $quantity * $costPrice;
    $totalRetailPrice = $quantity * $retailPrice;
   
   
    // Check if the supplier ID exists in the suppliers table
    $check_supplier_sql = "SELECT Sup_Id FROM suppliers WHERE Sup_Id = ?";
    $check_stmt = $conn->prepare($check_supplier_sql);
    $check_stmt->bind_param("s", $supplierID);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows == 0) {
        echo "Error: Supplier ID does not exist.";
        exit;
    }

    // Prepare the insert statement
    $sql = "INSERT INTO products (Sup_Id, companyName, productName, costPrice, retailPrice, quantity, totalCostPrice, totalRetailPrice) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    

    $stmt->bind_param("sssiiiid", $supplierID, $companyName, $productName, $costPrice, $retailPrice, $quantity, $totalCostPrice, $totalRetailPrice);

    if ($stmt->execute()) {
        // Redirect to a new page or refresh with a success message
        header("Location: products.php?status=success");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close prepared statement
    $stmt->close();
}
?>





<!-- FETCH -->
<?php
include 'cus_db.php'; // Include your database connection for suppliers

// Fetch supplier IDs from the database
$sql_suppliers = "SELECT Sup_Id FROM suppliers";
$result_suppliers = $conn->query($sql_suppliers);
$suppliers = [];
if ($result_suppliers->num_rows > 0) {
    while ($row_supplier = $result_suppliers->fetch_assoc()) {
        $suppliers[] = $row_supplier['Sup_Id'];
    }
}

// Fetch company names from the database
$sql_companies = "SELECT DISTINCT companyName FROM suppliers";
$result_companies = $conn->query($sql_companies);
$companies = [];
if ($result_companies->num_rows > 0) {
    while ($row_company = $result_companies->fetch_assoc()) {
        $companies[] = $row_company["companyName"];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['productName'])) {
    
    // All your POST handling and SQL execution code here
}

?>

<!-- FETCH AGAIN -->
<?php
// Connect to the database
include 'cus_db.php';

// Fetch suppliers
$sql_suppliers = "SELECT Sup_Id, companyName FROM suppliers";
$result_suppliers = $conn->query($sql_suppliers);

// Check if there are any suppliers
if ($result_suppliers->num_rows > 0) {
    // Prepare options for supplier ID and company name dropdowns
    $supplierOptions = "";
    while($row = $result_suppliers->fetch_assoc()) {
        $supplierOptions .= "<option value='" . $row['Sup_Id'] . "'>" . $row['Sup_Id'] . " - " . $row['companyName'] . "</option>";
    }
} else {
    $supplierOptions = "<option value=''>No suppliers found</option>";
}

// Close the connection
$conn->close();
?>


<!-- DELETE PRODUCT -->
<?php
include 'cus_db.php';  // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteProductId'])) {
    $productId = $_POST['deleteProductId'];

    // Start transaction
    $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);  // Explicitly start a RW transaction

    try {
        // First, fetch the product data to be deleted
        $selectSql = "SELECT *, costPrice * quantity AS totalCostPrice, retailPrice * quantity AS totalRetailPrice FROM products WHERE productId = ?";
        $selectStmt = $conn->prepare($selectSql);
        if (false === $selectStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $selectStmt->bind_param("i", $productId);
        $selectStmt->execute();
        $productData = $selectStmt->get_result()->fetch_assoc();
        $selectStmt->close();

        if (!$productData) {
            throw new Exception("Product not found.");
        }

        // Log the deletion in the deleted_products table, including total prices
        $insertSql = "INSERT INTO deleted_products (productId, Sup_Id, companyName, productName, costPrice, retailPrice, quantity, totalCostPrice, totalRetailPrice) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        if (false === $insertStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $insertStmt->bind_param("iisssdddd", $productData['productId'], $productData['Sup_Id'], $productData['companyName'], $productData['productName'], $productData['costPrice'], $productData['retailPrice'], $productData['quantity'], $productData['totalCostPrice'], $productData['totalRetailPrice']);
        $insertStmt->execute();
        $insertStmt->close();

        // Delete the product from the products table
        $deleteSql = "DELETE FROM products WHERE productId = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        if (false === $deleteStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $deleteStmt->bind_param("i", $productId);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Commit the transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
        // Optionally redirect to an error page or rethrow the exception
        // header('Location: error_page.php');
        exit;
    }

    $conn->close();

    // Redirect or update state as necessary
    header("Location: products.php");
    exit();
}
?>


<!-- ERROR AND SUCCESS MESSAGE -->
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success">
    <?= $_SESSION['success_message']; ?>
    <?php unset($_SESSION['success_message']); ?>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger">
    <?= $_SESSION['error_message']; ?>
    <?php unset($_SESSION['error_message']); ?>
</div>
<?php endif; ?>

<!-- FETCH AGAIN WITH SUPPLIERS AND COMPANY -->
<?php
include 'cus_db.php'; // Include your database connection file

// Fetch suppliers and their company names
$sql = "SELECT Sup_Id, companyName FROM suppliers";
$result = $conn->query($sql);
$suppliers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $suppliers[$row['Sup_Id']] = $row['companyName'];
    }
}
$conn->close();
?>


<!-- EDIT PRODUCT -->
<?php
include 'cus_db.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ep_productId"])) {
    // Get product details from the form using the updated name attributes
    $productId = $_POST['ep_productId'];
    $supplierId = $_POST['ep_Sup_Id'];
    $companyName = $_POST['ep_companyName'];
    $productName = $_POST['ep_productName'];
    $costPrice = $_POST['ep_costPrice'];
    $retailPrice = $_POST['ep_retailPrice'];
    $quantity = $_POST['ep_quantity'];
    $totalCostPrice = $_POST['ep_totalCostPrice'];
    $totalRetailPrice = $_POST['ep_totalRetailPrice'];
    

   // SQL to update existing product
$sql = "UPDATE products SET 
Sup_Id=?, 
companyName=?, 
productName=?, 
costPrice=?, 
retailPrice=?, 
quantity=?, 
totalCostPrice=?, 
totalRetailPrice=?

WHERE productId=?";

if ($stmt = $conn->prepare($sql)) {
// Bind parameters to the prepared statement
$stmt->bind_param("issdddddi", $supplierId, $companyName, $productName, $costPrice, $retailPrice, $quantity, $totalCostPrice, $totalRetailPrice, $productId);


        // Execute the prepared statement to update product information
        if ($stmt->execute()) {
            // Check if any rows were updated
            if ($stmt->affected_rows === 0) {
                echo "No rows updated. Please check the product ID.";
            } else {
                // Redirect to the products page after successful update
                header("Location: products.php");
                exit();
            }
        } else {
            // Handle errors in updating the products table
            echo "Error updating product: " . $stmt->error;
        }
        
        // Close the prepared statement
        $stmt->close();
    } else {
        // Handle errors in preparing the SQL query
        echo "ERROR: Could not prepare SQL: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
}
// Removed the echo statement for missing product ID or incorrect form submission
?>





<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Products</title>

    <!-- Custom fonts for this template -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">


    <!-- Custom styles for this template -->
    <link href="../css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom styles for this page -->
    <link href="../vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <!-- FONT AWESOME ICONS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- LORDICONS -->
    <script src="../https://cdn.lordicon.com/lordicon.js"></script>

    <!-- BOXICONS AWESOME ICONS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
    /* .gradient-header {
        background-image: linear-gradient(to right, #003366, #004080, #0059b3); 
        color: white; 
    } */
    .edit-column button i,
    .trash-column button i {
        color: black; /* Set icon color to black */
    }

    .edit-column button:hover {
        background-color: #28a745; /* Change background color to green on hover for edit button */
        border-color: #28a745; /* Change border color to match background color */
    }

    .trash-column button:hover {
        background-color: #dc3545; /* Change background color to red on hover for delete button */
        border-color: #dc3545; /* Change border color to match background color */
    }
    
    </style>    
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="nav-background navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="../index.php">
                <div class="sidebar-brand-icon">
                    <img src="../img/logo/logo.png" class="logo">
                </div>
                <!-- <div class="sidebar-brand-text mx-3">WE-AIMS <sup></sup></div> -->
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                MAIN NAVIGATION
            </div>

            <!-- Nav Item - CUSTOMERS -->
            <li class="nav-item">
                <a class="nav-link" href="../pages/customers.php">
                    <i class="fas fa-fw fa-solid fa-users"></i>
                    <span>Customers</span></a>
            </li>

            <!-- Nav Item - SUPPLIERS -->
            <li class="nav-item">
                <a class="nav-link" href="../pages/suppliers.php">
                    <i class="fas fa-fw fa-solid fa-truck-field"></i>
                    <span>Suppliers</span></a>
            </li>

            <!-- Nav Item - EMPLOYEES -->
            <li class="nav-item">
                <a class="nav-link" href="../pages/employees.php">
                    <i class="fas fa-fw fa-solid fa-building-user"></i>
                    <span>Employees</span></a>
            </li>

            <!-- Nav Item - PRODUCTS -->
            <li class="nav-item active">
                <a class="nav-link" href="../pages/products.php">
                    <i class="fas fa-fw fa-solid fa-dolly"></i>
                    <span>Products</span></a>
            </li>

            <!-- Nav Item - SERVICES -->
            <li class="nav-item">
                <a class="nav-link" href="../pages/services.php">
                    <i class="fas fa-fw fa-solid fa-screwdriver-wrench"></i>
                    <span>Services</span></a>
            </li>

            <!-- Nav Item - SALES REPORT Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSalesReport"
                    aria-expanded="true" aria-controls="collapseSalesReport">
                    <i class="fas fa-fw fa-solid fa-chart-line"></i>
                    <span>Sales Reports</span>
                </a>
                <div id="collapseSalesReport" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <!-- <h6 class="collapse-header">Custom Components:</h6> -->
                        <a class="collapse-item" href="SalesRepPro.php">Product Sales Reports</a>
                        <a class="collapse-item" href="SalesRepSer.php">Service Sales Reports</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - TRANSACTIONS Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTransactions"
                    aria-expanded="true" aria-controls="collapseTransactions">
                    <i class="fas fa-fw fa-solid fa-clock-rotate-left"></i>
                    <span>Transactions</span>
                </a>
                <div id="collapseTransactions" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <!-- <h6 class="collapse-header">Custom Components:</h6> -->
                        <a class="collapse-item" href="product_transactions.php">Product Transactions</a>
                        <a class="collapse-item" href="service_transactions.php">Service Transactions</a>
                    </div>
                </div>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar DATE AND TIME -->
                    <div class="d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="date-and-time">
                            <span class="calendar-logo"><i class='bx bx-calendar'></i></span>
                            <span id="date_now" class="date-now"></span>
                            <span id="current-time" class="time-now"></span>
                        </div>
                    </div>

                    <ul class="navbar-nav ml-auto">

                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-bell fa-fw"></i>
                                
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                            
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Alerts Center
                                </h6>
                                <div class="dropdown-item d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        Hi <?php echo $user_data['fname'];?>, please don't forget to monitor which products are in low supply.
                                    </div>
                                </div>
                                <div class="dropdown-item d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-donate text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        New Arrival Alert: LED Headlights. Get 20% off our new LED headlights. Upgrade today!
                                    </div>
                                </div>
                                <div class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        All brake discs have passed quality checks. Sell with confidence!
                                    </div>
                                </div>
                                
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $user_data['fname']?> <?php echo $user_data['lname']?></span>
                                <img class="img-profile rounded-circle"
                                    src="../img/profile-icons/undraw_pic_profile_re_7g2h.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="admin.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Admin
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Products</h1>
                        <div>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addProductModal">
                            <i class="fas fa-solid fa-plus fa-sm text-white-50"></i> Add Products
                            </a>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#deleteHistoryModal">
                                <i class="fas fa-history fa-sm text-white-50"></i> Delete History
                            </a>
                        </div>
                    </div>

                    <!-- Delete History Modal for Products -->
                    <div class="modal fade" id="deleteHistoryModal" tabindex="-1" role="dialog" aria-labelledby="deleteHistoryProductModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="deleteHistoryProductModalLabel">Product Delete History</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Product ID</th>
                                                <th>Product Name</th>
                                                <th>Supplier ID</th>
                                                <th>Company Name</th>
                                                <th>Cost Price</th>
                                                <th>Retail Price</th>
                                                <th>Quantity</th>
                                                <th>Total Cost Price</th>
                                                <th>Total Retail Price</th>
                                                <th>Deleted At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            include 'cus_db.php';
                                            $sql = "SELECT * FROM deleted_products ORDER BY deleted_at DESC";
                                            $result = $conn->query($sql);
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['productId']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['productName']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['Sup_Id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['companyName']) . "</td>";
                                                echo "<td>Php " . number_format($row['costPrice'], 2) . "</td>";
                                                echo "<td>Php " . number_format($row['retailPrice'], 2) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                                                echo "<td>Php " . number_format($row['totalCostPrice'], 2) . "</td>";
                                                echo "<td>Php " . number_format($row['totalRetailPrice'], 2) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['deleted_at']) . "</td>";
                                                echo "<td><button class='btn btn-warning' onclick='undoDelete(" . $row['productId'] . ")'>Undo</button></td>";
                                                echo "</tr>";
                                            }
                                            $conn->close();
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL FOR ADDING A PRODUCT -->
                    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="addProductForm" method="POST" action="products.php">
                                        <div class="form-group">
                                            <label for="supplierID">Supplier ID</label>
                                            <select class="form-control" id="supplierID" name="Sup_Id" required onchange="updateCompanyName(this.value);">
                                                <option value="">Select Supplier ID</option>
                                                <?php foreach ($suppliers as $supplierID => $companyName) {
                                                    echo "<option value='$supplierID'>$supplierID</option>";
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="companyName">Company Name</label>
                                            <input type="text" class="form-control" id="companyName" name="companyName" required readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="productName">Product Name</label>
                                            <input type="text" class="form-control" id="productName" name="productName" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="costPrice">Cost Price</label>
                                            <input type="text" class="form-control" id="costPrice" name="costPrice" required onchange="validatePrices(); ADDcalculateTotalPrices();">
                                        </div>
                                        <div class="form-group">
                                            <label for="retailPrice">Retail Price</label>
                                            <input type="text" class="form-control" id="retailPrice" name="retailPrice" required onchange="validatePrices(); ADDcalculateTotalPrices();">
                                        </div>
                                        <div class="form-group">
                                            <label for="quantity">Quantity</label>
                                            <input type="number" class="form-control" id="quantity" name="quantity" required onchange="ADDcalculateTotalPrices();">
                                        </div>
                                        <div class="modal-footer">
                                            <div class="form-group">
                                                <label for="totalCostPrice">Total Cost Price</label>
                                                <input type="text" class="form-control" id="totalCostPrice" name="totalCostPrice" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="totalRetailPrice">Total Retail Price</label>
                                                <input type="text" class="form-control" id="totalRetailPrice" name="totalRetailPrice" readonly>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" form="addProductForm">Add Product</button>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- MODAL FOR EDITING A PRODUCT -->
                    <div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editProductForm" method="POST" action="products.php">
                                        <div class="form-group">
                                            <label for="ep_editSupplierID">Supplier ID</label>
                                            <select class="form-control" id="ep_editSupplierID" name="ep_Sup_Id" required onchange="updateEditCompanyName(this.value, 'edit');">
                                                <option value="">Select Supplier ID</option>
                                                <?php foreach ($suppliers as $supplierID => $companyName) {
                                                    echo "<option value='$supplierID'>$supplierID</option>";
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editCompanyName">Company Name</label>
                                            <input type="text" class="form-control" id="ep_editCompanyName" name="ep_companyName" required readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editProductName">Product Name</label>
                                            <input type="text" class="form-control" id="ep_editProductName" name="ep_productName" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editCostPrice">Cost Price</label>
                                            <input type="text" class="form-control" id="ep_editCostPrice" name="ep_costPrice" required onchange="validatePricesEdit(); calculateTotalPrices('edit');">
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editRetailPrice">Retail Price</label>
                                            <input type="text" class="form-control" id="ep_editRetailPrice" name="ep_retailPrice" required onchange="validatePricesEdit(); calculateTotalPrices('edit');">
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editQuantity">Quantity</label>
                                            <input type="number" class="form-control" id="ep_editQuantity" name="ep_quantity" required onchange="calculateTotalPrices('edit');">
                                        </div>
                                        <div class="modal-footer">
                                            <div class="form-group">
                                                <label for="ep_editTotalCostPrice">Total Cost Price</label>
                                                <input type="text" class="form-control" id="ep_editTotalCostPrice" name="ep_totalCostPrice" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="ep_editTotalRetailPrice">Total Retail Price</label>
                                                <input type="text" class="form-control" id="ep_editTotalRetailPrice" name="ep_totalRetailPrice" readonly>
                                            </div>
                                        </div>
                                        <input type="hidden" id="ep_editProductId" name="ep_productId"> <!-- Hidden field for product ID -->
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" form="editProductForm">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable for Products -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 gradient-header" style="display: flex; justify-content: space-between;">
                            <h6 class="m-0 font-weight-bold text-white">Products List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Product ID</th>
                                            <th>Product Name</th>
                                            <th>Supplier ID</th>
                                            <th>Company Name</th>
                                            <th>Cost Price</th>
                                            <th>Retail Price</th>
                                            <th>Quantity</th>
                                            <th>Total Cost Price</th>
                                            <th>Total Retail Price</th>
                                            
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            include 'cus_db.php'; // Include your database connection file

                                            // Fetch products data from the database along with the company name and supplier ID
                                            $sql = "SELECT p.*, s1.companyName AS companyName, s2.Sup_Id AS Sup_Id
                                                    FROM products p 
                                                    JOIN suppliers s1 ON p.companyName = s1.companyName
                                                    JOIN suppliers s2 ON p.Sup_Id = s2.Sup_Id";
                                            $result = $conn->query($sql);


                                            if ($result === false) {
                                                echo "Error: " . $conn->error;
                                            } else {
                                                if ($result->num_rows > 0) {
                                                    while ($row = $result->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td>" . htmlspecialchars($row["productId"]) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row["productName"]) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row["Sup_Id"]) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row["companyName"]) . "</td>";
                                                        echo "<td>Php" . htmlspecialchars(number_format($row["costPrice"], 2)) . "</td>";
                                                        echo "<td>Php" . htmlspecialchars(number_format($row["retailPrice"], 2)) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row["quantity"]) . "</td>";
                                                        echo "<td>Php" . number_format($row["totalCostPrice"], 2) . "</td>";
                                                        
                                                        echo "<td>Php" . number_format($row["totalRetailPrice"], 2) . "</td>";
                                                    
                                                        echo "<td>
                                                                <button type='button' class='btn btn-success centered-button' data-toggle='modal' data-target='#editProductModal'onclick='setEditProductFormData(\"" . htmlspecialchars($row["productId"]) . "\", \"" . htmlspecialchars($row["Sup_Id"]) . "\", \"" . htmlspecialchars($row["companyName"]) . "\", \"" . htmlspecialchars($row["productName"]) . "\", \"" . htmlspecialchars($row["costPrice"]) . "\", \"" . htmlspecialchars($row["retailPrice"]) . "\", \"" . htmlspecialchars($row["quantity"]) . "\")'>
                                                                    <i class='fa fa-edit'></i>
                                                                </button>
                                                            </td>";


                                                        echo "<td>
                                                            <form method='POST' action='products.php' onsubmit='return confirm(\"Are you sure you want to delete this product?\");'>
                                                                <input type='hidden' name='deleteProductId' value='" . $row["productId"] . "'>
                                                                <button type='submit' class='btn btn-danger centered-button'>
                                                                    <i class='fa fa-trash'></i>
                                                                </button>
                                                            </form>
                                                        </td>";
                                                        echo "</tr>";
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='10'>No results found</td></tr>";
                                                }
                                            }
                                            $conn->close();
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; We-AIMS 2024</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>



    <!-- CUSTOMIZED JS -->
    <script src="../js/customized.js"></script>
    <script src="../js/date-and-time.js"></script>


    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

     <!-- Page level plugins -->
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
    $(document).ready( function () {
        $('#dataTable').DataTable();
    } );
    </script>



    <script>
        function ADDcalculateTotalPrices() {
            // Retrieve the values from the input fields and convert them to floating point numbers
            var costPrice = parseFloat(document.getElementById('costPrice').value) || 0; // Using || 0 to handle NaN if the field is empty
            var retailPrice = parseFloat(document.getElementById('retailPrice').value) || 0; // Using || 0 to handle NaN if the field is empty
            var quantity = parseInt(document.getElementById('quantity').value, 10) || 0; // Using || 0 to handle NaN if the field is empty

            // Calculate total prices based on the input values
            var totalRetailPrice = retailPrice * quantity;
            var totalCostPrice = costPrice * quantity;

            // Set the calculated total prices to their respective input fields and format the output to 2 decimal places
            document.getElementById('totalRetailPrice').value = totalRetailPrice.toFixed(7);
            document.getElementById('totalCostPrice').value = totalCostPrice.toFixed(7);
        }
    </script>

    <script>
        function calculateTotalPrices() {
            var editCostPrice = parseFloat(document.getElementById('ep_editCostPrice').value) || 0;
            var editRetailPrice = parseFloat(document.getElementById('ep_editRetailPrice').value) || 0;
            var editQuantity = parseInt(document.getElementById('ep_editQuantity').value, 10) || 0;

            var totalRetailPrice = editRetailPrice * editQuantity;
            var totalCostPrice = editCostPrice * editQuantity;

            document.getElementById('ep_editTotalCostPrice').value = totalCostPrice.toFixed(7);
            document.getElementById('ep_editTotalRetailPrice').value = totalRetailPrice.toFixed(7);
        
        }

    </script>

    <script>
        //para automatic
        // JavaScript to update company name based on selected supplier ID
        function updateCompanyName(supplierID) {
            const suppliers = <?php echo json_encode($suppliers); ?>;
            const companyNameInput = document.getElementById('companyName');
            companyNameInput.value = suppliers[supplierID] || ''; // Update the company name based on supplier ID or clear if not found
        }
    </script>

    <script>
        function updateEditCompanyName(supplierID) {
            const suppliers = <?php echo json_encode($suppliers); ?>;
            const companyNameInput = document.getElementById('ep_editCompanyName');
            companyNameInput.value = suppliers[supplierID] || ''; // Automatically fill in the company name based on the supplier ID
        }
    </script>


    <script>
        function setEditProductFormData(productId, supplierId, companyName, productName, costPrice, retailPrice, quantity, totalCostPrice, totalRetailPrice) {
            document.getElementById('ep_editProductId').value = productId;
            document.getElementById('ep_editSupplierID').value = supplierId;
            document.getElementById('ep_editCompanyName').value = companyName;
            document.getElementById('ep_editProductName').value = productName;
            document.getElementById('ep_editCostPrice').value = costPrice;
            document.getElementById('ep_editRetailPrice').value = retailPrice;
            document.getElementById('ep_editQuantity').value = quantity;
            document.getElementById('ep_totalCostPrice').value = totalCostPrice;
            document.getElementById('ep_totalRetailPrice').value = totalRetailPrice;

        }

    </script>



    <!-- SCRIPT FOR VALIDATING PRICES -->
    <script>
    function validatePrices() {
        var costPrice = parseFloat(document.getElementById('costPrice').value);
        var retailPrice = parseFloat(document.getElementById('retailPrice').value);

        if (retailPrice < costPrice) {
            alert("Retail price cannot be less than the cost price.");
            document.getElementById('retailPrice').value = costPrice;
        }
    }
    </script>

    <script>
    function validatePricesEdit() {
        var ep_editCostPrice = parseFloat(document.getElementById('ep_editCostPrice').value);
        var ep_editRetailPrice = parseFloat(document.getElementById('ep_editRetailPrice').value);

        if (ep_editRetailPrice < ep_editCostPrice) {
            alert("Retail price cannot be less than the cost price.");
            document.getElementById('ep_editRetailPrice').value = ep_editCostPrice;
        }
    }
    </script>

    <!-- RESTORING DELETED PRODUCT -->
    <script>
    function undoDelete(productId) {
        if (confirm('Are you sure you want to restore this product?')) {
            $.ajax({
                url: 'restore_product.php',
                type: 'POST',
                data: { productId: productId },
                success: function(response) {
                    var data = JSON.parse(response);
                    alert(data.message);
                    if (data.success) {
                        location.reload(); // Reload to update the view
                    }
                },
                error: function() {
                    alert('Error restoring product.');
                }
            });
        }
    }
    </script>

</body>

</html>