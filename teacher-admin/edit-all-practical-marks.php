<?php
include('authentication.php');

// --- 1. Get Filters from URL ---
$class_code    = $_GET['class_code'] ?? null;
$subject       = $_GET['subject'] ?? null;
$exam_type_str = $_GET['exam_type'] ?? '';

// --- 2. Redirect if missing minimum info ---
if (!$class_code || !$subject || empty($exam_type_str)) {
    $_SESSION['error'] = "Please select Class, Subject, and Test Type to update maximum practical marks.";
    header("Location: view-partical-marks.php");
    exit();
}

// --- 3. Exam Types Mapping ---
$exam_types = [
    1 => 'Unit Test 1',
    2 => 'Unit Test 2',
    3 => 'Unit Test 3',
    4 => 'Unit Test 4',
    5 => 'Half Year Exam',
    6 => 'Final Exam',
    7 => 'Class Test'
];

$exam_type_map = [
    'unit_test_1' => 1, 'unit_test_2' => 2, 'unit_test_3' => 3,
    'unit_test_4' => 4, 'half_year' => 5, 'final' => 6, 'class_test' => 7
];
$filter_test_code = $exam_type_map[$exam_type_str] ?? (is_numeric($exam_type_str) ? intval($exam_type_str) : 0);

$full_class_options = [
    '100' => 'Nursery', '101' => 'LKG', '102' => 'UKG',
    '1' => 'Class 1', '2' => 'Class 2', '3' => 'Class 3', '4' => 'Class 4',
    '5' => 'Class 5', '6' => 'Class 6', '7' => 'Class 7', '8' => 'Class 8',
    '9' => 'Class 9', '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)', '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm. (with Math)', '14' => 'Class 11 Comm. (without Math)', '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)', '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (with Math)', '19' => 'Class 12 Comm. (without Math)', '20' => 'Class 12 Arts'
];
$class_name = $full_class_options[$class_code] ?? ("Class " . $class_code);
$exam_title = $exam_types[$filter_test_code] ?? 'Test';

// --- 4. Fetch ALL Students in this Practical Test ---
$scores = [];
$teacher_id = $_SESSION['auth_user']['user_id'] ?? $_SESSION['auth_user']['id'] ?? 0;

