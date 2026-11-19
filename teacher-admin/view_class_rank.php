<?php
// view_class_rank.php (Student Ranking Page with Auth-Based Class Selection and All Exams)
include('authentication.php'); 
include('includes/header.php'); 
include('includes/fetch_class.php');

// Define ALL exam types (Must match the existing report card map)
$all_exam_types = [
    1 => 'Unit Test 1', 
    2 => 'Unit Test 2',
    5 => 'Half Year Exam',
    3 => 'Unit Test 3',
    4 => 'Unit Test 4', 
    6 => 'Final Exam', 
];

// Passing percentage (33%)
if (!defined('PASS_PERCENTAGE')) {
    define('PASS_PERCENTAGE', 0.33);
}

if (!isset($con) || !$con) {
    die("<div class='container mt-4'><p class='alert alert-danger'>Database connection failed.</p></div>");
}

// Map classMapping if available
$class_map = $classMapping ?? [
    '100' => 'Nursery', '101' => 'LKG', '102' => 'UKG',
    '1' => 'Class 1', '2' => 'Class 2', '3' => 'Class 3', '4' => 'Class 4',
    '5' => 'Class 5', '6' => 'Class 6', '7' => 'Class 7', '8' => 'Class 8',
    '9' => 'Class 9', '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)', '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm. (with Math)', '14' => 'Class 11 Comm. (without Math)', '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)', '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (with Math)', '19' => 'Class 12 Comm. (without Math)', '20' => 'Class 12 Arts'
];

// Handle class change via POST or GET
if (isset($_POST['class']) && !empty($_POST['class'])) {
    $selected_class = $_POST['class'];
    $_SESSION['teacher_selected_class'] = $selected_class;
} elseif (isset($_POST['class_code']) && !empty($_POST['class_code'])) {
    $selected_class = $_POST['class_code'];
    $_SESSION['teacher_selected_class'] = $selected_class;
}

$selected_class_code = $selected_class; 

// Fetch selected test code from POST or GET, defaulting to 0 (All Exams)
$selected_test_code = isset($_POST['test_code']) ? intval($_POST['test_code']) : (isset($_GET['test_code']) ? intval($_GET['test_code']) : 0); 
$students_data = [];
$total_students = 0;

// --- 2. Data Processing (when class code is available and filter is selected) ---
if (!empty($selected_class_code) && $selected_test_code !== '') {
    
    // Marks Aggregation Logic
    $base_query = "
        SELECT 
            u.id AS student_id, u.fname, u.lname, u.rollno,
            SUM(ss.marks_obtained + ss.practical_marks) AS total_obtained,
            SUM(ss.total_marks + ss.total_practical) AS total_max
        FROM student_score ss
        JOIN users u ON ss.student_id = u.id
        WHERE u.class = ? ";

    $ranking_query = "";
    $stmt = null;

    if ($selected_test_code > 0) {
        // Case 1: Specific Test Selected (test_code > 0)
        $ranking_query = $base_query . " AND ss.test_code = ? ";
        $ranking_query .= " GROUP BY u.id, u.fname, u.lname, u.rollno
                            ORDER BY total_obtained DESC, u.rollno ASC";
        
        $stmt = mysqli_prepare($con, $ranking_query);
        mysqli_stmt_bind_param($stmt, "si", $selected_class_code, $selected_test_code);

    } else {
        // Case 2: All Exams Selected (test_code = 0) - Calculate overall rank
        $ranking_query = $base_query;
        $ranking_query .= " GROUP BY u.id, u.fname, u.lname, u.rollno
                            ORDER BY total_obtained DESC, u.rollno ASC";

        $stmt = mysqli_prepare($con, $ranking_query);
        mysqli_stmt_bind_param($stmt, "s", $selected_class_code);
    }
    
    if ($stmt) {
        if (!mysqli_stmt_execute($stmt)) {
            error_log("SQL Error during ranking query execution: " . mysqli_stmt_error($stmt));
        } else {
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $students_data[] = $row;
            }
            mysqli_stmt_close($stmt);

            // 3. Apply Ranking Logic (Dense Ranking with Tie Handling)
            if (!empty($students_data)) {
                $rank = 0;
                $prev_score = -1;
                $rank_counter = 0; 
                $total_students = count($students_data);

                foreach ($students_data as $i => &$student) {
                    $rank_counter++;
                    // If the current score is different from the previous one, update the rank
                    if ($student['total_obtained'] != $prev_score) {
                        $rank = $rank_counter;
                        $prev_score = $student['total_obtained'];
                    }
                    $student['rank'] = $rank;
                    $student['percentage'] = ($student['total_max'] > 0) ? 
                                              round(($student['total_obtained'] / $student['total_max']) * 100, 2) : 0;
                }
                unset($student); // Break reference
            }
        }
    }
}
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

