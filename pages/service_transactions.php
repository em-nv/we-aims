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

<?php
// Include your database connection file
include 'cus_db.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $date = $_POST['date'];
    $serviceName = trim($_POST['serviceName']); // Ensure whitespace is removed
    $servicePrice = $_POST['servicePrice']; // Received as read-only from the form
    $customerName = trim($_POST['customerName']); // Ensure whitespace is removed
    $employeeName = trim($_POST['employeeName']); // Ensure whitespace is removed
    $role = $_POST['role']; // Received as read-only from the form
    $paymentMethod = $_POST['paymentMethod']; // Received as read-only from the form

    // Log the values received to help debugging
    error_log("Received serviceName: " . $serviceName);
    error_log("Received customerName: " . $customerName);
    error_log("Received employeeName: " . $employeeName);

    // Query to get service details
    $serviceQuery = "SELECT serviceId FROM services WHERE serviceName = ?";
    $stmt = $conn->prepare($serviceQuery);
    if (!$stmt) {
        die("Error preparing service query: " . $conn->error);
    }
    $stmt->bind_param("s", $serviceName);
    if (!$stmt->execute()) {
        die("Error executing service query: " . $stmt->error);
    }
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($serviceId);
        $stmt->fetch();
        $stmt->close();

        // Fetch customer details
        $customerQuery = "SELECT id FROM customers WHERE firstName = ?";
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
            $stmt->bind_result($customerId);
            $stmt->fetch();
            $stmt->close();

            // Fetch employee details
            $employeeQuery = "SELECT employee_id FROM employees WHERE first_name = ?";
            $stmt = $conn->prepare($employeeQuery);
            if (!$stmt) {
                die("Error: " . $conn->error);
            }
            $stmt->bind_param("s", $employeeName);
            if (!$stmt->execute()) {
                die("Error executing employee query: " . $stmt->error);
            }
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($employeeId);
                $stmt->fetch();
                $stmt->close();

                // Proceed with inserting the transaction record
                $insertQuery = "INSERT INTO transactionsser (date, serviceId, serviceName, servicePrice, customerId, customerName, paymentMethod, employeeId, employeeName, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insertQuery);
                if (!$stmt) {
                    die("Error: " . $conn->error);
                }
                $stmt->bind_param("sissississ", $date, $serviceId, $serviceName, $servicePrice, $customerId, $customerName, $paymentMethod, $employeeId, $employeeName, $role);
                if (!$stmt->execute()) {
                    die("Error executing insert query: " . $stmt->error);
                }
                $stmt->close();
                header("Location: service_transactions.php"); // Adjust the redirection location as needed
                exit;
            } else {
                echo "Error: Employee not found";
            }
        } else {
            echo "Error: Customer not found";
        }
    } else {
        echo "Error: Service not found";
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
if ($_SERVER["REQUEST_METHOD"] == "POST") { // Changed from 'submit' to 'update'
    // Check if all required fields are set
    if (isset($_POST['ep_transactionId'], $_POST['ep_date'], $_POST['ep_serviceName'], $_POST['ep_customerName'], $_POST['ep_employeeName'])) {
        // Retrieve form data
        $transactionId = $_POST['ep_transactionId'];
        $date = $_POST['ep_date'];
        $serviceName = $_POST['ep_serviceName'];
        $customerName = $_POST['ep_customerName'];
        $employeeName = $_POST['ep_employeeName'];

        // Query to get service details including price
        $serviceQuery = "SELECT serviceId, servicePrice FROM services WHERE serviceName = ?";
        $stmt = $conn->prepare($serviceQuery);
        if (!$stmt) {
            die("Error: " . $conn->error);
        }
        $stmt->bind_param("s", $serviceName);
        if (!$stmt->execute()) {
            die("Error executing service query: " . $stmt->error);
        }
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($serviceId, $servicePrice);
            $stmt->fetch();
            $stmt->close();

            // Fetch customer ID
            $customerQuery = "SELECT id FROM customers WHERE firstName = ?";
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
                $stmt->bind_result($customerId);
                $stmt->fetch();
                $stmt->close();

                // Fetch employee ID
                $employeeQuery = "SELECT employee_id FROM employees WHERE first_name = ?";
                $stmt = $conn->prepare($employeeQuery);
                if (!$stmt) {
                    die("Error: " . $conn->error);
                }
                $stmt->bind_param("s", $employeeName);
                if (!$stmt->execute()) {
                    die("Error executing employee query: " . $stmt->error);
                }
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($employeeId);
                    $stmt->fetch();
                    $stmt->close();

                    // Update the transaction record
                    $updateTransactionQuery = "UPDATE transactionsser SET date = ?, serviceId = ?, customerId = ?, employeeId = ? WHERE transactionServiceId = ?";


                    $stmt = $conn->prepare($updateTransactionQuery);
                    if (!$stmt) {
                        die("Error: " . $conn->error);
                    }
                    $stmt->bind_param("siisi", $date, $serviceId, $customerId, $employeeId, $transactionId);

                    if (!$stmt->execute()) {
                        die("Error executing update query: " . $stmt->error);
                    }
                    $stmt->close();

                    header("Location: service_transactions.php"); // Redirect to prevent resubmission
                    exit();

                } else {
                    echo "Error: Employee not found";
                }
            } else {
                echo "Error: Customer not found";
            }
        } else {
            echo "Error: Service not found";
        }
    } else {
        echo "Error: Missing required fields";
    }
}

