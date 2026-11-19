<?php
include('authentication.php');

if (!isset($_GET['employee_id'])) {
    exit;
}

$employee_id = $_GET['employee_id'];

$sql = "SELECT employee_profile FROM employee_data WHERE employee_id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($image);
$stmt->fetch();

if ($image) {
    header("Content-Type: image/jpeg"); // change if png
    echo $image;
}
