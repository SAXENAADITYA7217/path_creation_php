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
        isset($data['get_employee_pass_data']) &&
        $data['get_employee_pass_data'] === 'sdf48946nknsd646s5' &&
        isset($data['Select_month']) &&
        !empty($data['Select_month'])
    ) {
        $Select_month = mysqli_real_escape_string($con, $data['Select_month']); // YYYY-MM format
        $response = [];

        $query = "SELECT * FROM employee_pass_info 
                  WHERE DATE_FORMAT(employee_pass_date, '%Y-%m') = '$Select_month' 
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
                        "employee_pass_date"     => $item['employee_pass_date'],
                        "employee_pass_time"     => $item['employee_pass_time'],
                        "employee_pass_status"   => $item['employee_pass_status']
                    ];
                }
            }

            echo json_encode([
                "status" => "success",
                "select_month" => $Select_month,
                "data" => $response
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "No pass records found"]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request parameters"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
