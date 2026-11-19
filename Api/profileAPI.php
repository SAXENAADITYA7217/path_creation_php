<?php
session_start();
include('../admin/config/dbcon.php');

// Check if user is logged in
if (!isset($_SESSION['auth_user'])) {
    // If not logged in, return error message
    $response = [
        'status' => false,
        'error' => "Unauthorized",
        'message' => "User is not authenticated"
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Fetch user ID from session
$user_id = $_SESSION['auth_user']['user_id'];

// Query to fetch student data
$query = "SELECT * FROM student WHERE user_id = '$user_id'";
$query_run = mysqli_query($con, $query);

if (mysqli_num_rows($query_run) > 0) {
    $student_data = mysqli_fetch_assoc($query_run);

    // Prepare the response array
    $response = [
        'status' => true,
        'message' => 'Student data fetched successfully',
        'data' => $student_data
    ];
} else {
    $response = [
        'status' => false,
        'message' => 'Student not found',
        'data' => null
    ];
}

// Set content type to application/json
header('Content-Type: application/json');
echo json_encode($response);
?>