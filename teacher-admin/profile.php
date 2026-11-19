<?php
// FILE: profile.php - Modern Teacher Profile & Academic Overview
include('authentication.php');
include('includes/header.php');

// Class Name Mapping
$class_names = [
    '100' => 'Nursery', '101' => 'LKG', '102' => 'UKG',
    '1' => 'Class 1', '2' => 'Class 2', '3' => 'Class 3', '4' => 'Class 4',
    '5' => 'Class 5', '6' => 'Class 6', '7' => 'Class 7', '8' => 'Class 8',
    '9' => 'Class 9', '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)', '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm. (with Math)', '14' => 'Class 11 Comm. (without Math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)', '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (with Math)', '19' => 'Class 12 Comm. (without Math)',
    '20' => 'Class 12 Arts'
];

// Authentication Check
if (!isset($_SESSION['auth_user']['user_id'])) {
    $_SESSION['message'] = "Please login to continue.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['auth_user']['user_id'];
$user_ids_for_class = $_SESSION['auth_user']['id'] ?? ''; 

// 1. Fetch User Record
$user_id_esc = mysqli_real_escape_string($con, $user_id);
$user_query = "SELECT * FROM users WHERE id = '$user_id_esc' LIMIT 1";
$user_result = mysqli_query($con, $user_query);

if ($user_result && mysqli_num_rows($user_result) > 0) {
    $user = mysqli_fetch_assoc($user_result);
} else {
    $_SESSION['message'] = "User not found!";
    header("Location: index.php");
    exit();
}

// 2. Assigned Classes (Class Teacher Role)
$assigned_classes_codes = [];
$class_query = "SELECT DISTINCT class FROM class_teacher WHERE (user_id = '$user_ids_for_class' OR user_id = '$user_id') AND (class_status = 0 OR class_status IS NULL)";
$class_result = mysqli_query($con, $class_query);

if ($class_result && mysqli_num_rows($class_result) > 0) {
    while ($row = mysqli_fetch_assoc($class_result)) {
        if (!empty($row['class']) && !in_array((string)$row['class'], $assigned_classes_codes)) {
            $assigned_classes_codes[] = (string)$row['class'];
        }
    }
}
if (empty($assigned_classes_codes) && !empty($user['class'])) {
    $assigned_classes_codes[] = (string)$user['class'];
}

// 3. Class student counts and total assigned students
$class_student_counts = [];
$total_assigned_students = 0;
if (!empty($assigned_classes_codes)) {
    $in_classes = implode("','", array_map('intval', $assigned_classes_codes));
    $q_stu = mysqli_query($con, "SELECT class, count(*) as cnt FROM users WHERE role_as = 0 AND class IN ('$in_classes') GROUP BY class");
    if ($q_stu) {
        while ($st = mysqli_fetch_assoc($q_stu)) {
            $cls_k = (string)$st['class'];
            $cnt = (int)$st['cnt'];
            $class_student_counts[$cls_k] = $cnt;
            $total_assigned_students += $cnt;
        }
    }
}

// 4. Assigned Subjects
$assigned_subjects = [];
$total_teaching_subjects_count = 0;
$subject_query = "SELECT class_code, subject FROM teacher_subject WHERE teacher_id = '$user_id' OR teacher_id = '$user_ids_for_class' ORDER BY CAST(class_code AS UNSIGNED) ASC, subject ASC";
$subject_result = mysqli_query($con, $subject_query);

if ($subject_result && mysqli_num_rows($subject_result) > 0) {
    while ($row = mysqli_fetch_assoc($subject_result)) {
        $c_code = (string)$row['class_code'];
        $sub = trim($row['subject']);
        if (!empty($sub)) {
            $assigned_subjects[$c_code][] = $sub;
            $total_teaching_subjects_count++;
        }
    }
}

// Teacher names for queries
$teacher_full_name = trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''));
$teacher_fname = trim($user['fname'] ?? '');
$tname_esc = mysqli_real_escape_string($con, $teacher_full_name);
$fname_esc = mysqli_real_escape_string($con, $teacher_fname);
$session_name_esc = mysqli_real_escape_string($con, $_SESSION['auth_user']['user_name'] ?? '');

