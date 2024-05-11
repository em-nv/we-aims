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

<!--ADD-->
<?php
// Include your database connection file
include 'cus_db.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $date = $_POST['date'];
    $productName = $_POST['productName'];
    $quantitySold = $_POST['quantity'];
    $customerName = $_POST['customerName'];

    // Query to get product details including the current stock quantity
    $productQuery = "SELECT productId, retailPrice, quantity FROM products WHERE productName = ?";
    $stmt = $conn->prepare($productQuery);
    if (!$stmt) {
        die("Error: " . $conn->error);
    }
    $stmt->bind_param("s", $productName);
    if (!$stmt->execute()) {
        die("Error executing product query: " . $stmt->error);
    }
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($productId, $retailPrice, $currentQuantity);
        $stmt->fetch();
        $stmt->close();

        // Fetch customer details
        $customerQuery = "SELECT id, paymentMethod FROM customers WHERE firstName = ?";
        $stmt = $conn->prepare($customerQuery);
        if (!$stmt) {
            die("Error: " . $conn->error);
        }
        $stmt->bind_param("s", $customerName);
        if (!$stmt->execute()) {
            die("Error executing customer query: " . $stmt->error);
        }
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($customerId, $customerPaymentMethod);
            $stmt->fetch();
            $stmt->close();

            if ($currentQuantity >= $quantitySold) {
                // Calculate the new quantity
                $newQuantity = $currentQuantity - $quantitySold;

                // Update the products table with the new quantity
                $updateQuery = "UPDATE products SET quantity = ? WHERE productId = ?";
                $updateStmt = $conn->prepare($updateQuery);
                if (!$updateStmt) {
                    die("Error: " . $conn->error);
                }
                $updateStmt->bind_param("ii", $newQuantity, $productId);
                if (!$updateStmt->execute()) {
                    die("Error updating product quantity: " . $updateStmt->error);
                }
                $updateStmt->close();

                // Proceed with inserting the transaction record
                $totalRetailPrice = $retailPrice * $quantitySold;
                $insertQuery = "INSERT INTO transactionspro (date, productId, productName, retailPrice, quantity, totalRetailPrice, customerId, customerName, paymentMethod) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertQuery);
                if (!$stmt) {
                    die("Error: " . $conn->error);
                }
                $stmt->bind_param("sisdsiiss", $date, $productId, $productName, $retailPrice, $quantitySold, $totalRetailPrice, $customerId, $customerName, $customerPaymentMethod);
                if (!$stmt->execute()) {
                    die("Error executing insert query: " . $stmt->error);
                }
                $stmt->close();
                header("Location: product_transactions.php");
                exit;
            } else {
                // Output error message for insufficient stock
                echo "Error: Insufficient stock for product";
            }
        }
    }
}

// Close database connection
$conn->close();
?>

<!--EDIT-->
<?php
// Include your database connection file
include 'cus_db.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if all required fields are set
    if (isset($_POST['ep_transactionId'], $_POST['ep_date'], $_POST['ep_productName'], $_POST['ep_quantity'], $_POST['ep_customerName'])) {
        // Retrieve form data
        $transactionId = $_POST['ep_transactionId'];
        $date = $_POST['ep_date'];
        $productName = $_POST['ep_productName'];
        $quantitySold = $_POST['ep_quantity'];
        $customerId = $_POST['ep_customerName'];

        // Query to get product details including the current stock quantity
        $productQuery = "SELECT productId, retailPrice, quantity FROM products WHERE productId = ?";
        $stmt = $conn->prepare($productQuery);
        if (!$stmt) {
            die("Error: " . $conn->error);
        }
        $stmt->bind_param("i", $productName);
        if (!$stmt->execute()) {
            die("Error executing product query: " . $stmt->error);
        }
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($productId, $retailPrice, $currentQuantity);
            $stmt->fetch();
            $stmt->close();

            if ($currentQuantity >= $quantitySold) {
                // Calculate the new quantity
                $newQuantity = $currentQuantity - $quantitySold;

                // Update the products table with the new quantity
                $updateQuery = "UPDATE products SET quantity = ? WHERE productId = ?";
                $updateStmt = $conn->prepare($updateQuery);
                if (!$updateStmt) {
                    die("Error: " . $conn->error);
                }
                $updateStmt->bind_param("ii", $newQuantity, $productId);
                if (!$updateStmt->execute()) {
                    die("Error updating product quantity: " . $updateStmt->error);
                }
                $updateStmt->close();

                // Update the transaction record
                $totalRetailPrice = $retailPrice * $quantitySold;
                $updateTransactionQuery = "UPDATE transactionspro SET date = ?, productId = ?, retailPrice = ?, quantity = ?, totalRetailPrice = ?, customerId = ? WHERE transactionId = ?";
                $stmt = $conn->prepare($updateTransactionQuery);
                if (!$stmt) {
                    die("Error: " . $conn->error);
                }
                $stmt->bind_param("sisdsii", $date, $productId, $retailPrice, $quantitySold, $totalRetailPrice, $customerId, $transactionId);
                if (!$stmt->execute()) {
                    die("Error executing update query: " . $stmt->error);
                }
                $stmt->close();
                header("Location: product_transactions.php");
                exit;
            } else {
                echo "Error: Insufficient stock for product";
            }
        } else {
            echo "Error: Product not found";
        }
    } 
}

