<?php
date_default_timezone_set('Asia/Kolkata');
include('authentication.php');
include('includes/header.php');

// Class Mapping
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

$user_id = $_SESSION['auth_user']['user_id']; // integer users.id
$user_code = $_SESSION['auth_user']['id'] ?? ''; // varchar code e.g. P000215
$teacher_display_name = !empty($full_name) ? trim($full_name) : trim($_SESSION['auth_user']['user_name'] ?? 'Teacher');

// 1. Fetch assigned classes
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

// 2. Fetch assigned subjects
$class_subjects = [];
$all_subjects = [];
$assigned_subject_items = [];
$q_subjects = mysqli_query($con, "SELECT class_code, subject FROM teacher_subject WHERE teacher_id = '$user_id' OR teacher_id = '$user_code' ORDER BY class_code ASC, subject ASC");
if($q_subjects){
    while($srow = mysqli_fetch_assoc($q_subjects)){
        $cc = (string)$srow['class_code'];
        $sub = trim($srow['subject']);
        if(!empty($sub)){
            $class_subjects[$cc][] = $sub;
            $all_subjects[] = $sub;
            $cName = $classMapping[$cc] ?? ('Class ' . $cc);
            $assigned_subject_items[] = [
                'class_code' => $cc,
                'class_name' => $cName,
                'subject' => $sub
            ];
        }
    }
}
$distinct_subjects = array_values(array_unique($all_subjects));

// 3. Fetch Students Count
$total_students = 0;
$class_student_count = [];
if(!empty($classes)){
    $in_classes = implode("','", array_map('intval', $classes));
    $q_stu = mysqli_query($con, "SELECT class, count(*) as cnt FROM users WHERE role_as = 0 AND class IN ('$in_classes') GROUP BY class");
    if($q_stu){
        while($stur = mysqli_fetch_assoc($q_stu)){
            $cls = (string)$stur['class'];
            $cnt = (int)$stur['cnt'];
            $class_student_count[$cls] = $cnt;
            $total_students += $cnt;
        }
    }
}
$maxStudentCount = 10;
foreach($classes as $c){
    if(!isset($class_student_count[$c])){
        $class_student_count[$c] = 0;
    }
    if($class_student_count[$c] > $maxStudentCount){
        $maxStudentCount = $class_student_count[$c];
    }
}

// Attach student count to assigned_subject_items
foreach($assigned_subject_items as &$item){
    $item['students'] = $class_student_count[$item['class_code']] ?? 0;
}
unset($item);

// Fallback if no specific entries in teacher_subject
if(empty($assigned_subject_items) && !empty($classes)){
    foreach($classes as $cc){
        $cName = $classMapping[$cc] ?? ('Class ' . $cc);
        $stuCount = $class_student_count[$cc] ?? 0;
        $assigned_subject_items[] = [
            'class_code' => $cc,
            'class_name' => $cName,
            'subject' => 'Class Teacher',
            'students' => $stuCount
        ];
    }
}

// 4. Attendance Today
$today = date('Y-m-d');
$today_present = 0;
$today_absent = 0;
$today_holiday = 0;
$class_today_att = [];

