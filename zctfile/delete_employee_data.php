<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");

include('../admin/config/dbcon.php');

if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

/* Required fields check */
if (
    empty($data['masterid']) ||
    empty($data['employee_id']) ||
    empty($data['delete_employee_id'])
) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

/* Authorization token check */
if ($data['delete_employee_id'] !== 'peo546s89asd454ascxcs78') {
    echo json_encode(["status" => "error", "message" => "Unauthorized request"]);
    exit;
}

$masterid    = $data['masterid'];
$employee_id = $data['employee_id'];

/* Prepared statement */
$stmt = $con->prepare(
    "DELETE FROM employee_data WHERE masterid = ? AND employee_id = ?"
);

$stmt->bind_param("ss", $masterid, $employee_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Employee deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete employee"]);
}

$stmt->close();
$con->close();
