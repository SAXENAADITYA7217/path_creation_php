<?php
include('authentication.php');

$date = $_POST['date'];
$time = $_POST['time'];
$source = $_POST['source'];
$employeeId = $_POST['employee_id'];
$action = $_POST['action']; // either 'update' or 'insert'
$attendanceId = $_POST['attendance_id'];
/*
Define your real table names here
*/
$table_id = $_SESSION['auth_user']['user_master_id'];
$attendanceTable = 'z_attendance_' . $table_id;
$attendanceTable2 = 'y_attendance_' . $table_id;
// Decide table based on source
if($source == 'table1'){
    $table = $attendanceTable;
}else{
    $table = $attendanceTable2;
}


if($action == 'update'){
    $update = "UPDATE $table 
               SET attendance_time='$time' 
               WHERE employee_id='$employeeId'
               AND attendance_date='$date'
               AND id='$attendanceId'
               LIMIT 1";
    mysqli_query($con,$update);
    $msg = "Attendance Updated Successfully";
} else {

    $present = "Present";

    $insert = "INSERT INTO $table (employee_id, attendance_date, attendance_time, attendance_status)
               VALUES ('$employeeId', '$date', '$time', '$present')";
    mysqli_query($con,$insert);
    $msg = "Attendance Inserted Successfully";
}

echo "<script>
        window.history.back();
      </script>";

?>
