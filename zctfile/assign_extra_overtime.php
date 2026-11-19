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

    if (isset($data['assign_extra_overtime']) && $data['assign_extra_overtime'] === 'sfab568sni654scg54') {
        
        // Validate required fields
        if (!isset($data['id']) || !isset($data['extra_time'])) {
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            exit;
        }

        $extraDate = date('Y-m-d');  // today
        $id        = mysqli_real_escape_string($con, $data['id']);
        $extraTime = mysqli_real_escape_string($con, $data['extra_time']);

        if ($id == "0" || $id == "-1" || $id == "-2") {
            // Build condition
            $whereCondition = "";
            if ($id == "-1") {
                $whereCondition = "AND ed.worker_type = 'overtime'";
            } elseif ($id == "-2") {
                $whereCondition = "AND ed.worker_type = 'non_overtime'";
            }

            // Fetch employees present today
            $fetchQuery = "
                SELECT ed.id 
                FROM employee_data ed
                INNER JOIN attendance_mark_info am ON ed.id = am.user_id
                WHERE am.attendance_date = '$extraDate'
                  AND am.attendance_status = 'Present'
                  $whereCondition
                GROUP BY ed.id
            ";
            $result = mysqli_query($con, $fetchQuery);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $userId = $row['id'];

                    mysqli_query($con, "
                        INSERT INTO employee_extra_work (user_id, extra_date, extra_time, server_update_status) 
                        VALUES ('$userId', '$extraDate', '$extraTime', '1')
                    ");
                }

                echo json_encode(["status" => "success", "message" => "Overtime assigned successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "No employees found for selection"]);
            }

        } else {
            // Insert for one specific employee
            $query = "
                INSERT INTO employee_extra_work (user_id, extra_date, extra_time, server_update_status) 
                VALUES ('$id', '$extraDate', '$extraTime', '1')
            ";
            $query_run = mysqli_query($con, $query);

            if ($query_run) {
                echo json_encode(["status" => "success", "message" => "Overtime assigned successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to assign overtime", "error" => mysqli_error($con)]);
            }
        }

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Generate ID"]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
