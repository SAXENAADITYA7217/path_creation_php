<?php
include('authentication.php');
include('includes/header.php');

// Get Employee ID from URL
if (!isset($_GET['id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$employee_id = mysqli_real_escape_string($con, $_GET['id']);
$masterid = $_SESSION['auth_user']['user_master_id'];

// 1. Fetch Employee Profile Info + Current Balances from employee_data
// Replace 'advance_amount', 'dues_amount', 'penalty_amount' with your actual column names
$emp_query = "SELECT employee_name, employee_id, designation, 
                     advance_amount, dues_amount, panalty_amount 
              FROM employee_data 
              WHERE employee_id='$employee_id' AND masterid='$masterid' LIMIT 1";
$emp_query_run = mysqli_query($con, $emp_query);
$emp_data = mysqli_fetch_assoc($emp_query_run);

// 2. Combined Transaction Query
$query = "
    SELECT 
        money_date, 
        money_description, 
        money_type, 
        money_transaction_type, 
        money_amount, 
        'addition' as source 
    FROM employee_money_detail 
    WHERE employee_id = '$employee_id' AND masterid = '$masterid'

    UNION ALL

    SELECT 
        money_date, 
        money_description, 
        money_type, 
        money_transaction_type, 
        money_amount, 
        'settlement' as source 
    FROM employee_money_settlement 
    WHERE employee_id = '$employee_id' AND masterid = '$masterid'

    ORDER BY money_date DESC
";

$query_run = mysqli_query($con, $query);
?>

<div class="container py-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="fw-bold mb-0"><?= $emp_data['employee_name'] ?></h4>
                    <p class="text-muted small mb-0">Employee ID: #<?= $emp_data['employee_id'] ?></p>
                </div>
                <a href="view_money_summary.php" class="btn btn-sm btn-outline-secondary">Back to Overview</a>
            </div>
            
            <hr class="opacity-10">
            
            <div class="row text-center">
                <div class="col-md-4 border-end">
                    <span class="text-muted small text-uppercase fw-bold">Current Advance</span>
                    <h5 class="text-success fw-bold mb-0">₹<?= number_format($emp_data['advance_amount'], 2) ?></h5>
                </div>
                <div class="col-md-4 border-end">
                    <span class="text-muted small text-uppercase fw-bold">Pending Dues</span>
                    <h5 class="text-primary fw-bold mb-0">₹<?= number_format($emp_data['dues_amount'], 2) ?></h5>
                </div>
                <div class="col-md-4">
                    <span class="text-muted small text-uppercase fw-bold">Penalty Due</span>
                    <h5 class="text-danger fw-bold mb-0">₹<?= number_format($emp_data['panalty_amount'], 2) ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold">Transaction History</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Method</th>
                        <th class="text-center">Category</th>
                        <th class="text-end pe-4">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(mysqli_num_rows($query_run) > 0) {
                        while($row = mysqli_fetch_assoc($query_run)) {
                            
                            $type_label = "";
                            if($row['money_type'] == 0) $type_label = "Dues";
                            elseif($row['money_type'] == 1) $type_label = "Advance";
                            elseif($row['money_type'] == 2) $type_label = "Penalty";

                            $method_label = "";
                            if($row['source'] == 'addition') {
                                $method_label = ($row['money_transaction_type'] == 0) ? "Cash" : "Online";
                            } else {
                                $method_label = ($row['money_transaction_type'] == 0) ? "Salary" : "Other";
                            }

                            $is_settlement = ($row['source'] == 'settlement');
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="text-dark fw-bold"><?= date('d M, Y', strtotime($row['money_date'])) ?></span>
                                </td>
                                <td>
                                    <span class="text-muted small"><?= $row['money_description'] ?: 'No description' ?></span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-light text-dark border"><?= $type_label ?></span>
                                </td>
                                <td>
                                    <span class="text-secondary small fw-semibold"><?= $method_label ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if($is_settlement): ?>
                                        <span class="badge bg-soft-success text-success px-3">Settlement (-)</span>
                                    <?php else: ?>
                                        <span class="badge bg-soft-danger text-danger px-3">Added (+)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="fw-bold <?= $is_settlement ? 'text-success' : 'text-danger' ?>">
                                        <?= $is_settlement ? '-' : '+' ?> ₹<?= number_format($row['money_amount'], 2) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center py-5 text-muted'>No transaction history found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-soft-success { background-color: #e8f5e9; color: #2e7d32; }
    .bg-soft-danger { background-color: #ffebee; color: #c62828; }
    .table thead th { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: #6c757d; }
    .border-end { border-right: 1px solid #eee !important; }
</style>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>