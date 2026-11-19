<?php
include('authentication.php');
include('includes/header.php');

// ✅ Step 1: Check if user is logged in
if (!isset($_SESSION['auth_user'])) {
    header("Location: ../profile.php");
    exit();
}

$user_id = $_SESSION['auth_user']['id'];
$user_id_num = $_SESSION['auth_user']['user_id'] ?? '';

// ✅ Step 2: Class mapping
$classMapping = [
    '100' => 'Nursery',
    '101' => 'LKG',
    '102' => 'UKG',
    '1' => 'Class 1',
    '2' => 'Class 2',
    '3' => 'Class 3',
    '4' => 'Class 4',
    '5' => 'Class 5',
    '6' => 'Class 6',
    '7' => 'Class 7',
    '8' => 'Class 8',
    '9' => 'Class 9',
    '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)',
    '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm. (with Math)',
    '14' => 'Class 11 Comm. (without Math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)',
    '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (with Math)',
    '19' => 'Class 12 Comm. (without Math)',
    '20' => 'Class 12 Arts'
];

// ✅ Step 3: Fetch classes assigned to this teacher
$query_class = "SELECT DISTINCT class FROM class_teacher WHERE user_id = '$user_id' OR user_id = '$user_id_num'";
$result_class = mysqli_query($con, $query_class);

$classes = [];
if ($result_class) {
    while($row = mysqli_fetch_assoc($result_class)) {
        if (!empty($row['class']) && !in_array($row['class'], $classes)) {
            $classes[] = $row['class'];
        }
    }
}

// ✅ Step 4: Handle selected class
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
if (empty($selected_class) && !empty($classes)) {
    $selected_class = $classes[0];
}

$selected_class_name = $selected_class !== '' ? ($classMapping[$selected_class] ?? ('Class ' . $selected_class)) : 'None';

// ✅ Step 5: Fetch complaints for selected class with student details
$result = false;
$complaints_list = [];
if (!empty($selected_class)) {
    $selected_class_esc = mysqli_real_escape_string($con, $selected_class);
    $complaintQuery = "
        SELECT c.*, u.fname, u.lname, u.rollno, u.email, u.phone_no
        FROM complaints AS c
        INNER JOIN users AS u ON c.user_id = u.user_id
        WHERE c.class_code = '$selected_class_esc'
        ORDER BY c.id DESC
    ";
    $result = mysqli_query($con, $complaintQuery);
    if ($result) {
        while ($c_row = mysqli_fetch_assoc($result)) {
            $complaints_list[] = $c_row;
        }
    }
}
$current_complaints_count = count($complaints_list);

// Total complaints across all assigned classes
$total_all_complaints = 0;
if (!empty($classes)) {
    $in_classes_str = implode("','", array_map('intval', $classes));
    $q_all = mysqli_query($con, "SELECT COUNT(*) as cnt FROM complaints WHERE class_code IN ('$in_classes_str')");
    if ($q_all && $r_all = mysqli_fetch_assoc($q_all)) {
        $total_all_complaints = (int)$r_all['cnt'];
    }
}

// Avatar palette for initials
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

.teacher-complaints-wrap {
    font-family: var(--pca-font) !important;
    color: var(--pca-text-main);
    padding: 0 0 2rem 0;
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
    box-sizing: border-box;
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

.stat-card-clean.card-complaints {
    background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);
    border-color: #fecdd3;
}

