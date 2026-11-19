<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];
// Fetch employee with leave details
$query = "
    SELECT 
        e.employee_id, 
        e.employee_name, 
        e.employee_profile, 
        l.id AS leave_id,
        l.employee_sl, 
        l.employee_el, 
        l.employee_cl 
    FROM employee_data e
    INNER JOIN employee_leave l ON e.employee_id = l.employee_id
    WHERE e.masterid = '$masterid'
    AND l.masterid = '$masterid'
    ORDER BY e.employee_name ASC
";
$query_run = mysqli_query($con, $query);
?>

<div class="card radius-10">
    <div class="card-body">
        <?php include('message.php'); ?>
        <div class="d-flex align-items-center mb-3">
            <div>
                <h5 class="mb-0">Employee Leave List</h5>
            </div>
            <div class="ms-auto">
                <a href="view_employee.php" class="btn btn-success btn-sm">+ Add Employee Leave</a>
                <a href="view_employee_approved_leave.php" class="btn btn-secondary btn-sm">Approved Leave</a>
                <a href="view_employee_approval.php" class="btn btn-primary btn-sm">Leave Approval</a>
            </div>
        </div>
        <hr>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Emloyee Id</th>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Sick Leave</th>
                        <th>Earned Leave</th>
                        <th>Casual Leave</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($query_run) > 0) {
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($query_run)) {

                            // Count used leaves by type
                            $id = $row['employee_id'];

                            $usedSLQuery = mysqli_query($con, "SELECT COUNT(*) AS used FROM employee_leave_use_data WHERE masterid = '$masterid' AND employee_id = '$id' AND leave_type = 'sick_leave' AND leave_status = 1");
                            $usedSL = mysqli_fetch_assoc($usedSLQuery)['used'];

                            $usedELQuery = mysqli_query($con, "SELECT COUNT(*) AS used FROM employee_leave_use_data WHERE masterid = '$masterid' AND employee_id = '$id' AND leave_type = 'earned_leave' AND leave_status = 1");
                            $usedEL = mysqli_fetch_assoc($usedELQuery)['used'];

                            $usedCLQuery = mysqli_query($con, "SELECT COUNT(*) AS used FROM employee_leave_use_data WHERE masterid = '$masterid' AND employee_id = '$id' AND leave_type = 'casual_leave' AND leave_status = 1");
                            $usedCL = mysqli_fetch_assoc($usedCLQuery)['used'];

                            $profileImage = 'data:image/jpeg;base64,' . base64_encode($row['employee_profile']);
                            ?>
                            <tr>
                                <td><?= $row['employee_id']; ?></td>
                                <td><img src="<?= $profileImage; ?>" width="30" height="30" style="border-radius:50%; object-fit:cover;"></td>
                                <td><?= htmlspecialchars($row['employee_name']); ?></td>
                                <td><?= $usedSL; ?>/<?= htmlspecialchars($row['employee_sl']); ?></td>
                                <td><?= $usedEL; ?>/<?= htmlspecialchars($row['employee_el']); ?></td>
                                <td><?= $usedCL; ?>/<?= htmlspecialchars($row['employee_cl']); ?></td>
                                <td>
                                    <a href="view_employee_leave_info.php?id=<?= $row['employee_id']; ?>" class="btn btn-primary btn-sm">View</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>No Employee Leave Records Found</td></tr>";
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
