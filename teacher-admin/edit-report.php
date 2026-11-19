<?php
include('authentication.php');

// --- 1. Get Filters from URL ---
$student_id    = $_GET['student_id'] ?? null;
$class_code    = $_GET['class_code'] ?? null;
$subject       = $_GET['subject'] ?? null;
$exam_type_str = $_GET['exam_type'] ?? '';

// --- 2. Redirect if missing minimum info ---
if (!$student_id || !$class_code) {
    $_SESSION['error'] = "Invalid student or class information.";
    header("Location: view-report.php");
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

// --- 4. Fetch Student Info, Class Students (for quick switcher), and Scores ---
$student_info = null;
$class_students = [];
$scores = [];
$teacher_id = $_SESSION['auth_user']['user_id'] ?? $_SESSION['auth_user']['id'] ?? 0;

if (isset($con)) {
    // a. Student info
    $stmt = mysqli_prepare($con, "SELECT id, fname, lname, rollno FROM users WHERE id=? AND role_as='0' LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($res && mysqli_num_rows($res) > 0) {
        $student_info = mysqli_fetch_assoc($res);
    }
    mysqli_stmt_close($stmt);

    // b. Fetch all students in this class for quick switcher
    $stmt_cs = mysqli_prepare($con, "SELECT id, fname, lname, rollno FROM users WHERE class=? AND role_as='0' ORDER BY CAST(rollno AS UNSIGNED) ASC, rollno ASC");
    if ($stmt_cs) {
        mysqli_stmt_bind_param($stmt_cs, "s", $class_code);
        mysqli_stmt_execute($stmt_cs);
        $res_cs = mysqli_stmt_get_result($stmt_cs);
        if ($res_cs) {
            while ($cs_row = mysqli_fetch_assoc($res_cs)) {
                $class_students[] = $cs_row;
            }
        }
        mysqli_stmt_close($stmt_cs);
    }

    // c. Fetch ONLY this student's score records
    $query = "SELECT ss.id, ss.student_id, ss.test_code, ss.subject, ss.marks_obtained, ss.total_marks, DATE(ss.created_at) as date
              FROM student_score ss
              WHERE ss.student_id = ? AND ss.class_code = ?";
    $params = [$student_id, $class_code];
    $types = "is";

    if (!empty($subject)) {
        $query .= " AND ss.subject = ?";
        $params[] = $subject;
        $types .= "s";
    }

    if ($filter_test_code > 0) {
        $query .= " AND ss.test_code = ?";
        $params[] = $filter_test_code;
        $types .= "i";
    }

    if ($teacher_id) {
        $query .= " AND ss.teacher_id = ?";
        $params[] = $teacher_id;
        $types .= "i";
    }

    $query .= " ORDER BY ss.created_at DESC";

    $stmt = mysqli_prepare($con, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $res_scores = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res_scores)) {
            $scores[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

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

/* Badges */
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

.badge-roll-pill {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0.25rem 0.6rem;
    border-radius: 6px;
    display: inline-block;
    white-space: nowrap;
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

/* Buttons */
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

.btn-secondary-action {
    background: #f8fafc;
    color: #4338ca !important;
    border: 1px solid #c7d2fe;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.btn-secondary-action:hover {
    background: #eef2ff;
    border-color: #a5b4fc;
    color: #3730a3 !important;
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

/* Student Profile Banner */
.student-profile-strip {
    background: #fbfbfe;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.1rem 1.3rem;
    margin-bottom: 1.4rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.student-avatar-lg {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
    color: #4338ca;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    font-weight: 800;
    flex-shrink: 0;
}

.form-label-clean {
    font-size: 0.8rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 0.35rem;
    display: block;
}

.form-control-clean {
    border-radius: 10px !important;
    border: 1px solid #e2e8f0 !important;
    padding: 0.5rem 0.85rem !important;
    font-size: 0.88rem !important;
    color: #1e293b !important;
    background: #ffffff !important;
    height: 38px;
    transition: all 0.2s ease;
}

.form-control-clean:focus {
    background: #ffffff !important;
    border-color: #5e48eb !important;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12) !important;
    outline: none;
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
    .pca-card-header .btn-secondary-action,
    .pca-card-header .btn-back-link {
        width: 100% !important;
        justify-content: center !important;
    }
    .student-profile-strip {
        flex-direction: column !important;
        align-items: stretch !important;
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

/* Student mark inputs */
.student-single-mark-input {
    border-radius: 8px 0 0 8px !important;
    border: 1px solid #e2e8f0 !important;
    padding: 0.45rem 0.75rem !important;
    font-size: 0.9rem !important;
    font-weight: 700;
    background: #f8fafc;
    transition: all 0.2s ease;
}

.student-single-mark-input:focus {
    background: #ffffff !important;
    border-color: #5e48eb !important;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12) !important;
    outline: none;
}

.student-single-mark-input.is-invalid {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
}

.mark-error-msg {
    font-size: 0.8rem;
    font-weight: 600;
    color: #dc2626;
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
                    <i class="fa-solid fa-user-pen"></i>
                    Edit Student Marks
                </h1>
                <?php if ($student_info): ?>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                        <span class="badge-ctx-pill">
                            <i class="fa-solid fa-user me-1 text-primary"></i> <?= htmlspecialchars($student_info['fname'].' '.$student_info['lname']) ?>
                        </span>
                        <span class="badge-ctx-pill">
                            <i class="fa-solid fa-id-badge me-1 text-primary"></i> Roll: #<?= htmlspecialchars($student_info['rollno']) ?>
                        </span>
                        <span class="badge-ctx-pill">
                            <i class="fa-solid fa-graduation-cap me-1 text-primary"></i> <?= htmlspecialchars($class_name) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <?php if (!empty($exam_type_str) && !empty($subject)): ?>
                    <a href="edit-all-report.php?class_code=<?= urlencode($class_code) ?>&subject=<?= urlencode($subject) ?>&exam_type=<?= urlencode($exam_type_str) ?>" class="btn-secondary-action">
                        <i class="fas fa-sliders-h"></i> Update Max Marks & All Scores
                    </a>
                <?php endif; ?>
                <a href="view-report.php?class_code=<?= htmlspecialchars($class_code) ?>&subject=<?= urlencode($subject ?? '') ?>&exam_type=<?= urlencode($exam_type_str) ?>" class="btn-back-link">
                    <i class="fas fa-arrow-left me-1"></i> Back to Report
                </a>
            </div>
        </div>

        <div class="pca-card-body">

            <!-- Student Profile & Quick Switcher Strip -->
            <?php if ($student_info): 
                $initial = strtoupper(substr($student_info['fname'], 0, 1));
            ?>
                <div class="student-profile-strip">
                    <div class="d-flex align-items-center gap-3">
                        <div class="student-avatar-lg">
                            <?= $initial ?>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
                                    <?= htmlspecialchars($student_info['fname'].' '.$student_info['lname']) ?>
                                </h5>
                                <span class="badge-roll-pill">#<?= htmlspecialchars($student_info['rollno']) ?></span>
                            </div>
                            <span class="text-muted" style="font-size: 0.82rem;">
                                <?= htmlspecialchars($class_name) ?>
                                <?php if (!empty($subject)): ?> &bull; Subject: <?= htmlspecialchars($subject) ?><?php endif; ?>
                                <?php if ($filter_test_code > 0 && isset($exam_types[$filter_test_code])): ?> &bull; Test: <?= htmlspecialchars($exam_types[$filter_test_code]) ?><?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <?php if (!empty($class_students)): ?>
                        <div class="d-flex align-items-center gap-2">
                            <label for="studentSwitcher" class="form-label-clean mb-0 text-nowrap">
                                <i class="fa-solid fa-users text-primary me-1"></i> Switch Student:
                            </label>
                            <select id="studentSwitcher" class="form-control form-control-clean" style="min-width: 220px;" onchange="if(this.value) location.href='edit-report.php?student_id='+this.value+'&class_code=<?= urlencode($class_code) ?>&subject=<?= urlencode($subject ?? '') ?>&exam_type=<?= urlencode($exam_type_str) ?>'">
                                <?php foreach ($class_students as $cs): ?>
                                    <option value="<?= $cs['id'] ?>" <?= ($cs['id'] == $student_id) ? 'selected' : '' ?>>
                                        Roll <?= htmlspecialchars($cs['rollno']) ?> - <?= htmlspecialchars($cs['fname'].' '.$cs['lname']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($scores)): ?>
                <div class="empty-state-clean text-center">
                    <div class="empty-icon-circle mx-auto mb-3">
                        <i class="fa-solid fa-clipboard-question text-muted fa-lg"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No theory marks found</h6>
                    <p class="text-muted mb-3" style="font-size: 0.85rem;">No theory marks records were found for this student.</p>
                    <a href="view-report.php?class_code=<?= htmlspecialchars($class_code) ?>&subject=<?= urlencode($subject ?? '') ?>&exam_type=<?= urlencode($exam_type_str) ?>" class="btn-back-link">
                        <i class="fas fa-arrow-left me-1"></i> Back to Report
                    </a>
                </div>
            <?php else: ?>
                <form action="teacher_code.php" method="POST" id="singleStudentMarkForm">
                    <input type="hidden" name="action" value="update_single_student_marks">
                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($student_id) ?>">
                    <input type="hidden" name="redirect_class" value="<?= htmlspecialchars($class_code) ?>">
                    <input type="hidden" name="redirect_subject" value="<?= htmlspecialchars($subject ?? '') ?>">
                    <input type="hidden" name="exam_type" value="<?= htmlspecialchars($exam_type_str) ?>">

                    <div class="table-responsive">
                        <table class="pca-table">
                            <colgroup>
                                <col style="width: 16%;">
                                <col style="width: 24%;">
                                <col style="width: 22%;">
                                <col style="width: 16%;">
                                <col style="width: 22%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Test Type</th>
                                    <th>Subject</th>
                                    <th class="text-center">Max Marks</th>
                                    <th>Marks Obtained</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($scores as $score): 
                                    $test_name = $exam_types[$score['test_code']] ?? ('Test '.$score['test_code']);
                                    $max_m = floatval($score['total_marks']);
                                ?>
                                    <tr class="score-row" data-score-id="<?= $score['id'] ?>" data-max-marks="<?= $max_m ?>">
                                        <td>
                                            <span class="text-muted fw-semibold"><?= htmlspecialchars($score['date']) ?></span>
                                        </td>
                                        <td>
                                            <strong class="text-dark"><?= htmlspecialchars($test_name) ?></strong>
                                        </td>
                                        <td>
                                            <span class="text-dark fw-semibold"><?= htmlspecialchars($score['subject']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-max-pill">
                                                <?= $max_m ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" 
                                                       name="marks_<?= $score['id'] ?>" 
                                                       class="form-control form-control-sm fw-bold student-single-mark-input" 
                                                       value="<?= htmlspecialchars($score['marks_obtained']) ?>" 
                                                       step="0.01" 
                                                       min="0" 
                                                       max="<?= $max_m ?>" 
                                                       required>
                                                <span class="input-group-text bg-light text-muted" style="border: 1px solid #e2e8f0; border-left: none; border-radius: 0 8px 8px 0; font-size: 0.8rem;">
                                                    / <?= $max_m ?>
                                                </span>
                                            </div>
                                            <div class="mark-error-msg" style="display:none;"></div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Action Bar -->
                    <div class="mt-4 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <button type="submit" name="update_single_student_marks" id="saveSingleMarkBtn" class="btn-primary-action">
                            <i class="fas fa-save me-1"></i> Save Student Marks
                        </button>
                        <div class="d-flex align-items-center gap-2 text-muted fw-semibold" style="font-size: 0.86rem;">
                            <i class="fas fa-file-lines text-primary"></i> Total records: <span class="badge bg-light text-dark border px-2 py-1"><?= count($scores) ?></span>
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
function validateSingleRow($input) {
    let $row = $input.closest("tr");
    let max = parseFloat($row.data("max-marks")) || 0;
    let raw = $input.val();
    let $err = $row.find(".mark-error-msg");

    if (raw === "" || raw === null || raw === undefined) {
        $input.removeClass("is-invalid");
        $err.hide().empty();
        return true;
    }

    let val = parseFloat(raw);
    if (isNaN(val)) {
        $input.addClass("is-invalid");
        $err.text("⚠️ Please enter a valid number.").show();
        return false;
    }

    if (val < 0) {
        $input.addClass("is-invalid");
        $err.text("⚠️ Marks cannot be negative!").show();
        return false;
    } else if (max > 0 && val > max) {
        $input.addClass("is-invalid");
        $err.text("⚠️ Marks (" + val + ") cannot exceed maximum marks (" + max + ")!").show();
        return false;
    } else {
        $input.removeClass("is-invalid");
        $err.hide().empty();
        return true;
    }
}

function checkAllSingleValidity() {
    let allValid = true;
    $(".student-single-mark-input").each(function () {
        if (!validateSingleRow($(this))) {
            allValid = false;
        }
    });

    if (allValid) {
        $("#saveSingleMarkBtn").removeClass("btn-has-error");
    } else {
        $("#saveSingleMarkBtn").addClass("btn-has-error");
    }
    return allValid;
}

$(document).on("input keyup change paste", ".student-single-mark-input", function () {
    validateSingleRow($(this));
    checkAllSingleValidity();
});

$("#singleStudentMarkForm").on("submit", function (e) {
    if (!checkAllSingleValidity()) {
        e.preventDefault();
        let $firstError = $(".student-single-mark-input.is-invalid").first();
        if ($firstError.length > 0) {
            $firstError.focus();
        }
        alert("Please correct the mark exceeding the maximum limit before saving.");
        return false;
    }
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
