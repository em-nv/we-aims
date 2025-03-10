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

<!-- ADD EMPLOYEE -->
<?php
include 'cus_db.php'; // Include your database connection

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect post data
    $firstName = isset($_POST['employeeFirstName']) ? $_POST['employeeFirstName'] : '';
    $lastName = isset($_POST['employeeLastName']) ? $_POST['employeeLastName'] : '';
    $role = isset($_POST['employeeRole']) ? $_POST['employeeRole'] : '';
    $salary = isset($_POST['employeeSalary']) ? $_POST['employeeSalary'] : '';
    $phone_no = isset($_POST['employeePhone']) ? $_POST['employeePhone'] : '';
    
    // Check if required fields are not empty
    if (!empty($firstName)) {
        $check_query = "SELECT COUNT(*) AS count FROM employees WHERE phone_no = ?";
        if ($stmt = $conn->prepare($check_query)) {
            $stmt->bind_param("s", $phone_no);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $phone_no_count = $row['count'];
            if ($phone_no_count > 0) {
                echo "ERROR: Phone number already exists.";
                exit; // Stop further execution
            }
        }


        // Prepare an insert statement
        $sql = "INSERT INTO employees (first_name, last_name, role, salary, phone_no) VALUES (?, ?, ?, ?, ?)";
        
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssds", $firstName, $lastName, $role, $salary, $phone_no);
            
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
       /*  echo "ERROR: First Name is required."; */
    }
}
?>

<!--EDIT EMPLOYEE-->
<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["editEmployeeId"]) && isset($_POST["editEmployeeFirstName"]) && isset($_POST["editEmployeeLastName"]) && isset($_POST["editEmployeeRole"]) && isset($_POST["editEmployeeSalary"]) && isset($_POST["editEmployeePhone"])) {
    $employee_id = $_POST["editEmployeeId"];
    $firstName = $_POST["editEmployeeFirstName"];
    $lastName = $_POST["editEmployeeLastName"];
    $role = $_POST["editEmployeeRole"];
    $salary = $_POST["editEmployeeSalary"];
    $phone_no = $_POST["editEmployeePhone"];

    // Check if phone number already exists
    $check_query = "SELECT COUNT(*) AS count FROM employees WHERE phone_no = ? AND employee_id != ?";
    if ($stmt = $conn->prepare($check_query)) {
        $stmt->bind_param("si", $phone_no, $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phone_no_count = $row['count'];
        if ($phone_no_count > 0) {
            echo 'ERROR: Phone number already exists.';
            exit; // Stop further execution
        }
    }

    $sql = "UPDATE employees SET first_name=?, last_name=?, role=?, salary=?, phone_no=? WHERE employee_id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssi", $firstName, $lastName, $role, $salary, $phone_no, $employee_id);
        if ($stmt->execute()) {
            // If update successful, redirect to the previous page or show a success message
            header("Location: {$_SERVER['HTTP_REFERER']}");
            exit();
        } else {
            echo "Error updating record: " . $stmt->error;
        }
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

}
?>

