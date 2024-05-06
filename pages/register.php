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

    <title>Register</title>

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

        <div class="card o-hidden border-0 shadow-lg my-5 register-card">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <img class="col-lg-5 d-none d-lg-block bg-register-image" id="randomRegisterImage" src=""></img>
                    <div class="col-lg-7">
                        <div class="p-5">
                            <div class="text-center">
                                <img src="../img/logo/Red-and-Black-WE-AIMS-Logo.png" class="logo-register">
                            </div>
                            <?php 
                            if (isset($_POST["submit"])) {
                                $fname = $_POST["fname"];
                                $lname = $_POST["lname"];
                                $email = $_POST["email"];
                                $password = $_POST["password"];
                                $rpassword = $_POST["rpassword"];
                                $errors = array();

                                if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    array_push($errors, "Email is not valid");
                                }
                                if (strlen($password) < 8) {
                                    array_push($errors, "Password must be at least 8 characters long");
                                }
                                if ($password !== $rpassword) {
                                    array_push($errors, "Password does not match");
                                }

                                require_once "database_login.php";
                                $sql = "SELECT * FROM admin WHERE email = '$email'";
                                $result = mysqli_query($con_login, $sql);
                                $rowCount = mysqli_num_rows($result);
                                if($rowCount > 0) {
                                    array_push($errors, "Email already exists!");
                                }

                                if (count($errors) > 0) {
                                    foreach ($errors as $error) {
                                        echo "<div class='alert alert-danger'>$error</div>";
                                    }
                                } else {
                                    $sql = "INSERT INTO admin (fname, lname, email, password) VALUES ( ?, ?, ?, ? )";
                                    $stmt = mysqli_stmt_init($con_login);
                                    $prepareStmt = mysqli_stmt_prepare($stmt, $sql);
                                    if ($prepareStmt) {
                                        mysqli_stmt_bind_param($stmt,"ssss", $fname, $lname, $email, $password);
                                        mysqli_stmt_execute($stmt);
                                        echo "<div class='alert alert-success'>You are registered successfully.</div>";
                                    } else {
                                        die("Something went wrong");
                                    }
                                }
                            }
                            ?>
                            <form class="user" action="register.php" method="post">
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="text" class="form-control form-control-user" id="exampleFirstName"
                                            placeholder="First Name" name="fname" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control form-control-user" id="exampleLastName"
                                            placeholder="Last Name" name="lname" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-user" id="exampleInputEmail"
                                        placeholder="Email Address" name="email" required>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="password" class="form-control form-control-user"
                                            id="exampleInputPassword" placeholder="Password" name="password" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="password" class="form-control form-control-user"
                                            id="exampleRepeatPassword" placeholder="Repeat Password" name="rpassword" required>
                                    </div>
                                </div>
                                
                                <input type="submit" class="btn btn-primary btn-user btn-block" value="Register Account" name="submit">
                            </form>
                            <hr>
                            <div class="text-center">
                                <a class="small" href="login.php">Already have an account? Login!</a>
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