if (isset($con)) {
    $query = "SELECT ss.id, ss.student_id, ss.test_code, ss.subject, ss.practical_marks, ss.total_practical, DATE(ss.created_at) as date,
                     u.fname, u.lname, u.rollno
              FROM student_score ss
              JOIN users u ON ss.student_id = u.id
              WHERE ss.class_code = ? AND ss.subject = ? AND ss.test_code = ? AND ss.teacher_id = ?
              ORDER BY CAST(u.rollno AS UNSIGNED) ASC, u.rollno ASC, ss.id ASC";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssii", $class_code, $subject, $filter_test_code, $teacher_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $scores[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Initial maximum practical marks from first score
$initial_max_practical = !empty($scores) ? ($scores[0]['total_practical'] ?? 20) : 20;

include('includes/header.php');
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

/* Badges for current context */
.badge-ctx-pill {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
}

/* Back Link Button */
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

/* Form Controls */
.form-label-clean {
    font-size: 0.82rem;
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
    background: #ffffff !important;
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

/* Global Max Box */
.global-max-box {
    background: #fbfbfe;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.2rem;
}

/* Primary Action Button */
.btn-primary-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.6rem 1.6rem;
    border: none;
    box-shadow: 0 3px 10px rgba(94, 72, 235, 0.25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none !important;
}

.btn-primary-action:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    box-shadow: 0 5px 14px rgba(94, 72, 235, 0.35);
    transform: translateY(-1px);
}

.btn-primary-action.btn-has-error {
    background: #dc2626 !important;
    box-shadow: 0 3px 10px rgba(220, 38, 38, 0.3) !important;
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
    min-width: 600px;
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

.badge-max-pill {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 0.3rem 0.8rem;
    border-radius: 8px;
    display: inline-block;
}

/* Student mark inputs */
.student-practical-input {
    border-radius: 8px 0 0 8px !important;
    border: 1px solid #e2e8f0 !important;
    padding: 0.45rem 0.75rem !important;
    font-size: 0.88rem !important;
    font-weight: 700;
    background: #f8fafc;
    transition: all 0.2s ease;
}

.student-practical-input:focus {
    background: #ffffff !important;
    border-color: #5e48eb !important;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12) !important;
    outline: none;
}

.student-practical-input.is-invalid, 
#global_total_practical.is-invalid {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
}

.student-practical-input.is-valid {
    border-color: #10b981 !important;
}

.mark-error-feedback {
    font-size: 0.8rem;
    font-weight: 600;
    color: #dc2626;
    margin-top: 4px;
}

.adjusted-badge {
    font-size: 0.78rem;
    font-weight: 600;
    color: #d97706;
    margin-top: 4px;
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
            <div>
                <h1 class="pca-card-title">
                    <i class="fa-solid fa-sliders"></i>
                    Update Total Practical Marks & All Student Scores
                </h1>
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                    <span class="badge-ctx-pill">
                        <i class="fa-solid fa-graduation-cap me-1 text-primary"></i> <?= htmlspecialchars($class_name) ?>
                    </span>
                    <span class="badge-ctx-pill">
                        <i class="fa-solid fa-book-open me-1 text-primary"></i> <?= htmlspecialchars($subject) ?>
                    </span>
                    <span class="badge-ctx-pill">
                        <i class="fa-solid fa-file-lines me-1 text-primary"></i> <?= htmlspecialchars($exam_title) ?>
                    </span>
                </div>
            </div>
            <div>
                <a href="view-partical-marks.php?class_code=<?= htmlspecialchars($class_code) ?>&subject=<?= urlencode($subject) ?>&exam_type=<?= urlencode($exam_type_str) ?>" class="btn-back-link">
                    <i class="fas fa-arrow-left me-1"></i> Back to Marks
                </a>
            </div>
        </div>

        <div class="pca-card-body">
            <?php if (empty($scores)): ?>
                <div class="empty-state-clean text-center">
                    <div class="empty-icon-circle mx-auto mb-3">
                        <i class="fa-solid fa-clipboard-question text-muted fa-lg"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No practical marks found</h6>
                    <p class="text-muted mb-3" style="font-size: 0.85rem;">No student practical marks records were found for this test selection.</p>
                    <a href="view-partical-marks.php?class_code=<?= htmlspecialchars($class_code) ?>&subject=<?= urlencode($subject) ?>&exam_type=<?= urlencode($exam_type_str) ?>" class="btn-back-link">
                        <i class="fas fa-arrow-left me-1"></i> Back to Marks
                    </a>
                </div>
            <?php else: ?>
                <form action="teacher_code.php" method="POST" id="editAllPracticalForm">
                    <input type="hidden" name="action" value="update_all_practical_marks">
                    <input type="hidden" name="redirect_class" value="<?= htmlspecialchars($class_code) ?>">
                    <input type="hidden" name="redirect_subject" value="<?= htmlspecialchars($subject) ?>">
                    <input type="hidden" name="exam_type" value="<?= htmlspecialchars($exam_type_str) ?>">

                    <!-- Single Global Maximum Practical Marks Control -->
                    <div class="global-max-box mb-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <label for="global_total_practical" class="form-label-clean mb-1" style="font-size: 0.92rem;">
                                    <i class="fa-solid fa-gauge-high text-primary me-1"></i> Total Practical Marks (Global Limit)
                                </label>
                                <p class="text-muted mb-0" style="font-size: 0.8rem;">
                                    Changing this value syncs row limits and automatically validates all student scores below.
                                </p>
                            </div>
                            <div style="min-width: 220px; max-width: 280px; width: 100%;">
                                <div class="input-group">
                                    <input type="number" 
                                           id="global_total_practical" 
                                           name="global_total_practical" 
                                           class="form-control form-control-clean fw-bold text-primary" 
                                           style="border-radius: 10px 0 0 10px !important;"
                                           value="<?= htmlspecialchars($initial_max_practical) ?>" 
                                           step="0.01" 
                                           min="0.01" 
                                           required>
                                    <span class="input-group-text bg-white text-muted fw-bold" style="border: 1px solid #e2e8f0; border-left: none; border-radius: 0 10px 10px 0; font-size: 0.82rem;">Marks</span>
                                </div>
                                <div id="globalPracticalFeedback" class="text-danger fw-bold small mt-1" style="display:none;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Validation Alert Box -->
                    <div id="practicalValidationAlert" class="alert alert-danger d-none mb-3" role="alert" style="border-radius: 10px;">
                        <span class="me-2">⚠️</span>
                        <span id="practicalValidationAlertText"></span>
                    </div>

                    <!-- Students Table -->
                    <div class="table-responsive">
                        <table class="pca-table">
                            <colgroup>
                                <col style="width: 10%;">
                                <col style="width: 28%;">
                                <col style="width: 16%;">
                                <col style="width: 16%;">
                                <col style="width: 14%;">
                                <col style="width: 16%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Roll No</th>
                                    <th>Student Name</th>
                                    <th>Test Type</th>
                                    <th>Subject</th>
                                    <th class="text-center">Total Practical</th>
                                    <th>Practical Obtained</th>
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

                                foreach ($scores as $score): 
                                    $test_name = $exam_types[$score['test_code']] ?? ('Test '.$score['test_code']);
                                    $student_name = htmlspecialchars(trim($score['fname'].' '.$score['lname']));
                                    $pal_idx = abs(crc32($score['student_id'] . $student_name)) % count($palette);
                                    $avatar = $palette[$pal_idx];
                                    $initial = strtoupper(substr($score['fname'], 0, 1));
                                ?>
                                    <tr class="score-row" data-score-id="<?= $score['id'] ?>">
                                        <td>
                                            <span class="badge-roll-pill">#<?= htmlspecialchars($score['rollno']) ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="student-avatar-circle" style="background: <?= $avatar['bg'] ?>; color: <?= $avatar['color'] ?>;">
                                                    <?= $initial ?>
                                                </div>
                                                <span class="fw-semibold text-dark" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    <?= $student_name ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted fw-semibold"><?= htmlspecialchars($test_name) ?></span>
                                        </td>
                                        <td>
                                            <span class="text-muted fw-semibold"><?= htmlspecialchars($score['subject']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-max-pill">
                                                <span class="max-practical-badge"><?= htmlspecialchars($initial_max_practical) ?></span>
                                            </span>
                                            <input type="hidden" 
                                                   name="total_practical_<?= $score['id'] ?>" 
                                                   class="row-total-practical" 
                                                   value="<?= htmlspecialchars($initial_max_practical) ?>">
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" 
                                                       name="practical_<?= $score['id'] ?>" 
                                                       class="form-control form-control-sm student-practical-input fw-bold"
                                                       value="<?= htmlspecialchars($score['practical_marks']) ?>" 
                                                       step="0.01" 
                                                       min="0" 
                                                       max="<?= htmlspecialchars($initial_max_practical) ?>" 
                                                       required>
                                                <span class="input-group-text bg-light text-muted" style="border: 1px solid #e2e8f0; border-left: none; border-radius: 0 8px 8px 0; font-size: 0.8rem;">
                                                    / <span class="max-practical-badge"><?= htmlspecialchars($initial_max_practical) ?></span>
                                                </span>
                                            </div>
                                            <div class="mark-error-feedback" style="display:none;"></div>
                                            <div class="adjusted-badge" style="display:none;"></div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Bar -->
                    <div class="mt-4 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <button type="submit" name="update_all_practical_marks" id="updateAllPracticalMarksBtn" class="btn-primary-action">
                            <i class="fas fa-save me-1"></i> Save All Changes
                        </button>
                        <div class="d-flex align-items-center gap-2 text-muted fw-semibold" style="font-size: 0.86rem;">
                            <i class="fas fa-users text-primary"></i> Total students: <span class="badge bg-light text-dark border px-2 py-1"><?= count($scores) ?></span>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function getGlobalTotalPractical() {
    let val = parseFloat($("#global_total_practical").val());
    return isNaN(val) ? 0 : val;
}

function validatePracticalMark($input) {
    let globalMax = getGlobalTotalPractical();
    let rawVal = $input.val();
    let $td = $input.closest("td");
    let $feedback = $td.find(".mark-error-feedback");

    if (rawVal === "" || rawVal === null || rawVal === undefined) {
        $input.removeClass("is-invalid is-valid");
        $feedback.hide().empty();
        return true;
    }

    let val = parseFloat(rawVal);
    if (isNaN(val)) {
        $input.addClass("is-invalid").removeClass("is-valid");
        $feedback.html('⚠️ Please enter a valid number.').show();
        return false;
    }

    if (val < 0) {
        $input.addClass("is-invalid").removeClass("is-valid");
        $feedback.html('⚠️ Marks cannot be negative!').show();
        return false;
    } else if (globalMax > 0 && val > globalMax) {
        $input.addClass("is-invalid").removeClass("is-valid");
        $feedback.html('⚠️ Practical marks (' + val + ') cannot exceed total practical (' + globalMax + ')!').show();
        return false;
    } else {
        $input.removeClass("is-invalid").addClass("is-valid");
        $feedback.hide().empty();
        return true;
    }
}

function checkAllPracticalValidity() {
    let hasError = false;
    let countErrors = 0;
    let globalMax = getGlobalTotalPractical();

    if (isNaN(globalMax) || globalMax <= 0) {
        hasError = true;
    }

    $(".student-practical-input").each(function () {
        let isValid = validatePracticalMark($(this));
        if (!isValid) {
            hasError = true;
            countErrors++;
        }
    });

    if (hasError) {
        if (countErrors > 0) {
            $("#practicalValidationAlertText").html("<strong>Error:</strong> " + countErrors + " practical score(s) exceed total practical marks (<strong>" + globalMax + "</strong>) or have invalid values. Please correct them before saving.");
        } else {
            $("#practicalValidationAlertText").html("<strong>Error:</strong> Please enter a valid Total Practical Marks greater than 0.");
        }
        $("#practicalValidationAlert").removeClass("d-none alert-warning").addClass("alert-danger");
        $("#updateAllPracticalMarksBtn").addClass("btn-has-error");
    } else {
        $("#practicalValidationAlert").addClass("d-none");
        $("#updateAllPracticalMarksBtn").removeClass("btn-has-error");
    }

    return !hasError;
}

function syncGlobalTotalPractical() {
    let globalMax = getGlobalTotalPractical();
    let $globalInput = $("#global_total_practical");
    let $globalFeedback = $("#globalPracticalFeedback");

    if (globalMax <= 0 || isNaN(globalMax)) {
        $globalInput.addClass("is-invalid");
        $globalFeedback.html('⚠️ Total practical marks must be greater than 0!').show();
        return;
    } else {
        $globalInput.removeClass("is-invalid");
        $globalFeedback.hide().empty();
    }

    // Reflect to all table badges and hidden inputs
    $(".max-practical-badge").text(globalMax);
    $(".row-total-practical").val(globalMax);
    $(".student-practical-input").attr("max", globalMax);

    let adjustedCount = 0;

    // If max practical marks is reduced, automatically update each student's mark to remove the violation!
    $(".student-practical-input").each(function () {
        let $input = $(this);
        let currentVal = parseFloat($input.val());
        let $td = $input.closest("td");
        let $adjustedBadge = $td.find(".adjusted-badge");

        if (!isNaN(currentVal) && currentVal > globalMax) {
            $input.val(globalMax);
            $input.removeClass("is-invalid").addClass("is-valid");
            $td.find(".mark-error-feedback").hide().empty();

            $adjustedBadge.html('<i class="fas fa-exclamation-triangle me-1"></i> Auto-updated from ' + currentVal + ' to ' + globalMax + ' (max limit)').show();
            adjustedCount++;
        } else {
            validatePracticalMark($input);
        }
    });

    if (adjustedCount > 0) {
        $("#practicalValidationAlertText").html("<strong>Notice:</strong> Total practical marks updated to <strong>" + globalMax + "</strong>. " + adjustedCount + " student mark(s) exceeded " + globalMax + " and were automatically updated to " + globalMax + " to remove violations. You can edit individual marks below.");
        $("#practicalValidationAlert").removeClass("d-none alert-danger").addClass("alert-warning");
    }

    checkAllPracticalValidity();
}

$(document).on("input keyup change paste", ".student-practical-input", function () {
    let $input = $(this);
    $input.closest("td").find(".adjusted-badge").fadeOut();
    validatePracticalMark($input);
    checkAllPracticalValidity();
});

$("#global_total_practical").on("input keyup change paste", function () {
    syncGlobalTotalPractical();
});

$(document).ready(function () {
    syncGlobalTotalPractical();
});

$("#editAllPracticalForm").on("submit", function (e) {
    let globalMax = getGlobalTotalPractical();
    if (isNaN(globalMax) || globalMax <= 0) {
        e.preventDefault();
        $("#global_total_practical").focus();
        alert("Please enter a valid Total Practical Marks greater than 0.");
        return false;
    }

    let allValid = checkAllPracticalValidity();
    if (!allValid) {
        e.preventDefault();
        let $firstError = $(".student-practical-input.is-invalid").first();
        if ($firstError.length > 0) {
            $('html, body').animate({
                scrollTop: $firstError.offset().top - 140
            }, 300);
            $firstError.focus();
        }
        alert("Cannot save changes! Some practical marks exceed total practical (" + globalMax + "). Please fix the highlighted errors.");
        return false;
    }
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
