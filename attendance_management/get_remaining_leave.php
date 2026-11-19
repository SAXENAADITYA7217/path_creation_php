<?php
include('authentication.php');

if (isset($_POST['employee_id']) && isset($_POST['leave_type'])) {

    $masterid = $_SESSION['auth_user']['user_master_id'];
    $employee_id = $_POST['employee_id'];
    $leave_type  = $_POST['leave_type'];

    // Map column
    switch ($leave_type) {
        case 'sick_leave':
            $leaveColumn = 'employee_sl';
            break;
        case 'earned_leave':
            $leaveColumn = 'employee_el';
            break;
        case 'casual_leave':
            $leaveColumn = 'employee_cl';
            break;
        default:
            echo 0;
            exit();
    }

    // Get assigned leave
    $stmt = $con->prepare("SELECT $leaveColumn 
                           FROM employee_leave 
                           WHERE employee_id = ? 
                           AND masterid = ?");
    $stmt->bind_param("ss", $employee_id, $masterid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo 0;
        exit();
    }

    $row = $result->fetch_assoc();
    $assigned = (int)$row[$leaveColumn];

    // Get used approved leave
    $stmt = $con->prepare("SELECT COUNT(*) as used
                           FROM employee_leave_use_data
                           WHERE employee_id = ?
                           AND leave_type = ?
                           AND leave_status = 1
                           AND masterid = ?");
    $stmt->bind_param("sss", $employee_id, $leave_type, $masterid);
    $stmt->execute();
    $result = $stmt->get_result();
    $usedRow = $result->fetch_assoc();
    $used = (int)$usedRow['used'];

    $remaining = $assigned - $used;

    echo $remaining > 0 ? $remaining : 0;
}
?>
