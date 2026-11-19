<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
include('../admin/config/dbcon.php');

// Set timezone to India
date_default_timezone_set('Asia/Kolkata');

if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (isset($data['masterid']) && isset($data['employee_id'])) {

        // Validate masterid exists in users table
        $masterid = mysqli_real_escape_string($con, $data['masterid']);
        $checkMasterQuery = "SELECT id FROM users WHERE masterid = '$masterid' AND role_as = '9' LIMIT 1";
        $result = mysqli_query($con, $checkMasterQuery);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo json_encode(["status" => "error", "message" => "Invalid master ID: Not found in users table"]);
            exit;
        }


        $employeeId = $data['employee_id'];
        $today = date('Y-m-d');
        $currentTime = date('H:i:s');  // 24-hour format time

        // 1. Check for existing row for today with status = 0 and time >= current time
        $checkQuery = "SELECT * FROM employee_pass_info 
                       WHERE masterid = '$masterid'
                       AND employee_id = '$employeeId' 
                       AND employee_pass_date = '$today' 
                       AND employee_pass_status = 0 
                       AND employee_pass_time <= '$currentTime'
                       LIMIT 1";
        $result = mysqli_query($con, $checkQuery);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $passId = $row['id'];

            // 2. Update the status to 1
            $updateQuery = "UPDATE employee_pass_info 
                            SET employee_pass_status = 1 
                            WHERE id = '$passId'";
            $updateResult = mysqli_query($con, $updateQuery);

            if ($updateResult) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Pass status updated to 1.",
                    "pass_id" => $passId
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to update pass status."
                ]);
            }
        } else {
            echo json_encode([
                "status" => "not_found",
                "message" => "No valid pass found for today with future time."
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Missing employee_id in request."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}
?>
