<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
include('../admin/config/dbcon.php');

if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (
        !isset($data['masterid']) ||
        !isset($data['employee_id']) ||
        !isset($data['employee_name']) ||
        !isset($data['phone_no']) ||
        !isset($data['designation']) ||
        !isset($data['start_time']) ||
        !isset($data['end_time']) ||
        !isset($data['worker_type']) ||
        !isset($data['grace_time']) ||
        !isset($data['over_time'])
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

    // Sanitize input
    $employee_id = mysqli_real_escape_string($con, $data['employee_id']);
    $employee_name = mysqli_real_escape_string($con, $data['employee_name']);
    $phone_no = mysqli_real_escape_string($con, $data['phone_no']);
    $designation = mysqli_real_escape_string($con, $data['designation']);
    $worker_type = mysqli_real_escape_string($con, $data['worker_type']);
    $grace_time = mysqli_real_escape_string($con, $data['grace_time']);
    $over_time = mysqli_real_escape_string($con, $data['over_time']);

    // Convert to 24-hour format
    $start_time_obj = DateTime::createFromFormat('h:i A', trim($data['start_time']));
    $end_time_obj = DateTime::createFromFormat('h:i A', trim($data['end_time']));

    if (!$start_time_obj || !$end_time_obj) {
        echo json_encode(["status" => "error", "message" => "Invalid time format"]);
        exit;
    }

    $start_time = $start_time_obj->format('H:i:s');
    $end_time = $end_time_obj->format('H:i:s');

    // Prepare UPDATE SQL
    $stmt = $con->prepare("UPDATE employee_data SET  
        employee_name = ?, 
        phone_no = ?, 
        designation = ?, 
        start_time = ?, 
        end_time = ?, 
        worker_type = ?, 
        grace_time = ?, 
        over_time = ? 
        WHERE masterid = '$masterid' AND employee_id = '$employee_id'");

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Statement preparation failed", "error" => $con->error]);
        exit;
    }

    $stmt->bind_param(
        "ssssssss",
        $employee_name,
        $phone_no,
        $designation,
        $start_time,
        $end_time,
        $worker_type,
        $grace_time,
        $over_time
    );

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Employee updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update employee", "error" => $stmt->error]);
    }

    $stmt->close();
    $con->close();

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
