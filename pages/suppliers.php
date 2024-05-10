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

<!--ADD SUPPLIER-->
<?php
include 'cus_db.php'; // Include your database connection for suppliers

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $companyName = isset($_POST['supplierCompanyName']) ? $_POST['supplierCompanyName'] : '';
    $province = isset($_POST['supplierProvince']) ? $_POST['supplierProvince'] : '';
    $city = isset($_POST['supplierCity']) ? $_POST['supplierCity'] : '';
    $zipCode = isset($_POST['supplierZipCode']) ? $_POST['supplierZipCode'] : '';
    $phoneNumber = isset($_POST['supplierPhoneNumber']) ? $_POST['supplierPhoneNumber'] : '';

    if (!empty($companyName)){
        $check_query = "SELECT COUNT(*) AS count FROM suppliers WHERE phoneNumber = ?";
        if ($stmt = $conn->prepare($check_query)) {
            $stmt->bind_param("s", $phoneNumber);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $phoneNumber_count = $row['count'];
            if ($phoneNumber_count > 0) {
                echo "ERROR: Phone number already exists.";
                exit; // Stop further execution
            }
        }

        $sql = "INSERT INTO suppliers (companyName, province, city, zipCode, phoneNumber) VALUES (?, ?, ?, ?, ?)";

        if($stmt = $conn->prepare($sql)){
            
            $stmt->bind_param("sssss", $companyName, $province, $city, $zipCode, $phoneNumber);
            
            if($stmt->execute()){
                echo "Records inserted successfully.";
            } else{
                echo "ERROR: Could not execute query: $sql. " . $conn->error;
            }
        } else{
            echo "ERROR: Could not prepare query: $sql. " . $conn->error;
        }
    
    } else {
        echo "ERROR: Company Name is required.";
    }
}
?>


