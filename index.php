<!-- FOR LOGIN -->
<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: pages/home.php");
}

include ("pages/database_login.php");
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = "SELECT * FROM admin WHERE email = '$email' limit 1";

    $result = mysqli_query($con_login, $query);
    if($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
    }
}
?>

<!-- CUSTOMER COUNT -->
<?php 
include 'pages/cus_db.php'; // database connection

// Execute SQL query
$sql = "SELECT COUNT(*) AS customer_count FROM customers";
$result = $conn->query($sql);

// Fetch the count result
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $customer_count = $row["customer_count"];
} else {
    $customer_count = 0;
}

$conn->close();


?>


<!-- SUPPLIER COUNT -->
<?php 
include 'pages/cus_db.php'; // database connection

// Execute SQL query
$sql = "SELECT COUNT(*) AS suppliers_count FROM suppliers";
$result = $conn->query($sql);

// Fetch the count result
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $supplier_count = $row["suppliers_count"];
} else {
    $supplier_count = 0;
}

$conn->close();

?>

<!-- PRODUCTS SOLD COUNT -->
<?php 
include 'pages/cus_db.php'; // database connection

// Execute SQL query to sum the quantity
$sql = "SELECT SUM(quantity) AS total_quantity FROM transactionspro";
$result = $conn->query($sql);

// Fetch the sum result
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_quantity_sold = $row["total_quantity"];
} else {
    $total_quantity_sold = 0;
}

$conn->close();
?>

<!-- SERVICE TRANSACTIONS COUNT -->
<?php 
include 'pages/cus_db.php'; // database connection

// Execute SQL query
$sql = "SELECT COUNT(*) AS service_transactions FROM transactionsser";
$result = $conn->query($sql);

// Fetch the count result
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_service_transactions_count = $row["service_transactions"];
} else {
    $total_service_transactions_count = 0;
}

$conn->close();

?>



