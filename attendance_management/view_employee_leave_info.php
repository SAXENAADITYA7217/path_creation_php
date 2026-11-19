<?php
include('authentication.php');
include('includes/header.php');

$emp_id = $_GET['id'] ?? null;
$masterid = $_SESSION['auth_user']['user_master_id'];

if ($emp_id) {
    $stmt = $con->prepare("
        SELECT 
            l.id AS leave_id,
            l.leave_date,
            l.leave_status,
            l.leave_type,
            e.employee_name,
            e.employee_profile
        FROM employee_leave_use_data l
        INNER JOIN employee_data e ON l.employee_id = e.employee_id
        WHERE e.employee_id = ?
        AND e.masterid = '$masterid'
        AND l.masterid = '$masterid'
        ORDER BY l.leave_date DESC
    ");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $query_run = $stmt->get_result();
} else {
    $query_run = false;
}

?>


<div class="card radius-10">
    <div class="card-body">
        <?php include('message.php'); ?>
        <div class="d-flex align-items-center mb-3">
            <div>
                <h5 class="mb-0">Leave info</h5>
            </div>
            <div class="ms-auto">
                <a href="view_employee_approval.php" class="btn btn-primary btn-sm">Leave Approval</a>
            </div>
        </div>
        <hr>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Profile</th>
                        <th>Employee Name</th>
                        <th>Leave Type</th>
                        <th>Leave Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($query_run) > 0) {
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($query_run)) {
                            ?>
                            <tr>
                                <td><?= $i++; ?></td>
                                <td>
                                    <?php if (!empty($row['employee_profile'])): ?>
                                        <img src="data:image/jpeg;base64,<?= base64_encode($row['employee_profile']); ?>" width="40" height="40" class="rounded-circle">
                                    <?php else: ?>
                                        <img src="assets/default-profile.png" width="40" height="40" class="rounded-circle">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['employee_name']); ?></td>
                                <td>
                                    <?php
                                        if ($row['leave_type'] == 'sick_leave') echo "Sick Leave";
                                        elseif ($row['leave_type'] == 'earned_leave') echo "Earned Leave";
                                        elseif ($row['leave_type'] == 'casual_leave') echo "Casual Leave";
                                        else echo "Unknown";
                                    ?>
                                </td>
                                <td><?= date('d-M-Y', strtotime($row['leave_date'])); ?></td>
                                <td>
                                    <?php
                                        if ($row['leave_status'] == 0) {
                                            echo '<span class="badge bg-warning">Pending</span>';
                                        } elseif ($row['leave_status'] == 1) {
                                            echo '<span class="badge bg-success">Approved</span>';
                                        } elseif ($row['leave_status'] == 2) {
                                            echo '<span class="badge bg-danger">Rejected</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Unknown</span>';
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        if ($row['leave_status'] == 0) {
                                            echo '
                                                <a href="leave_status_update.php?id=' . $row['leave_id'] . '&status=1" class="btn btn-success btn-sm">Approve</a>
                                                <a href="leave_status_update.php?id=' . $row['leave_id'] . '&status=2" class="btn btn-danger btn-sm">Reject</a>
                                            ';
                                        }else {
                                            echo '
                                                <a href="leave_edit.php?id=' . $row['leave_id'] . '" class="btn btn-primary btn-sm">Edit</a>
                                                <a href="leave_delete.php?id=' . $row['leave_id'] .'" class="btn btn-danger btn-sm">Delete</a>
                                            ';
                                        }
                                    ?>
                                </td>        
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No pending leave requests</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<?php
include('includes/footer.php');
include('includes/scripts.php');
?>