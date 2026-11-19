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

    $today = date('Y-m-d');

    if (isset($data['masterid']) && isset($data['get_employee_extra_work_id']) && $data['get_employee_extra_work_id'] === 'sefw46848asfsdf6464') {

        // Validate masterid exists in users table
        $masterid = mysqli_real_escape_string($con, $data['masterid']);
        $checkMasterQuery = "SELECT id FROM users WHERE masterid = '$masterid' AND role_as = '9' LIMIT 1";
        $result = mysqli_query($con, $checkMasterQuery);

        if (!$result || mysqli_num_rows($result) === 0) {
            echo json_encode(["status" => "error", "message" => "Invalid master ID: Not found in users table"]);
            exit;
        }

        $query = "SELECT * FROM employee_extra_work WHERE masterid = '$masterid' AND server_update_status = 1  AND extra_date = '$today' ";
        $result = mysqli_query($con, $query);


        if ($result && mysqli_num_rows($result) > 0) {
            $employeeExtradataList = [];

            while ($row = mysqli_fetch_assoc($result)) {

                $employeeExtradataList[] = [
                    "id" => $row['id'],
                    "employee_id" => $row['employee_id'],
                    "extra_date" => $row['extra_date'],
                    "extra_time" => $row['extra_time'],
                    "extra_work_status" => $row['extra_work_status'],
                ];
            }

            // Update server_update to 0 after successful fetch
            $updateQuery = "UPDATE employee_extra_work SET server_update_status = 0 WHERE server_update_status = 1";
            mysqli_query($con, $updateQuery);

            echo json_encode([
                "status" => "success",
                "message" => "Data fetched successfully",
                "data" => $employeeExtradataList
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "No data found"]);
        }


    } else {
        echo json_encode(["status" => "error", "message" => "Invalid customer ID"]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}
?>