// Close database connection
$conn->close();
?>

<?php
include 'cus_db.php';  // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deletetransId'])) {
    $transId = intval($_POST['deletetransId']);

    // Start transaction
    $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);  // Explicitly start a RW transaction

    try {
        // First, fetch the transaction data to be deleted
        $selectSql = "SELECT t.transactionId, t.productId, t.customerId, t.date, p.productName, p.retailPrice, t.quantity, (p.retailPrice * t.quantity) as totalRetailPrice, c.firstName as customerName, c.paymentMethod FROM transactionspro t JOIN customers c ON t.customerId = c.id JOIN products p ON t.productId = p.productId WHERE transactionId = ?";
        $selectStmt = $conn->prepare($selectSql);
        if (false === $selectStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $selectStmt->bind_param("i", $transId);
        $selectStmt->execute();
        $transactionData = $selectStmt->get_result()->fetch_assoc();
        $selectStmt->close();

        if (!$transactionData) {
            throw new Exception("Transaction not found.");
        }

        // Log the deletion in the deleted_transactions table
        $insertSql = "INSERT INTO deleted_transactions (transactionId, productId, customerId, date, productName, retailPrice, quantity, totalRetailPrice, customerName, paymentMethod, deleted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $insertStmt = $conn->prepare($insertSql);
        if (false === $insertStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $insertStmt->bind_param("iiisssidss", $transactionData['transactionId'], $transactionData['productId'], $transactionData['customerId'], $transactionData['date'], $transactionData['productName'], $transactionData['retailPrice'], $transactionData['quantity'], $transactionData['totalRetailPrice'], $transactionData['customerName'], $transactionData['paymentMethod']);
        $insertStmt->execute();
        $insertStmt->close();

        // Delete the transaction from the transactionspro table
        $deleteSql = "DELETE FROM transactionspro WHERE transactionId = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        if (false === $deleteStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $deleteStmt->bind_param("i", $transId);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Commit the transaction
        $conn->commit();
        echo "Record deleted successfully";
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $conn->close();

    // Redirect back to the transaction products page
    header("Location: product_transactions.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Product Transactions</title>

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

    <style>
    .gradient-header {
        background-image: linear-gradient(to right, #003366, #004080, #0059b3); 
        color: white; /* White text color */
            /* Add this CSS to your existing styles */
    }
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

    .modal-xl {
    max-width: 1400px; 
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
            <li class="nav-item">
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
            <li class="nav-item active">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true"
                    aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-solid fa-hand-holding-dollar"></i>
                    <span>Transactions</span>
                </a>
                <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <!-- <h6 class="collapse-header">Custom Components:</h6> -->
                        <a class="collapse-item active" href="product_transactions.php">Product Transactions</a>
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
                                <!-- Counter - Alerts -->
                                <span class="badge badge-danger badge-counter">3+</span>
                            </a>
                            <!-- Dropdown - Alerts -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header">
                                    Alerts Center
                                </h6>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 12, 2019</div>
                                        <span class="font-weight-bold">A new monthly report is ready to download!</span>
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-success">
                                            <i class="fas fa-donate text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 7, 2019</div>
                                        $290.29 has been deposited into your account!
                                    </div>
                                </a>
                                <a class="dropdown-item d-flex align-items-center" href="#">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-warning">
                                            <i class="fas fa-exclamation-triangle text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">December 2, 2019</div>
                                        Spending Alert: We've noticed unusually high spending for your account.
                                    </div>
                                </a>
                                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
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
                        <h1 class="h3 mb-0 text-gray-800">Products Transactions</h1>
                        <div>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addTransactionModal">
                                <i class="fas fa-solid fa-plus fa-sm text-white-50"></i> New Transaction
                            </a>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#deleteHistoryModal">
                                <i class="fas fa-history fa-sm text-white-50"></i> Delete History
                            </a>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
                            </a>
                        </div>
                    </div>

                    <!-- Delete History Modal for Transaction Products -->
                    <div class="modal fade" id="deleteHistoryModal" tabindex="-1" role="dialog" aria-labelledby="deleteHistoryModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="deleteHistoryModalLabel">Transaction Delete History</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Transaction ID</th>
                                                <th>Date</th>
                                                <th>Product Name</th>
                                                <th>Retail Price</th>
                                                <th>Quantity Sold</th>
                                                <th>Total Retail Sold Price</th>
                                                <th>Customer Name</th>
                                                <th>Payment Method</th>
                                                <th>Deleted At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            include 'cus_db.php';
                                            $historySql = "SELECT * FROM deleted_transactions ORDER BY deleted_at DESC";
                                            $historyResult = $conn->query($historySql);
                                            if ($historyResult && $historyResult->num_rows > 0) {
                                                while ($row = $historyResult->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row['transactionId']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['productName']) . "</td>";
                                                    echo "<td>Php " . htmlspecialchars(number_format($row['retailPrice'], 2)) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                                                    echo "<td>Php " . number_format($row['totalRetailPrice'], 2) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['customerName']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['paymentMethod']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['deleted_at']) . "</td>";
                                                    echo "<td><button class='btn btn-warning' onclick='undoDelete(" . $row['transactionId'] . ")'>Undo</button></td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='10'>No deletion history found</td></tr>";
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

                    <!-- MODAL FOR ADDING A TRANSACTION -->
                    <div class="modal fade" id="addTransactionModal" tabindex="-1" role="dialog" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="addTransactionModalLabel">Add New Transaction</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="addTransactionForm" method="POST" action="product_transactions.php">
                                        <div class="form-group">
                                            <label for="date">Date</label>
                                            <input type="date" class="form-control" id="date" name="date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="productName">Product Name</label>
                                            <select class="form-control" id="productName" name="productName" required onchange="updateProductDetails(this.value);">
                                                <option value="">Select Product</option>
                                                <?php
                                                // PHP snippet for fetching and populating product names
                                                include 'cus_db.php';
                                                $productQuery = "SELECT productId, productName, retailPrice FROM products";
                                                $productResult = $conn->query($productQuery);
                                                if ($productResult->num_rows > 0) {
                                                    while ($product = $productResult->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($product['productName']) . "' data-price='" . htmlspecialchars($product['retailPrice']) . "'>" . htmlspecialchars($product['productName']) . "</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>No products found</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="retailPrice">Retail Price ($)</label>
                                            <input type="text" class="form-control" id="retailPrice" name="retailPrice" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="quantity">Quantity Sold</label>
                                            <input type="number" class="form-control" id="quantity" name="quantity" required onchange="updateTotalRetailPrice()">
                                        </div>
                                        <div class="form-group">
                                            <label for="totalRetailPrice">Total Retail Sold Price ($)</label>
                                            <input type="text" class="form-control" id="totalRetailPrice" name="totalRetailPrice" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="customerName">Customer Name</label>
                                            <select class="form-control" id="customerName" name="customerName" required onchange="updatePaymentMethod(this.value);">
                                                <option value="">Select Customer</option>
                                                <?php
                                                // PHP snippet for fetching and populating customer names
                                                $customerQuery = "SELECT id, firstName, paymentMethod FROM customers";
                                                $customerResult = $conn->query($customerQuery);
                                                if ($customerResult->num_rows > 0) {
                                                    while ($customer = $customerResult->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($customer['firstName']) . "' data-payment-method='" . htmlspecialchars($customer['paymentMethod']) . "'>" . htmlspecialchars($customer['firstName']) . "</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>No customers found</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="paymentMethod">Payment Method</label>
                                            <input type="text" class="form-control" id="paymentMethod" name="paymentMethod" readonly>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" form="addTransactionForm">Add Transaction Product</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL FOR EDITING A TRANSACTION -->
                    <div class="modal fade" id="editTransactionModal" tabindex="-1" role="dialog" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction Products</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editTransactionForm" method="POST" action="product_transactions.php">
                                        <div class="form-group">
                                            <label for="ep_date">Date</label>
                                            <input type="date" class="form-control" id="ep_editdate" name="ep_date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_productName">Product Name</label>
                                            <select class="form-control" id="ep_editproductName" name="ep_productName" required onchange="updateEditProductDetails();">
                                                <option value="">Select Product</option>
                                                <?php
                                                include 'cus_db.php';
                                                $productQuery = "SELECT productId, productName, retailPrice FROM products";
                                                $productResult = $conn->query($productQuery);
                                                if ($productResult->num_rows > 0) {
                                                    while ($product = $productResult->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($product['productId']) . "' data-price='" . htmlspecialchars($product['retailPrice']) . "'>" . htmlspecialchars($product['productName']) . "</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>No products found</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_retailPrice">Retail Price ($)</label>
                                            <input type="text" class="form-control" id="ep_editretailPrice" name="ep_retailPrice" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_quantity">Quantity Sold</label>
                                            <input type="number" class="form-control" id="ep_editquantity" name="ep_quantity" required onchange="updateEditTotalRetailPrice();">
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_totalRetailPrice">Total Retail Sold Price ($)</label>
                                            <input type="text" class="form-control" id="ep_edittotalRetailPrice" name="ep_totalRetailPrice" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_customerName">Customer Name</label>
                                            <select class="form-control" id="ep_editcustomerName" name="ep_customerName" required onchange="updateEditPaymentMethod();">
                                                <option value="">Select Customer</option>
                                                <?php
                                                $customerQuery = "SELECT id, firstName, paymentMethod FROM customers";
                                                $customerResult = $conn->query($customerQuery);
                                                if ($customerResult->num_rows > 0) {
                                                    while ($customer = $customerResult->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($customer['id']) . "' data-payment-method='" . htmlspecialchars($customer['paymentMethod']) . "'>" . htmlspecialchars($customer['firstName']) . "</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>No customers found</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_paymentMethod">Payment Method</label>
                                            <input type="text" class="form-control" id="ep_editpaymentMethod" name="ep_paymentMethod" readonly>
                                        </div>
                                        
                                            <input type="hidden" id="ep_edittransactionId" name="ep_transactionId"> <!-- Hidden field for product ID -->
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" form="editTransactionForm">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable for Products -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 gradient-header" style="display: flex; justify-content: space-between;">
                            <h6 class="m-0 font-weight-bold text-white">Transaction Products List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                            <tr>
                                            <th>TransPro ID</th>
                                            <th>Date</th>
                                            <th>Product Name</th>
                                            <th>Retail Price</th>
                                            <th>Quantity Sold</th>
                                            <th>Total Retail Sold Price</th>
                                            <th>Customer Name</th>
                                            <th>Payment Method</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                            $sql = "SELECT 
                                            t.transactionId AS transId, 
                                            t.date AS transDate, 
                                            p.productName AS productName, 
                                            p.retailPrice AS retailPrice,
                                            t.quantity AS quantity, 
                                            (p.retailPrice * t.quantity) AS totalRetailPrice,
                                            c.firstName AS customerName, 
                                            c.paymentMethod AS paymentMethod
                                            FROM transactionspro t
                                            JOIN customers c ON t.customerId = c.id 
                                            JOIN products p ON t.productId = p.productId";



                                            $result = $conn->query($sql);

                                            if ($result === false) {
                                                echo "Error: " . $conn->error;
                                            } else {
                                                if ($result->num_rows > 0) {
                                                    while ($row = $result->fetch_assoc()) {
                                                        echo "<tr>";
                                                        echo "<td>" . htmlspecialchars($row["transId"]) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row["transDate"]) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row["productName"]) . "</td>";
                                                        echo "<td>Php " . htmlspecialchars(number_format($row["retailPrice"], 2)) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row["quantity"]) . "</td>";
                                                        echo "<td>Php " . number_format($row["totalRetailPrice"], 2) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row["customerName"]) . "</td>";
                                                        echo "<td>" . htmlspecialchars($row["paymentMethod"]) . "</td>";
                                                        echo "<td>
                                                                <button type='button' class='btn btn-success' data-toggle='modal' data-target='#editTransactionModal' onclick='setEditModalValues(\"" . htmlspecialchars($row['transId']) . "\", \"" . htmlspecialchars($row['transDate']) . "\", \"" . htmlspecialchars($row['productName']) . "\", \"" . htmlspecialchars($row['retailPrice']) . "\", \"" . htmlspecialchars($row['quantity']) . "\", \"" . htmlspecialchars($row['customerName']) . "\", \"" . htmlspecialchars($row['paymentMethod']) . "\")'>
                                                                    <i class='fa fa-edit'></i>
                                                                </button>
                                                            </td>";


                                                        echo "<td>
                                                                <form action='product_transactions.php' method='POST' onsubmit='return confirm(\"Are you sure you want to delete this record?\");'>
                                                                    <input type='hidden' name='deletetransId' value='" . htmlspecialchars($row['transId']) . "'/>
                                                                        <button type='submit' class='btn btn-danger'>
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
                        <span>Copyright &copy; Your Website 2020</span>
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
                    <a class="btn btn-primary" href="login.php">Logout</a>
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
        function updateProductDetails(productName) {
            var productSelect = document.getElementById("productName");
            var retailPriceInput = document.getElementById("retailPrice");
            
            // Find the selected option and get its data-price attribute
            var selectedOption = productSelect.options[productSelect.selectedIndex];
            var retailPrice = selectedOption.getAttribute("data-price");
            
            // Update the retail price input field
            retailPriceInput.value = retailPrice;
            
            // Update total retail price
            updateTotalRetailPrice();
        }

        function updateTotalRetailPrice() {
            var quantityInput = document.getElementById("quantity");
            var retailPriceInput = document.getElementById("retailPrice");
            var totalRetailPriceInput = document.getElementById("totalRetailPrice");
            
            // Calculate total retail price
            var quantity = quantityInput.value;
            var retailPrice = retailPriceInput.value;
            var totalRetailPrice = quantity * retailPrice;
            
            // Update the total retail price input field
            totalRetailPriceInput.value = totalRetailPrice.toFixed(7); // Format to two decimal places
        }

        function updatePaymentMethod(customerName) {
            var customerSelect = document.getElementById("customerName");
            var paymentMethodInput = document.getElementById("paymentMethod");
            
            // Find the selected option and get its data-payment-method attribute
            var selectedOption = customerSelect.options[customerSelect.selectedIndex];
            var paymentMethod = selectedOption.getAttribute("data-payment-method");
            
            // Update the payment method input field
            paymentMethodInput.value = paymentMethod;
        }
    </script>

    <script>
    function setEditModalValues(transactionId, date, productName, retailPrice, quantitySold, totalRetailPrice, customerName, customerPaymentMethod) {
        document.getElementById('ep_edittransactionId').value = transactionId;
        document.getElementById('ep_editdate').value = date;

        // Set Product Name
        var productSelect = document.getElementById('ep_editproductName');
        for (var i = 0; i < productSelect.options.length; i++) {
            if (productSelect.options[i].text === productName) {
                productSelect.selectedIndex = i;
                break;
            }
        }
        updateEditProductDetails(); // Call to update retail price and total retail price based on product selection

        // Set Quantity Sold
        document.getElementById('ep_editquantity').value = quantitySold;

        // Set Customer Name
        var customerSelect = document.getElementById('ep_editcustomerName');
        for (var j = 0; j < customerSelect.options.length; j++) {
            if (customerSelect.options[j].text === customerName) {
                customerSelect.selectedIndex = j;
                break;
            }
        }
        updateEditPaymentMethod(); // Call to update payment method based on customer selection
    }

    function updateEditProductDetails() {
        var productSelect = document.getElementById('ep_editproductName');
        var selectedOption = productSelect.options[productSelect.selectedIndex];
        var retailPrice = selectedOption.getAttribute('data-price');
        document.getElementById('ep_editretailPrice').value = retailPrice;
        updateEditTotalRetailPrice();
    }

    function updateEditTotalRetailPrice() {
        var quantityInput = document.getElementById('ep_editquantity');
        var retailPriceInput = document.getElementById('ep_editretailPrice');
        var totalRetailPriceInput = document.getElementById('ep_edittotalRetailPrice');
        var quantity = quantityInput.value || 0;
        var retailPrice = retailPriceInput.value || 0;
        var totalRetailPrice = parseFloat(retailPrice) * parseInt(quantity);
        totalRetailPriceInput.value = totalRetailPrice.toFixed(2);
    }

    function updateEditPaymentMethod() {
        var customerSelect = document.getElementById('ep_editcustomerName');
        var selectedOption = customerSelect.options[customerSelect.selectedIndex];
        var paymentMethod = selectedOption.getAttribute('data-payment-method');
        document.getElementById('ep_editpaymentMethod').value = paymentMethod;
    }
    </script>

    <script>
    function undoDelete(transactionId) {
        if (confirm('Are you sure you want to restore this transaction?')) {
            $.ajax({
                url: 'restore_transpro.php',
                type: 'POST',
                data: { transactionId: transactionId },
                success: function(response) {
                    var data = JSON.parse(response);
                    alert(data.message);
                    if (data.success) {
                        location.reload(); // Reload to update the view
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error restoring transaction: ' + textStatus + ', ' + errorThrown);
                }
            });
        }
    }
    </script>



</body>

</html>