<!-- EDIT SUPPLIER -->
<?php 
include 'cus_db.php'; // Include your database connection

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["editSupplierId"])) {
    // Retrieve the old Supplier ID from the form
    $supplier_id = $_POST["editSupplierId"];
    
    // Retrieve other updated supplier information from the form
    $companyName = $_POST["editSupplierCompanyName"];
    $province = $_POST["editSupplierProvince"];
    $city = $_POST["editSupplierCity"];
    $zipCode = $_POST["editSupplierZipCode"];
    $phoneNumber = $_POST["editSupplierPhoneNumber"];

    // Check if phone number already exists
    $check_query = "SELECT COUNT(*) AS count FROM suppliers WHERE phoneNumber = ? AND Sup_Id != ?";
    if ($stmt = $conn->prepare($check_query)) {
        $stmt->bind_param("si", $phoneNumber, $supplier_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $phoneNumber_count = $row['count'];
        if ($phoneNumber_count > 0) {
            echo 'ERROR: Phone number already exists.';
            exit; // Stop further execution
        }
    }

    // Prepare the SQL query to update supplier information in the suppliers table
    $sql = "UPDATE suppliers SET companyName=?, province=?, city=?, zipCode=?, phoneNumber=? WHERE Sup_Id=?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssi", $companyName, $province, $city, $zipCode, $phoneNumber, $supplier_id);
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


<!-- DELETE SUPPLIERS -->
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['supplierId'])) {
    include 'cus_db.php'; // Ensure this file correctly initializes the database connection.
    $supplier_id = $_POST['supplierId'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Prepare and execute the deletion of the supplier.
        $stmt = $conn->prepare("DELETE FROM suppliers WHERE Sup_Id = ?");
        $stmt->bind_param("i", $supplier_id);
        if (!$stmt->execute()) {
            throw new Exception("Error deleting supplier: " . $stmt->error);
        }
        $stmt->close();

        // If everything is fine, commit the transaction
        $conn->commit();
        $_SESSION['message'] = "Supplier record deleted successfully";
    } catch (Exception $e) {
        // If an error occurs, roll back the transaction and save the error message in session
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    // Close the connection
    $conn->close();

    // Redirect to suppliers page
    header("Location: suppliers.php");
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

    <title>Suppliers</title>

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
            <li class="nav-item active">
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
                        <a class="collapse-item" href="">Customer Transactions</a>
                        <a class="collapse-item" href="">Product Transactions</a>
                        <a class="collapse-item" href="">Supplier Transactions</a>
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
                        <h1 class="h3 mb-0 text-gray-800">Suppliers</h1>
                        <div>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal"
                                data-target="#addSupplierModal">
                                <i class="fas fa-user-plus fa-sm text-white-50"></i> Add Supplier
                            </a>
                            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                    class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
                        </div>
                    </div>

                    <!-- MODAL FOR ADDING A SUPPLIER -->
                    <div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="addSupplierModalLabel">Add New Supplier</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                     <form id="addSupplierForm" method="POST" action="suppliers.php">
                                        <div class="form-group">
                                            <label for="supplierCompanyName">Company Name</label>
                                            <input type="text" class="form-control" id="supplierCompanyName" name="supplierCompanyName" required>
                                            <span id="companyNameError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="supplierProvince">Province</label>
                                            <input type="text" class="form-control" id="supplierProvince" name="supplierProvince" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                                            <span id="provinceError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="supplierCity">City/Municipality</label>
                                            <input type="text" class="form-control" id="supplierCity" name="supplierCity" required pattern="[A-Za-z -]+" title="Only letters, spaces, and dashes allowed">
                                            <span id="cityError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="supplierZipCode">Zip Code</label>
                                            <input type="text" class="form-control" id="supplierZipCode" name="supplierZipCode" required>
                                            <span id="zipCodeError" class="text-danger"></span>
                                        </div>
                                        <div class="form-group">
                                            <label for="supplierPhoneNumber">Phone Number</label>
                                            <input type="text" class="form-control" id="supplierPhoneNumber" name="supplierPhoneNumber" required pattern="[0-9]{11}" title="Phone number must be 11 digits">
                                            <span id="phoneNumberError" class="text-danger"></span>
                                        </div>
                                        
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" id="addSupplierBtn">Add Supplier</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL FOR EDITING A SUPPLIER -->
                    <div class="modal fade" id="editSupplierModal" tabindex="-1" role="dialog" aria-labelledby="editSupplierModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header gradient-header">
                                    <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                <form id="editSupplierForm" method="POST" action="suppliers.php">
                                <div class="form-group">
                                    <label for="editSupplierCompanyName">Company Name</label>
                                    <input type="text" class="form-control" id="editSupplierCompanyName" name="editSupplierCompanyName" required>
                                    <span id="editCompanyNameError" class="text-danger"></span>
                                </div>
                                <div class="form-group">
                                    <label for="editSupplierProvince">Province</label>
                                    <input type="text" class="form-control" id="editSupplierProvince" name="editSupplierProvince" required pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                                    <span id="editProvinceError" class="text-danger"></span>
                                </div>
                                <div class="form-group">
                                    <label for="editSupplierCity">City/Municipality</label>
                                    <input type="text" class="form-control" id="editSupplierCity" name="editSupplierCity" required pattern="[A-Za-z -]+" title="Only letters and spaces allowed">
                                    <span id="editCityError" class="text-danger"></span>
                                </div>
                                <div class="form-group">
                                    <label for="editSupplierZipCode">Zip Code</label>
                                    <input type="text" class="form-control" id="editSupplierZipCode" name="editSupplierZipCode" required pattern="[0-9]{4}" title="Phone number must be 4 digits">
                                    <span id="editZipCodeError" class="text-danger"></span>
                                </div>
                                <div class="form-group">
                                    <label for="editSupplierPhoneNumber">Phone Number</label>
                                    <input type="text" class="form-control" id="editSupplierPhoneNumber" name="editSupplierPhoneNumber" required pattern="[0-9]{11}" title="Phone number must be 11 digits">
                                    <span id="editPhoneNumberError" class="text-danger"></span>
                                </div>
                            
                                <!-- Hidden field for supplier ID -->
                                <input type="hidden" id="editSupplierId" name="editSupplierId">
                                </form>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary" id="saveCustomerBtn">Save Changes</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- DataTable for Suppliers -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 gradient-header" style="display: flex; justify-content: space-between;">
                            <h6 class="m-0 font-weight-bold text-white">Suppliers List</h6>
                            
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Supplier ID</th>
                                            <th>Company Name</th>
                                            <th>Province</th>
                                            <th>City/Municipality</th>
                                            <th>Zip Code</th>
                                            <th>Phone Number</th>
                                            <th>Edit</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        include 'cus_db.php'; // Adjust to your suppliers database connection file

                                        // SQL query to select data from the suppliers table
                                        $sql = "SELECT Sup_Id, companyName, province, city, zipCode, phoneNumber FROM suppliers";
                                        $result = $conn->query($sql);

                                        if ($result === false) {
                                            echo "Error: " . $conn->error;
                                        } else {
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($row["Sup_Id"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["companyName"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["province"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["city"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["zipCode"]) . "</td>";
                                                    echo "<td>" . htmlspecialchars($row["phoneNumber"]) . "</td>";
                                                    echo "<td>
                                                            <button type='button' class='btn btn-success centered-button' data-toggle='modal' data-target='#editSupplierModal' onclick='setEditSupplierFormData(\"" . htmlspecialchars($row["Sup_Id"]) . "\", \"" . htmlspecialchars($row["companyName"]) . "\", \"" . htmlspecialchars($row["province"]) . "\", \"" . htmlspecialchars($row["city"]) . "\", \"" . htmlspecialchars($row["zipCode"]) . "\", \"" . htmlspecialchars($row["phoneNumber"]) . "\")'>
                                                                <i class='fa fa-edit'></i>
                                                            </button>
                                                        </td>";

                                                    echo "<td>
                                                            <form method='POST' action='suppliers.php' onsubmit='return confirm(\"Are you sure you want to delete this record?\");'>
                                                                <input type='hidden' name='supplierId' value='" . $row["Sup_Id"] . "'>
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


    <!-- ADD SUPPLIER VALIDATION -->
    <script>
        $(document).ready(function(){
            $('#addSupplierBtn').click(function(){
                var companyName = $('#supplierCompanyName').val();
                var province = $('#supplierProvince').val();
                var city = $('#supplierCity').val();
                var zipCode = $('#supplierZipCode').val();
                var phoneNumber = $('#supplierPhoneNumber').val();

                var companyNamePattern = /^[A-Za-z !"#$%&'()*+,\-./:;<=>?@[\\\]^_`{|}~]+$/;
                var addressPattern = /^[A-Za-z\- ]+$/;
                var zipCodePattern = /^\d{4}$/;
                var phoneNumberPattern = /^[0-9]{11}$/;

                // Validate Company name
                if(companyName.trim() == '' || !companyNamePattern.test(companyName)){
                    $('#companyNameError').text('Please enter a valid company name.');
                    $('#supplierCompanyName').addClass('is-invalid');
                    return false;
                } else {
                    $('#companyNameError').text('');
                    $('#supplierCompanyName').removeClass('is-invalid');
                }

                // Validate PROVINCE
                if(province.trim() == '' || !addressPattern.test(province)){
                    $('#provinceError').text('Please enter a valid province.');
                    $('#supplierProvince').addClass('is-invalid');
                    return false;
                } else {
                    $('#provinceError').text('');
                    $('#supplierProvince').removeClass('is-invalid');
                }

                // Validate CITY
                if(city.trim() == '' || !addressPattern.test(city)){
                    $('#cityError').text('Please enter a valid city.');
                    $('#supplierCity').addClass('is-invalid');
                    return false;
                } else {
                    $('#cityError').text('');
                    $('#supplierCity').removeClass('is-invalid');
                }

                // Validate ZIP CODE
                if(zipCode.trim() == '' || !zipCodePattern.test(zipCode)){
                    $('#zipCodeError').text('Please enter a valid zip code.');
                    $('#supplierZipCode').addClass('is-invalid');
                    return false;
                } else {
                    $('#zipCodeError').text('');
                    $('#supplierZipCode').removeClass('is-invalid');
                }

                // Validate phone number
                if(phoneNumber.trim() == '' || !phoneNumberPattern.test(phoneNumber)){
                    $('#phoneNumberError').text('Phone number must be 11 digits.');
                    $('#customerPhoneNumber').addClass('is-invalid');
                    return false;
                } else {
                    $('#phoneNumberError').text('');
                    $('#customerPhoneNumber').removeClass('is-invalid');
                }

                // AJAX request
                $.ajax({
                    url: 'sup_check_add_phone_existence.php', // PHP script to check phone number
                    type: 'post',
                    data: $('#addSupplierForm').serialize(),
                    success: function(response){
                        if(response == 'exists'){
                            $('#phoneNumberError').text('Phone number already exists.');
                        } else {
                            $('#phoneNumberError').text('');
                            $('#addSupplierForm').submit();
                        }
                    }
                });
            });
        });
    </script>



    <!-- EDIT SUPPLIER VALIDATION -->
    <script>
        $(document).ready(function(){
            $('#saveCustomerBtn').click(function(){

                var companyName = $('#editSupplierCompanyName').val();
                var province = $('#editSupplierProvince').val();
                var city = $('#editSupplierCity').val();
                var zipCode = $('#editSupplierZipCode').val();
                var phoneNumber = $('#editSupplierPhoneNumber').val();

                var companyNamePattern = /^[A-Za-z !"#$%&'()*+,\-./:;<=>?@[\\\]^_`{|}~]+$/;
                var addressPattern = /^[A-Za-z\- ]+$/;
                var zipCodePattern = /^\d{4}$/;
                var phoneNumberPattern = /^[0-9]{11}$/;

                // Validate Company name
                if(companyName.trim() == '' || !companyNamePattern.test(companyName)){
                    $('#editCompanyNameError').text('Please enter a valid company name.');
                    $('#editSupplierCompanyName').addClass('is-invalid');
                    return false;
                } else {
                    $('#editCompanyNameError').text('');
                    $('#editSupplierCompanyName').removeClass('is-invalid');
                }

                // Validate PROVINCE
                if(province.trim() == '' || !addressPattern.test(province)){
                    $('#editProvinceError').text('Please enter a valid province.');
                    $('#editSupplierProvince').addClass('is-invalid');
                    return false;
                } else {
                    $('#editProvinceError').text('');
                    $('#editSupplierProvince').removeClass('is-invalid');
                }

                // Validate CITY
                if(city.trim() == '' || !addressPattern.test(city)){
                    $('#editCityError').text('Please enter a valid city.');
                    $('#editSupplierCity').addClass('is-invalid');
                    return false;
                } else {
                    $('#editCityError').text('');
                    $('#editSupplierCity').removeClass('is-invalid');
                }

                // Validate ZIP CODE
                if(zipCode.trim() == '' || !zipCodePattern.test(zipCode)){
                    $('#editZipCodeError').text('Please enter a valid zip code.');
                    $('#editSupplierZipCode').addClass('is-invalid');
                    return false;
                } else {
                    $('#editZipCodeError').text('');
                    $('#editSupplierZipCode').removeClass('is-invalid');
                }

                // Validate phone number
                if(phoneNumber.trim() == '' || !phoneNumberPattern.test(phoneNumber)){
                    $('#editPhoneNumberError').text('Phone number must be 11 digits.');
                    $('#editSupplierPhoneNumber').addClass('is-invalid');
                    return false;
                } else {
                    $('#editPhoneNumberError').text('');
                    $('#editSupplierPhoneNumber').removeClass('is-invalid');
                }

                // AJAX request
                $.ajax({
                    url: 'sup_check_edit_phone_existence.php', // PHP script to check phone number
                    type: 'post',
                    data: $('#editSupplierForm').serialize(),
                    success: function(response){
                        if(response.trim() == 'exists'){
                            $('#editPhoneNumberError').text('Phone number already exists.');
                        } else {
                            $('#editPhoneNumberError').text('');
                            $('#editSupplierForm').submit();
                        }
                    }
                });
            });
        });
    </script>








    <script>
        function setEditSupplierFormData(Sup_Id, companyName, province, city, zipCode, phoneNumber, productID) {
            document.getElementById("editSupplierId").value = Sup_Id;
            document.getElementById("editSupplierCompanyName").value = companyName;
            document.getElementById("editSupplierProvince").value = province;
            document.getElementById("editSupplierCity").value = city;
            document.getElementById("editSupplierZipCode").value = zipCode;
            document.getElementById("editSupplierPhoneNumber").value = phoneNumber;

        }
    </script>


</body>

</html>