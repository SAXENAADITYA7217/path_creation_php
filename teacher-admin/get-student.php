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

// 2. Class selection logic
if (isset($_GET['class']) && in_array((string)$_GET['class'], $classes)) {
    $selected_class = (string)$_GET['class'];
    $_SESSION['teacher_selected_class'] = $selected_class;
} elseif (isset($_SESSION['teacher_selected_class']) && in_array((string)$_SESSION['teacher_selected_class'], $classes)) {
    $selected_class = (string)$_SESSION['teacher_selected_class'];
} elseif (!empty($classes)) {
    $selected_class = (string)$classes[0];
    $_SESSION['teacher_selected_class'] = $selected_class;
} else {
    $selected_class = '';
}

$selected_class_name = $selected_class !== '' ? ($classMapping[$selected_class] ?? ('Class ' . $selected_class)) : 'None';

// 3. Overall Student counts
$total_assigned_students = 0;
$class_counts = [];
if (!empty($classes)) {
    $in_classes = implode("','", array_map('intval', $classes));
    $q_stu_all = mysqli_query($con, "SELECT class, COUNT(*) as cnt FROM users WHERE role_as = 0 AND class IN ('$in_classes') GROUP BY class");
    if ($q_stu_all) {
        while ($st = mysqli_fetch_assoc($q_stu_all)) {
            $class_counts[(string)$st['class']] = (int)$st['cnt'];
            $total_assigned_students += (int)$st['cnt'];
        }
    }
}

// 4. Fetch students for currently selected class
$students_list = [];
if ($selected_class !== '') {
    $selected_class_esc = mysqli_real_escape_string($con, $selected_class);
    $q_students = mysqli_query($con, "SELECT * FROM users WHERE role_as = 0 AND class = '$selected_class_esc' ORDER BY rollno ASC, id ASC");
    if ($q_students) {
        while ($s_row = mysqli_fetch_assoc($q_students)) {
            $students_list[] = $s_row;
        }
    }
}
$current_class_count = count($students_list);

// Palette for avatar initials
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
/* Prevent any horizontal scroll across page and containers */
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

.teacher-dashboard-wrap {
    font-family: var(--pca-font) !important;
    color: var(--pca-text-main);
    padding: 0 0 2rem 0;
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
    box-sizing: border-box;
}

.teacher-dashboard-wrap .row {
    margin-left: 0 !important;
    margin-right: 0 !important;
}

/* Header */
.welcome-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #6366f1;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.dashboard-main-title {
    font-size: 1.85rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
    margin-bottom: 0.2rem;
    line-height: 1.2;
}

.dashboard-sub-title {
    color: #64748b;
    font-size: 0.88rem;
    margin-bottom: 0;
}

.date-pill-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.5rem 0.9rem;
    font-size: 0.84rem;
    font-weight: 600;
    color: #334155;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.btn-primary-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.86rem;
    padding: 0.52rem 1.15rem;
    box-shadow: 0 3px 10px rgba(94, 72, 235, 0.25);
    border: none;
    display: inline-flex;
    align-items: center;
    text-decoration: none !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
}

.btn-primary-action:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    transform: translateY(-2px);
    box-shadow: 0 5px 14px rgba(94, 72, 235, 0.35);
    color: #ffffff !important;
}

/* Stat Cards (Top Row) */
.stat-card-clean {
    border-radius: 14px;
    border: 1px solid transparent;
    padding: 1rem 1.15rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
    overflow: hidden;
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
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
    margin-right: 0.8rem;
}