// 5. Total Study Materials uploaded by this teacher (Table: study_materials)
$total_materials = 0;
try {
    $q_mat = mysqli_query($con, "SELECT COUNT(*) as cnt FROM study_materials WHERE teacher_name = '$tname_esc' OR teacher_name = '$fname_esc' OR teacher_name = '$session_name_esc'");
    if ($q_mat && $m_row = mysqli_fetch_assoc($q_mat)) {
        $total_materials = (int)$m_row['cnt'];
    }
} catch (Throwable $e) {
    $total_materials = 0;
}

// Profile Photo Info
$photo_source = !empty($user['user_photo']) ? $user['user_photo'] : '';
$has_real_photo = !empty($photo_source) && file_exists('uploads/profile_image/' . $photo_source);
$avatar_initials = mb_strtoupper(mb_substr(trim($user['fname'] ?? 'T'), 0, 1) . mb_substr(trim($user['lname'] ?? ''), 0, 1));
if (empty($avatar_initials)) $avatar_initials = 'TC';

// Teacher employee display code
$display_code = !empty($user['user_id']) ? $user['user_id'] : (!empty($user_ids_for_class) ? $user_ids_for_class : ('#T' . $user['id']));
?>

<!-- FontAwesome & Plus Jakarta Sans Font for exact theme match -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* Prevent horizontal overflow */
html, body {
    overflow-x: hidden !important;
    max-width: 100vw !important;
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
    --pca-primary-gradient: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
}

.teacher-profile-wrapper {
    font-family: var(--pca-font) !important;
    color: var(--pca-text-main);
    padding: 0 0 2.5rem 0;
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
}

/* Page Header */
.welcome-label {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--pca-primary);
    margin-bottom: 0.2rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.dashboard-main-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--pca-text-main);
    letter-spacing: -0.5px;
    margin-bottom: 0.25rem;
}

.dashboard-sub-title {
    font-size: 0.9rem;
    color: var(--pca-text-muted);
    margin-bottom: 0;
}

.date-pill-badge {
    background: #ffffff;
    border: 1px solid var(--pca-border);
    padding: 0.45rem 0.95rem;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--pca-text-main);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

/* Stat Cards (4 Modern Pastel Cards) */
.stat-card-clean {
    border-radius: 16px;
    padding: 1.25rem 1.4rem;
    display: flex;
    align-items: center;
    gap: 16px;
    border: 1px solid transparent;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
    height: 100%;
}

.stat-card-clean:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
}

