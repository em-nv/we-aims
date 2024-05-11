
<!-- ADD SERVICE -->
<?php
include 'cus_db.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['serviceName'], $_POST['employeeId'])) {
    
    // Retrieve service details from form data
    $serviceName = $_POST['serviceName'];
    $timeRequired = $_POST['timeRequired'];
    $servicePrice = $_POST['servicePrice'];
    $employeeId = $_POST['employeeId'];
    $employeeName = $_POST['first_name']; // Assuming 'first_name' comes from the form as a hidden input
    $employeeRole = $_POST['role'];       // Retrieve 'role' from the form data

    // Debugging output
    echo "Service Name: " . $serviceName . "<br>";
    echo "Employee Name: " . $employeeName . "<br>";
    echo "Employee Role: " . $employeeRole; // Displaying role for debugging

    // Prepare the SQL statement for inserting service details
    $sql = "INSERT INTO services (serviceName, timeRequired, servicePrice, employee_id, employee_name, employee_role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiss", $serviceName, $timeRequired, $servicePrice, $employeeId, $employeeName, $employeeRole);

    if ($stmt->execute()) {
        // Redirect to a new page or refresh with a success message
        header("Location: services.php?status=success");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close prepared statement and database connection
    $stmt->close();
    $conn->close();
}
?>


<!--FETCH-->
<?php
include 'cus_db.php'; // Make sure this file contains the correct database connection setup

$employees = [];
$query = "SELECT employee_id, first_name, role FROM employees"; // Assuming 'role' is a column in your 'employees' table
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[$row['employee_id']] = ['first_name' => $row['first_name'], 'role' => $row['role']];
    }
} else {
    echo "Error fetching employees: " . $conn->error;
}

$conn->close();

?>


