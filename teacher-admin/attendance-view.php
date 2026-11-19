<?php
date_default_timezone_set('Asia/Kolkata');
include('authentication.php');
include('includes/header.php');

// CLASS MAPPING
$classMapping = [
    '100' => 'Nursery',
    '101' => 'LKG',
    '102' => 'UKG',
    '1' => 'Class I',
    '2' => 'Class II',
    '3' => 'Class III',
    '4' => 'Class IV',
    '5' => 'Class V',
    '6' => 'Class VI',
    '7' => 'Class VII',
    '8' => 'Class VIII',
    '9' => 'Class IX',
    '10' => 'Class X',
    '11' => 'Class XI Sci (PCB)',
    '12' => 'Class XI Sci (PCM)',
    '13' => 'Class XI Comm. (with Math)',
    '14' => 'Class XI Comm. (without Math)',
    '15' => 'Class XI Arts',
    '16' => 'Class XII Sci (PCB)',
    '17' => 'Class XII Sci (PCM)',
    '18' => 'Class XII Comm. (with Math)',
    '19' => 'Class XII Comm. (without Math)',
    '20' => 'Class XII Arts'
];

$user_id = $_SESSION['auth_user']['user_id'] ?? '';
$user_code = $_SESSION['auth_user']['id'] ?? '';

// 1. Fetch assigned classes for this teacher
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

if(empty($classes)) {
    echo "<div class='container mt-5'><div class='alert alert-warning text-center rounded-3'><h5>No classes assigned to your account.</h5></div></div>";
    include('includes/footer.php');
    exit;
}

// 2. Class Selection
$selectedClass = $_GET['class'] ?? ($_SESSION['teacher_selected_class'] ?? ($classes[0] ?? ''));
if (!in_array((string)$selectedClass, $classes) && !empty($classes)) {
    $selectedClass = (string)$classes[0];
}
$_SESSION['teacher_selected_class'] = $selectedClass;

// 3. Student Filter
$selectedStudent = isset($_GET['student']) ? trim($_GET['student']) : '';

// 4. Date Selection - Default to today, restricted to today or earlier
$current_date = date('Y-m-d');
$selectedDate = !empty($_GET['date']) ? trim($_GET['date']) : $current_date;
if ($selectedDate > $current_date) {
    $selectedDate = $current_date;
}

// 5. Fetch all students for the dropdown
$selectedClassEsc = mysqli_real_escape_string($con, $selectedClass);
$studentsQuery = "SELECT id, fname, lname, rollno FROM users WHERE class='$selectedClassEsc' AND role_as=0 ORDER BY rollno ASC";
$studentsResult = mysqli_query($con, $studentsQuery);
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

/* Primary Action Buttons */
.btn-primary-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 0.55rem 1.3rem;
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

