<?php
include('authentication.php');

// Validate parameters
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = intval($_GET['status']); // 1 = Approve, 2 = Reject

    $masterid = $_SESSION['auth_user']['user_master_id'];
    // Update leave_status in DB
    $query = "UPDATE employee_leave_use_data SET leave_status = '$status' WHERE masterid = '$masterid' AND id = '$id'";

    if (mysqli_query($con, $query)) {

        if($status == 1){
            // Step 1: Get employee_id and leave_type for this leave request
            $sql = "SELECT employee_id, leave_type FROM employee_leave_use_data WHERE masterid = '$masterid' AND id = '$id'";
            $result = mysqli_query($con, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $employee_id = $row['employee_id'];
                $leave_type = $row['leave_type'];

                // Step 2: Map leave_type to employee_leave column
                $leave_field = '';
                switch ($leave_type) {
                    case 'sick_leave': 
                        $leave_field = 'employee_sl';
                        break;
                    case 'earned_leave': 
                        $leave_field = 'employee_el';
                        break;
                    case 'casual_leave': 
                        $leave_field = 'employee_cl';
                        break;
                    default:
                        echo "Invalid leave type.";
                        exit;
                }

                // Step 3: Get total allowed leave for this user & leave_type
                $sqlLeave = "SELECT $leave_field AS allowed_leave FROM employee_leave WHERE masterid = '$masterid' AND employee_id = '$employee_id'";
                $resultLeave = mysqli_query($con, $sqlLeave);
                $allowed_leave = 0;

                if ($resultLeave && mysqli_num_rows($resultLeave) > 0) {
                    $rowLeave = mysqli_fetch_assoc($resultLeave);
                    $allowed_leave = intval($rowLeave['allowed_leave']);
                }

                // Step 4: Count approved leaves of this type
                $sqlCount = "SELECT COUNT(*) AS approved_count FROM employee_leave_use_data 
                            WHERE masterid = '$masterid' AND employee_id = '$employee_id' AND leave_type = '$leave_type' AND leave_status = 1";
                $resultCount = mysqli_query($con, $sqlCount);
                $approved_count = 0;

                if ($resultCount && mysqli_num_rows($resultCount) > 0) {
                    $rowCount = mysqli_fetch_assoc($resultCount);
                    $approved_count = intval($rowCount['approved_count']);
                }

                // Step 5: Update current leave request status
                $queryUpdate = "UPDATE employee_leave_use_data SET leave_status = '$status' WHERE masterid = '$masterid' AND id = '$id'";
                if (mysqli_query($con, $queryUpdate)) {

                    // Step 6: If allowed leaves exhausted, reject all pending
                    if ($status == 1 && $approved_count >= $allowed_leave) {
                        $queryRejectPending = "UPDATE employee_leave_use_data 
                                                SET leave_status = 2 
                                                WHERE masterid = '$masterid' 
                                                AND employee_id = '$employee_id' 
                                                AND leave_type = '$leave_type' 
                                                AND leave_status = 0";
                        mysqli_query($con, $queryRejectPending);
                    }


                } else {
                    echo "Error updating record: " . mysqli_error($con);
                }

            } else {
                echo "Record not found!";
            }

        }

        $_SESSION['message'] = "Update Successfully!";
        header("Location: view_employee_approval.php");
        exit;
    } else {
        echo "Error updating record: " . mysqli_error($con);
    }
} else {
    echo "Invalid request";
}

?>