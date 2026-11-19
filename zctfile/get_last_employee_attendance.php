<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
include('../admin/config/dbcon.php');

if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (isset($data['masterid']) && isset($data['get_last_attendance_id']) && $data['get_last_attendance_id'] === 'rg546hfuasd6s8978') {

        
         // Validate masterid exists in users table
        $masterid = mysqli_real_escape_string($con, $data['masterid']);
        $checkMasterQuery = "SELECT id FROM users WHERE masterid = '$masterid' AND role_as = '9' LIMIT 1";
        $result = mysqli_query($con, $checkMasterQuery);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo json_encode(["status" => "error", "message" => "Invalid master ID: Not found in users table"]);
            exit;
        }

        $attendanceTable = 'z_attendance_' . $masterid;

        $query = "SELECT id FROM $attendanceTable ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($con, $query);

        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                echo json_encode([
                    "status" => "success",
                    "last_id" => $row['id']
                ]);
            } else {
                // Table is empty, return 0 as last_id
                echo json_encode([
                    "status" => "success",
                    "last_id" => 0
                ]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Query failed"]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid customer ID"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>