if(!empty($classes)){
    $in_classes = implode("','", array_map('intval', $classes));
    $q_att = mysqli_query($con, "SELECT a.status, u.class, count(*) as cnt 
                                 FROM attendance a 
                                 JOIN users u ON a.user_id = u.id 
                                 WHERE u.class IN ('$in_classes') AND a.date = '$today' 
                                 GROUP BY a.status, u.class");
    if($q_att){
        while($attr = mysqli_fetch_assoc($q_att)){
            $st = (int)$attr['status'];
            $cls = (string)$attr['class'];
            $cnt = (int)$attr['cnt'];
            if(!isset($class_today_att[$cls])){
                $class_today_att[$cls] = ['present' => 0, 'absent' => 0, 'holiday' => 0];
            }
            if($st === 1){
                $today_present += $cnt;
                $class_today_att[$cls]['present'] += $cnt;
            } elseif($st === 0){
                $today_absent += $cnt;
                $class_today_att[$cls]['absent'] += $cnt;
            } else {
                $today_holiday += $cnt;
                $class_today_att[$cls]['holiday'] += $cnt;
            }
        }
    }
}

$today_marked_total = $today_present + $today_absent + $today_holiday;
$present_pct = $today_marked_total > 0 ? round(($today_present / $today_marked_total) * 100, 1) : 0;
$absent_pct = $today_marked_total > 0 ? round(($today_absent / $today_marked_total) * 100, 1) : 0;

// Recent Activities data
$recent_student_name = "Rahul Sharma";
$recent_student_class = "Class I";
if(!empty($classes)){
    $in_classes = implode("','", array_map('intval', $classes));
    $q_rst = mysqli_query($con, "SELECT fname, lname, class, created_at FROM users WHERE role_as = 0 AND class IN ('$in_classes') ORDER BY id DESC LIMIT 1");
    if($q_rst && $r_st = mysqli_fetch_assoc($q_rst)){
        $recent_student_name = trim($r_st['fname'] . ' ' . $r_st['lname']);
        $recent_student_class = $classMapping[$r_st['class']] ?? ('Class ' . $r_st['class']);
    }
}

$recent_material_title = "Class Notes & Practice Sheet";
$q_mat = mysqli_query($con, "SELECT title, class_name FROM study_materials ORDER BY id DESC LIMIT 1");
if($q_mat && $r_mat = mysqli_fetch_assoc($q_mat)){
    $recent_material_title = htmlspecialchars($r_mat['title']);
}
?>

<!-- FontAwesome & Plus Jakarta Sans Font for exact match -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* Exact Theme System matching the reference image */
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

.teacher-dashboard-wrap {
    font-family: var(--pca-font) !important;
    color: var(--pca-text-main);
    padding: 0.5rem 0.5rem 2rem 0.5rem;
}

/* Header */
.welcome-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #6366f1;
    margin-bottom: 0.25rem;
}

.dashboard-main-title {
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
    margin-bottom: 0.2rem;
    line-height: 1.2;
}

.dashboard-sub-title {
    color: #64748b;
    font-size: 0.92rem;
    margin-bottom: 0;
}

.date-pill-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.55rem 1rem;
    font-size: 0.86rem;
    font-weight: 600;
    color: #334155;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

/* Stat Cards (Top Row) */
.stat-card-clean {
    border-radius: 16px;
    border: 1px solid transparent;
    padding: 1.2rem 1.3rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
}

.stat-card-clean:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
}

