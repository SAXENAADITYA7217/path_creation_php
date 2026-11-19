<?php
include('authentication.php');

$password = $_POST['password'];
$masterid = $_SESSION['auth_user']['user_master_id'];

$query = "SELECT * FROM attendance_edit_password 
          WHERE masterid='$masterid' 
          AND edit_password='$password'";

$result = mysqli_query($con,$query);

if(mysqli_num_rows($result) > 0){
    echo "success";
}else{
    echo "fail";
}
?>
