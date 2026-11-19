<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
include('../admin/config/dbcon.php');

// Check DB connection
if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get JSON input
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    // Validate input
    if (!isset($data['masterid']) || !isset($data['phone_no']) || !isset($data['email'])) {
        echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
        exit;
    }

    $masterid = mysqli_real_escape_string($con, $data['masterid']);
    $phoneno = mysqli_real_escape_string($con, $data['phone_no']);
    $email = mysqli_real_escape_string($con, $data['email']);

    // Prepare query
    $query = "SELECT * FROM users WHERE masterid = ? AND phone_no = ? AND email = ? AND role_as = 9 AND status = 0 LIMIT 1";
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $masterid, $phoneno, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);
            echo json_encode([
                "status" => "success",
                "message" => "Login session valid",
                "user" => $user
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid session or user not found"]);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "Query preparation failed"]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