.stat-card-clean.card-students {
    background: linear-gradient(135deg, #f0f1ff 0%, #e8eaff 100%);
    border-color: #e0e3ff;
}

.stat-card-clean.card-courses {
    background: linear-gradient(135deg, #ecfdf5 0%, #e0f8ed 100%);
    border-color: #d1fae5;
}

.stat-card-clean.card-tests {
    background: linear-gradient(135deg, #fff7ed 0%, #feeddc 100%);
    border-color: #ffedd5;
}

.stat-card-clean.card-teachers {
    background: linear-gradient(135deg, #f0f9ff 0%, #dff2fe 100%);
    border-color: #e0f2fe;
}

.stat-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
    margin-right: 0.95rem;
}

.stat-card-clean.card-students .stat-icon-box { background: #e0e3ff; color: #5e48eb; }
.stat-card-clean.card-courses  .stat-icon-box { background: #d1fae5; color: #10b981; }
.stat-card-clean.card-tests    .stat-icon-box { background: #ffedd5; color: #f97316; }
.stat-card-clean.card-teachers .stat-icon-box { background: #bae6fd; color: #0284c7; }

.stat-info-group {
    flex-grow: 1;
}

.stat-header-flex {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.25rem;
}

.stat-label-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
}

.stat-trend-tag {
    font-size: 0.72rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 3px;
}

.stat-trend-tag.trend-up {
    color: #059669;
}

.stat-trend-tag.trend-down {
    color: #dc2626;
}

.stat-number-display {
    font-size: 1.95rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}

/* White Container Cards */
.pca-white-card {
    background: #ffffff;
    border: 1px solid #edf0f5;
    border-radius: 18px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.pca-card-header {
    padding: 1.2rem 1.4rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f3f7;
}

.pca-card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0;
}

.pca-card-body {
    padding: 1.3rem;
    flex-grow: 1;
}

/* Quick Action Grid (All features shown) */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
}

@media (max-width: 1400px) {
    .quick-actions-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 1100px) {
    .quick-actions-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 576px) {
    .quick-actions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.quick-action-tile {
    border-radius: 12px;
    padding: 0.95rem 0.45rem;
    text-align: center;
    text-decoration: none !important;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    min-height: 94px;
    border: 1px solid rgba(0, 0, 0, 0.03);
}

.quick-action-tile:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
    background: #ffffff !important;
    border-color: #e2e8f0;
}

.quick-action-icon-wrap {
    font-size: 1.35rem;
    margin-bottom: 0.4rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.quick-action-name {
    font-size: 0.78rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.25;
}

/* Pastel Tint Accents for Quick Actions */
.action-tile-1 { background: #f0f9ff; }
.action-tile-2 { background: #f5f3ff; }
.action-tile-3 { background: #ecfdf5; }
.action-tile-4 { background: #eef2ff; }
.action-tile-5 { background: #fff7ed; }
.action-tile-6 { background: #f0fdfa; }
.action-tile-7 { background: #fdf2f8; }
.action-tile-8 { background: #fffbeb; }
.action-tile-9 { background: #f3e8ff; }
.action-tile-10 { background: #e6fffa; }
.action-tile-11 { background: #faf5ff; }
.action-tile-12 { background: #fff1f2; }
.action-tile-13 { background: #f0fdf4; }
.action-tile-14 { background: #fef2f2; }
.action-tile-15 { background: #f1f5f9; }

/* Assigned Subjects Progress List & Custom Smooth Scroll */
.assigned-subjects-scroll {
    max-height: 480px;
    overflow-y: auto;
    padding-right: 6px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.assigned-subjects-scroll::-webkit-scrollbar {
    width: 5px;
}

.assigned-subjects-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.assigned-subjects-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.assigned-subjects-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.top-course-item {
    margin-bottom: 1.15rem;
    padding-bottom: 0.25rem;
}

.top-course-item:last-child {
    margin-bottom: 0;
}

.top-course-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0.4rem;
}

.top-course-icon {
    font-size: 0.95rem;
    color: var(--pca-primary);
    flex-shrink: 0;
}

.top-course-name {
    font-size: 0.86rem;
    font-weight: 600;
    color: #334155;
    flex-grow: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.top-course-value {
    font-size: 0.8rem;
    font-weight: 700;
    color: #0f172a;
    flex-shrink: 0;
}

.top-course-progress-track {
    width: 100%;
    height: 7px;
    background: #f1f5f9;
    border-radius: 10px;
    overflow: hidden;
}

.top-course-progress-fill {
    height: 100%;
    border-radius: 10px;
    background: linear-gradient(90deg, #5e48eb 0%, #8b7af7 100%);
    transition: width 0.6s ease;
}

/* Class-wise Table Styling */
.table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
}

.dash-table {
    width: 100%;
    min-width: 540px;
    margin-bottom: 0;
    font-size: 13px;
    border-collapse: separate;
    border-spacing: 0;
}

.dash-table thead th {
    background: #f8fafc;
    color: #64748b;
    font-weight: 600;
    font-size: 12px;
    padding: 13px 16px;
    border-bottom: 1px solid #f1f5f9;
    letter-spacing: 0.2px;
}

.dash-table tbody td {
    padding: 13px 16px;
    border-bottom: 1px solid #f8fafc;
    color: #1e293b;
    vertical-align: middle;
}

.dash-table tbody tr:last-child td {
    border-bottom: none;
}

.dash-table tbody tr:hover td {
    background-color: #f8fafc;
}

.badge-section {
    display: inline-block;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.badge-subject {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    font-size: 11.5px;
    font-weight: 600;
    border-radius: 20px;
    background: #ede9fe;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
    margin-right: 4px;
}

.badge-subject-generic {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    font-size: 11.5px;
    font-weight: 500;
    border-radius: 20px;
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

.badge-pct {
    display: inline-block;
    padding: 4px 10px;
    font-size: 11.5px;
    font-weight: 700;
    border-radius: 6px;
}

.badge-pct-green {
    background: #dcfce7;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

.badge-pct-neutral {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}

/* Recent Activity List */
.activity-list-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.15rem;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.activity-icon-badge {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
    margin-top: 2px;
}

.activity-icon-badge.badge-green  { background: #dcfce7; color: #16a34a; }
.activity-icon-badge.badge-purple { background: #f3e8ff; color: #9333ea; }
.activity-icon-badge.badge-blue   { background: #e0f2fe; color: #0284c7; }
.activity-icon-badge.badge-orange { background: #ffedd5; color: #ea580c; }

.activity-content {
    flex-grow: 1;
}

.activity-title-time {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.activity-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2px;
}

.activity-time {
    font-size: 0.75rem;
    color: #94a3b8;
    white-space: nowrap;
}

.activity-description {
    font-size: 0.82rem;
    color: #64748b;
    margin-bottom: 0;
}
@media (max-width: 768px) {
    .dashboard-main-title {
        font-size: 1.5rem !important;
    }
    .date-pill-badge {
        width: 100% !important;
        justify-content: center !important;
    }
    .teacher-dashboard-wrap {
        padding: 0 0 1.5rem 0 !important;
    }
}
</style>

<div class="teacher-dashboard-wrap">

    <!-- Top Welcome Header (matching image) -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="welcome-label">WELCOME BACK 👋</div>
            <h1 class="dashboard-main-title">Dashboard</h1>
            <p class="dashboard-sub-title">Here's what's happening with your classes and students.</p>
        </div>
        <div>
            <div class="date-pill-badge">
                <i class="far fa-calendar-alt text-primary"></i>
                <span id="liveIndiaDate"><?= date('D, M d, Y'); ?></span>
                <span class="text-muted opacity-50 mx-1">•</span>
                <i class="far fa-clock text-primary"></i>
                <span id="liveIndiaTime"><?= date('h:i:s A'); ?></span>
            </div>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- 4 Clean Stat Cards Row (matching image) -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Students -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-clean card-students">
                <div class="stat-icon-box">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-header-flex">
                        <span class="stat-label-title">Total Students</span>
                        <span class="stat-trend-tag trend-up">
                            <i class="fas fa-arrow-up"></i> +12% <small class="text-muted fw-normal ms-1">vs last month</small>
                        </span>
                    </div>
                    <div class="stat-number-display"><?= $total_students; ?></div>
                </div>
            </div>
        </div>

        <!-- 2. Total Courses / Classes -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-clean card-courses">
                <div class="stat-icon-box">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-header-flex">
                        <span class="stat-label-title">My Classes</span>
                        <span class="stat-trend-tag trend-up">
                            <i class="fas fa-arrow-up"></i> +8% <small class="text-muted fw-normal ms-1">vs last month</small>
                        </span>
                    </div>
                    <div class="stat-number-display"><?= count($classes); ?></div>
                </div>
            </div>
        </div>

        <!-- 3. Test Series / Attendance -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-clean card-tests">
                <div class="stat-icon-box">
                    <i class="far fa-clipboard"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-header-flex">
                        <span class="stat-label-title">Today Attendance</span>
                        <span class="stat-trend-tag <?= $today_marked_total > 0 ? 'trend-up' : 'trend-down'; ?>">
                            <?= $today_marked_total > 0 ? ('<i class="fas fa-arrow-up"></i> ' . $present_pct . '%') : '<i class="fas fa-clock"></i> Pending'; ?>
                        </span>
                    </div>
                    <div class="stat-number-display"><?= $today_marked_total > 0 ? $today_present : $total_students; ?></div>
                </div>
            </div>
        </div>

        <!-- 4. Total Teachers / My Subjects -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-clean card-teachers">
                <div class="stat-icon-box">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-header-flex">
                        <span class="stat-label-title">My Subjects</span>
                        <span class="stat-trend-tag trend-up">
                            <i class="fas fa-arrow-up"></i> +20% <small class="text-muted fw-normal ms-1">vs last month</small>
                        </span>
                    </div>
                    <div class="stat-number-display"><?= count($distinct_subjects); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Quick Actions (Left Col 7) & Assigned Subjects (Right Col 5) - Equal Height -->
    <div class="row g-4 mb-4 align-items-stretch">
        <!-- Quick Actions Card (All 15 Features Shown - No View All) -->
        <div class="col-xl-7 col-lg-7">
            <div class="pca-white-card h-100">
                <div class="pca-card-header">
                    <h5 class="pca-card-title d-flex align-items-center">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Quick Actions
                        <span class="badge rounded-pill ms-2" style="background:#f1f5f9; color:#475569; font-size:0.75rem; font-weight:700;">15 Features</span>
                    </h5>
                </div>
                <div class="pca-card-body d-flex flex-column justify-content-center">
                    <div class="quick-actions-grid">
                        <!-- 1. Show Students -->
                        <a href="get-student.php" class="quick-action-tile action-tile-1" title="View all enrolled students">
                            <div class="quick-action-icon-wrap" style="color: #0284c7;">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="quick-action-name">Show Students</div>
                        </a>

                        <!-- 2. Add Student -->
                        <a href="add-student.php" class="quick-action-tile action-tile-2" title="Register a new student">
                            <div class="quick-action-icon-wrap" style="color: #8b5cf6;">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="quick-action-name">Add Student</div>
                        </a>

                        <!-- 3. Mark Attendance -->
                        <a href="attendance-add.php" class="quick-action-tile action-tile-3" title="Take daily class attendance">
                            <div class="quick-action-icon-wrap" style="color: #10b981;">
                                <i class="far fa-calendar-check"></i>
                            </div>
                            <div class="quick-action-name">Mark Attendance</div>
                        </a>

                        <!-- 4. View Attendance -->
                        <a href="attendance-view.php" class="quick-action-tile action-tile-4" title="Check attendance history">
                            <div class="quick-action-icon-wrap" style="color: #6366f1;">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <div class="quick-action-name">View Attendance</div>
                        </a>

                        <!-- 5. Attendance Percentage -->
                        <a href="attendence-percentage.php" class="quick-action-tile action-tile-10" title="Overall attendance analytics">
                            <div class="quick-action-icon-wrap" style="color: #059669;">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                            <div class="quick-action-name">Attendance %</div>
                        </a>

                        <!-- 6. Add Theory Marks -->
                        <a href="add-report.php" class="quick-action-tile action-tile-5" title="Enter theory test marks">
                            <div class="quick-action-icon-wrap" style="color: #ea580c;">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="quick-action-name">Add Theory</div>
                        </a>

                        <!-- 7. View Theory Marks -->
                        <a href="view-report.php" class="quick-action-tile action-tile-8" title="View student theory reports">
                            <div class="quick-action-icon-wrap" style="color: #d97706;">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="quick-action-name">View Theory</div>
                        </a>

                        <!-- 8. Add Practical Marks -->
                        <a href="add-partical-marks.php" class="quick-action-tile action-tile-6" title="Enter lab & practical marks">
                            <div class="quick-action-icon-wrap" style="color: #0d9488;">
                                <i class="fas fa-flask"></i>
                            </div>
                            <div class="quick-action-name">Add Practical</div>
                        </a>

                        <!-- 9. View Practical Marks -->
                        <a href="view-partical-marks.php" class="quick-action-tile action-tile-13" title="View practical mark records">
                            <div class="quick-action-icon-wrap" style="color: #0891b2;">
                                <i class="fas fa-vial"></i>
                            </div>
                            <div class="quick-action-name">View Practical</div>
                        </a>

                        <!-- 10. Report Card -->
                        <a href="report_card_select.php" class="quick-action-tile action-tile-7" title="Generate comprehensive report card">
                            <div class="quick-action-icon-wrap" style="color: #ec4899;">
                                <i class="far fa-file-alt"></i>
                            </div>
                            <div class="quick-action-name">Report Card</div>
                        </a>

                        <!-- 11. See Rank -->
                        <a href="view_class_rank.php" class="quick-action-tile action-tile-9" title="Class ranking & top scores">
                            <div class="quick-action-icon-wrap" style="color: #8b5cf6;">
                                <i class="fas fa-medal"></i>
                            </div>
                            <div class="quick-action-name">See Rank</div>
                        </a>

                        <!-- 12. Upload Study Material -->
                        <a href="studymaterial.php" class="quick-action-tile action-tile-8" title="Upload notes and assignments">
                            <div class="quick-action-icon-wrap" style="color: #f59e0b;">
                                <i class="fas fa-book-reader"></i>
                            </div>
                            <div class="quick-action-name">Upload Material</div>
                        </a>

                        <!-- 13. View Study Materials -->
                        <a href="view-study-material.php" class="quick-action-tile action-tile-11" title="Browse shared notes">
                            <div class="quick-action-icon-wrap" style="color: #3b82f6;">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <div class="quick-action-name">View Material</div>
                        </a>

                        <!-- 14. Student Complaints -->
                        <a href="viewcomplaints.php" class="quick-action-tile action-tile-14" title="Review student feedbacks & complaints">
                            <div class="quick-action-icon-wrap" style="color: #ef4444;">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                            <div class="quick-action-name">Complaints</div>
                        </a>

                        <!-- 15. My Profile -->
                        <a href="profile.php" class="quick-action-tile action-tile-15" title="Manage teacher profile & settings">
                            <div class="quick-action-icon-wrap" style="color: #6366f1;">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <div class="quick-action-name">My Profile</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Subjects Card (All Subjects Shown - No View All - Scrollable Container) -->
        <div class="col-xl-5 col-lg-5">
            <div class="pca-white-card h-100">
                <div class="pca-card-header">
                    <h5 class="pca-card-title d-flex align-items-center">
                        <i class="fas fa-book-open text-primary me-2"></i>
                        Assigned Subjects
                        <span class="badge rounded-pill ms-2" style="background:#e0e3ff; color:#5e48eb; font-size:0.75rem; font-weight:700;">
                            <?= count($assigned_subject_items); ?> <?= count($assigned_subject_items) === 1 ? 'Subject' : 'Subjects'; ?>
                        </span>
                    </h5>
                </div>
                <div class="pca-card-body d-flex flex-column justify-content-center">
                    <div class="assigned-subjects-scroll">
                        <?php if(!empty($assigned_subject_items)): ?>
                            <?php foreach($assigned_subject_items as $item): 
                                $pct = $maxStudentCount > 0 ? min(100, round(($item['students'] / $maxStudentCount) * 100)) : 30;
                            ?>
                                <div class="top-course-item">
                                    <div class="top-course-header">
                                        <i class="fas fa-book-open top-course-icon"></i>
                                        <span class="top-course-name" title="<?= htmlspecialchars($item['class_name'] . ' — ' . $item['subject']); ?>">
                                            <strong><?= htmlspecialchars($item['class_name']); ?></strong> &bull; <?= htmlspecialchars($item['subject']); ?>
                                        </span>
                                        <span class="top-course-value"><?= $item['students']; ?> <?= $item['students'] === 1 ? 'Student' : 'Students'; ?></span>
                                    </div>
                                    <div class="top-course-progress-track">
                                        <div class="top-course-progress-fill" style="width: <?= max(15, $pct); ?>%;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle fs-3 text-secondary mb-2 d-block"></i>
                                No subjects assigned yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Class-wise Attendance & Details Table (Left Col 8) & Recent Activity (Right Col 4) -->
    <div class="row g-4 align-items-stretch">
        <!-- Class-wise Details Table (Left Col 8) -->
        <div class="col-lg-8">
            <div class="pca-white-card h-100">
                <div class="pca-card-header">
                    <h5 class="pca-card-title d-flex align-items-center">
                        <i class="fas fa-table-list text-primary me-2"></i>
                        Class & Attendance Details
                    </h5>
                </div>
                <div class="pca-card-body p-0">
                    <div class="table-responsive">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th class="ps-3">Class</th>
                                    <th class="text-center">Section</th>
                                    <th>Subject</th>
                                    <th class="text-center">Total Students</th>
                                    <th class="text-end pe-3">Today Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($classes)): ?>
                                    <?php foreach($classes as $c): 
                                        $cName = $classMapping[$c] ?? ('Class ' . $c);
                                        $tot = $class_student_count[$c] ?? 0;
                                        $p = $class_today_att[$c]['present'] ?? 0;
                                        $a = $class_today_att[$c]['absent'] ?? 0;
                                        $m = $p + $a;
                                        $cls_pct = $m > 0 ? round(($p / $m) * 100, 1) : 0;
                                    ?>
                                        <tr>
                                            <td class="ps-3 fw-semibold text-dark"><?= htmlspecialchars($cName); ?></td>
                                            <td class="text-center"><span class="badge-section">A</span></td>
                                            <td>
                                                <?php if(!empty($class_subjects[$c])): ?>
                                                    <?php foreach(array_slice($class_subjects[$c], 0, 3) as $s): ?>
                                                        <span class="badge-subject"><i class="fas fa-book me-1"></i><?= htmlspecialchars($s); ?></span>
                                                    <?php endforeach; ?>
                                                <?php elseif(!empty($distinct_subjects)): ?>
                                                    <span class="badge-subject"><i class="fas fa-book me-1"></i><?= htmlspecialchars($distinct_subjects[0]); ?></span>
                                                <?php else: ?>
                                                    <span class="badge-subject-generic"><i class="fas fa-check me-1"></i>Class Teacher</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center fw-bold text-dark"><?= $tot; ?></td>
                                            <td class="text-end pe-3">
                                                <?php if($m > 0): ?>
                                                    <span class="badge-pct badge-pct-green"><?= $cls_pct; ?>% (<?= $p; ?>/<?= $m; ?>)</span>
                                                <?php else: ?>
                                                    <span class="badge-pct badge-pct-neutral"><i class="far fa-clock me-1"></i>Pending</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No classes assigned.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Card (Right Col 4) -->
        <div class="col-lg-4">
            <div class="pca-white-card h-100">
                <div class="pca-card-header">
                    <h5 class="pca-card-title d-flex align-items-center">
                        <i class="fas fa-clock-rotate-left text-primary me-2"></i>
                        Recent Activity
                    </h5>
                </div>
                <div class="pca-card-body">
                    <div class="activity-list-wrapper">
                        <!-- Activity 1 -->
                        <div class="activity-item">
                            <div class="activity-icon-badge badge-green">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title-time">
                                    <div class="activity-title">Today's Attendance</div>
                                    <div class="activity-time">Today</div>
                                </div>
                                <p class="activity-description">
                                    <?= $today_marked_total > 0 ? ($today_present . " Present, " . $today_absent . " Absent recorded") : "Attendance pending for your assigned classes"; ?>
                                </p>
                            </div>
                        </div>

                        <!-- Activity 2 -->
                        <div class="activity-item">
                            <div class="activity-icon-badge badge-purple">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title-time">
                                    <div class="activity-title">New student registered</div>
                                    <div class="activity-time">Recent</div>
                                </div>
                                <p class="activity-description">
                                    <?= htmlspecialchars($recent_student_name); ?> joined <?= htmlspecialchars($recent_student_class); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Activity 3 -->
                        <div class="activity-item">
                            <div class="activity-icon-badge badge-blue">
                                <i class="far fa-clipboard"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title-time">
                                    <div class="activity-title">Class Assignments</div>
                                    <div class="activity-time">Active</div>
                                </div>
                                <p class="activity-description">
                                    <?= count($classes); ?> Classes & <?= count($distinct_subjects); ?> Subjects assigned
                                </p>
                            </div>
                        </div>

                        <!-- Activity 4 -->
                        <div class="activity-item">
                            <div class="activity-icon-badge badge-orange">
                                <i class="far fa-sticky-note"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title-time">
                                    <div class="activity-title">Study Material</div>
                                    <div class="activity-time">Updated</div>
                                </div>
                                <p class="activity-description">
                                    <?= htmlspecialchars($recent_material_title); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live India Clock & Date updater (matching image)
    function updateIndiaClock() {
        try {
            const now = new Date();
            const dateOptions = {
                timeZone: 'Asia/Kolkata',
                weekday: 'short',
                month: 'short',
                day: '2-digit',
                year: 'numeric'
            };
            const timeOptions = {
                timeZone: 'Asia/Kolkata',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            const dateElem = document.getElementById('liveIndiaDate');
            const timeElem = document.getElementById('liveIndiaTime');
            if (dateElem) {
                dateElem.textContent = new Intl.DateTimeFormat('en-US', dateOptions).format(now);
            }
            if (timeElem) {
                timeElem.textContent = new Intl.DateTimeFormat('en-US', timeOptions).format(now);
            }
        } catch(e) {}
    }
    setInterval(updateIndiaClock, 1000);
});
</script>
