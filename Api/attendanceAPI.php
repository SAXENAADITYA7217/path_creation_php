<?php
session_start();
include('../admin/config/dbcon.php');

// Check if user is logged in
if (!isset($_SESSION['auth_user'])) {
    // If not logged in, return error message
    $response['error'] = "Unauthorized";
    $response['message'] = "User is not authenticated";
    echo json_encode($response);
    exit();
}

// Fetch user ID from session
$user_id = $_SESSION['auth_user']['user_id'];

// Query to fetch attendance data
$attendance_query = "SELECT * FROM attendance WHERE user_id='$user_id'";
$user_attendance = mysqli_query($con, $attendance_query);

if (mysqli_num_rows($user_attendance) > 0) {
    $presentCount = 0;
    $absentCount = 0;

    // Loop through the attendance data to count present and absent days
    while ($attendance_row = mysqli_fetch_assoc($user_attendance)) {
        $status = $attendance_row['status'];
        if ($status == 1) {
            $presentCount++;
        } else {
            $absentCount++;
        }
    }

    // Prepare response with total present and absent counts
    $response['status'] = "success";
    $response['data'] = [
        'presentCount' => $presentCount,
        'absentCount' => $absentCount
    ];
    echo json_encode($response);
} else {
    // No attendance data found
    $response['error'] = "NoData";
    $response['message'] = "No attendance data found";
    echo json_encode($response);
}
?>
