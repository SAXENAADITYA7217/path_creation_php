<?php

// Set default timezone to Indian Standard Time (Asia/Kolkata)
date_default_timezone_set('Asia/Kolkata');

$host = "localhost";
$username = "root";
$password = "";
$database = "u652066992_pathlok";

$con = mysqli_connect("$host","$username","$password","$database");

if(!$con)
{
    header("Location: ../errors/dberror.php");
    die();
}

// Set MySQL connection timezone to IST (+05:30)
@mysqli_query($con, "SET time_zone = '+05:30'");

?>
