<?php
include('authentication.php');

$masterid      = $_SESSION['auth_user']['user_master_id'];
$employee_id   = $_POST['employee_id'];
$holiday_date  = $_POST['holiday_date'];
$holiday_count = $_POST['holiday_count'];
$holiday_status= $_POST['holiday_status'];

$year  = date('Y', strtotime($holiday_date));
$month = date('m', strtotime($holiday_date));

/* ===============================
   AUTO HANDLE ZERO COUNT
   =============================== */

if($holiday_count == 0){
    $holiday_status = 0;   // force status 0 if count 0
}

/* ===============================
   CHECK IF RECORD EXISTS
   =============================== */

$check_query = "SELECT id FROM employee_extra_holiday
                WHERE masterid = '$masterid'
                AND employee_id = '$employee_id'
                AND YEAR(holiday_set_date) = '$year'
                AND MONTH(holiday_set_date) = '$month'
                LIMIT 1";

$check_run = mysqli_query($con, $check_query);

if(mysqli_num_rows($check_run) > 0){

    // ✅ UPDATE
    $update_query = "UPDATE employee_extra_holiday SET
                        holiday_count  = '$holiday_count',
                        holiday_status = '$holiday_status',
                        holiday_set_date = '$holiday_date'
                     WHERE masterid = '$masterid'
                     AND employee_id = '$employee_id'
                     AND YEAR(holiday_set_date) = '$year'
                     AND MONTH(holiday_set_date) = '$month'";

    mysqli_query($con, $update_query);

} else {

    // ✅ INSERT
    $insert_query = "INSERT INTO employee_extra_holiday
                        (masterid, employee_id, holiday_set_date, holiday_count, holiday_status)
                     VALUES
                        ('$masterid', '$employee_id', '$holiday_date', '$holiday_count', '$holiday_status')";

    mysqli_query($con, $insert_query);
}

header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
?>
