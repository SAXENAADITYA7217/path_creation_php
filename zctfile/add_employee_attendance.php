<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
include('../admin/config/dbcon.php');

// Check DB connection
if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    // Validate required fields
    if (
        !isset($data['masterid']) ||
        !isset($data['employee_Id']) ||
        !isset($data['attendance_date']) ||
        !isset($data['attendance_time']) || 
        !isset($data['attendance_status']) ||
        !isset($data['face_image']) // base64 encoded image
    ) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    // Validate masterid exists in users table
    $masterid = mysqli_real_escape_string($con, $data['masterid']);
    $checkMasterQuery = "SELECT id FROM users WHERE masterid = '$masterid' AND role_as = '9' LIMIT 1";
    $result = mysqli_query($con, $checkMasterQuery);

    if (!$result || mysqli_num_rows($result) === 0) {
        echo json_encode(["status" => "error", "message" => "Invalid master ID: Not found in users table"]);
        exit;
    }

    $attendanceTable = 'z_attendance_' . $masterid;

    // Extract fields
    $employee_id = $data['employee_Id'];
    $attendance_date = $data['attendance_date'];
    $attendance_time = $data['attendance_time'];
    $attendance_status = $data['attendance_status'];
    $face_image_base64 = $data['face_image'];

    // Decode the base64 image
    $face_image = base64_decode($face_image_base64);

    if ($face_image === false) {
        echo json_encode(["status" => "error", "message" => "Invalid base64 image data"]);
        exit;
    }


    // Prepare the statement with placeholders
    $stmt = $con->prepare("INSERT INTO $attendanceTable (employee_id, attendance_date, attendance_time, attendance_status, face_image) VALUES (?, ?, ?, ?, ?)");

    // Temporarily bind null to blob, then send binary data
    $null = NULL;
    $stmt->bind_param("ssssb", $employee_id, $attendance_date, $attendance_time, $attendance_status, $null);

    // Now send the BLOB
    $stmt->send_long_data(4, $face_image);

    // Execute
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Attendance added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to insert attendance", "error" => $stmt->error]);
    }


    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
