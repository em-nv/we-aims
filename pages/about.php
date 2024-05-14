<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>About</title>

    <!-- Custom fonts for this template-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <link href="../css/styles-1.css" rel="stylesheet">

     <!-- FONT AWESOME ICONS -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

     <!-- LORDICONS -->
     <script src="https://cdn.lordicon.com/lordicon.js"></script>

    <!-- CUSTOMIZED JS -->
    <script src="../js/customized.js"></script>

    <style>
        @media screen and (max-width: 768px) {
            body {
            position: relative;
            overflow: hidden; /* Hide the overflow from the blurred pseudo-element */
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('../img/about/branch.jpg');
            background-size: cover;
            background-position: center;
           /*  filter: blur(5px); */ /* Adjust the blur radius as needed */
            z-index: -1; /* Ensure the pseudo-element stays behind the content */
        }

        }
    </style>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper" class="about-body">

        

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav>
                    <ul class='nav-bar'>
                        <li class='logo'><a href='#'><img src='../img/logo/logo.png'><span class="top-bar-text">We-AIMS - WILLEM'S AUTOMOTIVE INVANTORY MANAGEMENT SYSTEM</span></a></li>
                        <input type='checkbox' id='check' />
                        <span class="menu">
                            <li><a href="home.php">Home</a></li>
                            <li class="shade"><a href="about.php" class="active">About</a></li>
                            <li><a href="features.php">Features</a></li>
                            <li><a href="contact.php">Contact</a></li>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Sign Up</a></li>
                            <label for="check" class="close-menu"><i class="fas fa-times"></i></label>
                        </span>
                        <label for="check" class="open-menu"><i class="fas fa-bars"></i></label>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid about-content-container">
                    <div class="about-caption">
                        <!-- Page Heading -->
                        <h4>ABOUT US</h4>
                        <h1>LEGAZPI WILLEM MARKETING CORPORATION</h1>
                        <p>Welcome to Legazpi Willem Marketing Corporation, your trusted partner in auto parts retail and installation services. At Legazpi Willem, we are committed to delivering top-quality automotive products and services to our valued customers.</p>
                        <h4 class="subtitle">Introducing We-AIMS: Your Comprehensive Inventory Management Solution</h4>
                        <p>We-AIMS (Willem Auto Inventory Management System) is our cutting-edge inventory management system designed specifically for our auto parts retail and installation business. Developed with the aim of streamlining operations and enhancing customer satisfaction, We-AIMS provides us with the tools needed to efficiently manage our vast inventory and ensure timely delivery of services.</p>
                    </div>
                    <div class="about-image">
                        <!-- Page Heading -->
                        
                    </div>
                    
                </div>
                <div class="container-fluid about-content-container">
                    <div class="about-image2">
                        <!-- Page Heading -->
                        
                    </div>
                    <div class="about-caption">
                        <!-- Page Heading -->
                        <!-- <h4>ABOUT US</h4> -->
                        <h4 class="subtitle">Why Choose Legazpi Willem Marketing Corporation and We-AIMS?</h4>
                        <p>Legazpi Willem Marketing Corporation has been a trusted name in the auto parts industry for 9 years. Our dedication to quality, reliability, and customer satisfaction sets us apart. With We-AIMS, we bring innovation to our operations, ensuring that every customer receives the best possible service. 
                            <br><br>Whether you're looking for quality auto parts or professional installation services, Legazpi Willem Marketing Corporation is here to meet your needs. Experience the difference with We-AIMS and discover why we are the preferred choice for auto enthusiasts and businesses alike.
                            <br><br>For inquiries or to learn more about our services, please contact us at 09097507203.
                        </p>
                    </div>
                    
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->
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

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.js"></script>

</body>

</html>