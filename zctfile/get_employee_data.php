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

    if (isset($data['masterid']) && isset($data['get_all_employee_id']) && $data['get_all_employee_id'] === 'peo546hfu6s8978') {

        // Validate masterid exists in users table
        $masterid = mysqli_real_escape_string($con, $data['masterid']);
        $checkMasterQuery = "SELECT id FROM users WHERE masterid = '$masterid' AND role_as = '9' LIMIT 1";
        $result = mysqli_query($con, $checkMasterQuery);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo json_encode(["status" => "error", "message" => "Invalid master ID: Not found in users table"]);
            exit;
        }


        $query = "SELECT * FROM employee_data WHERE masterid = '$masterid'";
        $result = mysqli_query($con, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $employeeList = [];

            while ($row = mysqli_fetch_assoc($result)) {
                // Convert BLOB image to base64
                $imageBase64 = base64_encode($row['employee_profile']);

                $employeeList[] = [
                    "employee_id" => $row['employee_id'],
                    "employee_name" => $row['employee_name'],
                    "employee_profile" => $imageBase64,
                    "phone_no" => $row['phone_no'],
                    "designation" => $row['designation'],
                    "face_embedding" => $row['face_embedding'],
                    "start_time" => $row['start_time'],
                    "end_time" => $row['end_time'],
                    "worker_type" => $row['worker_type'],
                    "grace_time" => $row['grace_time'],
                    "over_time" => $row['over_time'],
                    "employee_status" => $row['employee_status'],
                ];
            }

            echo json_encode([
                "status" => "success",
                "message" => "Data fetched successfully",
                "data" => $employeeList
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "No data found"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid customer ID"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
