<?php 

$hostName = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "we-aims";
$con_login = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);
if (!$con_login) {
    die("Something went wrong;");
}

?>