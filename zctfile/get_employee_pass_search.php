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

    if (isset($data['get_data_for_employee_pass']) && $data['get_data_for_employee_pass'] === 'voe84484784smd5484') {

        $today = date('Y-m-d');

        // Get ALL records from attendance for today
        $query = "SELECT DISTINCT user_id FROM attendance_mark_info WHERE attendance_date = '$today' ORDER BY id ASC";
        $query_run = mysqli_query($con, $query);

        if (mysqli_num_rows($query_run) > 0) {
            $result = [];

            while ($attendance = mysqli_fetch_assoc($query_run)) {
                // 👇 use user_id from attendance table (linked to employee_data.id)
                $empId = $attendance['user_id'];

                $employee_info_query = "SELECT id, employee_id, employee_name, employee_profile, phone_no, designation 
                                        FROM employee_data 
                                        WHERE id = '$empId' 
                                        LIMIT 1";
                $employee_info_query_run = mysqli_query($con, $employee_info_query);

                if (mysqli_num_rows($employee_info_query_run) > 0) {
                    $emp = mysqli_fetch_assoc($employee_info_query_run);

                    // convert BLOB to Base64
                    $profileBase64 = "";
                    if (!empty($emp['employee_profile'])) {
                        $profileBase64 = base64_encode($emp['employee_profile']);
                    }

                    $result[] = [
                        "id"              => $emp['id'],
                        "employee_id"     => $emp['employee_id'],
                        "employee_name"   => $emp['employee_name'],
                        "employee_profile"=> $profileBase64,
                        "phone_no"        => $emp['phone_no'],
                        "designation"     => $emp['designation']
                    ];
                }
            }

            
            echo json_encode(["status" => "success", "data" => $result]);

        } else {
            echo json_encode(["status" => "error", "message" => "No attendance found for today"]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid customer ID"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
