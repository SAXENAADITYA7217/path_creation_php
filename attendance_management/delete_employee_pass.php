<?php
include('authentication.php');

// Set timezone if needed
date_default_timezone_set('Asia/Kolkata'); // Change as per your region
$today = date('Y-m-d');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $masterid = $_SESSION['auth_user']['user_master_id'];
    // Only allow delete if date is today and status is 0 (Not Used)
    $checkQuery = "SELECT * FROM employee_pass_info WHERE id = '$id' AND masterid = '$masterid' AND employee_pass_status = 0 AND employee_pass_date = '$today'";
    $checkResult = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $deleteQuery = "DELETE FROM employee_pass_info WHERE id = '$id'";
        $deleteResult = mysqli_query($con, $deleteQuery);

        if ($deleteResult) {
            $_SESSION['message'] = "Employee pass deleted successfully.";
        } else {
            $_SESSION['message'] = "Failed to delete employee pass.";
        }
    } else {
        $_SESSION['message'] = "Pass cannot be deleted. It may already be used or the date does not match.";
    }

    header("Location: employee_pass.php");
    exit();
} else {
    $_SESSION['message'] = "Invalid request.";
    header("Location: employee_pass.php");
    exit();
}
?>