<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">





    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <!-- FONT AWESOME ICONS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

    <!-- BOXICONS AWESOME ICONS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        .reminders {
            padding: 10px 25px 0px 25px;
            max-height: 300px;
            overflow-y: auto;
        }
        .remindtable {
            border: none;
            margin-bottom: 10px;
        }
        .remindtable h1 {
            margin: 0px 0px 3px 0px;
            font-size: 18px;
        }
        .remindtable span {
            font-weight: bold;
            color: #5e8dab;
        }
        .remindtable p {
            margin: 0px;
            font-size: 14px;
        }
        .remindtable tr {
            padding: 50px;
        }
        .remindtable td {
            padding: 15px 10px 15px 10px;
            border-bottom: 1px solid #e3e6f0;
        }
        .centered {
            text-align: center;
        }
        .noremind {
            border-bottom: none !important;
        }
    </style>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="nav-background navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php">
                <div class="sidebar-brand-icon">
                    <img src="img/logo/logo.png" class="logo">
                </div>
                <!-- <div class="sidebar-brand-text mx-3">WE-AIMS <sup></sup></div> -->
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="index.php">
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
                <a class="nav-link" href="pages/customers.php">
                    <i class="fas fa-fw fa-solid fa-users"></i>
                    <span>Customers</span></a>
            </li>

            <!-- Nav Item - SUPPLIERS -->
            <li class="nav-item">
                <a class="nav-link" href="pages/suppliers.php">
                    <i class="fas fa-fw fa-solid fa-truck-field"></i>
                    <span>Suppliers</span></a>
            </li>

            <!-- Nav Item - EMPLOYEES -->
            <li class="nav-item">
                <a class="nav-link" href="pages/employees.php">
                    <i class="fas fa-fw fa-solid fa-building-user"></i>
                    <span>Employees</span></a>
            </li>

            <!-- Nav Item - PRODUCTS -->
            <li class="nav-item">
                <a class="nav-link" href="pages/products.php">
                    <i class="fas fa-fw fa-solid fa-dolly"></i>
                    <span>Products</span></a>
            </li>

            <!-- Nav Item - SERVICES -->
            <li class="nav-item">
                <a class="nav-link" href="pages/services.php">
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
                        <a class="collapse-item" href="pages/SalesRepPro.php">Product Sales Reports</a>
                        <a class="collapse-item" href="pages/SalesRepSer.php">Service Sales Reports</a>
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
                        <a class="collapse-item" href="pages/product_transactions.php">Product Transactions</a>
                        <a class="collapse-item" href="pages/service_transactions.php">Service Transactions</a>
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
                                    src="img/profile-icons/undraw_pic_profile_re_7g2h.svg">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="pages/admin.php">
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
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                        <!-- <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i
                                class="fas fa-download fa-sm text-white-50"></i> Generate Report</a> -->
                    </div>



                    <!-- Banner -->
                    <div class="card shadow mb-4 banner">
                        <!-- <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Development Approach</h6>
                        </div> -->
                        <div class="row">
                            <div class="">
                                <div class="d-flex align-items-center row my-2">
                                    <div class="col-sm-7">
                                        <div class="card-body banner-left">
                                            <h4 style="font-weight: bold;">Hello, <span style="font-weight: bolder;"><?php echo $user_data['fname']; ?>!</span>
                                                <lord-icon
                                                    src="https://cdn.lordicon.com/cqofjexf.json"
                                                    trigger="loop"
                                                    delay="1000"
                                                    stroke="bold"
                                                    colors="primary:#ffffff,secondary:#ffc738,tertiary:#4bb3fd"
                                                    style="width:45px;height:45px">
                                                </lord-icon>
                                            </h4>
                                            <p class="mb-0">Welcome back to your control center! Step into your dashboard to manage inventory efficiently. From tracking stock levels to analyzing sales trends, our system empowers you. Let's drive your business forward with streamlined operations and strategic insights.</p>
                                            
                                            <!-- Page Heading -->
                                            <div class="d-sm-flex align-items-center justify-content-between" style="padding-top:10px;">
                                                <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                                                    <i class="fas fa-solid fa-chart-line fa-sm text-white-50"></i> View Reports</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-5 text-center text-sm-left d-none d-sm-block">
                                        <div class="card-body pb-0 px-0 px-md-4 banner-right">
                                            <img id="randomBannerImage" src="" class="img-fluid px-3 px-sm-4 mt-3 mb-4 banner-img" style="width: 20rem;" >
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- TOTAL SALES CARD -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                TOTAL SALES</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Php 14,500</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-solid fa-peso-sign fa-2x text-blue-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PROFIT CARD -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                PROFIT</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Php 9,000</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-solid fa-peso-sign fa-2x text-blue-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RETAIL SALES CARD -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                RETAIL SALES</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Php 8,000</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-solid fa-peso-sign fa-2x text-blue-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SERVICE SALES CARD -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                SERVICE SALES</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">Php 6,500</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-solid fa-peso-sign fa-2x text-blue-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CUSTOMERS CARD -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                CUSTOMERS</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $customer_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-solid fa-users fa-2x text-blue-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SUPPLIERS CARD -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                SUPPLIERS</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $supplier_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-solid fa-truck-field fa-2x text-blue-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    
                        <!-- TOTAL PRODUCTS SOLD CARD -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                TOTAL PRODUCTS SOLD</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_quantity_sold; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-solid fa-box-open fa-2x text-blue-300"></i>
                                          
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TOTAL SERVICE TRANSACTION CARD -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                TOTAL SERVICE TRANSACTION</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_service_transactions_count; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-solid fa-screwdriver-wrench fa-2x text-blue-300"></i>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Content Row -->

                    <div class="row">

                        <!-- Area Chart -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <!-- REPORTS -->
                                <div
                                    class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">REPORTS</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pie Chart -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <!-- REMINDERS -->
                                <div
                                    class="card-header card-header-alert py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">REMINDERS</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                            aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Dropdown Header:</div>
                                            <a class="dropdown-item" href="#">Action</a>
                                            <a class="dropdown-item" href="#">Another action</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#">Something else here</a>
                                        </div>
                                    </div>
                                </div>
                                <!-- Card Body -->
                                <div class="reminders">
                                    <table class="remindtable" id="dataTable" width="100%" cellspacing="0">
                                        <!-- <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Phone No.</th>
                                            </tr>
                                        </thead> -->
                                        <!-- <tbody> -->
                                        <?php
                                            include 'pages/cus_db.php'; // database connection file
                                            $sql = "SELECT productName, quantity FROM products WHERE quantity <= 20"; // Selecting required columns

                                            $result = $conn->query($sql);

                                            // Fetching data from the result set and displaying it in table rows
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>";
                                                    echo "<i class='fa-solid fa-boxes-stacked fa-lg' style='color: #5e8dab;'></i>";
                                                    // echo "<i class='fa-solid fa-boxes-stacked fa-xl' style='color: #016193;'></i>";
                                                    echo "</td>";

                                                    echo "<td>";
                                                    echo "<h1><span>{$row['productName']}</span> is low in supply!</h1>";
                                                    echo "<p>{$row['quantity']} stocks left</p>";
                                                    echo "</td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr class='centered'><td class='noremind'><h1>There are no current reminders!</h1></td></tr>";
                                            }
                                            $conn->close();
                                        ?>
                                        <!-- </tbody> -->
                                    </table>
                                    
                                </div>
                                <?php
                                if ($result->num_rows > 0) {
                                    echo "<a href='pages/products.php' style='padding: 20px; display: block; margin: 0 auto; width: fit-content;'>View Products page</a>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">

                        <!-- Content Column -->
                        <div class="col-lg-6 mb-4">

                            <!-- CUSTOMER TABLE -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">CUSTOMER PREVIEW</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive-dashboard">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Phone No.</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php 
                                                include 'pages/cus_db.php'; // database connection file
                                                $sql = "SELECT firstName, lastName, phone FROM customers LIMIT 4"; // Selecting required columns

                                                $result = mysqli_query($conn, $sql); // Executing SQL query

                                                // Fetching data from the result set and displaying it in table rows
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td>" . $row['firstName'] . " " . $row['lastName'] . "</td>"; // Combining first name and last name
                                                    echo "<td>" . $row['phone'] . "</td>";
                                                    echo "</tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <a href="pages/customers.php" style="padding: 5px; display: block; margin: 0 auto; width: fit-content;">See more...</a>
                                    </div>
                                </div>
                            </div>

                            <!-- SERVICE TABLE -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">SERVICE PREVIEW</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive-dashboard">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Service Name</th>
                                                    <th>Assigned Employee</th>
                                                    <th>Tiime Required</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php 
                                                include 'pages/cus_db.php'; // database connection file
                                                $sql = "SELECT serviceId, serviceName, employee_id, employee_name, timeRequired FROM services LIMIT 4"; // Selecting required columns

                                                $result = mysqli_query($conn, $sql); // Executing SQL query

                                                // Fetching data from the result set and displaying it in table rows
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td>" ."(" . $row['serviceId'] . ")" . " " . $row['serviceName'] . "</td>";
                                                    echo "<td>" ."(" . $row['employee_id'] . ")" . " " . $row['employee_name'] . "</td>";
                                                    echo "<td>" . $row['timeRequired'] . "</td>";
                                                    echo "</tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <a href="pages/customers.php" style="padding: 5px; display: block; margin: 0 auto; width: fit-content;">See more...</a>
                                    </div>
                                </div>
                            </div>

        

                        </div>

                        <div class="col-lg-6 mb-4">

                            <!-- PRODUCTS TABLE -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">PRODUCTS PREVIEW</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive-dashboard">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Product Name</th>
                                                    <th>Retail Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php 
                                                include 'pages/cus_db.php'; // database connection file
                                                $sql = "SELECT productName, retailPrice FROM products LIMIT 4"; // Selecting required columns

                                                $result = mysqli_query($conn, $sql); // Executing SQL query

                                                // Fetching data from the result set and displaying it in table rows
                                                while ($row = mysqli_fetch_assoc($result)) {
                                                    echo "<tr>";
                                                    echo "<td>" . $row['productName'] . "</td>"; // Combining first name and last name
                                                    echo "<td>" . "Php " . number_format($row['retailPrice']) . "</td>";
                                                    echo "</tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <a href="pages/customers.php" style="padding: 5px; display: block; margin: 0 auto; width: fit-content;">See more...</a>
                                    </div>
                                </div>
                            </div>

                            <!-- EMPLOYEE TABLE -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">EMPLOYEE PREVIEW</h6>
                                </div>
                                <div class="card-body">
                                <div class="table-responsive-dashboard">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Employee ID</th>
                                                <th>Name</th>
                                                <th>Designation</th>
                                                <th>Phone No.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php 
                                            include 'pages/cus_db.php'; // database connection file
                                            $sql = "SELECT employee_ID, first_name, last_name, role, phone_no FROM employees LIMIT 4"; // Selecting required columns

                                            $result = mysqli_query($conn, $sql); // Executing SQL query

                                            // Fetching data from the result set and displaying it in table rows
                                            while ($row = mysqli_fetch_assoc($result)) {
                                                echo "<tr>";
                                                echo "<td>" . $row['employee_ID'] . "</td>";
                                                echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>"; // Combining first name and last name
                                                echo "<td>" . $row['role'] . "</td>";
                                                echo "<td>" . $row['phone_no'] . "</td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                    <a href="pages/employees.php" style="padding: 5px; display: block; margin: 0 auto; width: fit-content;">See more...</a>
                                </div>
                                </div>
                            </div>

                            <!-- Approach -->
                            <!-- <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Development Approach</h6>
                                </div>
                                <div class="card-body">
                                    <p>SB Admin 2 makes extensive use of Bootstrap 4 utility classes in order to reduce
                                        CSS bloat and poor page performance. Custom CSS classes are used to create
                                        custom components and custom utility classes.</p>
                                    <p class="mb-0">Before working with this theme, you should become familiar with the
                                        Bootstrap framework, especially the utility classes.</p>
                                </div>
                            </div> -->

                        </div>
                    </div>





                    <!-- SUPPLIER TABLE -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">SUPPLIER PREVIEW</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive-dashboard">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Company Name</th>
                                            <th>Adddress</th>
                                            <th>Zip Code</th>
                                            <th>Phone Number</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php 
                                        include 'pages/cus_db.php'; // database connection file
                                        $sql = "SELECT companyName, city, province, zipCode, phoneNumber FROM suppliers LIMIT 4"; // Selecting required columns

                                        $result = mysqli_query($conn, $sql); // Executing SQL query

                                        // Fetching data from the result set and displaying it in table rows
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>" . $row['companyName'] . "</td>"; // Combining first name and last name
                                            echo "<td>" . $row['city'] . ", " . $row['province'] . "</td>";
                                            echo "<td>" . $row['zipCode'] . "</td>";
                                            echo "<td>" . $row['phoneNumber'] . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <a href="pages/suppliers.php" style="padding: 5px; display: block; margin: 0 auto; width: fit-content;">See more...</a>
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
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="pages/logout.php">Logout</a>
                </div>
            </div>
        </div>
    </div>


    <!-- CUSTOMIZED JS -->
    <script src="js/customized.js"></script>
    <script src="js/date-and-time.js"></script>

    <!-- LORDICONS -->
    <script src="https://cdn.lordicon.com/lordicon.js"></script>


    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>
    <script src="js/demo/chart-pie-demo.js"></script>

</body>

</html>