<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];
// Fetch all holidays
$query = "SELECT * FROM employee_holidays WHERE masterid = '$masterid' ORDER BY date ASC";
$query_run = mysqli_query($con, $query);
?>

<div class="card radius-10">
    <div class="card-body">
        <?php include('message.php'); ?>
        <div class="d-flex align-items-center mb-3">
            <div>
                <h5 class="mb-0">Holidays / Events List</h5>
            </div>
            <div class="ms-auto">
                <a href="holidays_list.php" class="btn btn-success btn-sm">+ Add New Holiday</a>
            </div>
        </div>
        <hr>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Holiday Name</th>
                        <th>Date</th>
                        <th>Type</th>
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
                                <td><?= htmlspecialchars($row['holidays_name']); ?></td>
                                <td><?= date('d-M-Y', strtotime($row['date'])); ?></td>
                                <td>
                                    <?php 
                                        if ($row['type'] == 'all') echo "All";
                                        elseif ($row['type'] == 'overtime') echo "Overtime";
                                        else echo "Non Overtime";
                                    ?>
                                </td>
                                <td>
                                    <a href="edit_holiday.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="delete_holiday.php?id=<?= $row['id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure you want to delete this holiday?');">
                                       Delete
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No holidays found</td></tr>";
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
