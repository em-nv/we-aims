<?php
session_start();
if (isset($_SESSION["user"])) {
    header("Location: ../index.php");
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

    <title>Login</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.css" rel="stylesheet">

    <!-- CUSTOMIZED JS -->
    <script src="../js/customized.js"></script>

</head>

<body class="bg-gradient-primary">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5 log-in-card">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <img class="col-lg-6 d-none d-lg-block bg-login-image" id="randomLogInImage" src=""></img>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <img src="../img/logo/Red-and-Black-WE-AIMS-Logo.png" class="logo-login">
                                    </div>
                                    <?php
                                    if (isset($_POST["login"])) {
                                        $email = $_POST["email"];
                                        $password = $_POST["password"];
                                        require_once "database_login.php";
                                        $sql = "SELECT * FROM admin WHERE email = '$email'";
                                        $result = mysqli_query($con_login, $sql);
                                        $user = mysqli_fetch_array($result, MYSQLI_ASSOC);
                                        if ($user) {
                                            if ($password === $user["password"]) {
                                                session_start();
                                                $_SESSION["user"] = "yes";
                                                $_SESSION["email"] = $email;
                                                header("Location: ../index.php");
                                                die();
                                            } else {
                                                echo "<div class='alert alert-danger'>Incorrect Password</div>";
                                            }
                                        } else {
                                            echo "<div class='alert alert-danger'>Incorrect Email</div>";
                                        }
                                    }
                                    ?>
                                    <form class="user" action="login.php" method="post">
                                        <div class="form-group">
                                            <input type="email" class="form-control form-control-user"
                                                id="exampleInputEmail" aria-describedby="emailHelp"
                                                placeholder="Enter Email Address..." name="email" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user"
                                                id="exampleInputPassword" placeholder="Password" name="password" required>
                                        </div>
                                        <!-- <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="customCheck">
                                                <label class="custom-control-label" for="customCheck">Remember
                                                    Me</label>
                                            </div>
                                        </div> -->
                                        
                                        <input type="submit" class="btn btn-primary btn-user btn-block" value="Login" name="login">
                                      
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="register.php">Create an Account!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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