<!-- DELETE SERVICE -->
<?php
include 'cus_db.php';  // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['deleteServiceId'])) {
    $serviceId = $_POST['deleteServiceId'];

    // Start transaction
    $conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);  // Explicitly start a RW transaction

    try {
        // First, fetch the service data to be deleted
        $selectSql = "SELECT * FROM services WHERE serviceId = ?";
        $selectStmt = $conn->prepare($selectSql);
        if (false === $selectStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $selectStmt->bind_param("i", $serviceId);
        $selectStmt->execute();
        $serviceData = $selectStmt->get_result()->fetch_assoc();
        $selectStmt->close();

        if (!$serviceData) {
            throw new Exception("Service not found.");
        }

        // Log the deletion in the deleted_services table
        $insertSql = "INSERT INTO deleted_services (serviceId, serviceName, employee_id, employee_name, employee_role, timeRequired, servicePrice, delete_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $insertStmt = $conn->prepare($insertSql);
        if (false === $insertStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $insertStmt->bind_param("isisssd", $serviceData['serviceId'], $serviceData['serviceName'], $serviceData['employee_id'], $serviceData['employee_name'], $serviceData['employee_role'], $serviceData['timeRequired'], $serviceData['servicePrice']);
        $insertStmt->execute();
        $insertStmt->close();

        // Delete the service from the services table
        $deleteSql = "DELETE FROM services WHERE serviceId = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        if (false === $deleteStmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $deleteStmt->bind_param("i", $serviceId);
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
    header("Location: services.php"); // Update the redirection as per your URL structure
    exit();
}
?>

<!-- EDIT SERVICE -->
<?php
include 'cus_db.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ep_serviceId"])) {
    // Retrieve service details from the form
    $serviceId = $_POST['ep_serviceId'];
    $employeeId = $_POST['ep_employeeId'];
    $serviceName = $_POST['ep_serviceName'];
    $timeRequired = $_POST['ep_timeRequired'];
    $servicePrice = $_POST['ep_servicePrice'];

    // SQL to update existing service
    $sql = "UPDATE services SET 
            employee_id=?, 
            serviceName=?, 
            timeRequired=?, 
            servicePrice=?
            WHERE serviceId=?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters to the prepared statement
        $stmt->bind_param("issdi", $employeeId, $serviceName, $timeRequired, $servicePrice, $serviceId);

        // Execute the prepared statement to update service information
        if ($stmt->execute()) {
            // Check if any rows were updated
            if ($stmt->affected_rows === 0) {
                echo "No rows updated. Please check the service ID.";
            } else {
                // Redirect to the services page after successful update
                header("Location: services.php");
                exit();
            }
        } else {
            // Handle errors in updating the service table
            echo "Error updating service: " . $stmt->error;
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
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Services</title>

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
    max-width: 1300px; 
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
            <li class="nav-item active">
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
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">Douglas McGee</span>
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
                        <h1 class="h3 mb-0 text-gray-800">Services</h1>
                        <div>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addServiceModal">
                        <i class="fas fa-solid fa-plus fa-sm text-white-50"></i> Add Services
                        </a>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#deleteHistoryModal">
                            <i class="fas fa-history fa-sm text-white-50"></i> Delete History
                        </a>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> Generate Report
                        </a>
                        </div>
                    </div>

                    <!-- Delete History Modal -->
                    <div class="modal fade" id="deleteHistoryModal" tabindex="-1" role="dialog" aria-labelledby="deleteHistoryModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="deleteHistoryModalLabel">Service Delete History</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Service ID</th>
                                                <th>Service Name</th>
                                                <th>Employee ID</th>
                                                <th>Employee Name</th>
                                                <th>Role/Designation</th>
                                                <th>Time Required</th>
                                                <th>Service Price</th>
                                                <th>Deletion At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            include 'cus_db.php';
                                            $historySql = "SELECT * FROM deleted_services ORDER BY delete_at DESC";
                                            $historyResult = $conn->query($historySql);
                                            if ($historyResult && $historyResult->num_rows > 0) {
                                                while ($row = $historyResult->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row['serviceId']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['serviceName']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['employee_role']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['timeRequired']) . "</td>";
                                                    echo "<td>Php " . htmlspecialchars(number_format($row['servicePrice'], 2)) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['delete_at']) . "</td>";
                                                    echo "<td><button class='btn btn-warning' onclick='undoDelete(" . $row['serviceId'] . ")'>Undo</button></td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='9'>No deletion history found</td></tr>";
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




                    <!-- MODAL FOR ADDING A SERVICE -->
                    <div class="modal fade" id="addServiceModal" tabindex="-1" role="dialog" aria-labelledby="addServiceModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="addServiceForm" method="POST" action="services.php">
                                        <div class="form-group">
                                            <label for="serviceName">Service Name</label>
                                            <select class="form-control" id="serviceName" name="serviceName">
                                                <option value="">Select Service Name</option>
                                                <option value="Tire Mounting and Balancing">Tire Mounting and Balancing</option>
                                                <option value="Brake Pad Replacement">Brake Pad Replacement</option>
                                                <option value="Battery Installation">Battery Installation</option>
                                                <option value="Oil Change">Oil Change</option>
                                                <option value="Air Filter Replacement">Air Filter Replacement</option>
                                                <option value="Automatic Tire Changing">Automatic Tire Changing</option>
                                                <option value="Wheel Alignment">Wheel Alignment</option>
                                                <option value="Nitrogen Inflation">Nitrogen Inflation</option>
                                                <option value="Under Chassis Repair">Under Chassis Repair</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="timeRequired">Time Required</label>
                                            <input type="text" class="form-control" id="timeRequired" name="timeRequired" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="servicePrice">Service Price</label>
                                            <input type="number" class="form-control" id="servicePrice" name="servicePrice" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="employeeID">Employee ID</label>
                                            <select class="form-control" id="employeeID" name="employeeId" required onchange="updateEmployeeDetails(this);">
                                                <option value="">Select Employee ID</option>
                                                <?php foreach ($employees as $id => $info) {
                                                    echo "<option value='{$id}' data-first_name='{$info['first_name']}' data-role='{$info['role']}'>{$id} - {$info['first_name']}</option>";
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="first_name">Employee Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" required readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="role">Role/Designation</label>
                                            <input type="text" class="form-control" id="role" name="role" required readonly>
                                        </div>

                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" form="addServiceForm">Add Service</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- MODAL FOR EDITING A PRODUCT -->
                    <div class="modal fade" id="editServiceModal" tabindex="-1" role="dialog" aria-labelledby="editServiceModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editServiceForm" method="POST" action="services.php">
                    
                                        
                                        <div class="form-group">
                                            <label for="ep_editserviceName">Service Name</label>
                                            <select class="form-control" id="ep_editserviceName" name="ep_serviceName">
                                                <option value="">Select Service Name</option>
                                                <option value="Tire Mounting and Balancing">Tire Mounting and Balancing</option>
                                                <option value="Brake Pad Replacement">Brake Pad Replacement</option>
                                                <option value="Battery Installation">Battery Installation</option>
                                                <option value="Oil Change">Oil Change</option>
                                                <option value="Air Filter Replacement">Air Filter Replacement</option>
                                                <option value="Automatic Tire Changing">Automatic Tire Changing</option>
                                                <option value="Wheel Alignment">Wheel Alignment</option>
                                                <option value="Nitrogen Inflation">Nitrogen Inflation</option>
                                                <option value="Under Chassis Repair">Under Chassis Repair</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_edittimeRequired">Time Required</label>
                                            <input type="text" class="form-control" id="ep_edittimeRequired" name="ep_timeRequired" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editservicePrice">Service Price</label>
                                            <input type="number" class="form-control" id="ep_editservicePrice" name="ep_servicePrice" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editemployeeID">Employee ID</label>
                                            <select class="form-control" id="ep_editemployeeID" name="ep_employeeId" required onchange="updateEditEmployeeDetails(this);">
                                                <option value="">Select Employee ID</option>
                                                <?php foreach ($employees as $id => $info) {
                                                    echo "<option value='{$id}' data-first_name='{$info['first_name']}' data-role='{$info['role']}'>{$id} - {$info['first_name']}</option>";
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editfirst_name">Employee Name</label>
                                            <input type="text" class="form-control" id="ep_editfirst_name" name="ep_first_name" required readonly>
                                        </div>
                                        <div class="form-group">
                                            <label for="ep_editrole">Role/Designation</label>
                                            <input type="text" class="form-control" id="ep_editrole" name="ep_role" required readonly>
                                        </div>
                                        <!-- Hidden field for service ID -->
                                        <input type="hidden" id="ep_editServiceId" name="ep_serviceId">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" form="editServiceForm">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable for Products -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 gradient-header" style="display: flex; justify-content: space-between;">
                            <h6 class="m-0 font-weight-bold text-white">Services List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Service ID</th>
                                            <th>Service Name</th>
                                            <th>Employee ID</th>
                                            <th>Employee Name</th>
                                            <th>Role/Designation</th>
                                            <th>Time Required</th>
                                            <th>Service Price</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        include 'cus_db.php'; // Include your database connection file

                                        $sql = "SELECT s.serviceId, s.serviceName, s.timeRequired, s.servicePrice, e.employee_id, e.first_name, e.role
                                        FROM services s
                                        JOIN employees e ON s.employee_id = e.employee_id";

                                        $result = $conn->query($sql);

                                        if ($result === false) {
                                            echo "Error: " . $conn->error;
                                        } else {
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row["serviceId"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["serviceName"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["employee_id"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["first_name"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["role"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["timeRequired"]) . "</td>";
                                                    echo "<td>Php " . htmlspecialchars(number_format($row["servicePrice"], 2)) . "</td>";

                                                    // Edit button
                                                    echo "<td>
                                                            <button type='button' class='btn btn-success' data-toggle='modal' data-target='#editServiceModal' onclick='setEditServiceFormData(\"" . htmlspecialchars($row["serviceId"]) . "\", \"" . htmlspecialchars($row["serviceName"]) . "\", \"" . htmlspecialchars($row["employee_id"]) . "\", \"" . htmlspecialchars($row["first_name"]) . "\", \"" . htmlspecialchars($row["role"]) . "\", \"" . htmlspecialchars($row["timeRequired"]) . "\", " . htmlspecialchars($row["servicePrice"]) . ")'>
                                                                <i class='fa fa-edit'></i>
                                                            </button>
                                                        </td>";


                                                    // Delete button
                                                    echo "<td>
                                                            <form method='POST' action='services.php' onsubmit='return confirm(\"Are you sure you want to delete this service?\");'>
                                                                <input type='hidden' name='deleteServiceId' value='" . $row["serviceId"] . "'>
                                                                <button type='submit' class='btn btn-danger'>
                                                                <i class='fa fa-trash'></i>
                                                                </button>
                                                            </form>
                                                    </td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='9'>No results found</td></tr>";
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
    <script src="../js/demo/datatables-demo.js"></script>

    <script>
    $(document).ready( function () {
        $('#dataTable').DataTable();
    } );
    </script>


    <script>
    function updateEmployeeDetails(selectElement) {
        var selectedOption = selectElement.options[selectElement.selectedIndex];

        if (selectedOption) {
            document.getElementById('first_name').value = selectedOption.getAttribute('data-first_name');
            document.getElementById('role').value = selectedOption.getAttribute('data-role');
        } else {
            document.getElementById('first_name').value = '';
            document.getElementById('role').value = '';
        }
    }
    </script>
    
    <script>
    function updateEditEmployeeDetails(selectElement) {
        var selectedOption = selectElement.options[selectElement.selectedIndex];

        if (selectedOption) {
            // Ensure the IDs match those in the HTML
            document.getElementById('ep_editfirst_name').value = selectedOption.getAttribute('data-first_name');
            document.getElementById('ep_editrole').value = selectedOption.getAttribute('data-role');
        } else {
            document.getElementById('ep_editfirst_name').value = '';
            document.getElementById('ep_editrole').value = '';
        }
    }
    </script>


    <script>
    function setEditServiceFormData(serviceId, serviceName, employeeId, employeeName, role, timeRequired, servicePrice) {
        document.getElementById('ep_editServiceId').value = serviceId;
        document.getElementById('ep_editserviceName').value = serviceName;
        document.getElementById('ep_editemployeeID').value = employeeId;
        document.getElementById('ep_editfirst_name').value = employeeName;
        document.getElementById('ep_editrole').value = role;
        document.getElementById('ep_edittimeRequired').value = timeRequired;
        document.getElementById('ep_editservicePrice').value = servicePrice;
    }
    </script>

    <script>
    function undoDelete(serviceId) {
        console.log("Restoring service with ID:", serviceId);  // Add this to check the ID
        if (confirm("Are you sure you want to restore this service?")) {
            $.post('restore_service.php', { serviceId: serviceId }, function(data) {
                console.log("Response:", data);  // Log the response
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            }, 'json');
        }
    }
    </script>

</body>

</html>