.stat-card-clean.card-students .stat-icon-box { background: #e0e3ff; color: #5e48eb; }
.stat-card-clean.card-courses  .stat-icon-box { background: #d1fae5; color: #10b981; }
.stat-card-clean.card-tests    .stat-icon-box { background: #ffedd5; color: #f97316; }
.stat-card-clean.card-teachers .stat-icon-box { background: #bae6fd; color: #0284c7; }

.stat-info-group {
    flex-grow: 1;
    min-width: 0;
}

.stat-label-title {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #475569;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stat-number-display {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.15;
    margin-bottom: 0.1rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stat-subtitle-text {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* White Container Card */
.pca-white-card {
    background: #ffffff;
    border: 1px solid #edf0f5;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    width: 100% !important;
    max-width: 100% !important;
}

.pca-card-header {
    padding: 1.1rem 1.3rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f3f7;
    background: #ffffff;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.pca-card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

/* Controls Toolbar */
.toolbar-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    max-width: 100%;
}

.class-select-pill {
    position: relative;
    display: inline-flex;
    align-items: center;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0 0.65rem 0 0.8rem;
    height: 38px;
    transition: all 0.2s ease;
}

.class-select-pill:focus-within {
    border-color: #5e48eb;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12);
}

.class-select-pill select {
    border: none;
    background: transparent;
    font-size: 0.84rem;
    font-weight: 600;
    color: #1e293b;
    outline: none;
    cursor: pointer;
    padding-right: 1.2rem;
    max-width: 160px;
}

.search-input-pill {
    position: relative;
    display: inline-flex;
    align-items: center;
    width: 220px;
    max-width: 100%;
}

.search-input-pill input {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.45rem 2rem 0.45rem 2.2rem;
    font-size: 0.84rem;
    color: #1e293b;
    width: 100%;
    height: 38px;
    transition: all 0.2s ease;
}

.search-input-pill input:focus {
    background: #ffffff;
    border-color: #5e48eb;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12);
    outline: none;
}

.search-input-pill .search-icon {
    position: absolute;
    left: 0.8rem;
    color: #94a3b8;
    font-size: 0.82rem;
    pointer-events: none;
}

.btn-clear-search {
    position: absolute;
    right: 0.65rem;
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 0.8rem;
    padding: 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-clear-search:hover {
    color: #475569;
}

/* Smooth Table Layout - Horizontal Scroll on Mobile, Full Width on Desktop */
.table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    width: 100% !important;
    max-width: 100% !important;
    border-radius: 0 0 16px 16px;
    margin: 0 !important;
    padding: 0 !important;
}

.pca-table {
    margin-bottom: 0;
    width: 100% !important;
    min-width: 720px;
    border-collapse: collapse;
}

.pca-table thead th {
    background-color: #f8fafc !important;
    color: #475569 !important;
    font-size: 0.74rem !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.85rem 0.75rem !important;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top: none !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pca-table tbody td {
    padding: 0.85rem 0.75rem !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #f1f5f9 !important;
    font-size: 0.86rem;
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

/* Roll No Pill */
.badge-roll-pill {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-weight: 700;
    font-size: 0.78rem;
    padding: 0.25rem 0.55rem;
    border-radius: 6px;
    display: inline-block;
    white-space: nowrap;
}

/* Student Avatar Circle */
.student-avatar-circle {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
    flex-shrink: 0;
}

.student-name-text {
    font-weight: 700;
    color: #0f172a;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 0.88rem;
}

.student-email-text {
    font-size: 0.75rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Phone link */
.phone-link {
    color: #334155;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.84rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.phone-link:hover {
    color: #5e48eb;
}

/* Password Pill */
.password-box {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.2rem 0.45rem;
    max-width: 100%;
}

.password-text {
    font-family: monospace;
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    letter-spacing: 0.5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 60px;
    max-width: 100px;
    display: inline-block;
}

.btn-pwd-icon {
    background: none;
    border: none;
    color: #94a3b8;
    padding: 1px 3px;
    font-size: 0.78rem;
    cursor: pointer;
    border-radius: 4px;
    transition: all 0.15s;
    flex-shrink: 0;
}

.btn-pwd-icon:hover {
    color: #5e48eb;
    background: #edf0ff;
}

/* Action Buttons */
.btn-action-edit {
    background: #ecfdf5 !important;
    color: #059669 !important;
    border: 1px solid #d1fae5 !important;
    border-radius: 8px !important;
    width: 32px;
    height: 32px;
    padding: 0 !important;
    font-size: 0.85rem !important;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.btn-action-edit:hover {
    background: #d1fae5 !important;
    color: #047857 !important;
    transform: translateY(-1px);
}

.btn-action-delete {
    background: #fef2f2 !important;
    color: #dc2626 !important;
    border: 1px solid #fee2e2 !important;
    border-radius: 8px !important;
    width: 32px;
    height: 32px;
    padding: 0 !important;
    font-size: 0.85rem !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    cursor: pointer;
    flex-shrink: 0;
}

.btn-action-delete:hover {
    background: #fee2e2 !important;
    color: #b91c1c !important;
    transform: translateY(-1px);
}

/* Empty State */
.empty-state-box {
    padding: 3rem 1.5rem;
    text-align: center;
    width: 100%;
}

.empty-state-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: #f0f1ff;
    color: #5e48eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-bottom: 1rem;
}

.empty-state-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.35rem;
}

.empty-state-desc {
    font-size: 0.88rem;
    color: #64748b;
    max-width: 400px;
    margin: 0 auto 1.3rem auto;
}

/* Footer summary */
.table-footer-summary {
    padding: 0.85rem 1.3rem;
    background: #fafbfe;
    border-top: 1px solid #edf0f5;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.82rem;
    color: #64748b;
    flex-wrap: wrap;
    gap: 0.5rem;
    width: 100%;
}
/* Mobile Responsive Optimization */
@media (max-width: 768px) {
    .teacher-dashboard-wrap {
        padding: 0 0 1.5rem 0;
    }
    .dashboard-main-title {
        font-size: 1.45rem !important;
    }
    .pca-card-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem !important;
        padding: 1rem !important;
    }
    .toolbar-wrap {
        flex-direction: column !important;
        width: 100% !important;
        align-items: stretch !important;
    }
    .class-select-pill, .class-select-pill select, .search-input-pill, .search-input-pill input {
        width: 100% !important;
        max-width: 100% !important;
    }
    .btn-primary-action {
        width: 100% !important;
        justify-content: center !important;
    }
    .date-pill-badge {
        width: 100% !important;
        justify-content: center !important;
    }
    .table-footer-summary {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.5rem !important;
    }
}
</style>

<div class="teacher-dashboard-wrap">

    <!-- Top Greeting & Header Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 px-1">
        <div>
            <div class="welcome-label">
                <i class="fa-solid fa-graduation-cap"></i> Student Management
            </div>
            <h1 class="dashboard-main-title">Students Directory</h1>
            <p class="dashboard-sub-title">View, search, edit and manage students enrolled in your assigned classes.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="date-pill-badge">
                <i class="fa-regular fa-calendar-days text-primary"></i>
                <span><?= date('D, M d, Y') ?></span>
                <span class="text-muted">•</span>
                <span id="liveClockDisplay"><?= date('h:i:s A') ?></span>
            </div>
            <a href="add-student.php<?= $selected_class !== '' ? '?class=' . urlencode($selected_class) : '' ?>" class="btn-primary-action">
                <i class="fa-solid fa-user-plus me-2"></i> Add Student
            </a>
        </div>
    </div>

    <!-- Stat Summary Cards (Row of 4) -->
    <div class="row g-3 mb-3">
        <!-- Stat 1: Students in Current Class -->
        <div class="col-12 col-sm-6 col-xl-3 px-2">
            <div class="stat-card-clean card-students">
                <div class="stat-icon-box">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-label-title">Students in Class</div>
                    <div class="stat-number-display" id="statClassCount"><?= $current_class_count ?></div>
                    <div class="stat-subtitle-text">In <?= htmlspecialchars($selected_class_name) ?></div>
                </div>
            </div>
        </div>

        <!-- Stat 2: Active Class -->
        <div class="col-12 col-sm-6 col-xl-3 px-2">
            <div class="stat-card-clean card-courses">
                <div class="stat-icon-box">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-label-title">Active Class</div>
                    <div class="stat-number-display" style="font-size: 1.35rem;"><?= htmlspecialchars($selected_class_name) ?></div>
                    <div class="stat-subtitle-text">Code ID: #<?= htmlspecialchars($selected_class !== '' ? $selected_class : 'None') ?></div>
                </div>
            </div>
        </div>

        <!-- Stat 3: Assigned Classes -->
        <div class="col-12 col-sm-6 col-xl-3 px-2">
            <div class="stat-card-clean card-teachers">
                <div class="stat-icon-box">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-label-title">Assigned Classes</div>
                    <div class="stat-number-display"><?= count($classes) ?></div>
                    <div class="stat-subtitle-text"><?= count($classes) > 0 ? count($classes) . ' active sections' : 'No assigned class' ?></div>
                </div>
            </div>
        </div>

        <!-- Stat 4: Total Students Across All Classes -->
        <div class="col-12 col-sm-6 col-xl-3 px-2">
            <div class="stat-card-clean card-tests">
                <div class="stat-icon-box">
                    <i class="fa-solid fa-id-card-clip"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-label-title">Total Enrolled</div>
                    <div class="stat-number-display"><?= $total_assigned_students ?></div>
                    <div class="stat-subtitle-text">Across all your classes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Flash Message -->
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3 mx-1" role="alert" style="background: #eef2ff; color: #4338ca; border-left: 4px solid #6366f1 !important; padding: 0.9rem 1.2rem;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-info fs-5"></i>
                <div class="fw-medium"><?= htmlspecialchars($_SESSION['message']); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- Monolithic White Container Card -->
    <div class="pca-white-card">
        
        <!-- Card Header & Filter Toolbar -->
        <div class="pca-card-header">
            <div class="d-flex align-items-center gap-2">
                <h5 class="pca-card-title">
                    <i class="fa-solid fa-address-book text-primary"></i>
                    Student Roster
                </h5>
                <span class="badge rounded-pill" id="studentCountBadge" style="background: #f0f1ff; color: #5e48eb; font-weight: 700; font-size: 0.78rem; padding: 0.3rem 0.7rem;">
                    <?= $current_class_count ?> Enrolled
                </span>
            </div>

            <!-- Toolbar Filters -->
            <div class="toolbar-wrap">
                <!-- Class Selection Form -->
                <form method="GET" action="get-student.php" id="filterForm" class="d-inline-flex m-0">
                    <div class="class-select-pill">
                        <i class="fa-solid fa-graduation-cap text-primary me-2"></i>
                        <select name="class" id="classSelectDropdown" onchange="document.getElementById('filterForm').submit();">
                            <option value="">-- Select Class --</option>
                            <?php foreach($classes as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= ($selected_class == $c) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($classMapping[$c] ?? ('Class ' . $c)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <!-- Real-time Instant Search -->
                <div class="search-input-pill">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="liveStudentSearch" placeholder="Search by name, roll no..." autocomplete="off">
                    <button type="button" id="clearSearchBtn" class="btn-clear-search d-none" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Print Table -->
                <button type="button" class="btn btn-light border-0" onclick="window.print();" title="Print student roster" style="background: #f8fafc; border-radius: 10px; height: 38px; width: 38px; display: inline-flex; align-items: center; justify-content: center; color: #64748b;">
                    <i class="fa-solid fa-print"></i>
                </button>
            </div>
        </div>

        <!-- Fixed Width Table Container with NO Horizontal Scroll -->
        <div class="table-responsive">
            <table class="table pca-table" id="studentsTable">
                <thead>
                    <tr>
                        <th style="width: 8%; text-align: center;">Roll</th>
                        <th style="width: 30%;">Student Information</th>
                        <th style="width: 22%;">Father's Name</th>
                        <th style="width: 15%;">Phone</th>
                        <th style="width: 14%;">Password</th>
                        <th style="width: 11%; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody id="studentsTableBody">
                    <?php if(!empty($students_list)): ?>
                        <?php foreach($students_list as $student): 
                            $student_id = (int)$student['id'];
                            $roll_no = !empty($student['rollno']) ? htmlspecialchars($student['rollno']) : '—';
                            $full_name = trim(($student['fname'] ?? '') . ' ' . ($student['lname'] ?? ''));
                            if(empty($full_name)) $full_name = 'Student #' . $student_id;
                            $email = !empty($student['email']) ? htmlspecialchars($student['email']) : 'No email provided';
                            $father_name = !empty($student['father_name']) ? htmlspecialchars($student['father_name']) : (!empty($student['fathername']) ? htmlspecialchars($student['fathername']) : '—');
                            $phone_no = !empty($student['phone_no']) ? htmlspecialchars($student['phone_no']) : '';
                            $password = !empty($student['password']) ? htmlspecialchars($student['password']) : '';

                            // Avatar initials
                            $fname_initial = mb_substr(trim($student['fname'] ?? 'S'), 0, 1);
                            $lname_initial = mb_substr(trim($student['lname'] ?? ''), 0, 1);
                            $initials = mb_strtoupper($fname_initial . $lname_initial);
                            if(mb_strlen($initials) === 1 && mb_strlen($student['fname'] ?? '') > 1) {
                                $initials = mb_strtoupper(mb_substr(trim($student['fname']), 0, 2));
                            }
                            $palette_idx = abs(crc32($full_name . $student_id)) % count($avatar_palettes);
                            $avatar_color = $avatar_palettes[$palette_idx];

                            // Search index string
                            $search_haystack = strtolower($roll_no . ' ' . $full_name . ' ' . $father_name . ' ' . $email . ' ' . $phone_no);
                        ?>
                            <tr data-student-row data-search="<?= htmlspecialchars($search_haystack) ?>">
                                <!-- Roll No -->
                                <td style="text-align: center;">
                                    <span class="badge-roll-pill">
                                        #<?= $roll_no ?>
                                    </span>
                                </td>

                                <!-- Student Information -->
                                <td>
                                    <div class="d-flex align-items-center gap-2" style="min-width: 0;">
                                        <div class="student-avatar-circle" style="background: <?= $avatar_color['bg'] ?>; color: <?= $avatar_color['text'] ?>;">
                                            <?= htmlspecialchars($initials) ?>
                                        </div>
                                        <div style="min-width: 0; flex-grow: 1;">
                                            <div class="student-name-text" title="<?= htmlspecialchars($full_name) ?>">
                                                <?= htmlspecialchars($full_name) ?>
                                            </div>
                                            <div class="student-email-text" title="<?= $email ?>">
                                                <i class="fa-regular fa-envelope me-1 text-muted"></i><?= $email ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Father's Name -->
                                <td>
                                    <div class="d-flex align-items-center gap-1" style="min-width: 0;" title="<?= $father_name ?>">
                                        <i class="fa-solid fa-user-tie text-muted me-1" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                                        <span class="fw-medium text-dark text-truncate"><?= $father_name ?></span>
                                    </div>
                                </td>

                                <!-- Phone Number -->
                                <td>
                                    <?php if(!empty($phone_no)): ?>
                                        <a href="tel:<?= $phone_no ?>" class="phone-link" title="<?= $phone_no ?>">
                                            <i class="fa-solid fa-phone text-muted me-1" style="font-size: 0.78rem;"></i>
                                            <span><?= $phone_no ?></span>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic" style="font-size: 0.82rem;">No Phone</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Password -->
                                <td>
                                    <?php if(!empty($password)): ?>
                                        <div class="password-box">
                                            <span class="password-text pwd-masked" id="pwdDisplay-<?= $student_id ?>" data-real-pwd="<?= $password ?>" title="Click eye to reveal">••••••••</span>
                                            <button type="button" class="btn-pwd-icon btn-toggle-pwd" data-target="pwdDisplay-<?= $student_id ?>" title="Show / Hide">
                                                <i class="fa-regular fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn-pwd-icon btn-copy-pwd" data-pwd="<?= $password ?>" title="Copy">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic" style="font-size: 0.82rem;">None</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Actions -->
                                <td style="text-align: center;">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <!-- Edit Student -->
                                        <a href="edit-student.php?id=<?= $student_id ?>" class="btn-action-edit" title="Edit Student">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <!-- Delete Student -->
                                        <form action="teacher_code.php" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete <?= htmlspecialchars(addslashes($full_name)); ?>?');">
                                            <input type="hidden" name="delete_student" value="<?= $student_id ?>">
                                            <input type="hidden" name="class" value="<?= htmlspecialchars($selected_class); ?>">
                                            <button type="submit" class="btn-action-delete" title="Delete Student">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Empty search match row (hidden by default) -->
                        <tr id="noSearchMatchRow" style="display: none;">
                            <td colspan="6" class="text-center py-4">
                                <div class="empty-state-box py-2">
                                    <div class="empty-state-icon" style="width: 48px; height: 48px; font-size: 1.3rem; background: #fff7ed; color: #ea580c; margin-bottom: 0.6rem;">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                    </div>
                                    <h6 class="empty-state-title" style="font-size: 1rem;">No matching students found</h6>
                                    <p class="empty-state-desc mb-2" style="font-size: 0.82rem;">
                                        No student records matched your search query in this class.
                                    </p>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="clearLiveSearch();">
                                        Clear Search
                                    </button>
                                </div>
                            </td>
                        </tr>

                    <?php else: ?>
                        <!-- Empty Class State -->
                        <tr>
                            <td colspan="6" class="p-0">
                                <div class="empty-state-box">
                                    <div class="empty-state-icon">
                                        <i class="fa-solid fa-graduation-cap"></i>
                                    </div>
                                    <h5 class="empty-state-title">No Students Found in <?= htmlspecialchars($selected_class_name) ?></h5>
                                    <p class="empty-state-desc">
                                        There are currently no students registered in this class section.
                                    </p>
                                    <a href="add-student.php<?= $selected_class !== '' ? '?class=' . urlencode($selected_class) : '' ?>" class="btn-primary-action">
                                        <i class="fa-solid fa-plus me-2"></i> Add Student
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Table Footer Summary -->
        <div class="table-footer-summary">
            <div>
                Showing <strong id="visibleStudentCount"><?= $current_class_count ?></strong> of <strong><?= $current_class_count ?></strong> students in <strong><?= htmlspecialchars($selected_class_name) ?></strong>
            </div>
            <div>
                <span class="badge rounded-pill bg-white text-muted border px-3 py-1">
                    <i class="fa-solid fa-shield-halved text-success me-1"></i> Class Teacher Access
                </span>
            </div>
        </div>

    </div>

</div>

<!-- Copy notification toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="copyToast" class="toast align-items-center text-white bg-dark border-0 rounded-3 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" style="font-size: 0.85rem;">
                <i class="fa-solid fa-check text-success me-2"></i> Password copied to clipboard!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
// Live Clock
function updateClock() {
    const el = document.getElementById('liveClockDisplay');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
}
setInterval(updateClock, 1000);

// Password Toggle & Copy
document.addEventListener('DOMContentLoaded', function() {
    // Password reveal toggle
    document.querySelectorAll('.btn-toggle-pwd').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetEl = document.getElementById(targetId);
            if (!targetEl) return;
            const icon = this.querySelector('i');
            const realPwd = targetEl.getAttribute('data-real-pwd');

            if (targetEl.classList.contains('pwd-masked')) {
                targetEl.textContent = realPwd;
                targetEl.classList.remove('pwd-masked');
                targetEl.style.maxWidth = '100px';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                targetEl.textContent = '••••••••';
                targetEl.classList.add('pwd-masked');
                targetEl.style.maxWidth = '65px';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Password copy button
    const toastEl = document.getElementById('copyToast');
    const toastInstance = toastEl && typeof bootstrap !== 'undefined' ? new bootstrap.Toast(toastEl, { delay: 2000 }) : null;

    document.querySelectorAll('.btn-copy-pwd').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const pwd = this.getAttribute('data-pwd');
            if (!pwd) return;

            navigator.clipboard.writeText(pwd).then(function() {
                if (toastInstance) {
                    toastInstance.show();
                } else {
                    alert('Password copied to clipboard!');
                }
            }).catch(function() {
                const tmp = document.createElement('textarea');
                tmp.value = pwd;
                document.body.appendChild(tmp);
                tmp.select();
                document.execCommand('copy');
                document.body.removeChild(tmp);
                if (toastInstance) toastInstance.show();
            });
        });
    });

    // Live Instant Search
    const searchInput = document.getElementById('liveStudentSearch');
    const clearBtn = document.getElementById('clearSearchBtn');
    const tableRows = document.querySelectorAll('tr[data-student-row]');
    const noMatchRow = document.getElementById('noSearchMatchRow');
    const visibleCounter = document.getElementById('visibleStudentCount');
    const totalCount = tableRows.length;

    window.clearLiveSearch = function() {
        if (!searchInput) return;
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    };

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            let visibleCount = 0;

            if (clearBtn) {
                if (query.length > 0) {
                    clearBtn.classList.remove('d-none');
                } else {
                    clearBtn.classList.add('d-none');
                }
            }

            tableRows.forEach(function(row) {
                const searchData = row.getAttribute('data-search') || '';
                if (query === '' || searchData.indexOf(query) !== -1) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noMatchRow) {
                noMatchRow.style.display = (visibleCount === 0 && totalCount > 0) ? '' : 'none';
            }

            if (visibleCounter) {
                visibleCounter.textContent = visibleCount;
            }
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', window.clearLiveSearch);
    }
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
