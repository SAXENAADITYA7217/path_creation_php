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
        !isset($data['employee_profile']) || // base64 encoded
        !isset($data['face_embedding'])
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

    // Prepare data
    $employee_id = $data['employee_id'];
    $face_embedding = preg_replace('/\s+/', ' ', trim($data['face_embedding']));


    // Decode base64 image
    $employee_profile = base64_decode($data['employee_profile']);
    if ($employee_profile === false) {
        echo json_encode(["status" => "error", "message" => "Invalid profile image"]);
        exit;
    }


    // Prepare UPDATE SQL
    $stmt = $con->prepare("UPDATE employee_data SET 
        employee_profile = ?, 
        face_embedding = ?
        WHERE masterid = '$masterid' AND employee_id = '$employee_id'");

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Statement preparation failed", "error" => $con->error]);
        exit;
    }

    $stmt->bind_param(
        "ss",
        $employee_profile,
        $face_embedding
    );

    // Send the binary data as blob
    $stmt->send_long_data(0, $employee_profile); // 1rd parameter is the blob

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
