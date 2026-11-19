<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
include('../admin/config/dbcon.php');

// Check database connection
if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    // Check if the token is provided and valid
    if (isset($data['get_login_data']) && $data['get_login_data'] === 'pro5#losDSD466sdfws8978') {

        // Check if email and password are provided
        if (isset($data['email']) && isset($data['password'])) {
            $email = $data['email'];
            $password = $data['password'];

            // Sanitize the inputs to avoid SQL injection
            $email = mysqli_real_escape_string($con, $email);
            $password = mysqli_real_escape_string($con, $password);

            // Query to check if user exists with provided email and password
            $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password' AND status = 0 LIMIT 1";
            $result = mysqli_query($con, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                // User found, fetch data
                $user = mysqli_fetch_assoc($result);

                // Prepare response data
                $response = [
                    "status" => "success",
                    "user_id" => $user['user_id'],
                    "fname" => $user['fname'],
                    "lname" => $user['lname'],
                    "father_name" => $user['father_name'],
                    "phone_no" => $user['phone_no'],
                    "email" => $user['email'],
                    "role_as" => $user['role_as'],
                    "rollno" => $user['rollno'],
                    "class" => $user['class'],
                    "user_photo" => $user['user_photo'],
                    "user_photo_type" => $user['user_photo_type'],
                    "created_at" => $user['created_at']
                ];

                echo json_encode($response);
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid email or password"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Email and password are required"]);
        }
    } else {
        // Invalid token
        echo json_encode(["status" => "error", "message" => "Invalid token"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
