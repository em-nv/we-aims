<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Home</title>

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

    

    <style>
        body{
            background-image: url(../img/Cars.png);
            background-size:cover;
            background-position:center;
        }
    </style>

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper" class="home-body">

        

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
                            <li style="--i:1;" class="shade"><a href="home.php" class="active">Home</a></li>
                            <li style="--i:2;"><a href="about.php">About</a></li>
                            <li style="--i:3;"><a href="features.php">Features</a></li>
                            <li style="--i:4;"><a href="contact.php">Contact</a></li>
                            <li style="--i:5;"><a href="login.php">Login</a></li>
                            <li style="--i:6;"><a href="register.php">Sign Up</a></li>
                            <label for="check" class="close-menu"><i class="fas fa-times"></i></label>
                        </span>
                        <label for="check" class="open-menu"><i class="fas fa-bars"></i></label>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid home-content-container">

                    <!-- Page Heading -->
                    <h1 class="home-h1">Welcome to We-AIMS!</h1>
                    <p class="home-p"> “Willem's Wheels, Sorted Deals: Aims your Growth as Success Reveals!”</p>
                    <div class="home-content-button">
                        <img src="../img/red-car2.png" class="car">
                        <a class="button-link home-button" href="login.php">
                            <i style="font-size: 20px;" class="fa-solid fa-angles-right"></i>
                        </a>
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


    <!-- CUSTOMIZED JS -->
    <script src="../js/customized.js"></script>
</body>

</html>