.btn-mark-link {
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

.btn-mark-link:hover {
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
    margin-bottom: 1.5rem;
}

/* Table Design - Fixed Layout & Responsive Scroll */
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
    .pca-card-header .btn-mark-link {
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

/* Status Badges */
.badge-status-pill {
    font-weight: 700;
    font-size: 0.8rem;
    padding: 0.35rem 0.8rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.badge-status-pill.status-present {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #d1fae5;
}

.badge-status-pill.status-absent {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fee2e2;
}

.badge-status-pill.status-holiday {
    background: #f0f9ff;
    color: #0284c7;
    border: 1px solid #e0f2fe;
}

/* Status Inline Selector */
.status-select-clean {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    padding: 0.35rem 0.65rem;
    font-size: 0.84rem;
    font-weight: 600;
    width: 110px;
    background-color: #f8fafc;
}

.btn-update-status {
    background: #ecfdf5 !important;
    color: #059669 !important;
    border: 1px solid #d1fae5 !important;
    border-radius: 8px !important;
    font-weight: 700 !important;
    font-size: 0.82rem !important;
    padding: 0.35rem 0.85rem !important;
    transition: all 0.15s ease;
}

.btn-update-status:hover {
    background: #d1fae5 !important;
    color: #047857 !important;
}

/* Roll No Pill */
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
</style>

<div class="attendance-page-wrap">

    <!-- Flash message -->
    <?php include('message.php'); ?>

    <!-- Monolithic White Card -->
    <div class="pca-white-card">
        
        <!-- Header -->
        <div class="pca-card-header">
            <h4 class="pca-card-title">
                <i class="fa-solid fa-calendar-check text-primary"></i> Attendance Records
            </h4>
            <a href="attendance-add.php<?= !empty($selectedClass) ? '?class=' . urlencode($selectedClass) : '' ?>" class="btn-mark-link">
                <i class="fa-solid fa-plus me-1 text-primary"></i> Mark Attendance
            </a>
        </div>

        <div class="pca-card-body">
            
            <!-- FILTER FORM (GET) -->
            <form method="GET" class="filter-box-clean">
                <div class="row g-3 align-items-end">
                    
                    <!-- Class Filter -->
                    <div class="col-12 col-md-4">
                        <label class="form-label-clean">Class</label>
                        <select name="class" class="form-select form-control-clean" onchange="this.form.submit()">
                            <?php foreach($classes as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= ($selectedClass == $c) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($classMapping[$c] ?? ('Class ' . $c)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Student Filter -->
                    <div class="col-12 col-md-4">
                        <label class="form-label-clean">Student (Optional)</label>
                        <select name="student" class="form-select form-control-clean">
                            <option value="">-- All Students --</option>
                            <?php
                            if ($studentsResult) {
                                mysqli_data_seek($studentsResult, 0);
                                while($stu = mysqli_fetch_assoc($studentsResult)){
                                    $sId = (string)$stu['id'];
                                    $sName = trim($stu['fname'] . ' ' . $stu['lname']);
                                    $sRoll = !empty($stu['rollno']) ? ' (#' . $stu['rollno'] . ')' : '';
                                    $sel = ($selectedStudent === $sId) ? 'selected' : '';
                                    echo "<option value='{$sId}' {$sel}>" . htmlspecialchars($sName . $sRoll) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Date Filter (Allows selection up to today) -->
                    <div class="col-12 col-md-4">
                        <label class="form-label-clean">Date</label>
                        <div class="d-flex gap-2">
                            <input type="date" name="date" class="form-control form-control-clean" 
                                   value="<?= htmlspecialchars($selectedDate) ?>" 
                                   max="<?= $current_date ?>" required>
                            <button type="submit" class="btn-primary-action">
                                <i class="fa-solid fa-filter me-1"></i> View
                            </button>
                        </div>
                    </div>

                </div>
            </form>

            <?php
            // MAIN ATTENDANCE FETCH
            $attendanceQuery = "SELECT a.*, u.fname, u.lname, u.rollno 
                                FROM attendance a
                                JOIN users u ON a.user_id = u.id
                                WHERE u.class='$selectedClassEsc'";

            if(!empty($selectedStudent)) {
                $selectedStudentEsc = mysqli_real_escape_string($con, $selectedStudent);
                $attendanceQuery .= " AND u.id='$selectedStudentEsc'";
            }

            // Only single date (restricted to selectedDate <= today)
            $selectedDateEsc = mysqli_real_escape_string($con, $selectedDate);
            $attendanceQuery .= " AND a.date = '$selectedDateEsc'";
            $attendanceQuery .= " ORDER BY u.rollno ASC, a.date DESC";

            $attendanceResult = mysqli_query($con, $attendanceQuery);

            if($attendanceResult && mysqli_num_rows($attendanceResult) > 0): ?>

                <div class="table-responsive">
                    <table class="table pca-table">
                        <thead>
                            <tr>
                                <th style="width: 10%; text-align: center;">Roll No</th>
                                <th style="width: 34%;">Student Name</th>
                                <th style="width: 18%;">Date</th>
                                <th style="width: 18%;">Attendance Status</th>
                                <th style="width: 20%; text-align: center;">Quick Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($att = mysqli_fetch_assoc($attendanceResult)):
                                $stVal = (int)$att['status'];
                                $statusClass = ($stVal === 1) ? 'status-present' : (($stVal === 0) ? 'status-absent' : 'status-holiday');
                                $statusIcon = ($stVal === 1) ? 'fa-check' : (($stVal === 0) ? 'fa-xmark' : 'fa-sun');
                                $statusLabel = ($stVal === 1) ? 'Present' : (($stVal === 0) ? 'Absent' : 'Holiday');
                                $stName = trim($att['fname'] . ' ' . $att['lname']);
                            ?>
                                <tr>
                                    <!-- Roll No -->
                                    <td style="text-align: center;">
                                        <span class="badge-roll-pill">#<?= htmlspecialchars($att['rollno'] ?: '—') ?></span>
                                    </td>

                                    <!-- Student Name -->
                                    <td>
                                        <div class="fw-bold text-dark text-truncate" title="<?= htmlspecialchars($stName) ?>">
                                            <?= htmlspecialchars($stName) ?>
                                        </div>
                                    </td>

                                    <!-- Date -->
                                    <td>
                                        <span class="text-muted fw-semibold">
                                            <i class="fa-regular fa-calendar me-1"></i><?= htmlspecialchars($att['date']) ?>
                                        </span>
                                    </td>

                                    <!-- Status Badge -->
                                    <td>
                                        <span class="badge-status-pill <?= $statusClass ?>">
                                            <i class="fa-solid <?= $statusIcon ?>"></i> <?= $statusLabel ?>
                                        </span>
                                    </td>

                                    <!-- Quick Update Form -->
                                    <td style="text-align: center;">
                                        <form method="POST" action="teacher_code.php" class="d-inline-flex align-items-center justify-content-center gap-2 m-0">
                                            <input type="hidden" name="attendance_id" value="<?= (int)$att['id'] ?>">
                                            <input type="hidden" name="class" value="<?= htmlspecialchars($selectedClass) ?>">
                                            <input type="hidden" name="student" value="<?= htmlspecialchars($selectedStudent) ?>">
                                            <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate) ?>">

                                            <select name="status" class="form-select form-select-sm status-select-clean">
                                                <option value="1" <?= ($stVal === 1) ? 'selected' : '' ?>>Present</option>
                                                <option value="0" <?= ($stVal === 0) ? 'selected' : '' ?>>Absent</option>
                                                <option value="2" <?= ($stVal === 2) ? 'selected' : '' ?>>Holiday</option>
                                            </select>

                                            <button type="submit" name="update_attendance" class="btn btn-update-status" title="Save Status">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="text-center py-5">
                    <div style="width: 56px; height: 56px; border-radius: 14px; background: #f0f1ff; color: #5e48eb; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 0.8rem;">
                        <i class="fa-regular fa-calendar-xmark"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">No Attendance Records Found</h5>
                    <p class="text-muted small mb-3">No attendance records were marked for this class on <?= htmlspecialchars($selectedDate) ?>.</p>
                    <a href="attendance-add.php?class=<?= urlencode($selectedClass) ?>" class="btn-primary-action">
                        <i class="fa-solid fa-plus me-1"></i> Mark Attendance Now
                    </a>
                </div>
            <?php endif; ?>

            <!-- Overall Percentage Button Link -->
            <div class="mt-4 pt-3 border-top">
                <a href="attendence-percentage.php" class="btn btn-light border px-4 py-2" style="border-radius: 10px; font-weight: 600; color: #334155; font-size: 0.88rem;">
                    <i class="fa-solid fa-chart-pie text-primary me-2"></i> View Overall Student Attendance Percentage
                </a>
            </div>

        </div>

    </div>

</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
