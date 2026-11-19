<?php 
include('studentcode.php');
include('includes/header.php');

// Check login
if (!isset($_SESSION['auth_user']['user_id'])) {
    header('Location: login.php');
    exit();
}

$auth_user_id = $_SESSION['auth_user']['user_id'];

$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

$user_attendance = false;

// If a date range is selected
if (!empty($from_date) && !empty($to_date)) {

    $from_date = mysqli_real_escape_string($con, $from_date);
    $to_date   = mysqli_real_escape_string($con, $to_date);

    $query = "SELECT * FROM attendance 
              WHERE user_id='$auth_user_id' 
              AND date BETWEEN '$from_date' AND '$to_date'
              ORDER BY date DESC";

    $user_attendance = mysqli_query($con, $query);
}
?>

<style>
/* ------------ UI Styling ------------ */
.classic-card {
    background-color: #ffffff; 
    border: 1px solid #d1d9e6;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
}
.classic-title {
    color:#2c3e50; font-weight:700;
    margin-bottom:1.5rem; border-bottom:3px solid #0056b3;
    padding-bottom:0.5rem;
}
.table-classic th {
    background:#e9ecef; color:#495057;
    border-bottom:3px solid #ced4da; font-weight:bold;
}
.table-classic tbody tr:hover { background:#f5f5f5; }
.summary-row td {
    background:#e3f2fd !important; font-weight:bold;
}
.percentage-row td {
    background:#f8f9fa !important; font-size:1.2em;
}
.day-text { color:#6c757d; }
.status-present { color:#28a745; font-weight:bold; }
.status-absent { color:#dc3545; font-weight:bold; }
.status-holiday { color:#0d6efd; font-weight:bold; }
</style>

<div class="container-fluid pt-4 px-4">
    <div class="classic-card rounded p-4">

        <h3 class="text-center classic-title">📊 Attendance Report</h3>

        <!-- FILTER -->
        <form method="GET" class="row g-3 justify-content-center mb-4">
            <div class="col-md-3">
                <label class="fw-bold">From Date:</label>
                <input type="date" name="from_date" class="form-control" required 
                       value="<?= $from_date ?>">
            </div>

            <div class="col-md-3">
                <label class="fw-bold">To Date:</label>
                <input type="date" name="to_date" class="form-control" required 
                       value="<?= $to_date ?>">
            </div>

            <div class="col-md-2 align-self-end">
                <button class="btn btn-primary w-100">Show Report</button>
            </div>
        </form>

        <hr>

        <div class="table-responsive mt-3">
        <?php if ($user_attendance && mysqli_num_rows($user_attendance) > 0): ?>

            <h4 class="mb-3 text-center text-secondary">Attendance Details (Latest First)</h4>

            <table class="table table-bordered table-hover table-classic text-center">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                // Counters
                $presentCount = 0;
                $absentCount = 0;
                $holidayCount = 0;

                // TOTAL DAYS FROM DATE RANGE
                $start = strtotime($from_date);
                $end   = strtotime($to_date);
                $totalDays = ($end - $start) / (60*60*24) + 1;

                // Loop attendance
                while ($row = mysqli_fetch_assoc($user_attendance)) {

                    $date = $row['date'];
                    $formattedDate = date('d-m-Y', strtotime($date));
                    $day = date('l', strtotime($date));

                    if ($row['status'] == 1) {
                        $status = "Present";
                        $css = "status-present";
                        $presentCount++;
                    } 
                    elseif ($row['status'] == 0) {
                        $status = "Absent";
                        $css = "status-absent";
                        $absentCount++;
                    } 
                    else {
                        $status = "Holiday";
                        $css = "status-holiday";
                        $holidayCount++;
                    }
                ?>
                    <tr>
                        <td><?= $formattedDate ?></td>
                        <td class="day-text"><?= $day ?></td>
                        <td class="<?= $css ?>"><?= $status ?></td>
                    </tr>
                <?php } ?>

                <?php 
                // Working Days = Total Days – Holidays
                $workingDays = $totalDays - $holidayCount;

                // Percentage
                $percentage = ($workingDays > 0)
                    ? round(($presentCount / $workingDays) * 100, 2)
                    : 0;
                ?>

                <!-- SUMMARY ROWS -->
                <tr class="summary-row">
                    <td>Present</td>
                    <td colspan="2" class="status-present"><?= $presentCount ?></td>
                </tr>

                <tr class="summary-row">
                    <td>Absent</td>
                    <td colspan="2" class="status-absent"><?= $absentCount ?></td>
                </tr>

                <tr class="summary-row">
                    <td>Holidays</td>
                    <td colspan="2" class="status-holiday"><?= $holidayCount ?></td>
                </tr>

                <tr class="summary-row">
                    <td>Total Days (Selected Range)</td>
                    <td colspan="2" class="fw-bold text-secondary"><?= $totalDays ?></td>
                </tr>

                <tr class="summary-row">
                    <td>Working Days</td>
                    <td colspan="2" class="fw-bold text-warning"><?= $workingDays ?></td>
                </tr>

                <tr class="percentage-row">
                    <td colspan="3">
                        <span class="fw-bold text-secondary">Attendance Percentage:</span>
                        <span class="fw-bolder fs-5 text-primary"><?= $percentage ?>%</span>
                    </td>
                </tr>

                </tbody>
            </table>

        <?php elseif (!empty($from_date) && !empty($to_date)): ?>

            <div class="alert alert-warning text-center">
                No attendance data found for this date range.
            </div>

        <?php else: ?>

            <div class="alert alert-info text-center">
                Please select a date range to view your attendance report.
            </div>

        <?php endif; ?>
        </div>

    </div>
</div>

<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>