<!--DELETE EMPLOYEE-->
<?php
// Put this block at the top of the employees.php file
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['employee_id'])) {
    include 'cus_db.php'; // Include your database connection file
    $employee_id = $_POST['employee_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // First, fetch the data of the employee to be deleted
        $selectSql = "SELECT * FROM employees WHERE employee_id = ?";
        $selectStmt = $conn->prepare($selectSql);
        $selectStmt->bind_param("i", $employee_id);
        $selectStmt->execute();
        $employeeData = $selectStmt->get_result()->fetch_assoc();
        $selectStmt->close();

        if (!$employeeData) {
            throw new Exception("Employee not found.");
        }

        // Insert data into deleted_employees
        $insertSql = "INSERT INTO deleted_employees (employee_id, first_name, last_name, role, salary, phone_no) VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("isssss", $employeeData['employee_id'], $employeeData['first_name'], $employeeData['last_name'], $employeeData['role'], $employeeData['salary'], $employeeData['phone_no']);
        if (!$insertStmt->execute()) {
            throw new Exception("Error logging deletion: " . $insertStmt->error);
        }
        $insertStmt->close();

        // Delete the employee from the employees table
        $deleteStmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
        $deleteStmt->bind_param("i", $employee_id);
        if (!$deleteStmt->execute()) {
            throw new Exception("Error deleting employee: " . $deleteStmt->error);
        }
        $deleteStmt->close();

        // Commit the transaction
        $conn->commit();
        $_SESSION['message'] = "Employee record deleted successfully.";
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        $_SESSION['error'] = "Error deleting employee: " . $e->getMessage();
    }

    $conn->close();

    header("Location: employees.php");
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

    <title>Employees</title>

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
            <li class="nav-item active">
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

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Employees</h1>
                        <div>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addEmployeeModal">
                            <i class="fas fa-solid fa-plus fa-sm text-white-50"></i> Add Employee
                            </a>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#deleteHistoryModal">
                                <i class="fas fa-history fa-sm text-white-50"></i> Delete History
                            </a>
                        </div>
                    </div>

                    <!-- Delete History Modal for Employees -->
                    <div class="modal fade" id="deleteHistoryModal" tabindex="-1" role="dialog" aria-labelledby="deleteHistoryEmployeeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="deleteHistoryEmployeeModalLabel">Employee Delete History</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Employee ID</th>
                                                <th>First Name</th>
                                                <th>Last Name</th>
                                                <th>Role</th>
                                                <th>Salary</th>
                                                <th>Phone Number</th>
                                                <th>Deleted At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            include 'cus_db.php';
                                            $sql = "SELECT * FROM deleted_employees ORDER BY deleted_at DESC";
                                            $result = $conn->query($sql);
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['salary']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['phone_no']) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row['deleted_at']) . "</td>";
                                                    echo "<td><button class='btn btn-warning' onclick='undoDelete(" . $row['employee_id'] . ")'>Undo</button></td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='8'>No deleted records found</td></tr>";
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

                    <!-- MODAL FOR ADDING A EMPLOYEE -->
                    <div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="addEmployeeForm" method="POST" action="employees.php">
                                        <div class="form-group">
                                            <label for="employeeFirstName">First Name</label>
                                            <input type="text" class="form-control" id="employeeFirstName" name="employeeFirstName" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                                            <span id="firstNameError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="employeeLastName">Last Name</label>
                                            <input type="text" class="form-control" id="employeeLastName" name="employeeLastName" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                                            <span id="lastNameError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="employeeRole">Role/Designation</label>
                                            <select class="form-control" id="employeeRole" name="employeeRole">
                                                <option value="Store Manager">Store Manager</option>
                                                <option value="Marketing and Sales Manager">Marketing and Sales Manager</option>
                                                <option value="Sales Associate">Sales Associate</option>
                                                <option value="Inventory Manager">Inventory Manager</option>
                                                <option value="Parts Specialist">Parts Specialist</option>
                                                <option value="Installation Technician">Installation Technician</option>
                                                <option value="Quality Control Inspector">Quality Control Inspector</option>
                                                <option value="Administrative Staff">Administrative Staff</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="employeeSalary">Salary</label>
                                            <input type="text" class="form-control" id="employeeSalary" name="employeeSalary" required>
                                            <span id="salaryError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="employeePhone">Phone Number</label>
                                            <input type="text" class="form-control" id="employeePhone" name="employeePhone" required pattern="[0-9]{11}" title="Phone number must be 11 digits">
                                            <span id="phoneNumberError" class="text-danger"></span>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" form="addEmployeeForm" id="addEmployeeBtn">Add Employee</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- MODAL FOR EDITING (EDIT BUTTON) THE EMPLOYEE -->
                    <div class="modal fade" id="editEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="editEmployeeModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editEmployeeForm" method="POST" action="employees.php">
                                        <div class="form-group">
                                            <label for="editEmployeeFirstName">First Name</label>
                                            <input type="text" class="form-control" id="editEmployeeFirstName" name="editEmployeeFirstName" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                                            <span id="editFirstNameError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="editEmployeeLastName">Last Name</label>
                                            <input type="text" class="form-control" id="editEmployeeLastName" name="editEmployeeLastName" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                                            <span id="editLastNameError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="editEmployeeRole">Role/Designation</label>
                                            <select class="form-control" id="editEmployeeRole" name="editEmployeeRole">
                                                <option value="Store Manager">Store Manager</option>
                                                <option value="Marketing and Sales Manager">Marketing and Sales Manager</option>
                                                <option value="Sales Associate">Sales Associate</option>
                                                <option value="Inventory Manager">Inventory Manager</option>
                                                <option value="Parts Specialist">Parts Specialist</option>
                                                <option value="Installation Technician">Installation Technician</option>
                                                <option value="Quality Control Inspector">Quality Control Inspector</option>
                                                <option value="Administrative Staff">Administrative Staff</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="editEmployeeSalary">Salary</label>
                                            <input type="text" class="form-control" id="editEmployeeSalary" name="editEmployeeSalary" required>
                                            <span id="editSalaryError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="editEmployeePhone">Phone Number</label>
                                            <input type="text" class="form-control" id="editEmployeePhone" name="editEmployeePhone" required pattern="[0-9]{11}" title="Phone number must be 11 digits">
                                            <span id="editPhoneNumberError" class="text-danger"></span>
                                        </div>
                                    
                                        <!-- Hidden field for employee ID -->
                                        <input type="hidden" id="editEmployeeId" name="editEmployeeId">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" form="editEmployeeForm" id="editEmployeeBtn">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- DataTaleS -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 gradient-header" style="display: flex; justify-content: space-between;">
                            <h6 class="m-0 font-weight-bold text-white">Employees List</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>First Name</th>
                                            <th>Last Name</th>
                                            <th>Role/Designation</th>
                                            <th>salary</th>
                                            <th>Phone No.</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        include 'cus_db.php'; // Include your database connection file

                                        // SQL query to select data from database
                                        $sql = "SELECT employee_id, first_name, last_name, role, salary, phone_no FROM employees";
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
                                                    echo "<td>" . $row["employee_id"] . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["first_name"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["last_name"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["role"]) . "</td>";
                                                    echo "<td>Php" . htmlspecialchars(number_format(floatval($row["salary"]), 2)) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["phone_no"]) . "</td>";
                                                    // Edit button form
                                                    echo "<td>
                                                            <button type='button' class='btn btn-success centered-button' data-toggle='modal' data-target='#editEmployeeModal' onclick='setEditFormData(\"" . htmlspecialchars($row["employee_id"]) . "\", \"" . htmlspecialchars($row["first_name"]) . "\", \"" . htmlspecialchars($row["last_name"]) . "\", \"" . htmlspecialchars($row["role"]) . "\", \"" . htmlspecialchars($row["salary"]) . "\", \"" . htmlspecialchars($row["phone_no"]) . "\")'>
                                                                <i class='fa fa-edit'></i>
                                                            </button>
                                                        </td>";
                                                    // Delete button form
                                                    echo "<td>
                                                            <form method='POST' action='employees.php' onsubmit='return confirm(\"Are you sure you want to delete this record?\");'>
                                                                <input type='hidden' name='employee_id' value='" . $row["employee_id"] . "'>
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
    <script src="../js/demo/datatables-demo.js"></script>


    <!-- ADD SUPPLIER VALIDATION -->
    <script>
        $(document).ready(function(){
        $('#addEmployeeBtn').click(function(event){
            event.preventDefault(); // Prevent default form submission
            
            var firstName = $('#employeeFirstName').val();
            var lastName = $('#employeeLastName').val();
            var salary = $('#employeeSalary').val();
            var phone_no = $('#employeePhone').val();

            var namePattern = /^[A-Za-z ]+$/;
            var salaryPattern = /^[0-9.]+$/;
            var phone_noPattern = /^[0-9]{11}$/;

            // Validate First name
            if(firstName.trim() == '' || !namePattern.test(firstName)){
                $('#firstNameError').text('Please enter a valid first name.');
                $('#employeeFirstName').addClass('is-invalid');
                return false;
            } else {
                $('#firstNameError').text('');
                $('#employeeFirstName').removeClass('is-invalid');
            }

            // Validate Last name
            if(lastName.trim() == '' || !namePattern.test(lastName)){
                $('#lastNameError').text('Please enter a valid last name.');
                $('#employeeLastName').addClass('is-invalid');
                return false;
            } else {
                $('#lastNameError').text('');
                $('#employeeLastName').removeClass('is-invalid');
            }

            // Validate salary
            if(salary.trim() == '' || !salaryPattern.test(salary)){
                $('#salaryError').text('Please enter a valid salary.');
                $('#employeeSalary').addClass('is-invalid');
                return false;
            } else {
                $('#salaryError').text('');
                $('#employeeSalary').removeClass('is-invalid');
            }

            // Validate phone number
            if(phone_no.trim() == '' || !phone_noPattern.test(phone_no)){
                $('#phoneNumberError').text('Phone number must be 11 digits.');
                $('#employeePhone').addClass('is-invalid');
                return false;
            } else {
                $('#phoneNumberError').text('');
                $('#employeePhone').removeClass('is-invalid');
            }

            // AJAX request
            $.ajax({
                url: 'emp_check_add_phone_existence.php', // PHP script to check phone number
                type: 'post',
                data: { phone_no: phone_no }, // Corrected field name
                success: function(response){
                    if(response.trim() == 'exists'){ // Added trim to response
                        $('#phoneNumberError').text('Phone number already exists.');
                    } else {
                        $('#phoneNumberError').text('');
                        $('#addEmployeeForm').submit(); // Submit form if phone number is unique
                    }
                },
                error: function(xhr, status, error) {
                    $('#phoneNumberError').text('Error occurred while checking phone number existence.');
                }
            });
        });
    });
    </script>


    <!-- EDIT SUPPLIER VALIDATION -->
    <script>
        $(document).ready(function(){
        $('#editEmployeeBtn').click(function(event){
            event.preventDefault(); // Prevent default form submission
            
            var firstName = $('#editEmployeeFirstName').val();
            var lastName = $('#editEmployeeLastName').val();
            var salary = $('#editEmployeeSalary').val();
            var phone_no = $('#editEmployeePhone').val();

            var namePattern = /^[A-Za-z ]+$/;
            var salaryPattern = /^[0-9.]+$/;
            var phone_noPattern = /^[0-9]{11}$/;

            // Validate First name
            if(firstName.trim() == '' || !namePattern.test(firstName)){
                $('#editFirstNameError').text('Please enter a valid first name.');
                $('#editEmployeeFirstName').addClass('is-invalid');
                return false;
            } else {
                $('#editFirstNameError').text('');
                $('#editEmployeeFirstName').removeClass('is-invalid');
            }

            // Validate Last name
            if(lastName.trim() == '' || !namePattern.test(lastName)){
                $('#editLastNameError').text('Please enter a valid last name.');
                $('#editEmployeeLastName').addClass('is-invalid');
                return false;
            } else {
                $('#editLastNameError').text('');
                $('#editEmployeeLastName').removeClass('is-invalid');
            }

            // Validate salary
            if(salary.trim() == '' || !salaryPattern.test(salary)){
                $('#editSalaryError').text('Please enter a valid salary.');
                $('#editEmployeeSalary').addClass('is-invalid');
                return false;
            } else {
                $('#editSalaryError').text('');
                $('#editEmployeeSalary').removeClass('is-invalid');
            }

            // Validate phone number
            if(phone_no.trim() == '' || !phone_noPattern.test(phone_no)){
                $('#editPhoneNumberError').text('Phone number must be 11 digits.');
                $('#editEmployeePhone').addClass('is-invalid');
                return false;
            } else {
                $('#editPhoneNumberError').text('');
                $('#editEmployeePhone').removeClass('is-invalid');
            }

            // AJAX request
            $.ajax({
                url: 'emp_check_edit_phone_existence.php', // PHP script to check phone number
                type: 'post',
                data: $('#editEmployeeForm').serialize(),
                success: function(response){
                    if(response.trim() == 'exists'){
                        $('#editPhoneNumberError').text('Phone number already exists.');
                    } else {
                        $('#editPhoneNumberError').text('');
                        $('#editEmployeeForm').submit();
                    }
                }
            });
        });
    });
    </script>

    <script>
    function setEditFormData(employee_id, first_name, last_name, role, salary, phone_no) {
        document.getElementById("editEmployeeFirstName").value = first_name;
        document.getElementById("editEmployeeLastName").value = last_name;
        document.getElementById("editEmployeeRole").value = role;
        document.getElementById("editEmployeeSalary").value = salary;
        document.getElementById("editEmployeePhone").value = phone_no;
        document.getElementById("editEmployeeId").value = employee_id;
    }
    </script>

    <!-- RESTORING DELETED EMPLOYEE -->
    <script>
    function undoDelete(employeeId) {
        if (confirm('Are you sure you want to restore this employee?')) {
            $.post('restore_employee.php', { employee_id: employeeId }, function(data) {
                var response = JSON.parse(data);
                alert(response.message);
                if (response.success) {
                    location.reload();  // Refresh the page to reflect changes
                }
            });
        }
    }
    </script>

</body>

</html>