.stat-card-clean.card-courses {
    background: linear-gradient(135deg, #ecfdf5 0%, #e0f8ed 100%);
    border-color: #d1fae5;
}

.stat-card-clean.card-teachers {
    background: linear-gradient(135deg, #f0f9ff 0%, #dff2fe 100%);
    border-color: #e0f2fe;
}

.stat-card-clean.card-total {
    background: linear-gradient(135deg, #fff7ed 0%, #feeddc 100%);
    border-color: #ffedd5;
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
    margin-right: 0.85rem;
}

.stat-card-clean.card-complaints .stat-icon-box { background: #ffe4e6; color: #e11d48; }
.stat-card-clean.card-courses    .stat-icon-box { background: #d1fae5; color: #10b981; }
.stat-card-clean.card-teachers   .stat-icon-box { background: #bae6fd; color: #0284c7; }
.stat-card-clean.card-total      .stat-icon-box { background: #ffedd5; color: #ea580c; }

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
    font-size: 1.65rem;
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
}

.pca-card-header {
    padding: 1.1rem 1.35rem;
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

/* Toolbar Controls */
.toolbar-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
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
    padding-right: 1rem;
    max-width: 170px;
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
    padding: 0.45rem 1rem 0.45rem 2.2rem;
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

/* Modern Clean Table */
.table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
    width: 100% !important;
    border-radius: 0 0 16px 16px;
    margin: 0 !important;
}

.pca-table {
    margin-bottom: 0;
    width: 100% !important;
    min-width: 860px;
    border-collapse: collapse;
}

.pca-table thead th {
    background-color: #f8fafc !important;
    color: #475569 !important;
    font-size: 0.74rem !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.85rem 0.9rem !important;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top: none !important;
    white-space: nowrap;
}

.pca-table tbody td {
    padding: 0.95rem 0.9rem !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #f1f5f9 !important;
    font-size: 0.86rem;
    color: #1e293b;
    background: #ffffff;
}

.pca-table tbody tr:hover td {
    background-color: #fbfbfe !important;
}

.pca-table tbody tr:last-child td {
    border-bottom: none !important;
}

/* Student Avatar Circle */
.student-avatar-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.82rem;
    font-weight: 800;
    flex-shrink: 0;
}

.badge-roll-pill {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-weight: 700;
    font-size: 0.75rem;
    padding: 0.15rem 0.45rem;
    border-radius: 6px;
    display: inline-block;
}

/* Message Bubble */
.complaint-msg-box {
    background: #f8fafc;
    border: 1px solid #edf0f5;
    border-radius: 10px;
    padding: 0.65rem 0.85rem;
    font-size: 0.86rem;
    color: #1e293b;
    line-height: 1.5;
    word-break: break-word;
    max-width: 420px;
}

/* Solve Button */
.btn-action-solve {
    background: #ecfdf5 !important;
    color: #059669 !important;
    border: 1px solid #a7f3d0 !important;
    border-radius: 8px !important;
    font-weight: 700 !important;
    font-size: 0.8rem !important;
    padding: 0.45rem 0.85rem !important;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s ease;
    cursor: pointer;
    white-space: nowrap;
}

.btn-action-solve:hover {
    background: #059669 !important;
    color: #ffffff !important;
    border-color: #059669 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(5, 150, 105, 0.25);
}

/* Empty State */
.empty-state-box {
    padding: 3.5rem 1.5rem;
    text-align: center;
    width: 100%;
}

.empty-state-icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: #ecfdf5;
    color: #10b981;
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
    max-width: 420px;
    margin: 0 auto;
}

/* Mobile Media Queries */
@media (max-width: 768px) {
    .teacher-complaints-wrap {
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
    .toolbar-wrap {
        flex-direction: column !important;
        width: 100% !important;
        align-items: stretch !important;
    }
    .class-select-pill, .class-select-pill select, .search-input-pill, .search-input-pill input {
        width: 100% !important;
        max-width: 100% !important;
    }
    .date-pill-badge {
        width: 100% !important;
        justify-content: center !important;
    }
}
</style>

<div class="teacher-complaints-wrap">

    <!-- Top Greeting & Header Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 px-1">
        <div>
            <div class="welcome-label">
                <i class="fa-solid fa-comments"></i> Student Feedback &amp; Complaints
            </div>
            <h1 class="dashboard-main-title">Class Complaints</h1>
            <p class="dashboard-sub-title">Review, track, and resolve student complaints and academic inquiries for your assigned classes.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <div class="date-pill-badge">
                <i class="fa-regular fa-calendar-days text-primary"></i>
                <span><?= date('D, M d, Y') ?></span>
                <span class="text-muted">•</span>
                <span id="liveClockDisplay"><?= date('h:i:s A') ?></span>
            </div>
            <a href="index.php" class="btn btn-light border" style="border-radius: 12px; font-weight: 600; font-size: 0.86rem; padding: 0.52rem 1rem; color: #475569;">
                <i class="fa-solid fa-house me-1"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Stat Summary Cards Row (4 Modern Pastel Cards) -->
    <div class="row g-3 mb-4">
        <!-- Stat 1: Complaints in Active Class -->
        <div class="col-12 col-sm-6 col-xl-3 px-2">
            <div class="stat-card-clean card-complaints">
                <div class="stat-icon-box">
                    <i class="fa-solid fa-comment-dots"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-label-title">Active Complaints</div>
                    <div class="stat-number-display"><?= $current_complaints_count ?></div>
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
                    <div class="stat-label-title">Selected Class</div>
                    <div class="stat-number-display" style="font-size: 1.35rem;"><?= htmlspecialchars($selected_class_name) ?></div>
                    
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

        <!-- Stat 4: Total Complaints Across All Classes -->
        <div class="col-12 col-sm-6 col-xl-3 px-2">
            <div class="stat-card-clean card-total">
                <div class="stat-icon-box">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-label-title">Total Inquiries</div>
                    <div class="stat-number-display"><?= $total_all_complaints ?></div>
                    <div class="stat-subtitle-text">Across all your classes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Flash Message -->
    <?php if(isset($_SESSION['message'])): ?>
        <?php 
            $msg = $_SESSION['message'];
            $is_success = str_starts_with($msg, '✅') || str_contains($msg, 'success');
            $bg_color = $is_success ? '#ecfdf5' : '#fef2f2';
            $text_color = $is_success ? '#065f46' : '#991b1b';
            $border_color = $is_success ? '#10b981' : '#ef4444';
            $icon = $is_success ? 'fa-circle-check' : 'fa-circle-exclamation';
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

    <!-- Monolithic White Container Card -->
    <div class="pca-white-card">

        <!-- Card Header & Filter Toolbar -->
        <div class="pca-card-header">
            <div class="d-flex align-items-center gap-2">
                <h5 class="pca-card-title">
                    <i class="fa-solid fa-comments text-primary"></i>
                    Complaints Roster
                </h5>
                <span class="badge rounded-pill" style="background: <?= $current_complaints_count > 0 ? '#fff1f2' : '#ecfdf5' ?>; color: <?= $current_complaints_count > 0 ? '#e11d48' : '#059669' ?>; font-weight: 700; font-size: 0.78rem; padding: 0.3rem 0.7rem;">
                    <?= $current_complaints_count ?> <?= $current_complaints_count === 1 ? 'Open Issue' : 'Open Issues' ?>
                </span>
            </div>

            <!-- Toolbar Controls -->
            <div class="toolbar-wrap">
                <!-- Class Selector Form -->
                <form method="GET" action="viewcomplaints.php" id="filterForm" class="d-inline-flex m-0">
                    <div class="class-select-pill">
                        <i class="fa-solid fa-graduation-cap text-primary me-2"></i>
                        <select name="class" id="classSelectDropdown" onchange="document.getElementById('filterForm').submit();" required>
                            <?php foreach($classes as $class_id): ?>
                                <option value="<?= htmlspecialchars($class_id); ?>" <?= ($selected_class == $class_id) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($classMapping[$class_id] ?? ('Class ' . $class_id)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <!-- Client-side Instant Search -->
                <div class="search-input-pill">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" id="liveComplaintSearch" placeholder="Search student, message..." autocomplete="off">
                </div>

                <!-- Print Table -->
                <button type="button" class="btn btn-light border-0" onclick="window.print();" title="Print Complaints" style="background: #f8fafc; border-radius: 10px; height: 38px; width: 38px; display: inline-flex; align-items: center; justify-content: center; color: #64748b;">
                    <i class="fa-solid fa-print"></i>
                </button>
            </div>
        </div>

        <!-- Table Container with Smooth Touch Scrolling -->
        <?php if (!empty($complaints_list)): ?>
            <div class="table-responsive">
                <table class="table pca-table" id="complaintsTable">
                    <thead>
                        <tr>
                            <th style="width: 5%; text-align: center;">#</th>
                            <th style="width: 25%;">Student Information</th>
                            <th style="width: 18%;">Contact Details</th>
                            <th style="width: 32%;">Complaint Message</th>
                            <th style="width: 12%;">Date Submitted</th>
                            <th style="width: 8%; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="complaintsTableBody">
                        <?php 
                        $i = 1;
                        foreach ($complaints_list as $row): 
                            $student_id = (int)$row['id'];
                            $full_name = trim(($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''));
                            if (empty($full_name)) $full_name = 'Student #' . $student_id;
                            $roll_no = !empty($row['rollno']) ? htmlspecialchars($row['rollno']) : '—';
                            $phone_no = !empty($row['phone_no']) ? htmlspecialchars($row['phone_no']) : '';
                            $email = !empty($row['email']) ? htmlspecialchars($row['email']) : '';
                            $msg_text = !empty($row['message']) ? $row['message'] : 'No message body provided.';

                            // Avatar initials & color
                            $fname_initial = mb_substr(trim($row['fname'] ?? 'S'), 0, 1);
                            $lname_initial = mb_substr(trim($row['lname'] ?? ''), 0, 1);
                            $initials = mb_strtoupper($fname_initial . $lname_initial);
                            if (empty($initials)) $initials = 'ST';
                            $palette_idx = abs(crc32($full_name . $student_id)) % count($avatar_palettes);
                            $avatar_color = $avatar_palettes[$palette_idx];

                            // Search index string
                            $search_haystack = strtolower($full_name . ' ' . $roll_no . ' ' . $phone_no . ' ' . $email . ' ' . $msg_text);
                        ?>
                            <tr data-complaint-row data-search="<?= htmlspecialchars($search_haystack) ?>">
                                <!-- Serial No -->
                                <td style="text-align: center; font-weight: 700; color: #64748b;">
                                    <?= $i++; ?>
                                </td>

                                <!-- Student Information -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="student-avatar-circle" style="background: <?= $avatar_color['bg'] ?>; color: <?= $avatar_color['text'] ?>;">
                                            <?= htmlspecialchars($initials) ?>
                                        </div>
                                        <div style="min-width: 0;">
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem;">
                                                <?= htmlspecialchars($full_name) ?>
                                            </div>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                <span class="badge-roll-pill">Roll: #<?= $roll_no ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact Details -->
                                <td>
                                    <?php if(!empty($phone_no)): ?>
                                        <div class="mb-1">
                                            <a href="tel:<?= htmlspecialchars($phone_no) ?>" class="text-dark text-decoration-none fw-semibold small d-inline-flex align-items-center gap-1">
                                                <i class="fa-solid fa-phone text-muted"></i> +91 <?= htmlspecialchars($phone_no) ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(!empty($email)): ?>
                                        <div>
                                            <a href="mailto:<?= htmlspecialchars($email) ?>" class="text-primary text-decoration-none small d-inline-flex align-items-center gap-1">
                                                <i class="fa-solid fa-envelope text-muted"></i> <?= htmlspecialchars($email) ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(empty($phone_no) && empty($email)): ?>
                                        <span class="text-muted small">No contact provided</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Complaint Message -->
                                <td>
                                    <div class="complaint-msg-box">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="badge rounded-pill" style="background:#fee2e2; color:#b91c1c; font-size:0.7rem; font-weight:700;">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Issue Report
                                            </span>
                                            <span class="text-muted" style="font-size:0.7rem;">#C-<?= htmlspecialchars($row['id']) ?></span>
                                        </div>
                                        <div><?= nl2br(htmlspecialchars($msg_text)) ?></div>
                                    </div>
                                </td>

                                <!-- Date Submitted -->
                                <td>
                                    <div class="fw-semibold text-dark small">
                                        <i class="fa-regular fa-calendar text-muted me-1"></i>
                                        <?= date('d M Y', strtotime($row['created_at'])) ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.74rem;">
                                        <i class="fa-regular fa-clock text-muted me-1"></i>
                                        <?= date('h:i A', strtotime($row['created_at'])) ?>
                                    </div>
                                </td>

                                <!-- Action: Solve Button Form -->
                                <td style="text-align: center;">
                                    <form action="teacher_code.php" method="POST" onsubmit="return confirm('Are you sure you want to mark this complaint as solved?');" class="m-0">
                                        <input type="hidden" name="complaint_id" value="<?= htmlspecialchars($row['id']); ?>">
                                        <button type="submit" name="solve_complaint_btn" class="btn-action-solve" title="Mark as resolved">
                                            <i class="fa-solid fa-circle-check"></i> Solve
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer Summary -->
            <div class="p-3 bg-light border-top d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                <div>
                    Showing <strong><?= count($complaints_list) ?></strong> active <?= count($complaints_list) === 1 ? 'complaint' : 'complaints' ?> for <strong><?= htmlspecialchars($selected_class_name) ?></strong>.
                </div>
                <div>
                    Click <strong>Solve</strong> to resolve and close any inquiry.
                </div>
            </div>

        <?php else: ?>
            <div class="empty-state-box">
                <div class="empty-state-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h5 class="empty-state-title">All Clear! No Open Complaints</h5>
                <p class="empty-state-desc">There are no unresolved student complaints or academic feedback tickets in <?= htmlspecialchars($selected_class_name) ?>.</p>
            </div>
        <?php endif; ?>

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

// Client-side Instant Search
document.getElementById('liveComplaintSearch')?.addEventListener('input', function(e) {
    const val = e.target.value.toLowerCase().trim();
    const rows = document.querySelectorAll('[data-complaint-row]');
    rows.forEach(function(row) {
        const haystack = row.getAttribute('data-search') || '';
        row.style.display = haystack.includes(val) ? '' : 'none';
    });
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
