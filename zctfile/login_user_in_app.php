<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
include('../admin/config/dbcon.php');

// Check DB connection
if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Read JSON input
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    // Validate required fields
    if (!isset($data['email'], $data['password'], $data['loginkey'])) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit;
    }

    $email = mysqli_real_escape_string($con, $data['email']);
    $password = mysqli_real_escape_string($con, $data['password']);
    $loginkey = $data['loginkey'];

    // Check login key
    if ($loginkey !== "sdf94946DWSs4466") {
        echo json_encode(["status" => "error", "message" => "Invalid login key"]);
        exit;
    }

    // Query to check user
    $query = "SELECT masterid, fname, phone_no, email FROM users WHERE email = ? AND password = ? AND role_as = 9 AND status = 0 LIMIT 1";
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $email, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "user" => $user
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid credentials or user not active"]);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "Query preparation failed"]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
