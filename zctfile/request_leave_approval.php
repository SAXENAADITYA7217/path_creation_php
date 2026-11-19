<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
include('../admin/config/dbcon.php');

date_default_timezone_set('Asia/Kolkata');

if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (isset($data['employee_leave_approval']) && $data['employee_leave_approval'] === 't7rj68489reve654fgt') {

        if (!isset($data['id']) || !isset($data['leave_date']) || !isset($data['leave_type'])) {
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            exit;
        }

        $id            = mysqli_real_escape_string($con, $data['id']);
        $leaveDate     = mysqli_real_escape_string($con, $data['leave_date']);
        $leaveType     = mysqli_real_escape_string($con, $data['leave_type']); // sick_leave, earned_leave, casual_leave
        $leaveReason   = isset($data['leave_reason']) ? mysqli_real_escape_string($con, $data['leave_reason']) : NULL;
        $documentLink  = isset($data['document_link']) ? mysqli_real_escape_string($con, $data['document_link']) : NULL;

        // Map leave_type to employee table column
        $leaveColumn = '';
        if ($leaveType === 'sick_leave') {
            $leaveColumn = 'employee_sl';
        } elseif ($leaveType === 'earned_leave') {
            $leaveColumn = 'employee_el';
        } elseif ($leaveType === 'casual_leave') {
            $leaveColumn = 'employee_cl';
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid leave type"]);
            exit;
        }

        // 1. Get total leaves assigned to employee
        $assignedQuery = "SELECT $leaveColumn FROM employee_leave WHERE id = '$id' LIMIT 1";
        $assignedResult = mysqli_query($con, $assignedQuery);

        if (!$assignedResult || mysqli_num_rows($assignedResult) == 0) {
            echo json_encode(["status" => "error", "message" => "Employee not found"]);
            exit;
        }

        $assignedRow = mysqli_fetch_assoc($assignedResult);
        $assignedLeaves = (int)$assignedRow[$leaveColumn];

        // 2. Check if leave already applied for this date
        $checkDuplicateQuery = "SELECT id FROM employee_leave_use_data 
                                WHERE user_id = '$id'  
                                AND leave_date = '$leaveDate' 
                                LIMIT 1";
        $checkDuplicateResult = mysqli_query($con, $checkDuplicateQuery);

        if (mysqli_num_rows($checkDuplicateResult) > 0) {
            echo json_encode(["status" => "error", "message" => "Leave for this date already exists"]);
            exit;
        }

        // 3. Get used leaves count
        $usedQuery = "SELECT COUNT(*) as used 
                      FROM employee_leave_use_data 
                      WHERE user_id = '$id' AND leave_type = '$leaveType' AND leave_status = '1'";
        $usedResult = mysqli_query($con, $usedQuery);
        $usedRow = mysqli_fetch_assoc($usedResult);
        $usedLeaves = (int)$usedRow['used'];

        // 4. Check remaining leaves
        $remainingLeaves = $assignedLeaves - $usedLeaves;

        if ($remainingLeaves > 0) {
            // 5. Insert new leave record
            $insertQuery = "INSERT INTO employee_leave_use_data (user_id, leave_type, leave_date, leave_reason, document_link) 
                            VALUES ('$id', '$leaveType', '$leaveDate', 
                                    " . ($leaveReason !== NULL ? "'$leaveReason'" : "NULL") . ", 
                                    " . ($documentLink !== NULL ? "'$documentLink'" : "NULL") . ")";
            if (mysqli_query($con, $insertQuery)) {
                echo json_encode(["status" => "success", "message" => "Leave recorded successfully", "remaining" => $remainingLeaves - 1]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database error: " . mysqli_error($con)]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "No remaining leaves available"]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Generate ID"]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
