<?php
date_default_timezone_set('Asia/Kolkata');
include('authentication.php');
include('includes/header.php');

$classMapping = [
    '100' => 'Nursery', '101' => 'LKG', '102' => 'UKG',
    '1'=>'Class 1','2'=>'Class 2','3'=>'Class 3','4'=>'Class 4','5'=>'Class 5',
    '6'=>'Class 6','7'=>'Class 7','8'=>'Class 8','9'=>'Class 9','10'=>'Class 10',
    '11'=>'Class 11 Sci (PCB)','12'=>'Class 11 Sci (PCM)',
    '13'=>'Class 11 Comm w/ Math','14'=>'Class 11 Comm w/o Math',
    '15'=>'Class 11 Arts','16'=>'Class 12 Sci (PCB)','17'=>'Class 12 Sci (PCM)',
    '18'=>'Class 12 Comm w/ Math','19'=>'Class 12 Comm w/o Math','20'=>'Class 12 Arts'
];

$user_id = $_SESSION['auth_user']['user_id'] ?? '';
$user_code = $_SESSION['auth_user']['id'] ?? '';

// Fetch assigned classes
$classes = [];
$q_classes = mysqli_query($con, "SELECT DISTINCT class FROM class_teacher WHERE (user_id = '$user_code' OR user_id = '$user_id') AND (class_status = 0 OR class_status IS NULL)");
if ($q_classes) {
    while($row = mysqli_fetch_assoc($q_classes)) {
        if(!empty($row['class']) && !in_array((string)$row['class'], $classes)){
            $classes[] = (string)$row['class'];
        }
    }
}
if(empty($classes) && !empty($user_row['class'])){
    $classes[] = (string)$user_row['class'];
}

if (empty($classes)) {
    echo "<div class='container mt-5'><div class='alert alert-warning text-center rounded-3'><h5>No classes assigned to your account.</h5></div></div>";
    include('includes/footer.php');
    exit;
}

$selectedClass = $_GET['class'] ?? ($_SESSION['teacher_selected_class'] ?? ($classes[0] ?? ''));
if (!in_array((string)$selectedClass, $classes) && !empty($classes)) {
    $selectedClass = (string)$classes[0];
}
$_SESSION['teacher_selected_class'] = $selectedClass;

$current_date = date("Y-m-d");
$fromDate = $_GET['from_date'] ?? $current_date;
$toDate   = $_GET['to_date'] ?? $current_date;

// Students list
$selectedClassEsc = mysqli_real_escape_string($con, $selectedClass);
$stuQ = "SELECT id, fname, lname, rollno FROM users 
         WHERE class='$selectedClassEsc' AND role_as=0 
         ORDER BY rollno ASC";
$stuR = mysqli_query($con, $stuQ);

// Avatar color palettes
$avatar_palettes = [
    ['bg' => '#e0e7ff', 'text' => '#4338ca'],
    ['bg' => '#dcfce7', 'text' => '#15803d'],
    ['bg' => '#ffedd5', 'text' => '#c2410c'],
    ['bg' => '#fce7f3', 'text' => '#be185d'],
    ['bg' => '#e0f2fe', 'text' => '#0369a1'],
    ['bg' => '#f3e8ff', 'text' => '#7e22ce'],
    ['bg' => '#fee2e2', 'text' => '#b91c1c'],
    ['bg' => '#ccfbf1', 'text' => '#0f766e'],
];
?>

<!-- FontAwesome & Plus Jakarta Sans Font for exact theme match -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* Prevent horizontal scroll */
html, body {
    overflow-x: hidden !important;
    max-width: 100vw !important;
}

.page-wrapper, .page-content {
    overflow-x: hidden !important;
    max-width: 100% !important;
}

:root {
    --pca-font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --pca-bg: #f8fafc;
    --pca-card-bg: #ffffff;
    --pca-text-main: #0f172a;
    --pca-text-muted: #64748b;
    --pca-border: #edf0f5;
    --pca-primary: #5e48eb;
    --pca-primary-hover: #4d38d6;
}

.attendance-page-wrap {
    font-family: var(--pca-font) !important;
    color: var(--pca-text-main);
    padding: 0.5rem 0.5rem 2rem 0.5rem;
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
    box-sizing: border-box;
}

/* Monolithic White Card */
.pca-white-card {
    background: #ffffff;
    border: 1px solid #edf0f5;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    width: 100% !important;
}

.pca-card-header {
    padding: 1.1rem 1.4rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f3f7;
    background: #ffffff;
    flex-wrap: wrap;
    gap: 0.8rem;
}

.pca-card-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pca-card-body {
    padding: 1.4rem;
}

.form-label-clean {
    font-size: 0.8rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 0.4rem;
    display: block;
}

.form-control-clean {
    border-radius: 10px !important;
    border: 1px solid #e2e8f0 !important;
    padding: 0.55rem 0.85rem !important;
    font-size: 0.88rem !important;
    color: #1e293b !important;
    background: #f8fafc !important;
    height: 42px;
    transition: all 0.2s ease;
}