.page-wrapper, .page-content, .container-fluid {
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

.report-page-wrap {
    font-family: var(--pca-font) !important;
    color: var(--pca-text-main);
    padding: 0.5rem 0.5rem 2rem 0.5rem;
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
    box-sizing: border-box;
}

.report-page-wrap .row {
    margin-left: 0 !important;
    margin-right: 0 !important;
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
    gap: 10px;
}

.pca-card-title i {
    color: #5e48eb;
}

.pca-card-body {
    padding: 1.4rem;
}

/* Controls */
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
    width: 100%;
}

.form-control-clean:focus {
    background: #ffffff !important;
    border-color: #5e48eb !important;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12) !important;
    outline: none;
}

/* Buttons */
.btn-primary-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 0.55rem 1rem !important;
    border: none;
    box-shadow: 0 3px 10px rgba(94, 72, 235, 0.25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none !important;
    height: 42px;
    white-space: nowrap !important;
}

.btn-primary-action:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    box-shadow: 0 5px 14px rgba(94, 72, 235, 0.35);
    transform: translateY(-1px);
}

/* Filter Box */
.filter-box-clean {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.2rem;
    margin-bottom: 1.5rem;
}

/* Top 3 Performers Section */
.top-performers-wrap {
    background: #fbfbfe;
    border: 1px solid #edf0f5;
    border-radius: 14px;
    padding: 1.2rem;
    margin-bottom: 1.5rem;
}

.top-performers-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.top-performers-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}

@media (max-width: 820px) {
    .top-performers-grid {
        grid-template-columns: 1fr;
    }
}

.rank-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.1rem;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.rank-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}

.rank-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 0.72rem;
    font-weight: 800;
    flex-shrink: 0;
}

