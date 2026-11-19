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

    if (
        isset($data['get_employee_overtime_data']) &&
        $data['get_employee_overtime_data'] === 'sbds6546ads8546s5' &&
        isset($data['Select_month']) &&
        !empty($data['Select_month'])
    ) {
        $Select_month = mysqli_real_escape_string($con, $data['Select_month']); // YYYY-MM format
        $response = [];

        $query = "SELECT * FROM employee_extra_work 
                  WHERE DATE_FORMAT(extra_date, '%Y-%m') = '$Select_month' 
                  ORDER BY id DESC";

        $query_run = mysqli_query($con, $query);

        if (mysqli_num_rows($query_run) > 0) {
            foreach ($query_run as $item) {
                $user_id = $item['user_id'];

                // Get employee details
                $employee_data_query = "SELECT id, employee_name, employee_profile FROM employee_data WHERE id = $user_id";
                $employee_data_query_run = mysqli_query($con, $employee_data_query);

                if ($employee_item = mysqli_fetch_assoc($employee_data_query_run)) {
                    $response[] = [
                        "id"                     => $employee_item['id'],
                        "employee_name"          => $employee_item['employee_name'],
                        "employee_profile"       => base64_encode($employee_item['employee_profile']), // 👈 convert blob to base64
                        "extra_date"             => $item['extra_date'],
                        "extra_time"             => $item['extra_time'],
                        "extra_work_status"      => $item['extra_work_status'],
                        "server_update_status"   => $item['server_update_status']
                    ];
                }
            }

            echo json_encode([
                "status" => "success",
                "select_month" => $Select_month,
                "data" => $response
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "No Extra Overtime records found"]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request parameters"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
