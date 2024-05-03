<?php
include 'cus_db.php'; // Include your database connection

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect post data
    $firstName = isset($_POST['adminFirstName']) ? $_POST['adminFirstName'] : '';
    $lastName = isset($_POST['adminLastName']) ? $_POST['adminLastName'] : '';
    $email = isset($_POST['adminEmail']) ? $_POST['adminEmail'] : '';
    $password = isset($_POST['adminPassword']) ? $_POST['adminPassword'] : '';
    
    // Check if required fields are not empty
    if (!empty($firstName)) {
        // Prepare an insert statement
        $sql = "INSERT INTO admin (fname, lname, email, password) VALUES (?, ?, ?, ?)";
        
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssss", $firstName, $lastName, $email, $password);
            
            // Execute the query
            if($stmt->execute()){
                echo "Records inserted successfully.";
            } else{
                echo "ERROR: Could not execute query: $sql. " . $conn->error;
            }
        } else{
            echo "ERROR: Could not prepare query: $sql. " . $conn->error;
        }
    } else {
        echo "ERROR: First Name is required.";
    }
}
?>

<!--EDIT-->
<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["editAdminId"]) && isset($_POST["editAdminFirstName"]) && isset($_POST["editAdminLastName"]) && isset($_POST["editAdminEmail"]) && isset($_POST["editAdminPassword"])) {
    $admin_id = $_POST["editAdminId"];
    $firstName = $_POST["editAdminFirstName"];
    $lastName = $_POST["editAdminLastName"];
    $email = $_POST["editAdminEmail"];
    $password = $_POST["editAdminPassword"];

    // Update the customer data in the database
    $sql = "UPDATE admin SET fname='$firstName', lname='$lastName', email='$email', password='$password' WHERE admin_id=$admin_id";

    if ($conn->query($sql) === TRUE) {
        // If update successful, redirect to the previous page or show a success message
        header("Location: {$_SERVER['HTTP_REFERER']}");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!--DELETE-->
<?php
// Put this block at the top of the admin.php file
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_id'])) {
    include 'cus_db.php'; // Include your database connection file
    $admin_id = $_POST['admin_id'];

    // Prepare SQL and bind parameters
    $stmt = $conn->prepare("DELETE FROM admin WHERE admin_id = ?");
    $stmt->bind_param("i", $admin_id);
    
    if($stmt->execute()) {
        // Record deleted successfully, you can set a session message here
        $_SESSION['message'] = "Record deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting record: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();

    header("Location: admin.php");
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

    <title>Admin Page</title>

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

            <!-- Nav Item - SALE REPORTS -->
            <li class="nav-item">
                <a class="nav-link" href="pages/salesReport.php">
                    <i class="fas fa-fw fa-solid fa-chart-line"></i>
                    <span>Sales Report</span></a>
            </li>

            <!-- Nav Item - TRANSACTIONS Menu -->
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTransactions"
                    aria-expanded="true" aria-controls="collapseTransactions">
                    <i class="fas fa-fw fa-solid fa-hand-holding-dollar"></i>
                    <span>Transactions</span>
                </a>
                <div id="collapseTransactions" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <!-- <h6 class="collapse-header">Custom Components:</h6> -->
                        <a class="collapse-item" href="buttons.php">Transaction Customer</a>
                        <a class="collapse-item" href="cards.php">Products</a>
                        <a class="collapse-item" href="cards.php">Supplier</a>
                    </div>
                </div>
            </li>

            <!-- Nav Item - ADMIN -->
            <li class="nav-item active">
                <a class="nav-link" href="admin.php">
                    <i class="fas fa-fw fa-solid fa-user-tie"></i>
                    <span>Admin</span></a>
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
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Activity Log
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
                    <h1 class="h3 mb-0 text-gray-800">Admins</h1>
                    <div>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal"
                            data-target="#addAdminModal">
                            <i class="fas fa-user-plus fa-sm text-white-50"></i> Add Admin
                        </a>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
                    </div>
                </div>

        <!-- MODAL FOR ADDING A ADMIN -->
        <div class="modal fade" id="addAdminModal" tabindex="-1" role="dialog" aria-labelledby="addAdminModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header gradient-header">
                        <h5 class="modal-title" id="addAdminModalLabel">Add New Admin</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    <form id="addAdminForm" method="POST" action="admin.php">
                        <div class="form-group">
                            <label for="adminFirstName">First Name</label>
                            <input type="text" class="form-control" id="adminFirstName" name="adminFirstName" required pattern="[A-Za-z]+" title="Only letters allowed">
                        </div>
                        <div class="form-group">
                            <label for="adminLastName">Last Name</label>
                            <input type="text" class="form-control" id="adminLastName" name="adminLastName" required pattern="[A-Za-z]+" title="Only letters allowed">
                        </div>
                        <div class="form-group">
                            <label for="adminEmail">Email Address</label>
                            <input type="email" class="form-control" id="adminEmail" name="adminEmail" required>
                        </div>
                        <div class="form-group">
                            <label for="adminPassword">Password</label>
                            <input type="password" class="form-control" id="adminPassword" name="adminPassword" minlength="8" required>
                        </div>
                    </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="addAdminForm">Add Admin</button>
            </div>
        </div>
    </div>
</div>


<!-- MODAL FOR EDITING (EDIT BUTTON) THE ADMIN -->
<div class="modal fade" id="editAdminModal" tabindex="-1" role="dialog" aria-labelledby="editAdminModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header gradient-header">
                <h5 class="modal-title" id="editAdminModalLabel">Edit Admin</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editAdminForm" method="POST" action="admin.php">
                        <div class="form-group">
                            <label for="editAdminFirstName">First Name</label>
                            <input type="text" class="form-control" id="editAdminFirstName" name="editAdminFirstName" required pattern="[A-Za-z]+" title="Only letters allowed">
                        </div>
                        <div class="form-group">
                            <label for="editAdminLastName">Last Name</label>
                            <input type="text" class="form-control" id="editAdminLastName" name="editAdminLastName" required pattern="[A-Za-z]+" title="Only letters allowed">
                        </div>
                        <div class="form-group">
                            <label for="editAdminEmail">Email Address</label>
                            <input type="email" class="form-control" id="editAdminEmail" name="editAdminEmail" required>
                        </div>
                        <div class="form-group">
                            <label for="editAdminPassword">Password</label>
                            <input type="password" class="form-control" id="editAdminPassword" name="editAdminPassword" minlength="8" required>
                        </div>
                    <!-- Hidden field for admin ID -->
                    <input type="hidden" id="editAdminId" name="editAdminId">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary" form="editAdminForm">Save Changes</button>
            </div>
        </div>
    </div>
</div>

                    <!-- DataTales Example -->
                    <!-- <div class="card shadow mb-4">
                        <div class="card-header py-3" style="display: flex; justify-content: space-between; background-color: transparent !important;">
                            <h6 class="m-0 font-weight-bold text-primary"> </h6>
                            <div class="add-button">
                                <a href="#" class="d-sm-inline-block btn btn-sm btn-primary shadow-sm" ><i
                                    class="fas fa-solid fa-plus fa-sm text-white-50"></i> Add</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Office</th>
                                            <th>Age</th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Office</th>
                                            <th>Age</th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                        <tr>
                                            <td>A Nixon</td>
                                            <td>System Architect</td>
                                            <td>Edinburgh</td>
                                            <td>61</td>
                                            <td class="edit-column"><a href="#"><i class="fa-solid fa-pen"></i></a></td>
                                            <td class="trash-column"><a href="#"><i class="fa-solid fa-trash"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>B Nixon</td>
                                            <td>System FEFG</td>
                                            <td>Edinburgh</td>
                                            <td>61</td>
                                            <td class="edit-column"><a href="#"><i class="fa-solid fa-pen"></i></a></td>
                                            <td class="trash-column"><a href="#"><i class="fa-solid fa-trash"></i></a></td>
                                        </tr>
                                        <tr>
                                            <td>E Nixon</td>
                                            <td>System Architect</td>
                                            <td>Edinburgh</td>
                                            <td>61</td>
                                            <td class="edit-column"><a href="#"><i class="fa-solid fa-pen"></i></a></td>
                                            <td class="trash-column"><a href="#"><i class="fa-solid fa-trash"></i></a></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> -->

                    <div class="card shadow mb-4">
                    <div class="card-header py-3 gradient-header" style="display: flex; justify-content: space-between;">
                        <h6 class="m-0 font-weight-bold text-white">Admin List</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Admin ID</th>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Email</th>
                                        <th>Password</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    include 'cus_db.php'; // Include your database connection file

                                    // SQL query to select data from database
                                    $sql = "SELECT admin_id, fname, lname, email, password FROM admin";
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
                                                echo "<td>" . $row["admin_id"] . "</td>";
                                                echo "<td>" . htmlspecialchars($row["fname"]) . "</td>";
                                                echo "<td>" . htmlspecialchars($row["lname"]) . "</td>";
                                                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                                echo "<td>" . htmlspecialchars($row["password"]) . "</td>";
                                                // Edit button form
                                                echo "<td>
                                                <button type='button' class='btn btn-success centered-button' data-toggle='modal' data-target='#editAdminModal' 
                                                onclick='setEditFormData(\"" . htmlspecialchars($row["admin_id"]) . "\", \"" . htmlspecialchars($row["fname"]) . "\", \"" . htmlspecialchars($row["lname"]) . "\", \"" . htmlspecialchars($row["email"]) . "\", \"" . htmlspecialchars($row["password"]) . "\")'>
                                                    <i class='fa fa-edit'></i> Edit
                                                </button>
                                            </td>";
                                                // Delete button form
                                                echo "<td>
                                                        <form method='POST' action='admin.php' onsubmit='return confirm(\"Are you sure you want to delete this record?\");'>
                                                            <input type='hidden' name='admin_id' value='" . $row["admin_id"] . "'>
                                                            <button type='submit' class='btn btn-danger centered-button'><i class='fa fa-trash'></i> Delete</button>
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
                    <a class="btn btn-primary" href="login.php">Logout</a>
                </div>
            </div>
        </div>
    </div>


    <!-- CUSTOMIZED JS -->
    <script src="../js/customized.js"></script>
    <script src="../js/date-and-time.js"></script>


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
    function setEditFormData(admin_id, fname, lname, email, password) {
    document.getElementById("editAdminFirstName").value = fname;
    document.getElementById("editAdminLastName").value = lname;
    document.getElementById("editAdminEmail").value = email;
    document.getElementById("editAdminPassword").value = password;
    document.getElementById("editAdminId").value = admin_id;
}
</script>

</body>

</html>