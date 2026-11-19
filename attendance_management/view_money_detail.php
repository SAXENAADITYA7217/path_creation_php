<?php
include('authentication.php');
include('includes/header.php');

date_default_timezone_set('Asia/Kolkata');

$masterid = $_SESSION['auth_user']['user_master_id'];

/* -------------------------------------------------------------------------- */
/* MONTH SELECTION                              */
/* -------------------------------------------------------------------------- */

$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
$displayMonthName = date("F Y", strtotime($currentMonth));

$prevMonth = date('Y-m', strtotime($currentMonth . " -1 month"));
$nextMonth = date('Y-m', strtotime($currentMonth . " +1 month"));

/* -------------------------------------------------------------------------- */
/* TOGGLE TYPE                                 */
/* -------------------------------------------------------------------------- */

$type = isset($_GET['type']) ? $_GET['type'] : 'detail';

$table = ($type == 'settlement') 
    ? 'employee_money_settlement' 
    : 'employee_money_detail';

/* -------------------------------------------------------------------------- */
/* FETCH RECORDS                               */
/* -------------------------------------------------------------------------- */

$query = "SELECT * FROM $table 
          WHERE masterid='$masterid' 
          AND DATE_FORMAT(money_date,'%Y-%m')='$currentMonth' 
          ORDER BY money_date DESC, id DESC";

$query_run = mysqli_query($con, $query);

/* -------------------------------------------------------------------------- */
/* MONTHLY SUMMARY                              */
/* -------------------------------------------------------------------------- */

$summary_query = "SELECT 
                    SUM(CASE WHEN money_type = 0 THEN money_amount ELSE 0 END) AS total_dues,
                    SUM(CASE WHEN money_type = 1 THEN money_amount ELSE 0 END) AS total_advance,
                    SUM(CASE WHEN money_type = 2 THEN money_amount ELSE 0 END) AS total_penalty
                  FROM $table 
                  WHERE masterid='$masterid' 
                  AND DATE_FORMAT(money_date,'%Y-%m')='$currentMonth'";

$summary_run = mysqli_query($con, $summary_query);
$summary = mysqli_fetch_assoc($summary_run);

$total_dues    = $summary['total_dues'] ?? 0;
$total_advance = $summary['total_advance'] ?? 0;
$total_penalty = $summary['total_penalty'] ?? 0;

$net_balance = $total_dues - ($total_advance + $total_penalty);
?>

<?php include('message.php'); ?>

<div class="col-12">
    <div class="card radius-10 overflow-hidden">
        <div class="container mt-4">

            <div class="d-flex align-items-center mb-3">
                <h5 class="mb-0">
                    <?= $type == 'detail' ? 'Employee Money Details' : 'Employee Settlement Details'; ?>
                    (<?= $displayMonthName ?>)
                </h5>

                <div class="ms-auto d-flex align-items-center">
                    <div class="btn-group me-3">
                        <a href="?month=<?= $currentMonth ?>&type=detail" 
                           class="btn btn-sm <?= ($type == 'detail') ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            Money Detail
                        </a>
                        <a href="?month=<?= $currentMonth ?>&type=settlement" 
                           class="btn btn-sm <?= ($type == 'settlement') ? 'btn-warning' : 'btn-outline-warning'; ?>">
                            Settlement Detail
                        </a>
                    </div>

                    <a href="add_money_detail.php" class="btn btn-primary btn-sm me-2">+ Add Money</a>
                    <a href="add_money_settlement.php" class="btn btn-warning btn-sm me-3">+ Add Settlement</a>

                    <a href="view_money_summary.php" class="btn btn-primary btn-sm me-2">View Summary</a>

                    <div class="d-flex align-items-center bg-light p-1 rounded">
                        <a href="?month=<?= $prevMonth ?>&type=<?= $type ?>" class="btn btn-light btn-sm">&lt;</a>
                        <span class="mx-2 small fw-bold"><?= $displayMonthName ?></span>
                        <a href="?month=<?= $nextMonth ?>&type=<?= $type ?>" class="btn btn-light btn-sm">&gt;</a>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row text-center mb-4">
                <div class="col-md-3">
                    <div class="card bg-light-warning border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Total Dues</h6>
                            <h5 class="mb-0">₹ <?= number_format($total_dues, 2); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-success border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Total Advance</h6>
                            <h5 class="mb-0">₹ <?= number_format($total_advance, 2); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-danger border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Total Penalty</h6>
                            <h5 class="mb-0">₹ <?= number_format($total_penalty, 2); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light-primary border-0">
                        <div class="card-body">
                            <h6 class="text-muted">Net Balance</h6>
                            <h5 class="mb-0">₹ <?= number_format($net_balance, 2); ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-hover text-center">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($query_run) > 0) {
                            $i = 1;
                            foreach ($query_run as $row) {
                                // Determine Badge Style
                                if ($row['money_type'] == 0) {
                                    $typeBadge = '<span class="badge bg-warning text-dark">Dues</span>';
                                } elseif ($row['money_type'] == 1) {
                                    $typeBadge = '<span class="badge bg-success">Advance</span>';
                                } else {
                                    $typeBadge = '<span class="badge bg-danger">Penalty</span>';
                                }
                        ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td>
                                        <strong><?= $row['employee_name']; ?></strong><br>
                                        <small class="text-muted"><?= $row['employee_id']; ?></small>
                                    </td>
                                    <td><?= $typeBadge ?></td>
                                    <td><strong>₹ <?= number_format($row['money_amount'], 2); ?></strong></td>
                                    <td><?= $row['money_description']; ?></td>
                                    <td><?= date('d-m-Y', strtotime($row['money_date'])); ?></td>
                                    <td>
                                        <a href="edit_money_detail.php?id=<?= $row['id']; ?>" class="text-primary me-2">
                                            <i class="bx bx-edit-alt"></i>
                                        </a>
                                        <a href="attendance_code.php?delete_money_id=<?= $row['id']; ?>&type=<?= $type ?>" 
                                           class="text-danger" 
                                           onclick="return confirm('Delete this record?')">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo '<tr><td colspan="7" class="py-4">No Records Found for ' . $displayMonthName . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>