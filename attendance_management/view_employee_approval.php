<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];
// Fetch pending leave requests with employee details
$query = "
    SELECT 
        l.id AS leave_id,
        l.leave_date,
        l.leave_status,
        l.leave_type,
        e.employee_name,
        e.employee_profile
    FROM employee_leave_use_data l
    INNER JOIN employee_data e ON l.employee_id = e.employee_id
    WHERE l.leave_status = 0
    AND l.masterid = '$masterid'
    AND e.masterid = '$masterid'
    ORDER BY l.leave_date ASC
";
$query_run = mysqli_query($con, $query);

?>

<div class="card radius-10">
    <div class="card-body">
        <?php include('message.php'); ?>
        <div class="d-flex align-items-center mb-3">
            <div>
                <h5 class="mb-0">Pending Leave Approvals</h5>
            </div>
            <div class="ms-auto">
                <a href="view_employee.php" class="btn btn-success btn-sm">+ Add Employee Leave</a>
                <a href="view_employee_approved_leave.php" class="btn btn-secondary btn-sm">Approved Leave</a>
                <a href="view_employee_leave.php" class="btn btn-primary btn-sm">Employee Leave</a>
                <a href="add_employee_leave.php" class="btn btn-primary btn-sm">Add Leave</a>
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
                        <th>Actions</th>
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
                                <td><span class="badge bg-warning">Pending</span></td>
                                <td>
                                    <a href="leave_status_update.php?id=<?= $row['leave_id']; ?>&status=1" class="btn btn-success btn-sm">Approve</a>
                                    <a href="leave_status_update.php?id=<?= $row['leave_id']; ?>&status=2" class="btn btn-danger btn-sm">Reject</a>
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
