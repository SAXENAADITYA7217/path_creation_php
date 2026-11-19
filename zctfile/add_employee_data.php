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
        !isset($data['employee_id']) ||
        !isset($data['employee_name']) ||
        !isset($data['employee_profile']) || // base64 encoded
        !isset($data['phone_no']) ||
        !isset($data['designation']) ||
        !isset($data['face_embedding']) ||
        !isset($data['start_time']) ||
        !isset($data['end_time']) ||
        !isset($data['worker_type'])
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

    // Prepare employee_id after masterid check
    $employee_id = mysqli_real_escape_string($con, $data['employee_id']);

    // ✅ Check if employee_id already exists under the same masterid
    $checkDuplicateQuery = "SELECT id FROM employee_data WHERE masterid = '$masterid' AND employee_id = '$employee_id' LIMIT 1";
    $duplicateResult = mysqli_query($con, $checkDuplicateQuery);

    if ($duplicateResult && mysqli_num_rows($duplicateResult) > 0) {
        echo json_encode(["status" => "error", "message" => "Employee ID already exists under this master ID"]);
        exit;
    }


    // Prepare data
    $employee_id = mysqli_real_escape_string($con, $data['employee_id']);
    $employee_name = mysqli_real_escape_string($con, $data['employee_name']);
    $phone_no = mysqli_real_escape_string($con, $data['phone_no']);
    $designation = mysqli_real_escape_string($con, $data['designation']);
    $worker_type = mysqli_real_escape_string($con, $data['worker_type']);
    $grace_time = mysqli_real_escape_string($con, $data['grace_time']);
    $over_time = mysqli_real_escape_string($con, $data['over_time']);
   // $face_embedding = mysqli_real_escape_string($con, $data['face_embedding']);
    $face_embedding = preg_replace('/\s+/', ' ', trim($data['face_embedding']));


    // Convert time to 24-hour format
    $start_time_input = $data['start_time']; // e.g., "02:30 PM"
    $end_time_input = $data['end_time'];     // e.g., "06:45 AM"

    $start_time_obj = DateTime::createFromFormat('h:i A', trim($start_time_input));
    $end_time_obj = DateTime::createFromFormat('h:i A', trim($end_time_input));

    if (!$start_time_obj || !$end_time_obj) {
        echo json_encode(["status" => "error", "message" => "Invalid time format"]);
        exit;
    }

    // Format to 24-hour for MySQL
    $start_time = $start_time_obj->format('H:i:s'); // "14:30:00"
    $end_time = $end_time_obj->format('H:i:s');     // "06:45:00"


    // Decode base64 image
    $employee_profile = base64_decode($data['employee_profile']);
    if ($employee_profile === false) {
        echo json_encode(["status" => "error", "message" => "Invalid profile image"]);
        exit;
    }

    // Prepare SQL with BLOB using prepared statement
    $stmt = $con->prepare("INSERT INTO employee_data 
        (masterid, employee_id, employee_name, employee_profile, phone_no, designation, face_embedding, start_time, end_time, worker_type, grace_time, over_time) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "ssssssssssss",
        $masterid,
        $employee_id,
        $employee_name,
        $employee_profile,
        $phone_no,
        $designation,
        $face_embedding,
        $start_time,
        $end_time,
        $worker_type,
        $grace_time,
        $over_time,
    );


    // Send the binary data as blob
    $stmt->send_long_data(2, $employee_profile); // 3rd parameter is the blob

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Employee added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add employee", "error" => $stmt->error]);
    }

    $stmt->close();
    $con->close();

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
