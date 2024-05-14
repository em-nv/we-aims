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

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Transaction Products</title>

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

    <!-- TO DOWNLOAD PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- Transaction Products List with Date Filters and Profit Calculation -->
<style>
    .card-header {
        padding: 20px;
    }

    .input-group {
        display: flex;
        justify-content: center; 
        align-items: center; 
    }

    .input-group > div {
        margin-right: 10px; 
        margin-right: 10px;
    }

    .input-group input[type="date"] {
        width: 180px; 
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
    .echo_message {
        font-size: 18px;
        margin-bottom: 0px !important;
    }
   .hide-for-pdf {
        display: none;
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

            <!-- Nav Item - SALES REPORTS Menu -->
            <li class="nav-item active">
                <a class="nav-link" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true"
                    aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-solid fa-chart-line"></i>
                    <span>Sales Reports</span>
                </a>
                <div id="collapseTwo" class="collapse show" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <!-- <h6 class="collapse-header">Custom Components:</h6> -->
                        <a class="collapse-item" href="SalesRepPro.php">Product Sales Reports</a>
                        <a class="collapse-item active" href="SalesRepSer.php">Service Sales Reports</a>
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
    <h1 class="h3 mb-0 text-gray-800">Sales Report Services</h1>
    <div>
    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#confirmationModal"><i class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
</div>



</div>

<!-- Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Confirm Download</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to download the sales report for this services?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="downloadPDF();">Download</button>
            </div>
        </div>
    </div>
</div>


<div class="card shadow mb-4">
    <div class="card-header py-3 gradient-header">
        <form action="" method="GET">
            <div class="row justify-content-center">
                <div class="input-group">
                    <div style="display: flex;">
                        <label style="margin-right: 10px;" for="dateFrom" class="col-form-label text-white">Date From:</label>
                        <input type="date" class="form-control" id="dateFrom" name="dateFrom" required>
                    </div>
                    <div style="display: flex;">
                        <label style="margin-right: 10px; margin-left: 10px;" for="dateTo" class="col-form-label text-white">Date To:</label>
                        <input style="margin-right: 10px;" type="date" class="form-control" id="dateTo" name="dateTo" required>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-solid fa-magnifying-glass a-sm text-white-50"></i> Find Reports</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body" id="dataTableContainer">
    <?php
        if (isset($_GET['dateFrom']) && isset($_GET['dateTo'])) {
            echo "<div class='table-responsive'>";
            echo "<table class='table table-bordered' id='dataTable' width='100%' cellspacing='0'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>TransSer ID</th>";
            echo "<th>Date</th>";
            echo "<th>Service Name</th>";
            echo "<th>Service Price</th>";
            echo "<th>Customer Name</th>";
            echo "<th>Payment Method</th>";
            echo "<th>Employee Name</th>";
            echo "<th>Role/Designation</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            include 'cus_db.php'; 
            $sql = "SELECT 
                        t.transactionServiceId AS transSerId, 
                        t.date AS transSerDate, 
                        s.serviceName AS serviceName, 
                        s.servicePrice AS servicePrice,
                        c.firstName AS customerName, 
                        c.paymentMethod AS paymentMethod,
                        e.first_name AS employeeName,
                        e.role AS role
                    FROM transactionsser t
                    JOIN services s ON t.serviceId = s.serviceId
                    JOIN customers c ON t.customerId = c.id 
                    JOIN employees e ON t.employeeId = e.employee_id
                    WHERE t.date BETWEEN ? AND ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $_GET['dateFrom'], $_GET['dateTo']);
            $stmt->execute();
            $result = $stmt->get_result();

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
                    echo "<td>" . htmlspecialchars($row["role"]) . "</td>";
                    echo "</tr>";
                } 
            } else {
                echo "<tr><td colspan='8'><p class='text-center echo_message'>No results found</p></td></tr>";
            }
            $stmt->close();
            $conn->close();

            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        } else {
            echo "<p class='text-center echo_message'>Please choose a date range</p>";
        }
        ?>
    </div>
</div>

</div>
            


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

    <!-- ADD THIS PO -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
    $(document).ready( function () {
        $('#dataTable').DataTable();
    } );
    </script>

<script>
function downloadPDF() {
    document.querySelector('.dataTables_filter').classList.add('hide-for-pdf');
    document.querySelector('.dataTables_length').classList.add('hide-for-pdf');
    document.querySelector('.dataTables_paginate').classList.add('hide-for-pdf');

    const element = document.getElementById('dataTableContainer');
    html2canvas(element).then(canvas => {
        document.querySelector('.dataTables_filter').classList.remove('hide-for-pdf');
        document.querySelector('.dataTables_length').classList.remove('hide-for-pdf');
        document.querySelector('.dataTables_paginate').classList.remove('hide-for-pdf');

        const imgData = canvas.toDataURL('image/png');
        const pdf = new jspdf.jsPDF({orientation: 'landscape'});
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
        pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);

    
        const logo = 'log.jpg'; 
        const logoWidth = 25; 
        const logoHeight = 16; 
        const bottomMargin = 5;

 
        pdf.addImage(logo, 'PNG', 13, pdf.internal.pageSize.getHeight() - logoHeight - bottomMargin, logoWidth, logoHeight);

        pdf.setFontSize(11);
        pdf.setTextColor(150, 150, 150);

        const companyText = "Willem’s Automotive Inventory Management System";
        const textStart = logoWidth + 15; 
        pdf.text(companyText, textStart, pdf.internal.pageSize.getHeight() - bottomMargin - 7);

        const watermark = "UNOFFICIAL COPY";
        pdf.setTextColor(220, 220, 220);
        pdf.setFontSize(70);
        const textWidth = pdf.getStringUnitWidth(watermark) * pdf.getFontSize() / pdf.internal.scaleFactor;
        
        const xCenter = (pdfWidth - textWidth * Math.cos(Math.PI / 4)) / 2;
        const yCenter = (pdfHeight / 2) + (textWidth * Math.sin(Math.PI / 4));

        pdf.text(watermark, xCenter, yCenter, {angle: 45}); 

        pdf.save("Service Sales Report.pdf");
    }).catch(err => {
        console.error("Error generating PDF: ", err);
    });
}
</script>





</body>

</html>