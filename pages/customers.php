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


<!-- ADD CUSTOMER -->
<?php
include 'cus_db.php'; // Include your database connection

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect post data
    $firstName = isset($_POST['customerFirstName']) ? $_POST['customerFirstName'] : '';
    $lastName = isset($_POST['customerLastName']) ? $_POST['customerLastName'] : '';
    $phone = isset($_POST['customerPhone']) ? $_POST['customerPhone'] : '';
    $paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : '';
    
    // Check if required fields are not empty
    if (!empty($firstName)) {
        // Check if phone number already exists
        $check_query = "SELECT COUNT(*) AS count FROM customers WHERE phone = ?";
        if ($stmt = $conn->prepare($check_query)) {
            $stmt->bind_param("s", $phone);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $phone_count = $row['count'];
            if ($phone_count > 0) {
                echo "ERROR: Phone number already exists.";
                exit; // Stop further execution
            }
        }

        // Prepare an insert statement
        $sql = "INSERT INTO customers (firstName, lastName, phone, paymentMethod) VALUES (?, ?, ?, ?)";
        
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssss", $firstName, $lastName, $phone, $paymentMethod);
            
            // Execute the query
            if($stmt->execute()){
                /* echo "Records inserted successfully."; */
            } else{
                /* echo "ERROR: Could not execute query: $sql. " . $conn->error; */
            }
        } else{
            /* echo "ERROR: Could not prepare query: $sql. " . $conn->error; */
        }
    } else {
        /* echo "ERROR: First Name is required."; */
    }
}
?>

