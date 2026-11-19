<?php
include('authentication.php');

if(isset($_POST['check_aadhaar']))
{
    $aadhaar = mysqli_real_escape_string($con, $_POST['aadhaar']);

    $query = "SELECT aadhaar_card_no FROM erp_student_detail WHERE aadhaar_card_no='$aadhaar' LIMIT 1";
    $query_run = mysqli_query($con, $query);

    if(mysqli_num_rows($query_run) > 0){
        echo "exists";
    } else {
        echo "ok";
    }
    exit();
}

?>