.form-control-clean:focus {
    background: #ffffff !important;
    border-color: #5e48eb !important;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12) !important;
    outline: none;
}

.btn-primary-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 0.55rem 1.4rem;
    border: none;
    box-shadow: 0 3px 10px rgba(94, 72, 235, 0.25);
    display: inline-flex;
    align-items: center;
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none !important;
}

.btn-primary-action:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    box-shadow: 0 5px 14px rgba(94, 72, 235, 0.35);
    transform: translateY(-1px);
}

.btn-back-link {
    background: #ffffff;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.86rem;
    padding: 0.5rem 1rem;
    display: inline-flex;
    align-items: center;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.btn-back-link:hover {
    background: #f8fafc;
    color: #0f172a !important;
    border-color: #cbd5e1;
}

/* Filter Box */
.filter-box-clean {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.1rem;
    margin-bottom: 1.3rem;
}

/* Summary Grid */
.summary-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 1.3rem;
}

@media (max-width: 768px) {
    .summary-stats-grid {
        grid-template-columns: 1fr;
    }
}

.summary-stat-tile {
    background: #ffffff;
    border: 1px solid #edf0f5;
    border-radius: 12px;
    padding: 0.9rem 1.1rem;
    display: flex;
    align-items: center;
    gap: 12px;
}

