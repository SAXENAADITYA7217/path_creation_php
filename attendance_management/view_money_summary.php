<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];
$masterid = mysqli_real_escape_string($con, $masterid);

// Query pulling additions from 'detail' and subtractions from 'settlement'
$query = "
SELECT 
    e.employee_id, e.employee_name, e.employee_profile,
    
    -- Totals from Detail Table
    COALESCE(det.total_adv, 0) as total_advance,
    COALESCE(det.total_due, 0) as total_dues,
    COALESCE(det.total_pen, 0) as total_penalty,

    -- Totals from Settlement Table
    COALESCE(setl.setl_adv, 0) as advance_settlement,
    COALESCE(setl.setl_due, 0) as dues_settlement,
    COALESCE(setl.setl_pen, 0) as penalty_settlement

FROM employee_data e

-- Subquery 1: Get all entries from Detail Table
LEFT JOIN (
    SELECT employee_id,
        SUM(CASE WHEN money_type = 1 THEN money_amount ELSE 0 END) as total_adv,
        SUM(CASE WHEN money_type = 0 THEN money_amount ELSE 0 END) as total_due,
        SUM(CASE WHEN money_type = 2 THEN money_amount ELSE 0 END) as total_pen
    FROM employee_money_detail
    GROUP BY employee_id
) det ON e.employee_id = det.employee_id

-- Subquery 2: Get all entries from Settlement Table
LEFT JOIN (
    SELECT employee_id,
        SUM(CASE WHEN money_type = 1 THEN money_amount ELSE 0 END) as setl_adv,
        SUM(CASE WHEN money_type = 0 THEN money_amount ELSE 0 END) as setl_due,
        SUM(CASE WHEN money_type = 2 THEN money_amount ELSE 0 END) as setl_pen
    FROM employee_money_settlement
    GROUP BY employee_id
) setl ON e.employee_id = setl.employee_id

WHERE e.masterid='$masterid'
GROUP BY e.employee_id
";

$query_run = mysqli_query($con, $query);
?>

<style>
    .money-card { transition: transform 0.2s; border-radius: 15px; overflow: hidden; border: 1px solid #eee !important; }
    .money-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .stat-label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 700; }
    .amount-val { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-weight: 700; font-size: 1.1rem; }
    .bg-soft-success { background-color: #e8f5e9; }
    .bg-soft-primary { background-color: #e3f2fd; }
    .bg-soft-warning { background-color: #fff8e1; }
</style>

<div class="container py-4">
    <div class="mb-4">
        <h4 class="text-dark fw-bold mb-1">Active Financial Records</h4>
        <p class="text-muted small">Showing only employees with pending balances (Calculated from Ledger).</p>
    </div>

    <div class="row">
        <?php
        $hasData = false;
        if(mysqli_num_rows($query_run) > 0) {
            while($row = mysqli_fetch_assoc($query_run)) {
                
                // Balance = (All Details) - (All Settlements)
                $advance_bal = $row['total_advance'] - $row['advance_settlement'];
                $dues_bal    = $row['total_dues'] - $row['dues_settlement'];
                $penalty_bal = $row['total_penalty'] - $row['penalty_settlement'];
                
                // Hide card if everything is settled
                if($advance_bal <= 0 && $dues_bal <= 0 && $penalty_bal <= 0) {
                    continue; 
                }

                $hasData = true; 
                $image = !empty($row['employee_profile']) ? "data:image/jpeg;base64," . base64_encode($row['employee_profile']) : "https://ui-avatars.com/api/?name=".urlencode($row['employee_name'])."&background=random";
        ?>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card money-card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 pt-3 px-3">
                    <div class="d-flex align-items-center">
                        <img src="<?= $image ?>" width="50" height="50" class="rounded-circle border me-3" style="object-fit:cover;">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark"><?= $row['employee_name']; ?></h6>
                            <code class="text-muted small">ID: <?= $row['employee_id']; ?></code>
                        </div>
                    </div>
                </div>
                
                <div class="card-body px-3 pt-2">
                    <div class="p-3 mb-3 bg-soft-success rounded-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="stat-label text-success">Advance Bal.</span>
                            <span class="amount-val text-success">₹<?= number_format($advance_bal, 2); ?></span>
                        </div>
                        <div class="progress mb-2" style="height: 5px; background-color: rgba(0,0,0,0.05);">
                            <?php $perc = ($row['total_advance'] > 0) ? ($row['advance_settlement']/$row['total_advance'])*100 : 0; ?>
                            <div class="progress-bar bg-success" style="width: <?= $perc ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                            <span>Total: ₹<?= number_format($row['total_advance'], 0); ?></span>
                            <span>Settle: ₹<?= number_format($row['advance_settlement'], 0); ?></span>
                        </div>
                    </div>

                    <div class="p-3 mb-3 bg-soft-primary rounded-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="stat-label text-primary">Pending Dues</span>
                            <span class="amount-val text-primary">₹<?= number_format($dues_bal, 2); ?></span>
                        </div>
                        <div class="progress mb-2" style="height: 5px; background-color: rgba(0,0,0,0.05);">
                            <?php $perc = ($row['total_dues'] > 0) ? ($row['dues_settlement']/$row['total_dues'])*100 : 0; ?>
                            <div class="progress-bar bg-primary" style="width: <?= $perc ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                            <span>Total: ₹<?= number_format($row['total_dues'], 0); ?></span>
                            <span>Paid: ₹<?= number_format($row['dues_settlement'], 0); ?></span>
                        </div>
                    </div>

                    <div class="p-3 bg-soft-warning rounded-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="stat-label text-warning">Penalty Due</span>
                            <span class="amount-val text-danger">₹<?= number_format($penalty_bal, 2); ?></span>
                        </div>
                        <div class="progress mb-2" style="height: 5px; background-color: rgba(0,0,0,0.05);">
                            <?php $perc = ($row['total_penalty'] > 0) ? ($row['penalty_settlement']/$row['total_penalty'])*100 : 0; ?>
                            <div class="progress-bar bg-warning" style="width: <?= $perc ?>%"></div>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                            <span>Total: ₹<?= number_format($row['total_penalty'], 0); ?></span>
                            <span>Paid: ₹<?= number_format($row['penalty_settlement'], 0); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-0 pb-3 px-3">
                    <a href="employee_ledger.php?id=<?= $row['employee_id']; ?>" class="btn btn-outline-dark btn-sm w-100 fw-bold shadow-sm" style="border-radius: 8px;">
                        Full History &rarr;
                    </a>
                </div>
            </div>
        </div>
        <?php
            }
        }

        if(!$hasData) {
            echo "<div class='col-12 text-center my-5'>
                    <div class='card border-0 bg-light p-5'>
                        <h5 class='text-muted'>No Pending Balances Found</h5>
                        <p class='text-secondary small'>All employee records in the ledger are fully settled.</p>
                    </div>
                  </div>";
        }
        ?>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>