// Close database connection
$conn->close();
?>




<?php
include 'cus_db.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deletetransSerId'])) {
    $transSerId = intval($_POST['deletetransSerId']); // Ensure the ID is an integer to prevent SQL Injection

    // Start a transaction explicitly for Read and Write
    $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

    try {
        // Fetch the transaction data to be deleted
        $selectSql = "SELECT * FROM transactionsser WHERE transactionServiceId = ?";
        if (!$selectStmt = $conn->prepare($selectSql)) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $selectStmt->bind_param("i", $transSerId);
        $selectStmt->execute();
        $transactionData = $selectStmt->get_result()->fetch_assoc();
        $selectStmt->close();

        if (!$transactionData) {
            throw new Exception("Transaction not found with ID: $transSerId");
        }

        // Log the deletion in the deleted_transactionsser table
        $insertSql = "INSERT INTO deleted_transactionsser (transactionServiceId, serviceId, customerId, employeeId, date, serviceName, servicePrice, customerName, paymentMethod, employeeName, role, deleted_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        if (!$insertStmt = $conn->prepare($insertSql)) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $insertStmt->bind_param("iiiisssssss", 
            $transactionData['transactionServiceId'], 
            $transactionData['serviceId'],
            $transactionData['customerId'],
            $transactionData['employeeId'],
            $transactionData['date'], 
            $transactionData['serviceName'], 
            $transactionData['servicePrice'], 
            $transactionData['customerName'], 
            $transactionData['paymentMethod'], 
            $transactionData['employeeName'], 
            $transactionData['role']);
        $insertStmt->execute();
        $insertStmt->close();

        // Delete the transaction from the transactionsser table
        $deleteSql = "DELETE FROM transactionsser WHERE transactionServiceId = ?";
        if (!$deleteStmt = $conn->prepare($deleteSql)) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $deleteStmt->bind_param("i", $transSerId);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Commit the transaction
        $conn->commit();
        echo "Record deleted successfully";
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    } finally {
        $conn->close();
    }

    // Redirect back to the transactions page to avoid resubmission on refresh
    header("Location: service_transactions.php");
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

    <title>Service Transaction</title>

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
        color: white; /
            
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
                        <a class="collapse-item" href="product_transactions.php">Product Transactions</a>
                        <a class="collapse-item active" href="service_transactions.php">Service Transactions</a>
                    </div>
                </div>
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
                        <h1 class="h3 mb-0 text-gray-800">Service Transactions</h1>
                        <div>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addTransactionModal">
                            <i class="fas fa-solid fa-plus fa-sm text-white-50"></i> Add Transaction
                            </a>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#deleteHistoryTransactionModal">
                                <i class="fas fa-history fa-sm text-white-50"></i> Delete History
                            </a>
                        </div>
                    </div>

                    <!-- Delete History Modal for Transaction Services -->
                    <div class="modal fade" id="deleteHistoryTransactionModal" tabindex="-1" role="dialog" aria-labelledby="deleteHistoryTransactionModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="deleteHistoryTransactionModalLabel">Transaction Services Delete History</h5>
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
                                                <th>Service Name</th>
                                                <th>Service Price</th>
                                                <th>Customer Name</th>
                                                <th>Payment Method</th>
                                                <th>Employee Name</th>
                                                <th>Role/Designation</th>
                                                <th>Deleted At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            include 'cus_db.php';
                                            $sql = "SELECT * FROM deleted_transactionsser ORDER BY deleted_at DESC";
                                            $result = $conn->query($sql);
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row['transactionServiceId']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['serviceName']) . "</td>";
                                                    echo "<td>Php " . htmlspecialchars(number_format($row['servicePrice'], 2)) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['customerName']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['paymentMethod']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['employeeName']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['deleted_at']) . "</td>";
                                                    echo "<td><button class='btn btn-warning' onclick='undoDelete(" . $row['transactionServiceId'] . ")'>Undo</button></td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='10'>No deleted records found</td></tr>";
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
                                    <form id="addTransactionForm" method="POST" action="service_transactions.php">
                                        <div class="form-group">
                                            <label for="date">Date</label>
                                            <input type="date" class="form-control" id="date" name="date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="serviceName">Service Name</label>
                                            <select class="form-control" id="serviceName" name="serviceName" required onchange="updateServicePrice()">
                                                <option value="">Select Service</option>
                                                <?php
                                                include 'cus_db.php';
                                                $serviceQuery = "SELECT serviceId, serviceName, servicePrice FROM services";
                                                $serviceResult = $conn->query($serviceQuery);
                                                if ($serviceResult->num_rows > 0) {
                                                    while ($service = $serviceResult->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($service['serviceName']) . "' data-price='" . htmlspecialchars($service['servicePrice']) . "'>" . htmlspecialchars($service['serviceName']) . "</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>No services found</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="servicePrice">Service Price (Php)</label>
                                            <input type="text" class="form-control" id="servicePrice" name="servicePrice" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="customerName">Customer Name</label>
                                            <select class="form-control" id="customerName" name="customerName" required onchange="updatePaymentMethod()">
                                                <option value="">Select Customer</option>
                                                <?php
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
                                        <div class="form-group">
                                            <label for="employeeName">Employee Name</label>
                                            <select class="form-control" id="employeeName" name="employeeName" required onchange="updateRole()">
                                                <option value="">Select Employee</option>
                                                <?php
                                                $employeeQuery = "SELECT employee_id, first_name, role FROM employees";
                                                $employeeResult = $conn->query($employeeQuery);
                                                if ($employeeResult->num_rows > 0) {
                                                    while ($employee = $employeeResult->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($employee['first_name']) . "' data-role='" . htmlspecialchars($employee['role']) . "'>" . htmlspecialchars($employee['first_name']) . "</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>No employees found</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="role">Role/Designation</label>
                                            <input type="text" class="form-control" id="role" name="role" readonly>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" form="addTransactionForm">Add Transaction Services</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- MODAL FOR EDITING A TRANSACTION -->
                    <div class="modal fade" id="editTransactionModal" tabindex="-1" role="dialog" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="editTransactionModalLabel">Edit Transaction</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="ep_editTransactionForm" method="POST" action="service_transactions.php">
                                        <div class="form-group">
                                            <label for="ep_editDate">Date</label>
                                            <input type="date" class="form-control" id="ep_editDate" name="ep_date" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editServiceName">Service Name</label>
                                            <select class="form-control" id="ep_editServiceName" name="ep_serviceName" required onchange="ep_updateServicePrice()">
                                                <option value="">Select Service</option>
                                                <?php
                                                include 'cus_db.php';
                                                $serviceQuery = "SELECT serviceId, serviceName, servicePrice FROM services";
                                                $serviceResult = $conn->query($serviceQuery);
                                                if ($serviceResult->num_rows > 0) {
                                                    while ($service = $serviceResult->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($service['serviceName']) . "' data-price='" . htmlspecialchars($service['servicePrice']) . "'>" . htmlspecialchars($service['serviceName']) . "</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>No services found</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editServicePrice">Service Price (Php)</label>
                                            <input type="text" class="form-control" id="ep_editServicePrice" name="ep_servicePrice" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editCustomerName">Customer Name</label>
                                            <select class="form-control" id="ep_editCustomerName" name="ep_customerName" required onchange="ep_updatePaymentMethod()">
                                                <option value="">Select Customer</option>
                                                <?php
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
                                            <label for="ep_editPaymentMethod">Payment Method</label>
                                            <input type="text" class="form-control" id="ep_editPaymentMethod" name="ep_paymentMethod" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editEmployeeName">Employee Name</label>
                                            <select class="form-control" id="ep_editEmployeeName" name="ep_employeeName" required onchange="ep_updateRole()">
                                                <option value="">Select Employee</option>
                                                <?php
                                                $employeeQuery = "SELECT employee_id, first_name, role FROM employees";
                                                $employeeResult = $conn->query($employeeQuery);
                                                if ($employeeResult->num_rows > 0) {
                                                    while ($employee = $employeeResult->fetch_assoc()) {
                                                        echo "<option value='" . htmlspecialchars($employee['first_name']) . "' data-role='" . htmlspecialchars($employee['role']) . "'>" . htmlspecialchars($employee['first_name']) . "</option>";
                                                    }
                                                } else {
                                                    echo "<option value=''>No employees found</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editRole">Role/Designation</label>
                                            <input type="text" class="form-control" id="ep_editRole" name="ep_role" readonly>
                                        </div>
                                        <input type="hidden" id="ep_editTransactionId" name="ep_transactionId">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary"  form="ep_editTransactionForm">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable for Products -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 gradient-header" style="display: flex; justify-content: space-between;">
                            <h6 class="m-0 font-weight-bold text-white">Transaction Services List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>TransSer ID</th>
                                            <th>Date</th>
                                            <th>Service Name</th>
                                            <th>Service Price</th>
                                            <th>Customer Name</th>
                                            <th>Payment Method</th>
                                            <th>Employee Name</th>
                                            <th>Role/Designation</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT 
                                            t.transactionServiceId AS transSerId, 
                                            t.date AS transSerDate, 
                                            s.serviceName AS serviceName, 
                                            s.servicePrice AS servicePrice,
                                            c.firstName AS customerName, 
                                            c.paymentMethod AS paymentMethod,
                                            e.first_name AS employeeName,
                                            e.role AS roled
                                        FROM transactionsser t
                                        JOIN services s ON t.serviceId = s.serviceId
                                        JOIN customers c ON t.customerId = c.id 
                                        JOIN employees e ON t.employeeId = e.employee_id";
                                

                                        $result = $conn->query($sql);

                                        if ($result === false) {
                                            echo "Error: " . $conn->error;
                                        } else {
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row["transSerId"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["transSerDate"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["serviceName"]) . "</td>";
                                                    echo "<td>Php " . htmlspecialchars(number_format($row["servicePrice"], 2)) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["customerName"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["paymentMethod"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["employeeName"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["roled"]) . "</td>";
                                                    echo "<td>
                                                            <button type='button' class='btn btn-success centered-button' data-toggle='modal' data-target='#editTransactionModal' onclick='setEditModalValues(\"" . htmlspecialchars($row['transSerId']) . "\", \"" . htmlspecialchars($row['transSerDate']) . "\", \"" . htmlspecialchars($row['serviceName']) . "\", \"" . htmlspecialchars($row['servicePrice']) . "\", \"" . htmlspecialchars($row['customerName']) . "\", \"" . htmlspecialchars($row['paymentMethod']) . "\", \"" . htmlspecialchars($row['employeeName']) . "\", \"" . htmlspecialchars($row['roled']) . "\")'>
                                                                <i class='fa fa-edit'></i>
                                                            </button>
                                                        </td>";
                                                    echo "<td>
                                                            <form action='service_transactions.php' method='POST' onsubmit='return confirm(\"Are you sure you want to delete this record?\");'>
                                                                <input type='hidden' name='deletetransSerId' value='" . htmlspecialchars($row['transSerId']) . "'/>
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
    function setEditModalValues(transactionServiceId, transDate, serviceName, servicePrice, customerName, paymentMethod, employeeName, role) {
        // Set transaction ID
        document.getElementById('ep_editTransactionId').value = transactionServiceId; // example


        // Set the date
        document.getElementById('ep_editDate').value = transDate;

        // Set the service name
        selectDropdownByText('ep_editServiceName', serviceName);

        // Set the service price
        document.getElementById('ep_editServicePrice').value = servicePrice;

        // Set the customer name
        selectDropdownByText('ep_editCustomerName', customerName);

        // Set the payment method
        document.getElementById('ep_editPaymentMethod').value = paymentMethod;

        // Set the employee name
        selectDropdownByText('ep_editEmployeeName', employeeName);

        // Set the role/designation
        document.getElementById('ep_editRole').value = role;
    }

    // Helper function to select a dropdown option based on text content
    function selectDropdownByText(dropdownId, text) {
        var select = document.getElementById(dropdownId);
        for (var i = 0; i < select.options.length; i++) {
            if (select.options[i].text === text) {
                select.selectedIndex = i;
                // Trigger onchange event manually if needed
                if ("createEvent" in document) {
                    var evt = document.createEvent("HTMLEvents");
                    evt.initEvent("change", false, true);
                    select.dispatchEvent(evt);
                } else {
                    select.fireEvent("onchange");
                }
                break;
            }
        }
    }
    </script>

    <script>
    function updateServicePrice() {
        var serviceSelect = document.getElementById('serviceName');
        var servicePrice = document.getElementById('servicePrice');
        servicePrice.value = serviceSelect.options[serviceSelect.selectedIndex].getAttribute('data-price');
    }

    function updatePaymentMethod() {
        var customerSelect = document.getElementById('customerName');
        var paymentMethod = document.getElementById('paymentMethod');
        paymentMethod.value = customerSelect.options[customerSelect.selectedIndex].getAttribute('data-payment-method');
    }

    function updateRole() {
        var employeeSelect = document.getElementById('employeeName');
        var role = document.getElementById('role');
        role.value = employeeSelect.options[employeeSelect.selectedIndex].getAttribute('data-role');
    }
    </script>

    <script>
    function ep_updateServicePrice() {
        var serviceSelect = document.getElementById('ep_editServiceName');
        var servicePrice = document.getElementById('ep_editServicePrice');
        if (serviceSelect.selectedIndex !== -1) { // Check if any service is selected
            var selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
            servicePrice.value = selectedOption.getAttribute('data-price');
        }
    }

    function ep_updatePaymentMethod() {
        var customerSelect = document.getElementById('ep_editCustomerName');
        var paymentMethod = document.getElementById('ep_editPaymentMethod');
        if (customerSelect.selectedIndex !== -1) { // Check if any customer is selected
            var selectedOption = customerSelect.options[customerSelect.selectedIndex];
            paymentMethod.value = selectedOption.getAttribute('data-payment-method');
        }
    }

    function ep_updateRole() {
        var employeeSelect = document.getElementById('ep_editEmployeeName');
        var role = document.getElementById('ep_editRole');
        if (employeeSelect.selectedIndex !== -1) { // Check if any employee is selected
            var selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
            role.value = selectedOption.getAttribute('data-role');
        }
    }
    </script>
    <script>
    function undoDelete(transactionServiceId) {
        console.log("Restoring transaction service with ID:", transactionServiceId); // Debugging to console
        if (confirm("Are you sure you want to restore this transaction?")) {
            $.post('restore_transser.php', { transactionServiceId: transactionServiceId }, function(data) {
                alert(data.message);  // Display response message
                if (data.success) {
                    location.reload(); // Reload the page to update the list of deleted records
                }
            }, 'json').fail(function(xhr, status, error) {
                alert("Error: " + xhr.responseText); // Display technical error message
            });
        }
    }
    </script>


</body>

</html>