.summary-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.tile-days .summary-stat-icon { background: #f0f1ff; color: #5e48eb; }
.tile-holidays .summary-stat-icon { background: #fff7ed; color: #ea580c; }
.tile-working .summary-stat-icon { background: #ecfdf5; color: #059669; }

.summary-stat-val {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}

.summary-stat-lbl {
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 600;
}

/* Table */
.table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    overflow-y: visible !important;
    width: 100% !important;
    border: 1px solid #edf0f5;
    border-radius: 12px;
    background: #ffffff;
}

.pca-table {
    margin-bottom: 0;
    width: 100% !important;
    min-width: 650px;
    table-layout: fixed !important;
    border-collapse: collapse;
}

@media (max-width: 768px) {
    .pca-card-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem;
    }
    .pca-card-header .btn-primary-action,
    .pca-card-header .btn-back-link {
        width: 100% !important;
        justify-content: center !important;
    }
    .report-page-wrap {
        padding: 0.25rem 0.25rem 1.5rem 0.25rem !important;
    }
    .pca-card-body {
        padding: 1rem 0.85rem !important;
    }
}

.pca-table thead th {
    background-color: #f8fafc !important;
    color: #475569 !important;
    font-size: 0.76rem !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.85rem 1rem !important;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top: none !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pca-table tbody td {
    padding: 0.85rem 1rem !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #f1f5f9 !important;
    font-size: 0.88rem;
    color: #1e293b;
    background: #ffffff;
    overflow: hidden;
}

.pca-table tbody tr:hover td {
    background-color: #fbfbfe !important;
}

.pca-table tbody tr:last-child td {
    border-bottom: none !important;
}

.badge-roll-pill {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 0.25rem 0.55rem;
    border-radius: 6px;
    display: inline-block;
    white-space: nowrap;
}

.student-avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.78rem;
    font-weight: 700;
    flex-shrink: 0;
}

.badge-pct-pill {
    font-weight: 800;
    font-size: 0.84rem;
    padding: 0.3rem 0.75rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
}

.pct-high { background: #ecfdf5; color: #059669; border: 1px solid #d1fae5; }
.pct-mid  { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
.pct-low  { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
</style>

<div class="attendance-page-wrap">

    <div class="pca-white-card">
        
        <!-- Header -->
        <div class="pca-card-header">
            <h4 class="pca-card-title">
                <i class="fa-solid fa-chart-pie text-primary"></i> Student Attendance Percentage
            </h4>
            <a href="attendance-view.php<?= !empty($selectedClass) ? '?class=' . urlencode($selectedClass) : '' ?>" class="btn-back-link">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Attendance
            </a>
        </div>

        <div class="pca-card-body">
            
            <!-- Filters -->
            <form method="GET" class="filter-box-clean">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label-clean">Class</label>
                        <select name="class" class="form-select form-control-clean" onchange="this.form.submit()">
                            <?php foreach($classes as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= ($selectedClass==$c) ? "selected" : "" ?>>
                                    <?= htmlspecialchars($classMapping[$c] ?? ('Class ' . $c)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label-clean">From Date</label>
                        <input type="date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>" class="form-control form-control-clean" required>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label-clean">To Date</label>
                        <input type="date" name="to_date" value="<?= htmlspecialchars($toDate) ?>" class="form-control form-control-clean" required>
                    </div>

                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn-primary-action w-100 justify-content-center">
                            <i class="fa-solid fa-filter me-1"></i> Calculate
                        </button>
                    </div>
                </div>
            </form>

            <?php
            // Exact calculation logic preserved
            $start = strtotime($fromDate);
            $end = strtotime($toDate);
            $totalDays = ($end - $start)/(60*60*24) + 1;

            $fromDateEsc = mysqli_real_escape_string($con, $fromDate);
            $toDateEsc = mysqli_real_escape_string($con, $toDate);

            $holidayQ = "SELECT COUNT(DISTINCT date) AS holidayCount 
                         FROM attendance 
                         WHERE status = 2
                         AND date BETWEEN '$fromDateEsc' AND '$toDateEsc'";
            $holidayR = mysqli_query($con, $holidayQ);
            $holidayData = $holidayR ? mysqli_fetch_assoc($holidayR) : ['holidayCount' => 0];

            $totalHolidays = (int)($holidayData['holidayCount'] ?? 0);
            $totalWorking = $totalDays - $totalHolidays;
            ?>

            <!-- Summary Tiles -->
            <div class="summary-stats-grid">
                <div class="summary-stat-tile tile-days">
                    <div class="summary-stat-icon">
                        <i class="fa-regular fa-calendar-days"></i>
                    </div>
                    <div>
                        <div class="summary-stat-val"><?= $totalDays ?> Days</div>
                        <div class="summary-stat-lbl">Total Period Days</div>
                    </div>
                </div>

                <div class="summary-stat-tile tile-holidays">
                    <div class="summary-stat-icon">
                        <i class="fa-regular fa-sun"></i>
                    </div>
                    <div>
                        <div class="summary-stat-val"><?= $totalHolidays ?> Days</div>
                        <div class="summary-stat-lbl">Holidays in Period</div>
                    </div>
                </div>

                <div class="summary-stat-tile tile-working">
                    <div class="summary-stat-icon">
                        <i class="fa-solid fa-business-time"></i>
                    </div>
                    <div>
                        <div class="summary-stat-val"><?= $totalWorking ?> Days</div>
                        <div class="summary-stat-lbl">Actual Working Days</div>
                    </div>
                </div>
            </div>

            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table pca-table">
                    <thead>
                        <tr>
                            <th style="width: 12%; text-align: center;">Roll No</th>
                            <th style="width: 44%;">Student Name</th>
                            <th style="width: 24%;">Present / Working Days</th>
                            <th style="width: 20%; text-align: center;">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($stuR && mysqli_num_rows($stuR) > 0):
                            mysqli_data_seek($stuR, 0);

                            while($stu = mysqli_fetch_assoc($stuR)):
                                $sid = (int)$stu['id'];
                                $full_name = trim($stu['fname'] . ' ' . $stu['lname']);

                                // Avatar initials
                                $fname_initial = mb_substr(trim($stu['fname'] ?? 'S'), 0, 1);
                                $lname_initial = mb_substr(trim($stu['lname'] ?? ''), 0, 1);
                                $initials = mb_strtoupper($fname_initial . $lname_initial);
                                if(mb_strlen($initials) === 1 && mb_strlen($stu['fname'] ?? '') > 1) {
                                    $initials = mb_strtoupper(mb_substr(trim($stu['fname']), 0, 2));
                                }
                                $palette_idx = abs(crc32($full_name . $sid)) % count($avatar_palettes);
                                $avatar_color = $avatar_palettes[$palette_idx];

                                // Attendance for student - exact logic preserved
                                $attQ = "SELECT status FROM attendance 
                                         WHERE user_id='$sid'
                                         AND date BETWEEN '$fromDateEsc' AND '$toDateEsc'";
                                $attR = mysqli_query($con, $attQ);

                                $present = 0;
                                if ($attR) {
                                    while($a = mysqli_fetch_assoc($attR)) {
                                        if ((int)$a['status'] === 1) $present++;
                                    }
                                }

                                $workingDays = $totalDays - $totalHolidays;
                                $percent = ($workingDays > 0) ? round(($present / $workingDays) * 100, 2) : 0;
                                $pctClass = ($percent >= 75) ? 'pct-high' : (($percent >= 50) ? 'pct-mid' : 'pct-low');
                            ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <span class="badge-roll-pill">#<?= htmlspecialchars($stu['rollno'] ?: '—') ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="student-avatar-circle" style="background: <?= $avatar_color['bg'] ?>; color: <?= $avatar_color['text'] ?>;">
                                                <?= htmlspecialchars($initials) ?>
                                            </div>
                                            <span class="fw-bold text-dark text-truncate" title="<?= htmlspecialchars($full_name) ?>">
                                                <?= htmlspecialchars($full_name) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark"><?= $present ?></span>
                                        <span class="text-muted">/ <?= $workingDays ?> Days</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge-pct-pill <?= $pctClass ?>">
                                            <?= $percent ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    No students found in this class.
                                </td>
                            </tr>
                        <?php endif; ?>
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
