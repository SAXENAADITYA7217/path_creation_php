<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
include('../admin/config/dbcon.php');

// Set timezone to India
date_default_timezone_set('Asia/Kolkata');

// Check DB connection
if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (isset($data['generate_employee_pass']) && $data['generate_employee_pass'] === 'sdfs44879sdfsa87484') {
        // Validate required fields
        if (!isset($data['id']) || !isset($data['employee_pass_time'])) {
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            exit;
        }

        $today = date('Y-m-d');
        $id = mysqli_real_escape_string($con, $data['id']);
        $employee_pass_time = mysqli_real_escape_string($con, $data['employee_pass_time']);

        // Default status -> 0 (Pending)
        $employee_pass_status = 0;

        $query = "INSERT INTO employee_pass_info (user_id, employee_pass_date, employee_pass_time, employee_pass_status) 
                VALUES ('$id', '$today', '$employee_pass_time', '$employee_pass_status')";
        $query_run = mysqli_query($con, $query);

        if ($query_run) {
            echo json_encode(["status" => "success", "message" => "Pass entry created successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create pass entry", "error" => mysqli_error($con)]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Generate ID"]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