.rank-card-1 { border-left: 4px solid #f59e0b; }
.rank-card-1 .rank-badge { background: #fef3c7; color: #b45309; }

.rank-card-2 { border-left: 4px solid #10b981; }
.rank-card-2 .rank-badge { background: #d1fae5; color: #047857; }

.rank-card-3 { border-left: 4px solid #0284c7; }
.rank-card-3 .rank-badge { background: #e0f2fe; color: #0369a1; }

.rank-info {
    overflow: hidden;
    flex: 1;
}

.rank-student-name {
    font-weight: 700;
    font-size: 0.92rem;
    color: #0f172a;
    line-height: 1.25;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.rank-score-pill {
    display: inline-block;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0.2rem 0.6rem;
    border-radius: 6px;
    background: #f1f5f9;
    color: #334155;
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
    min-width: 680px;
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
    .pca-card-header .btn-view-link,
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

.student-name-link {
    color: #0f172a !important;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    transition: color 0.15s ease;
}

.student-name-link:hover {
    color: #5e48eb !important;
}

/* Rank number badges in table */
.rank-num-badge {
    font-size: 0.88rem;
    font-weight: 800;
    padding: 0.3rem 0.65rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #334155;
}

.rank-num-badge.rank-1 {
    background: #fef3c7;
    color: #b45309;
    border: 1px solid #fde68a;
}

.rank-num-badge.rank-2 {
    background: #d1fae5;
    color: #047857;
    border: 1px solid #a7f3d0;
}

.rank-num-badge.rank-3 {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.score-pill {
    font-weight: 700;
    font-size: 0.78rem;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
}

.score-pill-high {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.score-pill-low {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

/* Empty State */
.empty-state-clean {
    padding: 3rem 1.5rem;
    background: #fafbfe;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
}

.empty-icon-circle {
    width: 58px;
    height: 58px;
    background: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
</style>

<div class="report-page-wrap">

    <!-- Flash message -->
    <?php include('message.php'); ?>

    <div class="pca-white-card">
        <!-- Header -->
        <div class="pca-card-header">
            <h1 class="pca-card-title">
                <i class="fa-solid fa-ranking-star"></i>
                Class Performance Ranking
            </h1>
            <?php if (!empty($selected_class_code) && isset($class_map[$selected_class_code])): ?>
                <span class="badge bg-light text-dark border px-3 py-2 fw-semibold" style="font-size: 0.85rem;">
                    <i class="fa-solid fa-graduation-cap text-primary me-1"></i> <?= htmlspecialchars($class_map[$selected_class_code]) ?>
                </span>
            <?php endif; ?>
        </div>

        <div class="pca-card-body">

            <!-- Filter Form -->
            <form method="POST" action="view_class_rank.php" id="rankFilterForm" class="filter-box-clean">
                <div class="row g-3 align-items-end">
                    <!-- Class Display -->
                    <div class="col-lg-4 col-md-4 col-12">
                        <label for="class_select" class="form-label-clean">
                            <i class="fa-solid fa-graduation-cap me-1 text-primary"></i> Selected Class:
                        </label>
                        <select name="class" id="class_select" class="form-control form-control-clean" onchange="document.getElementById('rankFilterForm').submit();">
                            <option value="">-- Select Class --</option>
                            <?php foreach($classes as $c): ?>
                                <option value="<?= $c ?>" <?= ($selected_class == $c) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($classMapping[$c] ?? 'Class '.$c) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="class_code" value="<?= htmlspecialchars($selected_class) ?>">
                    </div>

                    <!-- Exam Type Selection -->
                    <div class="col-lg-5 col-md-5 col-12">
                        <label for="test_code" class="form-label-clean">
                            <i class="fa-solid fa-file-lines me-1 text-primary"></i> Select Exam Type:
                        </label>
                        <select name="test_code" id="test_code" class="form-control form-control-clean" required>
                            <option value="0" <?= ($selected_test_code === 0) ? 'selected' : '' ?>>All Exams (Overall Rank)</option>
                            <?php 
                            foreach ($all_exam_types as $code => $name) {
                                $selected = ($selected_test_code == $code) ? 'selected' : '';
                                echo '<option value="' . $code . '" ' . $selected . '>' . htmlspecialchars($name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div class="col-lg-3 col-md-3 col-12">
                        <button type="submit" class="btn-primary-action w-100" style="white-space: nowrap !important;">
                            <i class="fas fa-search me-1"></i> Show Ranking
                        </button>
                    </div>
                </div>
            </form>

            <!-- Ranking Results -->
            <?php if (!empty($selected_class_code) && $selected_test_code !== ''): ?>
                <?php 
                $class_display = $class_map[$selected_class_code] ?? ("Class " . $selected_class_code);
                $test_display = ($selected_test_code === 0) ? "All Exams (Overall)" : ($all_exam_types[$selected_test_code] ?? "Test " . $selected_test_code);
                ?>

                <?php if (empty($students_data)): ?>
                    <div class="empty-state-clean text-center">
                        <div class="empty-icon-circle mx-auto mb-3">
                            <i class="fa-solid fa-clipboard-question text-muted fa-lg"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">No ranking results found</h6>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">No test scores have been entered for <?= htmlspecialchars($class_display) ?> in <?= htmlspecialchars($test_display) ?>.</p>
                    </div>
                <?php else: ?>

                    <!-- Top 3 Performers Widget -->
                    <div class="top-performers-wrap">
                        <div class="top-performers-title">
                            <i class="fa-solid fa-trophy text-warning"></i>
                            <span>Top Performers in <strong><?= htmlspecialchars($class_display) ?></strong> &bull; <?= htmlspecialchars($test_display) ?></span>
                        </div>
                        <div class="top-performers-grid">
                            <?php 
                            $rank_icons = [1 => 'fa-trophy', 2 => 'fa-medal', 3 => 'fa-award'];
                            $top_subset = array_slice($students_data, 0, 3);
                            foreach ($top_subset as $t_idx => $top_student): 
                                $r_num = $t_idx + 1;
                                $s_name = htmlspecialchars(trim($top_student['fname'] . ' ' . $top_student['lname']));
                                $s_pct = $top_student['percentage'] . '%';
                                $c_icon = $rank_icons[$r_num] ?? 'fa-award';
                            ?>
                                <div class="rank-card rank-card-<?= $r_num ?>">
                                    <div class="rank-badge">
                                        <i class="fa-solid <?= $c_icon ?> mb-1"></i>
                                        <span>#<?= $top_student['rank'] ?></span>
                                    </div>
                                    <div class="rank-info">
                                        <div class="rank-student-name" title="<?= $s_name ?>"><?= $s_name ?></div>
                                        <span class="rank-score-pill"><?= $s_pct ?> &bull; <?= $top_student['total_obtained'] ?> / <?= $top_student['total_max'] ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Summary & Table -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.98rem;">
                                <i class="fa-solid fa-list-ol text-primary me-2"></i>Class Ranking Table
                            </h6>
                            <span class="text-muted" style="font-size: 0.8rem;">
                                <?= htmlspecialchars($class_display) ?> &bull; <?= htmlspecialchars($test_display) ?> &bull; Total Students: <strong><?= $total_students ?></strong>
                            </span>
                        </div>
                        <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i> Dense Ranking
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="pca-table">
                            <colgroup>
                                <col style="width: 10%;">
                                <col style="width: 12%;">
                                <col style="width: 28%;">
                                <col style="width: 14%;">
                                <col style="width: 12%;">
                                <col style="width: 12%;">
                                <col style="width: 12%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="text-center">Rank</th>
                                    <th>Roll No</th>
                                    <th>Student Name</th>
                                    <th class="text-center">Marks Obtained</th>
                                    <th class="text-center">Max Marks</th>
                                    <th class="text-center">Percentage</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $palette = [
                                    ['bg' => '#eef2ff', 'color' => '#4338ca'],
                                    ['bg' => '#ecfdf5', 'color' => '#047857'],
                                    ['bg' => '#fef3c7', 'color' => '#b45309'],
                                    ['bg' => '#fdf2f8', 'color' => '#be185d'],
                                    ['bg' => '#f0f9ff', 'color' => '#0369a1'],
                                ];

                                foreach ($students_data as $student): 
                                    $percentage = $student['percentage'];
                                    $is_pass = ($percentage >= PASS_PERCENTAGE * 100);
                                    $status_pill = $is_pass ? 'score-pill-high' : 'score-pill-low';
                                    $status_text = $is_pass ? 'Pass' : 'Fail';
                                    $status_icon = $is_pass ? 'fa-check' : 'fa-xmark';

                                    $s_fullname = htmlspecialchars(trim($student['fname'] . ' ' . $student['lname']));
                                    $pal_idx = abs(crc32($student['student_id'] . $s_fullname)) % count($palette);
                                    $avatar = $palette[$pal_idx];
                                    $initial = strtoupper(substr($student['fname'], 0, 1));

                                    $rank_class = '';
                                    if ($student['rank'] == 1) $rank_class = 'rank-1';
                                    elseif ($student['rank'] == 2) $rank_class = 'rank-2';
                                    elseif ($student['rank'] == 3) $rank_class = 'rank-3';
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <span class="rank-num-badge <?= $rank_class ?>">
                                                <?= $student['rank'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge-roll-pill">#<?= htmlspecialchars($student['rollno']) ?></span>
                                        </td>
                                        <td>
                                            <a href="print_report_card.php?student_id=<?= $student['student_id'] ?>&test_code=<?= $selected_test_code ?>" target="_blank" class="student-name-link" title="View Report Card">
                                                <div class="student-avatar-circle me-2" style="background: <?= $avatar['bg'] ?>; color: <?= $avatar['color'] ?>;">
                                                    <?= $initial ?>
                                                </div>
                                                <span class="fw-semibold text-dark"><?= $s_fullname ?></span>
                                                <i class="fas fa-arrow-up-right-from-square text-muted fa-xs ms-1"></i>
                                            </a>
                                        </td>
                                        <td class="text-center fw-bold text-dark">
                                            <?= $student['total_obtained'] ?>
                                        </td>
                                        <td class="text-center text-muted fw-semibold">
                                            <?= $student['total_max'] ?>
                                        </td>
                                        <td class="text-center fw-bold <?= $is_pass ? 'text-success' : 'text-danger' ?>">
                                            <?= $percentage ?>%
                                        </td>
                                        <td class="text-center">
                                            <span class="score-pill <?= $status_pill ?>">
                                                <i class="fa-solid <?= $status_icon ?> me-1"></i><?= $status_text ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