.card-students { background: #f0fdf4; border-color: #dcfce7; }
.card-students .stat-icon-box { background: #dcfce7; color: #16a34a; }

.card-courses  { background: #eff6ff; border-color: #dbeafe; }
.card-courses .stat-icon-box { background: #dbeafe; color: #2563eb; }

.card-teachers { background: #faf5ff; border-color: #f3e8ff; }
.card-teachers .stat-icon-box { background: #f3e8ff; color: #9333ea; }

.card-tests    { background: #fff7ed; border-color: #ffedd5; }
.card-tests .stat-icon-box { background: #ffedd5; color: #ea580c; }

.stat-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.stat-info-group {
    flex: 1;
    min-width: 0;
}

.stat-label-title {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    margin-bottom: 2px;
}

.stat-number-display {
    font-size: 1.7rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}

.stat-subtitle-text {
    font-size: 0.78rem;
    color: #64748b;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Monolithic White Card Containers */
.pca-white-card {
    background: #ffffff;
    border: 1px solid #edf0f5;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    width: 100% !important;
}

.pca-card-header {
    padding: 1.15rem 1.4rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f3f7;
    background: #ffffff;
    flex-wrap: wrap;
    gap: 0.8rem;
}

.pca-card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pca-card-title i {
    color: var(--pca-primary);
}

.pca-card-body {
    padding: 1.35rem 1.4rem;
}

/* Profile Banner & Avatar */
.profile-banner-clean {
    height: 90px;
    background: linear-gradient(135deg, #5e48eb 0%, #7c3aed 50%, #4f46e5 100%);
    position: relative;
}

.profile-avatar-outer {
    position: relative;
    width: 96px;
    height: 96px;
    margin: -48px auto 0 auto;
    border-radius: 50%;
    background: #ffffff;
    padding: 4px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
}

.profile-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.profile-avatar-initials {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #4338ca;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 800;
}

.btn-upload-camera {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--pca-primary);
    color: #ffffff;
    border: 2px solid #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none !important;
}

.btn-upload-camera:hover {
    background: var(--pca-primary-hover);
    color: #ffffff;
    transform: scale(1.1);
}

.teacher-name-title {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 0.25rem;
}

.badge-teacher-code {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    display: inline-block;
}

.badge-active-faculty {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #d1fae5;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

/* Detail List Items */
.profile-meta-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.profile-meta-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.82rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.88rem;
}

.profile-meta-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.profile-meta-label {
    color: #64748b;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.profile-meta-label i {
    color: #818cf8;
    width: 16px;
    text-align: center;
}

.profile-meta-value {
    color: #0f172a;
    font-weight: 700;
    text-align: right;
    word-break: break-all;
}

/* Assigned Class Clean Card */
.assigned-class-clean-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
    transition: all 0.2s ease;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.assigned-class-clean-card:hover {
    border-color: #cbd5e1;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}

.class-title-badge {
    background: #eef2ff;
    color: #4f46e5;
    font-weight: 800;
    font-size: 0.88rem;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    line-height: 1.25;
}

/* Subject Badges */
.badge-subject-pill {
    background: #f5f3ff;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
    font-weight: 600;
    font-size: 0.84rem;
    padding: 0.38rem 0.8rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s ease;
}

.badge-subject-pill:hover {
    background: #ede9fe;
    color: #5b21b6;
}

/* Guideline Item Cards (Equal Size, Uniform Box Dimensions) */
.guideline-item-card {
    background: #f8fafc;
    border: 1px solid #edf0f5;
    border-radius: 12px;
    padding: 1.15rem 1.2rem;
    height: 100%;
    min-height: 135px;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-sizing: border-box;
}

.guideline-item-card:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
}

.guideline-icon-box {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
    margin-top: 1px;
}

.guideline-content {
    flex: 1;
    min-width: 0;
}

.guideline-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0.35rem;
    line-height: 1.3;
}

.guideline-desc {
    font-size: 0.82rem;
    color: #64748b;
    line-height: 1.45;
    margin-bottom: 0;
}

/* Action Buttons */
.btn-action-primary {
    background: var(--pca-primary-gradient);
    color: #ffffff !important;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.86rem;
    padding: 0.55rem 1.15rem;
    box-shadow: 0 3px 10px rgba(94, 72, 235, 0.25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
}

.btn-action-primary:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    box-shadow: 0 5px 14px rgba(94, 72, 235, 0.35);
    transform: translateY(-1px);
}

.btn-action-outline {
    background: #ffffff;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.86rem;
    padding: 0.52rem 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.btn-action-outline:hover {
    background: #f8fafc;
    color: #0f172a !important;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

/* Modal Styling */
.modal-content-clean {
    border-radius: 16px;
    border: 1px solid #edf0f5;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.modal-header-clean {
    border-bottom: 1px solid #f1f3f7;
    padding: 1.2rem 1.5rem;
}

.modal-footer-clean {
    border-top: 1px solid #f1f3f7;
    padding: 1rem 1.5rem;
}

.form-control-clean {
    border-radius: 10px !important;
    border: 1px solid #e2e8f0 !important;
    padding: 0.6rem 0.9rem !important;
    font-size: 0.88rem !important;
}

.form-control-clean:focus {
    border-color: #5e48eb !important;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12) !important;
    outline: none;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .teacher-profile-wrapper {
        padding: 0 0 1.5rem 0 !important;
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
    .pca-card-body {
        padding: 1rem !important;
    }
    .date-pill-badge {
        width: 100% !important;
        justify-content: center !important;
    }
    .profile-meta-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 3px;
    }
    .profile-meta-value {
        text-align: left;
    }
}
</style>

<div class="teacher-profile-wrapper">

    <!-- Top Greeting & Header Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 px-1">
        <div>
            <div class="welcome-label">
                <i class="fa-solid fa-id-card-clip"></i> Faculty Profile
            </div>
            <h1 class="dashboard-main-title">Teacher Profile &amp; Overview</h1>
            <p class="dashboard-sub-title">Review your faculty details, assigned classes, and academic responsibilities.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="date-pill-badge">
                <i class="fa-regular fa-calendar-days text-primary"></i>
                <span><?= date('D, M d, Y') ?></span>
                <span class="text-muted">•</span>
                <span id="liveClockDisplay"><?= date('h:i:s A') ?></span>
            </div>
            <a href="index.php" class="btn-action-outline">
                <i class="fa-solid fa-house me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Session Flash Message -->
    <?php if(isset($_SESSION['message'])): ?>
        <?php 
            $msg = $_SESSION['message'];
            $is_success = str_starts_with($msg, '✅') || str_contains($msg, 'success');
            $is_warn = str_starts_with($msg, '⚠️') || str_contains($msg, 'warning');
            $bg_color = $is_success ? '#ecfdf5' : ($is_warn ? '#fffbeb' : '#fef2f2');
            $text_color = $is_success ? '#065f46' : ($is_warn ? '#92400e' : '#991b1b');
            $border_color = $is_success ? '#10b981' : ($is_warn ? '#f59e0b' : '#ef4444');
            $icon = $is_success ? 'fa-circle-check' : ($is_warn ? 'fa-triangle-exclamation' : 'fa-circle-exclamation');
        ?>
        <div class="alert alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3 mx-1" role="alert" style="background: <?= $bg_color ?>; color: <?= $text_color ?>; border-left: 4px solid <?= $border_color ?> !important; padding: 0.9rem 1.2rem;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid <?= $icon ?> fs-5"></i>
                <div class="fw-medium"><?= htmlspecialchars($msg); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- Stat Summary Cards Row (4 Modern Pastel Cards) -->
    <div class="row g-3 mb-3">
        <!-- Stat 1: Assigned Students -->
        <div class="col-12 col-sm-6 col-xl-3 px-2">
            <div class="stat-card-clean card-students">
                <div class="stat-icon-box">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-label-title">Enrolled Students</div>
                    <div class="stat-number-display"><?= $total_assigned_students ?></div>
                    <div class="stat-subtitle-text"><?= count($assigned_classes_codes) > 0 ? 'Across ' . count($assigned_classes_codes) . ' classes' : 'No class assigned' ?></div>
                </div>
            </div>
        </div>

        <!-- Stat 2: Assigned Classes -->
        <div class="col-12 col-sm-6 col-xl-3 px-2">
            <div class="stat-card-clean card-courses">
                <div class="stat-icon-box">
                    <i class="fa-solid fa-chalkboard-user"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-label-title">Assigned Classes</div>
                    <div class="stat-number-display"><?= count($assigned_classes_codes) ?></div>
                    <div class="stat-subtitle-text"><?= count($assigned_classes_codes) > 0 ? count($assigned_classes_codes) . ' active class sections' : 'Subject teacher only' ?></div>
                </div>
            </div>
        </div>

        <!-- Stat 3: Teaching Subjects -->
        <div class="col-12 col-sm-6 col-xl-3 px-2">
            <div class="stat-card-clean card-teachers">
                <div class="stat-icon-box">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-label-title">Assigned Subjects</div>
                    <div class="stat-number-display"><?= $total_teaching_subjects_count ?></div>
                    <div class="stat-subtitle-text"><?= count($assigned_subjects) ?> section allocations</div>
                </div>
            </div>
        </div>

        <!-- Stat 4: Study Materials Uploaded -->
        <div class="col-12 col-sm-6 col-xl-3 px-2">
            <div class="stat-card-clean card-tests">
                <div class="stat-icon-box">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-label-title">Study Materials</div>
                    <div class="stat-number-display"><?= $total_materials ?></div>
                    <div class="stat-subtitle-text">Shared notes &amp; files</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ROW 1: TOP TWO BOXES (Profile Summary vs Assigned Classes - EXACT SAME SIZE & HEIGHT) -->
    <div class="row g-3 mb-3 align-items-stretch">

        <!-- Top Left Box: Profile Summary Card -->
        <div class="col-12 col-lg-6 d-flex">
            <div class="pca-white-card w-100 h-100 d-flex flex-column">
                <div class="profile-banner-clean flex-shrink-0"></div>
                
                <div class="pca-card-body pt-0 text-center d-flex flex-column justify-content-center flex-grow-1">
                    <!-- Avatar with Camera Button for Photo Update -->
                    <div class="profile-avatar-outer">
                        <?php if($has_real_photo): ?>
                            <img src="uploads/profile_image/<?= htmlspecialchars($photo_source) ?>" class="profile-avatar-img" alt="Profile Photo">
                        <?php else: ?>
                            <div class="profile-avatar-initials"><?= $avatar_initials ?></div>
                        <?php endif; ?>
                        
                        <a href="javascript:;" class="btn-upload-camera" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal" title="Change Profile Picture">
                            <i class="fa-solid fa-camera"></i>
                        </a>
                    </div>

                    <!-- Teacher Name & Info -->
                    <h3 class="teacher-name-title mt-3"><?= htmlspecialchars($teacher_full_name) ?></h3>
                    <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap mb-1">
                        <span class="badge-teacher-code">ID: <?= htmlspecialchars($display_code) ?></span>
                        <span class="badge-active-faculty">
                            <i class="fa-solid fa-circle-check"></i> Active Faculty
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Right Box: Assigned Classes Card (Class Teacher Role) -->
        <div class="col-12 col-lg-6 d-flex">
            <div class="pca-white-card w-100 h-100 d-flex flex-column">
                <div class="pca-card-header flex-shrink-0">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="pca-card-title">
                            <i class="fa-solid fa-graduation-cap text-primary"></i> Assigned Classes
                        </h5>
                        <span class="badge rounded-pill" style="background: #eef2ff; color: #4f46e5; font-weight: 700; font-size: 0.78rem; padding: 0.3rem 0.7rem;">
                            <?= count($assigned_classes_codes) ?> Assigned
                        </span>
                    </div>
                </div>
                <div class="pca-card-body d-flex flex-column justify-content-center flex-grow-1">
                    <?php if(!empty($assigned_classes_codes)): ?>
                        <div class="row g-3 h-100 align-items-stretch">
                            <?php foreach($assigned_classes_codes as $code): 
                                $c_title = $class_names[$code] ?? ('Class ' . $code);
                                $stu_count = $class_student_counts[$code] ?? 0;
                            ?>
                                <div class="col-12 col-sm-6 d-flex">
                                    <div class="assigned-class-clean-card w-100">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="class-title-badge">
                                                <i class="fa-solid fa-chalkboard-user"></i> <?= htmlspecialchars($c_title) ?>
                                            </span>
                                        </div>
                                        <div class="text-end flex-shrink-0 ms-2">
                                            <span class="fw-bold text-dark d-block fs-6"><?= $stu_count ?></span>
                                            <span class="text-muted small">Students</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted my-auto">
                            <i class="fa-solid fa-chalkboard-user fa-3x mb-2 text-muted opacity-50"></i>
                            <h6 class="fw-bold text-dark">No Class Teacher Role Assigned</h6>
                            <p class="small text-muted mb-0">You are currently assigned subject-specific teaching roles.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- ROW 2: BOTTOM TWO BOXES (Personal Details vs Assigned Teaching Subjects - EXACT SAME SIZE & HEIGHT) -->
    <div class="row g-3 mb-3 align-items-stretch">

        <!-- Bottom Left Box: Personal Information Details -->
        <div class="col-12 col-lg-6 d-flex">
            <div class="pca-white-card w-100 h-100 d-flex flex-column">
                <div class="pca-card-header flex-shrink-0">
                    <h5 class="pca-card-title">
                        <i class="fa-solid fa-address-card text-primary"></i> Personal Details
                    </h5>
                </div>
                <div class="pca-card-body d-flex flex-column justify-content-between flex-grow-1">
                    <ul class="profile-meta-list d-flex flex-column justify-content-between h-100 my-auto">
                        <li class="profile-meta-item">
                            <span class="profile-meta-label"><i class="fa-solid fa-user"></i> Full Name</span>
                            <span class="profile-meta-value"><?= htmlspecialchars($teacher_full_name) ?></span>
                        </li>
                        <li class="profile-meta-item">
                            <span class="profile-meta-label"><i class="fa-solid fa-user-tag"></i> Father's Name</span>
                            <span class="profile-meta-value"><?= !empty($user['father_name']) ? htmlspecialchars($user['father_name']) : '—' ?></span>
                        </li>
                        <li class="profile-meta-item">
                            <span class="profile-meta-label"><i class="fa-solid fa-envelope"></i> Email Address</span>
                            <span class="profile-meta-value">
                                <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="text-primary text-decoration-none fw-bold">
                                    <?= htmlspecialchars($user['email']) ?>
                                </a>
                            </span>
                        </li>
                        <li class="profile-meta-item">
                            <span class="profile-meta-label"><i class="fa-solid fa-phone"></i> Phone Number</span>
                            <span class="profile-meta-value">
                                <a href="tel:<?= htmlspecialchars($user['phone_no']) ?>" class="text-dark text-decoration-none fw-bold">
                                    +91 <?= htmlspecialchars($user['phone_no']) ?>
                                </a>
                            </span>
                        </li>
                        <li class="profile-meta-item">
                            <span class="profile-meta-label"><i class="fa-solid fa-shield-halved"></i> Faculty Role</span>
                            <span class="profile-meta-value">Teacher / Academic Staff</span>
                        </li>
                        <li class="profile-meta-item">
                            <span class="profile-meta-label"><i class="fa-regular fa-calendar"></i> Member Since</span>
                            <span class="profile-meta-value"><?= !empty($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'Active' ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Bottom Right Box: Assigned Teaching Subjects (Teaching Roster) -->
        <div class="col-12 col-lg-6 d-flex">
            <div class="pca-white-card w-100 h-100 d-flex flex-column">
                <div class="pca-card-header flex-shrink-0">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="pca-card-title">
                            <i class="fa-solid fa-book-open text-primary"></i> Assigned Teaching Subjects
                        </h5>
                        <span class="badge rounded-pill" style="background: #f5f3ff; color: #7c3aed; font-weight: 700; font-size: 0.78rem; padding: 0.3rem 0.7rem;">
                            <?= $total_teaching_subjects_count ?> Subjects
                        </span>
                    </div>
                </div>
                <div class="pca-card-body d-flex flex-column justify-content-between flex-grow-1">
                    <?php if(!empty($assigned_subjects)): ?>
                        <div class="row g-3 h-100 align-items-stretch">
                            <?php foreach($assigned_subjects as $class_code => $subjects): 
                                $c_name = $class_names[$class_code] ?? ('Class ' . $class_code);
                            ?>
                                <div class="col-12 col-sm-6 d-flex">
                                    <div class="p-3 rounded-3 border bg-white w-100 h-100 shadow-sm d-flex flex-column">
                                        <div class="d-flex align-items-center justify-content-between pb-2 mb-2 border-bottom flex-shrink-0">
                                            <span class="fw-bold text-dark" style="font-size: 0.92rem;">
                                                <i class="fa-solid fa-layer-group text-primary me-1"></i> <?= htmlspecialchars($c_name) ?>
                                            </span>
                                            <span class="badge rounded-pill bg-light text-muted border" style="font-size: 0.75rem;">
                                                <?= count($subjects) ?> <?= count($subjects) === 1 ? 'Subject' : 'Subjects' ?>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2 pt-1 flex-grow-1 align-content-start">
                                            <?php foreach($subjects as $sub): ?>
                                                <span class="badge-subject-pill">
                                                    <i class="fa-solid fa-book"></i> <?= htmlspecialchars($sub) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4 text-muted my-auto">
                            <i class="fa-solid fa-book-bookmark fa-3x mb-2 text-muted opacity-50"></i>
                            <h6 class="fw-bold text-dark">No Subjects Assigned</h6>
                            <p class="small text-muted mb-0">Contact the academy administrator to map your teaching subjects.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- ROW 3: FULL-WIDTH GUIDELINES BOX (All 8 boxes identical equal size) -->
    <div class="row g-3">
        <div class="col-12">
            <div class="pca-white-card">
                <div class="pca-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="pca-card-title mb-0">
                            <i class="fa-solid fa-clipboard-check text-primary"></i> Faculty Guidelines &amp; Academic Responsibilities
                        </h5>
                        <span class="badge rounded-pill" style="background: #eef2ff; color: #4f46e5; font-weight: 700; font-size: 0.78rem; padding: 0.35rem 0.75rem;">
                            8 Core Standards
                        </span>
                    </div>
                    <span class="text-muted small">Standard Academic Protocol &amp; Institutional Compliance</span>
                </div>
                <div class="pca-card-body">
                    <div class="row g-3">
                        <!-- 1. Daily Attendance Tracking -->
                        <div class="col-12 col-sm-6 col-xl-3 d-flex">
                            <div class="guideline-item-card w-100">
                                <div class="guideline-icon-box" style="background:#ecfdf5; color:#059669;">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <div class="guideline-content">
                                    <h6 class="guideline-title">Daily Attendance Tracking</h6>
                                    <p class="guideline-desc">Mark classroom attendance every morning before 10:30 AM to generate accurate monthly percentage and eligibility reports.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Marks Entry & Evaluation -->
                        <div class="col-12 col-sm-6 col-xl-3 d-flex">
                            <div class="guideline-item-card w-100">
                                <div class="guideline-icon-box" style="background:#f0f1ff; color:#5e48eb;">
                                    <i class="fa-solid fa-chart-line"></i>
                                </div>
                                <div class="guideline-content">
                                    <h6 class="guideline-title">Marks Entry &amp; Evaluation</h6>
                                    <p class="guideline-desc">Promptly record unit tests, practical assessments, and term exam marks so report cards and rankings update on schedule.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Curriculum Pacing & Lesson Planning -->
                        <div class="col-12 col-sm-6 col-xl-3 d-flex">
                            <div class="guideline-item-card w-100">
                                <div class="guideline-icon-box" style="background:#fff7ed; color:#ea580c;">
                                    <i class="fa-solid fa-book-bookmark"></i>
                                </div>
                                <div class="guideline-content">
                                    <h6 class="guideline-title">Curriculum &amp; Lesson Planning</h6>
                                    <p class="guideline-desc">Follow syllabus benchmarks diligently, complete chapters before scheduled test windows, and conduct timely revision drills.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Study Notes & Digital Resources -->
                        <div class="col-12 col-sm-6 col-xl-3 d-flex">
                            <div class="guideline-item-card w-100">
                                <div class="guideline-icon-box" style="background:#e0f2fe; color:#0284c7;">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div class="guideline-content">
                                    <h6 class="guideline-title">Study Notes &amp; Learning Material</h6>
                                    <p class="guideline-desc">Regularly upload lecture summaries, sample question papers, homework tasks, and slides to support continuous learning.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Classroom Discipline & Engagement -->
                        <div class="col-12 col-sm-6 col-xl-3 d-flex">
                            <div class="guideline-item-card w-100">
                                <div class="guideline-icon-box" style="background:#fdf2f8; color:#be185d;">
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                </div>
                                <div class="guideline-content">
                                    <h6 class="guideline-title">Classroom Discipline &amp; Engagement</h6>
                                    <p class="guideline-desc">Cultivate an interactive, organized, and encouraging classroom culture that fosters student curiosity and mutual respect.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Student Mentorship & Remedial Care -->
                        <div class="col-12 col-sm-6 col-xl-3 d-flex">
                            <div class="guideline-item-card w-100">
                                <div class="guideline-icon-box" style="background:#f3e8ff; color:#7e22ce;">
                                    <i class="fa-solid fa-hands-holding-child"></i>
                                </div>
                                <div class="guideline-content">
                                    <h6 class="guideline-title">Student Mentorship &amp; Support</h6>
                                    <p class="guideline-desc">Identify struggling learners early, provide remedial guidance, and respond constructively to academic feedback and doubts.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 7. Parent Communication & Guardianship -->
                        <div class="col-12 col-sm-6 col-xl-3 d-flex">
                            <div class="guideline-item-card w-100">
                                <div class="guideline-icon-box" style="background:#fef2f2; color:#dc2626;">
                                    <i class="fa-solid fa-user-group"></i>
                                </div>
                                <div class="guideline-content">
                                    <h6 class="guideline-title">Parent-Teacher Communication</h6>
                                    <p class="guideline-desc">Keep guardians informed regarding chronic absenteeism, sudden performance drops, and behavioral progress constructively.</p>
                                </div>
                            </div>
                        </div>

                        <!-- 8. Examination Integrity & Ethics -->
                        <div class="col-12 col-sm-6 col-xl-3 d-flex">
                            <div class="guideline-item-card w-100">
                                <div class="guideline-icon-box" style="background:#ccfbf1; color:#0f766e;">
                                    <i class="fa-solid fa-scale-balanced"></i>
                                </div>
                                <div class="guideline-content">
                                    <h6 class="guideline-title">Exam Integrity &amp; Confidentiality</h6>
                                    <p class="guideline-desc">Safeguard question papers, ensure strict impartiality during examination invigilation, and uphold rigorous evaluation standards.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal 1: Upload Photo Modal -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1" aria-labelledby="uploadPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-clean">
            <div class="modal-header modal-header-clean">
                <h5 class="modal-title fw-bold text-dark fs-6" id="uploadPhotoModalLabel">
                    <i class="fa-solid fa-camera text-primary me-2"></i> Update Profile Picture
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="teacher_code.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <div class="p-4 border rounded-3 bg-light text-center" style="border-style: dashed !important; border-width: 2px !important;">
                            <i class="fa-solid fa-cloud-arrow-up fa-3x text-primary mb-2 opacity-75"></i>
                            <h6 class="fw-bold text-dark mb-1">Choose an image file</h6>
                            <p class="text-muted small mb-3">Supported formats: JPG, PNG, GIF (Max size 5 MB)</p>
                            <input class="form-control form-control-clean" type="file" id="user_photo" name="user_photo" accept="image/jpeg, image/png, image/gif" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-clean">
                    <button type="button" class="btn-action-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-action-primary">
                        <i class="fa-solid fa-upload me-1"></i> Upload Photo
                    </button>
                </div>
            </form>
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
</script>

<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>