<!-- EDIT CUSTOMER -->
<?php 
include 'cus_db.php'; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["editCustomerId"]) && isset($_POST["editCustomerFirstName"]) && isset($_POST["editCustomerLastName"]) && isset($_POST["editCustomerPhone"]) && isset($_POST["editPaymentMethod"])) {
    $id = $_POST["editCustomerId"];
    $firstName = $_POST["editCustomerFirstName"];
    $lastName = $_POST["editCustomerLastName"];
    $phone = $_POST["editCustomerPhone"];
    $paymentMethod = $_POST["editPaymentMethod"];

    // Check if phone number already exists
    $check_query = "SELECT COUNT(*) AS count FROM customers WHERE phone = ? AND id != ?";
    if ($stmt = $conn->prepare($check_query)) {
        $stmt->bind_param("si", $phone, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phone_count = $row['count'];
        if ($phone_count > 0) {
            echo 'ERROR: Phone number already exists.';
            exit; // Stop further execution
        }
    }

    // Update the customer data in the database
    $sql = "UPDATE customers SET firstName='$firstName', lastName='$lastName', phone='$phone', paymentMethod='$paymentMethod' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        // If update successful, redirect to the previous page or show a success message
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!--DELETE CUSTOMER-->
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    include 'cus_db.php'; // Include your database connection file
    $customer_id = $_POST['id'];

    // First, fetch the data of the customer to be deleted
    $query = "SELECT * FROM customers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customerData = $result->fetch_assoc();

    // Insert data into deleted_customers
    $insertQuery = "INSERT INTO deleted_customers (customer_id, firstName, lastName, phone, paymentMethod) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("issss", $customer_id, $customerData['firstName'], $customerData['lastName'], $customerData['phone'], $customerData['paymentMethod']);
    $stmt->execute();

    // Now delete the customer from the customers table
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param("i", $customer_id);
    if($stmt->execute()) {
        $_SESSION['message'] = "Record deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting record: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();

    header("Location: customers.php");
    exit();
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

    <title>Customers</title>

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
    .gradient-header {
        background-color: #016193; 
        color: white;
            
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
            <li class="nav-item active">
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

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Customers</h1>
                        <div>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addCustomerModal">
                                <i class="fas fa-solid fa-plus fa-sm text-white-50"></i> Add Customer
                            </a>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#deleteHistoryModal">
                                <i class="fas fa-history fa-sm text-white-50"></i> Delete History
                            </a>
                        </div>
                    </div>


                    <!-- Delete History Modal with Undo Option -->
                    <div class="modal fade" id="deleteHistoryModal" tabindex="-1" role="dialog" aria-labelledby="deleteHistoryModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="deleteHistoryModalLabel">Delete History</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Customer ID</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Phone</th>
                                                <th>Payment Method</th>
                                                <th>Deleted At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            include 'cus_db.php';
                                            $query = "SELECT * FROM deleted_customers ORDER BY deleted_at DESC";
                                            $result = $conn->query($query);
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . htmlspecialchars($row['customer_id']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['firstName']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['lastName']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['paymentMethod']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['deleted_at']) . "</td>";
                                                echo "<td><button onclick='undoDelete(" . $row['id'] . ")' class='btn btn-warning'>Undo</button></td>";
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


                    <!-- MODAL FOR ADDING A CUSTOMER -->
                    <div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="addCustomerForm" method="POST" action="customers.php">
                                        <div class="form-group">
                                            <label for="customerFirstName">First Name</label>
                                            <input type="text" class="form-control" id="customerFirstName" name="customerFirstName" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                                            <span id="firstNameError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="customerLastName">Last Name</label>
                                            <input type="text" class="form-control" id="customerLastName" name="customerLastName" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                                            <span id="lastNameError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="customerPhone">Phone Number</label>
                                            <input type="text" class="form-control" id="customerPhone" name="customerPhone" required pattern="[0-9]{11}" title="Phone number must be 11 digits">
                                            <span id="phoneError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="paymentMethod">Payment Method</label>
                                            <select class="form-control" id="paymentMethod" name="paymentMethod">
                                                <option value="Credit Card">Credit Card</option>
                                                <option value="PayPal">PayPal</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                            </select>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" id="addCustomerBtn">Add Customer</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- MODAL FOR EDITING (EDIT BUTTON) THE CUSTOMER -->
                    <div class="modal fade" id="editCustomerModal" tabindex="-1" role="dialog" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editCustomerForm" method="POST" action="customers.php">
                                        <div class="form-group">
                                            <label for="editCustomerFirstName">First Name</label>
                                            <input type="text" class="form-control" id="editCustomerFirstName" name="editCustomerFirstName" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                                            <span id="editFirstNameError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="editCustomerLastName">Last Name</label>
                                            <input type="text" class="form-control" id="editCustomerLastName" name="editCustomerLastName" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                                            <span id="editLastNameError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="editCustomerPhone">Phone Number</label>
                                            <input type="text" class="form-control" id="editCustomerPhone" name="editCustomerPhone" required pattern="[0-9]{11}" title="Phone number must be 11 digits">
                                            <span id="editPhoneError" class="text-danger"></span>
                                        </div>

                                        <div class="form-group">
                                            <label for="editPaymentMethod">Payment Method</label>
                                            <select class="form-control" id="editPaymentMethod" name="editPaymentMethod">
                                                <option value="Credit Card">Credit Card</option>
                                                <option value="PayPal">PayPal</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                            </select>
                                        </div>
                                        <!-- Hidden field for customer ID -->
                                        <input type="hidden" id="editCustomerId" name="editCustomerId">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" id="saveEditBtn">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- DataTaleS -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 gradient-header" style="display: flex; justify-content: space-between;">
                            <h6 class="m-0 font-weight-bold text-white">Customers List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Customer ID</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Phone No.</th>
                                            <th>Payment Method</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        include 'cus_db.php'; // Include your database connection file

                                        // SQL query to select data from database
                                        $sql = "SELECT id, firstName, lastName, phone, paymentMethod FROM customers";
                                        $result = $conn->query($sql);

                                        if ($result === false) {
                                            // If the query failed and no result is returned
                                            echo "Error: " . $conn->error;
                                        } else {
                                            // Check if there are rows returned
                                            if ($result->num_rows > 0) {
                                                // Output data of each row
                                                while($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . $row["id"] . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["firstName"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["lastName"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["paymentMethod"]) . "</td>";
                                                    // Edit button form
                                                    echo "<td>
                                                    <button type='button' class='btn btn-success centered-button' data-toggle='modal' data-target='#editCustomerModal' onclick='setEditFormData(\"" . htmlspecialchars($row["id"]) . "\", \"" . htmlspecialchars($row["firstName"]) . "\", \"" . htmlspecialchars($row["lastName"]) . "\", \"" . htmlspecialchars($row["phone"]) . "\", \"" . htmlspecialchars($row["paymentMethod"]) . "\")'>
                                                        <i class='fa fa-edit'></i>
                                                    </button>
                                                </td>";
                                                    // Delete button form
                                                    echo "<td>
                                                            <form method='POST' action='customers.php' onsubmit='return confirm(\"Are you sure you want to delete this record?\");'>
                                                                <input type='hidden' name='id' value='" . $row["id"] . "'>
                                                                <button type='submit' class='btn btn-danger centered-button'>
                                                                    <i class='fa fa-trash'></i>
                                                                </button>
                                                            </form>
                                                        </td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='7'>No results found</td></tr>";
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
    <script src="../js/demo/datatables-demo.js"></script>



    <!-- ADD CUSTOMER VALIDATION -->
    <script>
        $(document).ready(function(){
            $('#addCustomerBtn').click(function(){
                var firstName = $('#customerFirstName').val();
                var lastName = $('#customerLastName').val();
                var phone = $('#customerPhone').val();
                var phonePattern = /^[0-9]{11}$/;
                var namePattern = /^[A-Za-z ]+$/;

                // Validate first name
                if(firstName.trim() == '' || !namePattern.test(firstName)){
                    $('#firstNameError').text('Please enter a valid first name.');
                    $('#customerFirstName').addClass('is-invalid');
                    return false;
                } else {
                    $('#firstNameError').text('');
                    $('#customerFirstName').removeClass('is-invalid');
                }

                // Validate last name
                if(lastName.trim() == '' || !namePattern.test(lastName)){
                    $('#lastNameError').text('Please enter a valid last name.');
                    $('#customerLastName').addClass('is-invalid');
                    return false;
                } else {
                    $('#lastNameError').text('');
                    $('#customerLastName').removeClass('is-invalid');
                }

                // Validate phone number
                if(phone.trim() == '' || !phonePattern.test(phone)){
                    $('#phoneError').text('Phone number must be 11 digits.');
                    $('#customerPhone').addClass('is-invalid');
                    return false;
                } else {
                    $('#phoneError').text('');
                    $('#customerPhone').removeClass('is-invalid');
                }

                // AJAX request
                $.ajax({
                    url: 'cus_check_add_phone_existence.php', // PHP script to check phone number
                    type: 'post',
                    data: $('#addCustomerForm').serialize(),
                    success: function(response){
                        if(response == 'exists'){
                            $('#phoneError').text('Phone number already exists.');
                        } else {
                            $('#phoneError').text('');
                            $('#addCustomerForm').submit();
                        }
                    }
                });
            });
        });
    </script>


    <!-- EDIT CUSTOMER VALIDATION -->
    <script>
        $(document).ready(function(){
            $('#saveEditBtn').click(function(){
                var firstName = $('#editCustomerFirstName').val();
                var lastName = $('#editCustomerLastName').val();
                var phone = $('#editCustomerPhone').val();
                var phonePattern = /^[0-9]{11}$/;
                var namePattern = /^[A-Za-z ]+$/;

                // Validate first name
                if(firstName.trim() == '' || !namePattern.test(firstName)){
                    $('#editFirstNameError').text('Please enter a valid first name.');
                    $('#editCustomerFirstName').addClass('is-invalid');
                    return false;
                } else {
                    $('#editFirstNameError').text('');
                    $('#editCustomerFirstName').removeClass('is-invalid');
                }

                // Validate last name
                if(lastName.trim() == '' || !namePattern.test(lastName)){
                    $('#editLastNameError').text('Please enter a valid last name.');
                    $('#editCustomerLastName').addClass('is-invalid');
                    return false;
                } else {
                    $('#editLastNameError').text('');
                    $('#editCustomerLastName').removeClass('is-invalid');
                }

                // Validate phone number
                if(phone.trim() == '' || !phonePattern.test(phone)){
                    $('#editPhoneError').text('Phone number must be 11 digits.');
                    $('#editCustomerPhone').addClass('is-invalid');
                    return false;
                } else {
                    $('#editPhoneError').text('');
                    $('#editCustomerPhone').removeClass('is-invalid');
                }

                // AJAX request
                $.ajax({
                    url: 'cus_check_edit_phone_existence.php', // PHP script to check phone number
                    type: 'post',
                    data: $('#editCustomerForm').serialize(),
                    success: function(response){
                        if(response == 'exists'){
                            $('#editPhoneError').text('Phone number already exists.');
                        } else {
                            $('#editPhoneError').text('');
                            $('#editCustomerForm').submit();
                        }
                    }
                });
            });
        });
    </script>


    
    <script>
    function setEditFormData(id, firstName, lastName, phone, paymentMethod) {
        document.getElementById("editCustomerFirstName").value = firstName;
        document.getElementById("editCustomerLastName").value = lastName;
        document.getElementById("editCustomerPhone").value = phone;
        document.getElementById("editPaymentMethod").value = paymentMethod;
        document.getElementById("editCustomerId").value = id;
    }
    </script>

    <!-- RESTORING DELETED CUSTOMER -->
    <script>
    function undoDelete(deletedCustomerId) {
        if (confirm('Are you sure you want to undo this deletion?')) {
            $.post('restore_customer.php', { id: deletedCustomerId }, function(data) {
                alert(data.message);
                if (data.success) {
                    location.reload(); // Reload the page to update the table
                }
            }, 'json');
        }
    }
    </script>


</body>
</html>