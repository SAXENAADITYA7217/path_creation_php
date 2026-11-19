<?php
include('authentication.php');
include('includes/header.php');

// --- Class Name Mapping ---
$class_map = [
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

// Define all exam types with their test_code
$all_exam_types = [
    1 => 'Unit Test 1',
    2 => 'Unit Test 2',
    5 => 'Half Year Exam',
    3 => 'Unit Test 3',
    4 => 'Unit Test 4',
    6 => 'Final Exam',
    7 => 'Class Test'
];

// Distinct color theme for each test type badge
$test_theme_colors = [
    1 => ['name' => 'Unit Test 1', 'badge_bg' => '#1d4ed8'],
    2 => ['name' => 'Unit Test 2', 'badge_bg' => '#0e7490'],
    5 => ['name' => 'Half Year Exam', 'badge_bg' => '#6d28d9'],
    3 => ['name' => 'Unit Test 3', 'badge_bg' => '#c2410c'],
    4 => ['name' => 'Unit Test 4', 'badge_bg' => '#047857'],
    6 => ['name' => 'Final Exam', 'badge_bg' => '#b91c1c'],
    7 => ['name' => 'Class Test', 'badge_bg' => '#334155']
];

// Passing percentage (33%)
if (!defined('PASS_PERCENTAGE')) {
    define('PASS_PERCENTAGE', 0.33);
}

// Grading scale matching the sample report card:
// A+: >=90%, A: >=80%, B+: >=70%, B: >=60%, C: >=50%, D: >=40%, E: >=33%, F: <33%
if (!function_exists('get_grade')) {
    function get_grade($percentage) {
        if ($percentage >= 90) return "A+";
        if ($percentage >= 80) return "A";
        if ($percentage >= 70) return "B+";
        if ($percentage >= 60) return "B";
        if ($percentage >= 50) return "C";
        if ($percentage >= 40) return "D";
        if ($percentage >= 33) return "E";
        return "F";
    }
}

// Function to calculate rank in class for a specific test
if (!function_exists('get_student_rank_in_class')) {
    function get_student_rank_in_class($con, $student_id, $class_code, $t_code) {
        $total_students = 0;
        $stmt_count = mysqli_prepare($con, "SELECT COUNT(*) AS total_students FROM users WHERE class=? AND role_as=0");
        if ($stmt_count) {
            mysqli_stmt_bind_param($stmt_count, "s", $class_code);
            mysqli_stmt_execute($stmt_count);
            $res_count = mysqli_stmt_get_result($stmt_count);
            if ($row_c = mysqli_fetch_assoc($res_count)) {
                $total_students = intval($row_c['total_students']);
            }
            mysqli_stmt_close($stmt_count);
        }

        $rank = 1;
        $found = false;
        $total_ranked = 0;
        $rank_sql = "SELECT student_id, SUM(COALESCE(marks_obtained, 0) + COALESCE(practical_marks, 0)) AS total_score
                     FROM student_score
                     WHERE class_code=? AND test_code=?
                     GROUP BY student_id
                     ORDER BY total_score DESC";
        $stmt_rank = mysqli_prepare($con, $rank_sql);
        if ($stmt_rank) {
            mysqli_stmt_bind_param($stmt_rank, "si", $class_code, $t_code);
            mysqli_stmt_execute($stmt_rank);
            $res_rank = mysqli_stmt_get_result($stmt_rank);
            $current_pos = 1;
            while ($row_r = mysqli_fetch_assoc($res_rank)) {
                $total_ranked++;
                if (intval($row_r['student_id']) === intval($student_id)) {
                    $rank = $current_pos;
                    $found = true;
                }
                $current_pos++;
            }
            mysqli_stmt_close($stmt_rank);
        }

        $class_size = max($total_students, $total_ranked, 1);
        $formatted_rank = str_pad($rank, 2, '0', STR_PAD_LEFT);
        $formatted_total = str_pad($class_size, 2, '0', STR_PAD_LEFT);

        return [
            'rank' => $rank,
            'class_size' => $class_size,
            'display' => $formatted_rank . ' / ' . $formatted_total
        ];
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
    padding: 0.5rem 0.5rem 2.5rem 0.5rem;
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: hidden !important;
    box-sizing: border-box;
}

.report-page-wrap .row {
    margin-left: 0 !important;
    margin-right: 0 !important;
}

/* Monolithic White Card for Controls */
.pca-white-card {
    background: #ffffff;
    border: 1px solid #edf0f5;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    width: 100% !important;
    margin-bottom: 1.5rem;
}

.pca-card-header {
    padding: 1.1rem 1.4rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f3f7;
    background: #ffffff;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.pca-card-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.pca-card-title i {
    color: var(--pca-primary);
}

.pca-card-body {
    padding: 1.25rem 1.4rem;
}

/* Student Profile & Quick Switcher Strip */
.student-profile-strip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}

.student-avatar-lg {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: linear-gradient(135deg, #5e48eb 0%, #4338ca 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    font-weight: 800;
    flex-shrink: 0;
}

.badge-roll-pill {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
}

.badge-ctx-pill {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
}

/* Filter Controls Box */
.filter-box-clean {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
}

.form-label-clean {
    font-size: 0.84rem;
    font-weight: 700;
    color: #334155;
    margin-bottom: 0.45rem;
    display: flex;
    align-items: center;
}

.form-control-clean {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 500;
    color: #1e293b;
    padding: 0.5rem 0.85rem;
    transition: all 0.2s ease;
}

.form-control-clean:focus {
    border-color: #5e48eb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12);
}

/* Action Buttons */
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
    justify-content: center;
    gap: 7px;
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none !important;
}

.btn-primary-action:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    box-shadow: 0 5px 14px rgba(94, 72, 235, 0.35);
    transform: translateY(-1px);
}

.btn-print-action {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff !important;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 0.55rem 1.4rem;
    border: none;
    box-shadow: 0 3px 10px rgba(16, 185, 129, 0.25);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none !important;
}

.btn-print-action:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    box-shadow: 0 5px 14px rgba(16, 185, 129, 0.35);
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
    gap: 6px;
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.btn-back-link:hover {
    background: #f8fafc;
    color: #0f172a !important;
    border-color: #cbd5e1;
}

/* Quick selection pills */
.quick-pill-btn {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.35rem 0.75rem;
    border-radius: 8px;
    transition: all 0.15s ease;
    cursor: pointer;
}

.quick-pill-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #cbd5e1;
}

/* Test Checkbox Pills */
.test-checkbox-wrap {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.45rem 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}

.test-checkbox-wrap:hover {
    border-color: #cbd5e1;
    background: #fafafa;
}

.test-checkbox-wrap.is-checked {
    border-color: #5e48eb;
    background: #f5f3ff;
}

/* Radio Mode Wrap */
.radio-mode-wrap {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.5rem 0.75rem;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: all 0.15s ease;
}

.radio-mode-wrap:hover {
    border-color: #cbd5e1;
    background: #fafafa;
}

.radio-mode-wrap.is-checked {
    border-color: #5e48eb;
    background: #f5f3ff;
}

/* Exact Report Card Table Style for Printing & Screen */
.report-card-page {
    border: none !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    min-height: 1050px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    padding: 24px 28px;
    box-sizing: border-box;
    background: #ffffff;
    position: relative;
    overflow: hidden !important;
}

.report-header-wrap {
    margin-left: 175px;
    margin-top: 6px;
    max-width: 65%;
}

.student-info-card {
    margin-bottom: 12px !important;
    padding: 12px 20px !important;
}

.info-label {
    width: 140px;
    font-weight: 700;
    color: #111827;
}

.report-table-wrap {
    margin-top: 6px !important;
    margin-bottom: 12px !important;
    overflow: visible !important;
    overflow-x: visible !important;
    width: 100% !important;
}

.summary-cards-row {
    margin-top: 8px !important;
    margin-bottom: 14px !important;
}

.signatures-row,
.report-card-page .signatures-row {
    margin-top: auto !important;
    margin-bottom: 14px !important;
    padding-top: 18px !important;
}

.student-report-table {
    width: 100% !important;
    max-width: 100% !important;
    table-layout: fixed !important;
    border-collapse: collapse !important;
    background-color: #ffffff;
}

.student-report-table th, .student-report-table td {
    border: 1px solid #cbd5e1 !important;
    padding: 8px 4px !important;
    font-size: 0.88rem !important;
    vertical-align: middle !important;
    box-sizing: border-box !important;
}

.student-report-table td {
    word-break: break-word !important;
}

.col-percentage,
th.col-percentage,
td.col-percentage {
    white-space: nowrap !important;
}

.student-report-table thead th {
    border-color: #1e293b !important;
    background-color: #1e293b !important;
    color: #ffffff !important;
    padding: 8px 3px !important;
    font-size: 0.84rem !important;
    white-space: nowrap !important;
    word-break: keep-all !important;
    overflow-wrap: normal !important;
    letter-spacing: normal !important;
    hyphens: none !important;
    text-transform: none !important;
}

/* Fail subject: Entire row / all columns red */
.student-report-table tr.failed-row,
.student-report-table tr.failed-row td {
    background-color: #fee2e2 !important;
    color: #b91c1c !important;
    border-color: #fca5a5 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

/* Passed subject: Clean white row */
.student-report-table tr.passed-row,
.student-report-table tr.passed-row td {
    background-color: #ffffff !important;
    color: #1e293b !important;
    border-color: #cbd5e1 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
}

.student-report-table .fail-subject-text {
    color: #b91c1c !important;
    font-weight: 700 !important;
}

.student-report-table .fail-mark-text {
    color: #b91c1c !important;
    font-weight: 800 !important;
}

.student-report-table .fail-grade-badge {
    background-color: #dc2626 !important;
    color: #ffffff !important;
    font-size: 0.82rem !important;
    font-weight: 800 !important;
    padding: 2px 7px !important;
    border-radius: 4px !important;
    display: inline-block !important;
}

/* Print CSS - Full-Fidelity Professional Report Card Printing */
@media print {
    /* 1. Global Print Reset: hide everything by default */
    body * {
        visibility: hidden;
    }

    /* 2. Show ONLY the report card print area and its contents */
    #report-card-print-area,
    #report-card-print-area *,
    .print-visible-area,
    .print-visible-area * {
        visibility: visible !important;
    }

    /* 3. Completely eliminate all Web Page & Dashboard Chrome from DOM */
    html.semi-dark .sidebar-wrapper,
    .sidebar-wrapper,
    .sidebar-header,
    .sidebar-menu-wrapper,
    .sidebar-bottom-badge,
    header,
    .topbar,
    nav,
    .page-footer,
    footer,
    .overlay,
    .back-to-top,
    .mobile-toggle-menu,
    .pca-white-card,
    .btn-print-action,
    .btn-back-link,
    .print-button,
    .btn-primary-action,
    #sidebar-wrapper,
    #topbar {
        display: none !important;
        visibility: hidden !important;
        width: 0 !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        position: absolute !important;
        left: -9999px !important;
        top: -9999px !important;
    }

    /* 4. Reset Layout Containers to Natural Zero-Margin Stream */
    html, body {
        background: #ffffff !important;
        background-color: #ffffff !important;
        color: #000000 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
        position: static !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .wrapper,
    main,
    .page-wrapper,
    .page-content,
    .report-page-wrap,
    .container-fluid {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        min-height: 0 !important;
        overflow: visible !important;
        position: static !important;
        left: 0 !important;
        top: 0 !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }

    /* 5. Static Flow Print Area */
    .print-visible-area {
        display: block !important;
        position: static !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: transparent !important;
        overflow: visible !important;
    }

    /* 6. Exact A4 Fit Report Card Container: occupies entire page */
    .report-card-page {
        box-sizing: border-box !important;
        width: 100% !important;
        max-width: 194mm !important;
        height: 283mm !important;
        min-height: 283mm !important;
        max-height: 283mm !important;
        padding: 8mm 12mm 6mm 12mm !important;
        margin: 0 auto !important;
        border: none !important;
        border-radius: 0 !important;
        position: relative !important;
        background-color: #ffffff !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-start !important;
        overflow: hidden !important;
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        page-break-after: always !important;
        break-after: page !important;
        box-shadow: none !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .report-card-page:last-child {
        page-break-after: auto !important;
        break-after: auto !important;
    }

    /* 7. Header in Print - clear space from top-left corner */
    .report-header-wrap {
        margin-left: 145px !important;
        margin-top: 4px !important;
        margin-bottom: 10px !important;
        position: relative !important;
        z-index: 5 !important;
    }
    .report-header-wrap img {
        height: 50px !important;
        width: auto !important;
    }
    .report-header-wrap div[style*="font-size: 1.85rem;"] {
        font-size: 1.70rem !important;
    }
    .report-header-wrap div[style*="font-size: 0.76rem;"] {
        font-size: 0.74rem !important;
        letter-spacing: 2.5px !important;
        margin-top: 2px !important;
    }

    /* 8. Emblem & Title in Print */
    .report-title-wrap {
        margin-top: 2px !important;
        margin-bottom: 10px !important;
    }
    .report-title-wrap h2 {
        font-size: 1.90rem !important;
    }
    .report-title-wrap svg[width="48"] {
        width: 42px !important;
        height: 60px !important;
    }
    .report-title-wrap svg[width="44"] {
        width: 40px !important;
        height: 26px !important;
    }

    /* 9. Student Details Box in Print */
    .student-info-card {
        padding: 8px 16px !important;
        margin-bottom: 8px !important;
        border: 1.2px solid #64748b !important;
        border-radius: 8px !important;
    }
    .student-info-card .d-flex {
        margin-bottom: 2px !important;
        font-size: 0.88rem !important;
    }

    /* 10. Marks Table in Print: 100% fixed width, no cut-off */
    .report-table-wrap {
        margin-top: 4px !important;
        margin-bottom: 8px !important;
        overflow: visible !important;
        overflow-x: visible !important;
        width: 100% !important;
    }
    .student-report-table {
        width: 100% !important;
        max-width: 100% !important;
        table-layout: fixed !important;
        border: 1.5px solid #1e293b !important;
        border-collapse: collapse !important;
        background-color: #ffffff !important;
    }
    .student-report-table th, 
    .student-report-table td {
        padding: 7px 3px !important;
        font-size: 0.84rem !important;
        border: 1px solid #475569 !important;
        vertical-align: middle !important;
        box-sizing: border-box !important;
    }
    .student-report-table td {
        word-break: break-word !important;
    }
    .col-percentage,
    th.col-percentage,
    td.col-percentage {
        white-space: nowrap !important;
    }
    .student-report-table thead th {
        background-color: #1e293b !important;
        color: #ffffff !important;
        padding: 8px 3px !important;
        font-size: 0.82rem !important;
        white-space: nowrap !important;
        word-break: keep-all !important;
        overflow-wrap: normal !important;
        letter-spacing: normal !important;
        hyphens: none !important;
        text-transform: none !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    /* Entire row/columns red for failed/back subjects across all pages */
    .student-report-table tr.failed-row,
    .student-report-table tr.failed-row td {
        background-color: #fee2e2 !important;
        color: #b91c1c !important;
        border-color: #fca5a5 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    /* Passed subject: white */
    .student-report-table tr.passed-row,
    .student-report-table tr.passed-row td {
        background-color: #ffffff !important;
        color: #1e293b !important;
        border-color: #475569 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .student-report-table .fail-subject-text {
        color: #b91c1c !important;
        font-weight: 700 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .student-report-table .fail-mark-text {
        color: #b91c1c !important;
        font-weight: 800 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .student-report-table .fail-grade-badge {
        background-color: #dc2626 !important;
        color: #ffffff !important;
        font-size: 0.82rem !important;
        font-weight: 800 !important;
        padding: 2px 7px !important;
        border-radius: 4px !important;
        display: inline-block !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .student-report-table tfoot td {
        background-color: #f1f5f9 !important;
        color: #111827 !important;
        font-weight: 800 !important;
        padding: 7px 4px !important;
        font-size: 0.86rem !important;
        border-color: #475569 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* 11. Summary Cards Row in Print (Fixed 4 columns across A4) */
    .summary-cards-row {
        margin-top: 8px !important;
        margin-bottom: 10px !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 8px !important;
    }
    .summary-cards-row > div {
        flex: 1 1 25% !important;
        max-width: 25% !important;
        padding: 0 !important;
    }
    .summary-cards-row > div > div {
        padding: 10px 4px !important;
        border: 1.2px solid #64748b !important;
        border-radius: 8px !important;
        height: 100% !important;
        background-color: #ffffff !important;
    }
    .summary-cards-row i {
        font-size: 1.35rem !important;
    }
    .summary-cards-row div[style*="font-size: 1.45rem;"] {
        font-size: 1.30rem !important;
    }
    .summary-cards-row span[style*="font-size: 1.15rem;"] {
        font-size: 1.05rem !important;
        padding: 3px 10px !important;
    }

    /* 12. Signatures in Print: anchored to bottom border */
    .signatures-row,
    .report-card-page .signatures-row {
        margin-top: auto !important;
        margin-bottom: 8mm !important;
        padding-top: 14px !important;
    }
    .signatures-row strong {
        font-size: 0.90rem !important;
    }

    /* 13. Decorative Corners in Print */
    .top-corner-svg-left,
    .top-corner-svg-right {
        width: 165px !important;
        height: 124px !important;
    }
    .bottom-corner-svg-left,
    .bottom-corner-svg-right {
        width: 155px !important;
        height: 95px !important;
    }

    /* 14. Explicit A4 Page Dimensions with balanced margins */
    @page {
        size: A4 portrait;
        margin: 6mm 8mm 6mm 8mm;
    }

    /* Print overrides for responsive elements */
    .mobile-table-hint {
        display: none !important;
    }
    .report-header-wrap {
        margin-left: 145px !important;
        margin-top: 4px !important;
        margin-bottom: 10px !important;
        max-width: 65% !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        text-align: left !important;
    }
    .report-header-wrap .header-divider {
        display: block !important;
    }
    .student-info-card .info-col-left,
    .student-info-card .info-col-right {
        width: 50% !important;
        max-width: 50% !important;
        flex: 0 0 50% !important;
    }
    .student-info-card .info-col-right {
        border-left: 1px solid #64748b !important;
        border-top: none !important;
        margin-top: 0 !important;
        padding-top: 0 !important;
        padding-left: 1.5rem !important;
    }
    .student-info-card .info-label {
        width: 130px !important;
    }
}

/* ========================================================
   Mobile & Tablet Responsive Styles (Screen View Only)
   ======================================================== */
@media screen and (max-width: 768px) {
    /* 1. Page Wrap */
    .report-page-wrap {
        padding: 0 4px !important;
        overflow-x: hidden !important;
    }

    /* 2. Outer Report Card Container */
    .report-card-page {
        min-height: auto !important;
        padding: 16px 12px 14px 12px !important;
        margin: 0 auto 1.5rem auto !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
        max-width: 100% !important;
        width: 100% !important;
        overflow: hidden !important;
    }

    /* 3. Scaled Corner SVGs */
    .top-corner-svg-left,
    .top-corner-svg-right {
        width: 65px !important;
        height: 48px !important;
    }
    .bottom-corner-svg-left,
    .bottom-corner-svg-right {
        width: 70px !important;
        height: 44px !important;
    }

    /* 4. Responsive School Branding Header */
    .report-header-wrap {
        margin: 2px auto 10px auto !important;
        max-width: calc(100% - 60px) !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        position: relative !important;
        z-index: 5 !important;
    }
    .report-header-wrap .header-logo-box {
        height: 44px !important;
        margin-bottom: 4px !important;
    }
    .report-header-wrap img {
        height: 42px !important;
        width: auto !important;
    }
    .report-header-wrap .header-divider {
        display: none !important;
    }
    .report-header-wrap .brand-text-block {
        text-align: center !important;
        white-space: normal !important;
    }
    .report-header-wrap .brand-title {
        font-size: 1.20rem !important;
        line-height: 1.15 !important;
        white-space: nowrap !important;
    }
    .report-header-wrap .brand-subtitle {
        font-size: 0.60rem !important;
        letter-spacing: 2px !important;
        margin-top: 2px !important;
    }

    /* 5. Emblem & Title */
    .report-title-wrap {
        margin: 4px 0 10px 0 !important;
    }
    .report-title-wrap svg[width="48"] {
        width: 26px !important;
        height: 38px !important;
    }
    .report-title-wrap svg[width="44"] {
        width: 28px !important;
        height: 20px !important;
    }
    .report-title-wrap h2 {
        font-size: 1.30rem !important;
        letter-spacing: 0.2px !important;
        line-height: 1.2 !important;
    }
    .report-title-wrap .title-flank-line {
        width: 28px !important;
    }
    .report-title-wrap .test-name-text {
        font-size: 0.88rem !important;
        margin: 0 6px !important;
    }

    /* 6. Student Info Box */
    .student-info-card {
        padding: 10px 12px !important;
        margin-bottom: 8px !important;
        border-radius: 10px !important;
    }
    .student-info-card .info-col-left {
        padding-right: 0 !important;
    }
    .student-info-card .info-col-right {
        padding-left: 0 !important;
        border-left: none !important;
        border-top: 1px dashed #cbd5e1 !important;
        margin-top: 6px !important;
        padding-top: 6px !important;
    }
    .student-info-card .info-row {
        display: flex !important;
        align-items: baseline !important;
        font-size: 0.82rem !important;
        margin-bottom: 4px !important;
    }
    .student-info-card .info-label {
        width: 105px !important;
        min-width: 105px !important;
        font-size: 0.80rem !important;
        font-weight: 700 !important;
        color: #111827 !important;
    }
    .student-info-card .info-colon {
        margin-right: 6px !important;
        font-weight: 700 !important;
    }
    .student-info-card .info-value {
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        color: #1f2937 !important;
        word-break: break-word !important;
    }

    /* 7. Marks Table Horizontal Smooth Scroll */
    .report-table-wrap {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch !important;
        margin-top: 4px !important;
        margin-bottom: 8px !important;
        border-radius: 8px !important;
        border: 1px solid #cbd5e1 !important;
    }
    .student-report-table {
        min-width: 600px !important;
        width: 100% !important;
        table-layout: auto !important;
        border: none !important;
    }
    .student-report-table th,
    .student-report-table td {
        padding: 6px 5px !important;
        font-size: 0.80rem !important;
        white-space: nowrap !important;
    }
    .student-report-table th:nth-child(2),
    .student-report-table td:nth-child(2) {
        white-space: normal !important;
        min-width: 130px !important;
        word-break: normal !important;
    }

    /* 8. Summary Cards 2x2 Grid */
    .summary-cards-row {
        margin-top: 8px !important;
        margin-bottom: 10px !important;
    }
    .summary-cards-row .col-6 {
        padding: 3px !important;
    }
    .summary-cards-row .col-6 > div {
        padding: 10px 4px !important;
        border-radius: 8px !important;
    }
    .summary-cards-row i {
        font-size: 1.25rem !important;
    }
    .summary-cards-row .summary-title {
        font-size: 0.74rem !important;
        margin-bottom: 2px !important;
    }
    .summary-cards-row .summary-val {
        font-size: 1.15rem !important;
    }
    .summary-cards-row .summary-sub {
        font-size: 0.70rem !important;
    }
    .summary-cards-row .summary-badge {
        font-size: 0.95rem !important;
        padding: 3px 8px !important;
    }

    /* 9. Signatures Row */
    .signatures-row {
        margin-top: 12px !important;
        margin-bottom: 10px !important;
        padding-top: 6px !important;
    }
    .signatures-row strong {
        font-size: 0.78rem !important;
    }
    .signatures-row div[style*="border-top: 2px solid"] {
        width: 85% !important;
    }

    /* 10. Filter & Quick Switcher Controls on Mobile */
    .pca-card-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
    }
    .pca-card-header .btn-print-action,
    .pca-card-header .btn-back-link {
        width: 100% !important;
        justify-content: center !important;
        text-align: center !important;
    }
    .student-profile-strip {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 12px !important;
    }
    .student-profile-strip select {
        width: 100% !important;
        min-width: 100% !important;
    }
}
</style>

<div class="report-page-wrap">

<?php
if (isset($_GET['student_id'])):

    $student_id = intval($_GET['student_id']);

    if (!isset($con) || !$con) {
        die("<div class='container mt-4'><p class='alert alert-danger'>Database connection failed.</p></div>");
    }

    // --- Fetch Student Details (including Father's / Guardian's Name) ---
    $stmt_student = mysqli_prepare($con, "SELECT fname, lname, rollno, class, father_name FROM users WHERE id=? LIMIT 1");
    mysqli_stmt_bind_param($stmt_student, "i", $student_id);
    mysqli_stmt_execute($stmt_student);
    $student_q = mysqli_stmt_get_result($stmt_student);

    if (!$student_q || mysqli_num_rows($student_q) == 0):
?>
        <div class="pca-white-card">
            <div class="pca-card-header">
                <h1 class="pca-card-title">
                    <i class="fa-solid fa-file-invoice"></i> Result Report Card Generator
                </h1>
                <a href="report_card_select.php" class="btn-back-link">
                    <i class="fas fa-arrow-left me-1"></i> Back to Student List
                </a>
            </div>
            <div class="pca-card-body text-center py-5">
                <div class="mx-auto mb-3" style="width: 60px; height: 60px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-triangle-exclamation text-danger fa-xl"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">Student Not Found</h5>
                <p class="text-muted mb-4" style="font-size: 0.9rem;">The student ID requested does not exist or may have been removed.</p>
                <a href="report_card_select.php" class="btn-primary-action">
                    <i class="fa-solid fa-users"></i> Select Another Student
                </a>
            </div>
        </div>
<?php
        include('includes/footer.php');
        include('includes/scripts.php');
        exit;
    endif;

    $student = mysqli_fetch_assoc($student_q);
    $student_name = htmlspecialchars($student['fname'] . ' ' . $student['lname']);
    $student_initial = strtoupper(substr(trim($student['fname'] ?? 'S'), 0, 1));
    $guardian_name = (!empty($student['father_name']) && trim($student['father_name']) !== '')
        ? htmlspecialchars(trim($student['father_name']))
        : '—';
    $class_code = $student['class'];
    $class_name = isset($class_map[$class_code]) ? $class_map[$class_code] : "Class Code " . $class_code;
    mysqli_stmt_close($stmt_student);

    // Fetch classmates for quick switching
    $class_students = [];
    $stmt_cs = mysqli_prepare($con, "SELECT id, fname, lname, rollno FROM users WHERE class=? AND role_as=0 ORDER BY CAST(rollno AS UNSIGNED) ASC, rollno ASC");
    if ($stmt_cs) {
        mysqli_stmt_bind_param($stmt_cs, "s", $class_code);
        mysqli_stmt_execute($stmt_cs);
        $res_cs = mysqli_stmt_get_result($stmt_cs);
        while ($cs = mysqli_fetch_assoc($res_cs)) {
            $class_students[] = $cs;
        }
        mysqli_stmt_close($stmt_cs);
    }

    // Result Date is Current Date
    $result_date = date('d-m-Y');

    // --- CAPTURE SELECTED TEST TYPES (CHECKBOXES) ---
    $selected_tests = [];
    if (isset($_GET['tests']) && is_array($_GET['tests'])) {
        foreach ($_GET['tests'] as $t) {
            $t_code = intval($t);
            if (isset($all_exam_types[$t_code])) {
                $selected_tests[] = $t_code;
            }
        }
    } elseif (isset($_GET['test_code']) && intval($_GET['test_code']) > 0) {
        $t_code = intval($_GET['test_code']);
        if (isset($all_exam_types[$t_code])) {
            $selected_tests = [$t_code];
        }
    }

    // Default if not specified: select Unit 1 or active exams
    if (empty($selected_tests)) {
        if (isset($_GET['apply_filter'])) {
            $selected_tests = []; // Teacher explicitly unchecked all
        } else {
            $selected_tests = [1]; // Default first exam
        }
    }

    // Sort selected tests in canonical school order: 1, 2, 5, 3, 4, 6, 7
    $canonical_order = array_keys($all_exam_types);
    usort($selected_tests, function($a, $b) use ($canonical_order) {
        return array_search($a, $canonical_order) <=> array_search($b, $canonical_order);
    });

    // --- CAPTURE MARKS DISPLAY OPTION ---
    $allowed_modes = ['separate', 'combined', 'theory_only', 'practical_only'];
    $marks_display = isset($_GET['marks_display']) && in_array($_GET['marks_display'], $allowed_modes)
        ? $_GET['marks_display']
        : 'separate';

    $num_tests = count($selected_tests);
?>

    <!-- ==========================================
         FILTER & CONTROLS CARD (HIDDEN ON PRINT)
         ========================================== -->
    <div class="pca-white-card print-button">
        <!-- Header -->
        <div class="pca-card-header">
            <div>
                <h1 class="pca-card-title">
                    <i class="fa-solid fa-file-invoice"></i> Result Report Card Generator
                </h1>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="badge-ctx-pill">
                        <i class="fa-solid fa-graduation-cap me-1 text-primary"></i> <?= htmlspecialchars($class_name) ?>
                    </span>
                    <span class="badge-ctx-pill">
                        <i class="fa-solid fa-id-badge me-1 text-primary"></i> Roll #<?= htmlspecialchars($student['rollno']) ?>
                    </span>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <?php if ($num_tests > 0): ?>
                    <button type="button" onclick="window.print()" class="btn-print-action">
                        <i class="fa-solid fa-print"></i> Print Report Card<?= ($num_tests > 1 ? "s ($num_tests Pages)" : '') ?>
                    </button>
                <?php endif; ?>
                <a href="report_card_select.php" class="btn-back-link">
                    <i class="fa-solid fa-arrow-left"></i> Back to Student List
                </a>
            </div>
        </div>

        <div class="pca-card-body">
            <!-- Student Profile & Quick Switcher Strip -->
            <div class="student-profile-strip">
                <div class="d-flex align-items-center gap-3">
                    <div class="student-avatar-lg">
                        <?= $student_initial ?>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">
                                <?= $student_name ?>
                            </h5>
                            <span class="badge-roll-pill">#<?= htmlspecialchars($student['rollno']) ?></span>
                        </div>
                        <span class="text-muted" style="font-size: 0.82rem;">
                            <?= htmlspecialchars($class_name) ?> &bull; Guardian: <strong><?= $guardian_name ?></strong> &bull; Date: <?= $result_date ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($class_students)): ?>
                    <div class="d-flex align-items-center gap-2">
                        <label for="studentSwitcher" class="form-label-clean mb-0 text-nowrap">
                            <i class="fa-solid fa-users text-primary me-1"></i> Switch Student:
                        </label>
                        <select id="studentSwitcher" class="form-control form-control-clean" style="min-width: 220px;" onchange="switchStudent(this.value);">
                            <?php foreach ($class_students as $cs): ?>
                                <option value="<?= $cs['id'] ?>" <?= ($cs['id'] == $student_id) ? 'selected' : '' ?>>
                                    Roll #<?= htmlspecialchars($cs['rollno']) ?> - <?= htmlspecialchars($cs['fname'] . ' ' . $cs['lname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Filter Controls Form -->
            <form method="GET" action="print_report_card.php" id="reportFilterForm" class="filter-box-clean">
                <input type="hidden" name="student_id" value="<?= $student_id ?>">

                <div class="row g-3">
                    <!-- Column 1: Test Types Checkboxes -->
                    <div class="col-lg-7 col-12">
                        <label class="form-label-clean">
                            <i class="fa-solid fa-list-check text-primary me-2"></i> 1. Select Tests to Generate (Each test prints on its own clean page):
                        </label>

                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2 p-2 bg-white rounded border" style="min-height: 80px;">
                            <?php
                            foreach ($all_exam_types as $code => $name):
                                $checked = in_array($code, $selected_tests) ? 'checked' : '';
                                $is_checked_class = in_array($code, $selected_tests) ? 'is-checked' : '';
                                $theme = $test_theme_colors[$code] ?? ['badge_bg' => '#475569'];
                            ?>
                                <label class="test-checkbox-wrap <?= $is_checked_class ?>" for="test_<?= $code ?>">
                                    <input class="form-check-input test-checkbox m-0" type="checkbox" name="tests[]" id="test_<?= $code ?>" value="<?= $code ?>" <?= $checked ?>>
                                    <span class="badge shadow-sm" style="background-color: <?= $theme['badge_bg'] ?>; color: #fff; font-size: 0.8rem; padding: 4px 8px; font-weight: 600;">
                                        <?= htmlspecialchars($name) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Quick Selection Pills -->
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="quick-pill-btn" id="btnSelectAll">
                                <i class="fa-solid fa-check-double text-primary me-1"></i> All Tests
                            </button>
                            <button type="button" class="quick-pill-btn" id="btnDeselectAll">
                                <i class="fa-solid fa-xmark text-muted me-1"></i> None
                            </button>
                            <button type="button" class="quick-pill-btn" id="btnTermsOnly">
                                <i class="fa-solid fa-calendar-check text-success me-1"></i> Half Year & Final
                            </button>
                            <button type="button" class="quick-pill-btn" id="btnUnitsOnly">
                                <i class="fa-solid fa-layer-group text-info me-1"></i> Unit Tests
                            </button>
                        </div>
                    </div>

                    <!-- Column 2: Marks Display Format (Radio Buttons) -->
                    <div class="col-lg-5 col-12">
                        <label class="form-label-clean">
                            <i class="fa-solid fa-sliders text-primary me-2"></i> 2. Marks Display Format:
                        </label>
                        <div class="d-flex flex-column gap-2 p-2 bg-white rounded border" style="min-height: 80px;">
                            <?php
                            $modes_info = [
                                'separate' => ['label' => 'Separate', 'desc' => 'Theory & Practical in distinct columns'],
                                'combined' => ['label' => 'Combined', 'desc' => 'Theory + Practical combined into Max & Obtained'],
                                'theory_only' => ['label' => 'Theory Only', 'desc' => 'Shows only Theory Max & Obtained Marks'],
                                'practical_only' => ['label' => 'Practical Only', 'desc' => 'Shows only Practical Max & Obtained Marks']
                            ];

                            foreach ($modes_info as $m_key => $m_data):
                                $r_checked = ($marks_display === $m_key) ? 'checked' : '';
                                $r_checked_class = ($marks_display === $m_key) ? 'is-checked' : '';
                            ?>
                                <label class="radio-mode-wrap <?= $r_checked_class ?>" for="mode_<?= $m_key ?>">
                                    <input class="form-check-input marks-mode-radio m-0" type="radio" name="marks_display" id="mode_<?= $m_key ?>" value="<?= $m_key ?>" <?= $r_checked ?>>
                                    <span class="fw-bold text-dark" style="font-size: 0.86rem;"><?= htmlspecialchars($m_data['label']) ?></span>
                                    <span class="text-muted" style="font-size: 0.78rem;">(<?= htmlspecialchars($m_data['desc']) ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Action buttons bar -->
                    <div class="col-12 pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="submit" name="apply_filter" value="1" class="btn-primary-action">
                                <i class="fa-solid fa-arrows-rotate"></i> Apply Filter
                            </button>
                            <?php if ($num_tests > 0): ?>
                                <button type="button" onclick="window.print()" class="btn-print-action">
                                    <i class="fa-solid fa-print"></i> Print Report Card<?= ($num_tests > 1 ? "s ($num_tests Pages)" : '') ?>
                                </button>
                            <?php endif; ?>
                        </div>
                        <div>
                            <a href="report_card_select.php" class="btn-back-link">
                                <i class="fa-solid fa-arrow-left"></i> Back to Student List
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php
    // Check if at least one test is selected
    if (empty($selected_tests)):
?>
        <div class="pca-white-card print-button">
            <div class="pca-card-body text-center py-5">
                <div class="mx-auto mb-3" style="width: 58px; height: 58px; background: #fffbeb; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-triangle-exclamation text-warning fa-xl"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2">No Tests Selected</h5>
                <p class="text-muted mb-3" style="font-size: 0.9rem;">
                    Please select at least one test from the filter checkboxes above and click <strong>Apply Filter</strong> to generate the report card.
                </p>
                <button type="button" class="btn-primary-action" onclick="document.getElementById('btnSelectAll').click(); document.getElementById('reportFilterForm').submit();">
                    <i class="fa-solid fa-check-double"></i> Select All Tests & Generate
                </button>
            </div>
        </div>
<?php
        echo '</div>'; // .report-page-wrap
        include('includes/footer.php');
        include('includes/scripts.php');
        exit;
    endif;

    // ==========================================
    // PRE-FETCH SUBJECTS FOR THIS CLASS
    // ==========================================
    $class_subjects = [];
    $stmt_sub = mysqli_prepare($con, "SELECT subject FROM class_subject_info WHERE class_code=? AND status=1 ORDER BY subject ASC");
    if (!$stmt_sub) {
        $stmt_sub = mysqli_prepare($con, "SELECT subject FROM class_subject_info WHERE class_code=? ORDER BY subject ASC");
    }
    if ($stmt_sub) {
        mysqli_stmt_bind_param($stmt_sub, "s", $class_code);
        mysqli_stmt_execute($stmt_sub);
        $sub_res = mysqli_stmt_get_result($stmt_sub);
        while ($s_row = mysqli_fetch_assoc($sub_res)) {
            $class_subjects[] = trim($s_row['subject']);
        }
        mysqli_stmt_close($stmt_sub);
    }

    if (empty($class_subjects)) {
        // Fallback: check distinct subjects in student_score for this class
        $stmt_sub_fallback = mysqli_prepare($con, "SELECT DISTINCT subject FROM student_score WHERE class_code=? ORDER BY subject ASC");
        if ($stmt_sub_fallback) {
            mysqli_stmt_bind_param($stmt_sub_fallback, "s", $class_code);
            mysqli_stmt_execute($stmt_sub_fallback);
            $sub_res_fb = mysqli_stmt_get_result($stmt_sub_fallback);
            while ($s_row = mysqli_fetch_assoc($sub_res_fb)) {
                $class_subjects[] = trim($s_row['subject']);
            }
            mysqli_stmt_close($stmt_sub_fallback);
        }
    }

    // Prepared statement to fetch student mark for a given subject & test
    $stmt_mark = mysqli_prepare($con, "SELECT marks_obtained, practical_marks, total_marks, total_practical 
                                       FROM student_score 
                                       WHERE student_id=? AND TRIM(subject)=? AND test_code=? LIMIT 1");

    // ==========================================
    // MULTI-PAGE REPORT CARD GENERATION
    // Each selected test gets its OWN full page!
    // ==========================================
    echo '<div class="print-visible-area" id="report-card-print-area">';

    // Prepare official school logo as base64 data URI for instant rendering
    $logo_data_uri = '';
    $logo_file = __DIR__ . '/assets/images/path_creation_logo.png';
    if (file_exists($logo_file)) {
        $logo_data_uri = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_file));
    } else {
        $logo_data_uri = 'assets/images/path_creation_logo.png';
    }

    $total_selected = count($selected_tests);

    foreach ($selected_tests as $idx => $t_code) {
        $test_name = $all_exam_types[$t_code] ?? ("Test " . $t_code);
        $page_num = $idx + 1;

        // Fetch maximum marks for this test (Theory & Practical)
        $test_max_th = 0;
        $test_max_pr = 0;
        $stmt_max = mysqli_prepare($con, "SELECT MAX(total_marks) AS max_th, MAX(total_practical) AS max_pr FROM student_score WHERE student_id=? AND test_code=?");
        if ($stmt_max) {
            mysqli_stmt_bind_param($stmt_max, "ii", $student_id, $t_code);
            mysqli_stmt_execute($stmt_max);
            $res_max = mysqli_stmt_get_result($stmt_max);
            if ($row_max = mysqli_fetch_assoc($res_max)) {
                $test_max_th = is_numeric($row_max['max_th']) ? floatval($row_max['max_th']) : 0;
                $test_max_pr = is_numeric($row_max['max_pr']) ? floatval($row_max['max_pr']) : 0;
            }
            mysqli_stmt_close($stmt_max);
        }
        if ($test_max_th == 0 && $test_max_pr == 0) {
            // Fallback to class level max marks
            $stmt_max_cls = mysqli_prepare($con, "SELECT MAX(total_marks) AS max_th, MAX(total_practical) AS max_pr FROM student_score WHERE class_code=? AND test_code=?");
            if ($stmt_max_cls) {
                mysqli_stmt_bind_param($stmt_max_cls, "si", $class_code, $t_code);
                mysqli_stmt_execute($stmt_max_cls);
                $res_max_cls = mysqli_stmt_get_result($stmt_max_cls);
                if ($row_max_cls = mysqli_fetch_assoc($res_max_cls)) {
                    $test_max_th = is_numeric($row_max_cls['max_th']) ? floatval($row_max_cls['max_th']) : 0;
                    $test_max_pr = is_numeric($row_max_cls['max_pr']) ? floatval($row_max_cls['max_pr']) : 0;
                }
                mysqli_stmt_close($stmt_max_cls);
            }
        }
        if ($test_max_th == 0 && $test_max_pr == 0) {
            $test_max_th = 80;
            $test_max_pr = 20;
        }
        $test_max_total = $test_max_th + $test_max_pr;

        // Calculate Rank in Class for this test
        $rank_data = get_student_rank_in_class($con, $student_id, $class_code, $t_code);

        // On screen: Show page badge if multiple tests selected
        if ($total_selected > 1) {
            echo '<div class="print-button text-center mb-3">';
            echo '<span class="badge shadow-sm" style="background: #1e293b; color: #fff; font-size: 0.85rem; padding: 0.45rem 1rem; border-radius: 20px; font-weight: 600;">
                    <i class="fa-solid fa-file-lines me-1 text-primary"></i> Page ' . $page_num . ' of ' . $total_selected . ' &mdash; ' . htmlspecialchars($test_name) . '
                  </span>';
            echo '</div>';
        }

        // ==========================================
        // SINGLE PAGE CONTAINER (EXACT REPORT CARD FORMAT)
        // ==========================================
        echo '<div class="report-card-page position-relative bg-white" style="border: none !important; padding: 24px 28px; margin: 0 auto 2rem auto; max-width: 860px; min-height: 1050px; display: flex; flex-direction: column; justify-content: flex-start; overflow: hidden !important; page-break-after: always; break-after: page;">';

        // --- TOP-LEFT DECORATIVE GEOMETRIC CORNER (EXACT MIRROR OF TOP-RIGHT) ---
        echo '<svg class="top-corner-svg-left" viewBox="0 0 100 75" style="position: absolute; top: 0; left: 0; width: 180px; height: 135px; pointer-events: none; z-index: 1;">
            <polygon points="62,0 0,0 0,58" fill="#111827" />
            <path d="M 98,0 L 68,0 L 2,66 L 7,70 L 29,43 Q 40,43 52,42 Q 57,40 62,36 Z" fill="#d9232d" />
        </svg>';

        // --- TOP-RIGHT DECORATIVE GEOMETRIC CORNER ---
        echo '<svg class="top-corner-svg-right" viewBox="0 0 100 75" style="position: absolute; top: 0; right: 0; width: 180px; height: 135px; pointer-events: none; z-index: 1;">
            <polygon points="38,0 100,0 100,58" fill="#111827" />
            <path d="M 2,0 L 32,0 L 98,66 L 93,70 L 71,43 Q 60,43 48,42 Q 43,40 38,36 Z" fill="#d9232d" />
        </svg>';

        // --- HEADER: LOGO & SCHOOL BRANDING ---
        echo '<div class="report-header-wrap d-flex align-items-center mb-3" style="position: relative; z-index: 5;">
            <div class="header-logo-box" style="height: 60px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <img src="' . $logo_data_uri . '" alt="Path Creation Academy" style="height: 56px; width: auto; object-fit: contain; display: block;">
            </div>
            <div class="header-divider" style="width: 2px; height: 50px; background-color: #9ca3af; margin: 0 16px; flex-shrink: 0;"></div>
            <div class="brand-text-block" style="white-space: nowrap;">
                <div class="brand-title" style="font-size: 1.85rem; font-weight: 800; color: #111827; line-height: 1.1; letter-spacing: -0.5px;">Path Creation <span style="color: #d9232d;">Academy</span></div>
                <div class="brand-subtitle" style="font-size: 0.76rem; font-weight: 700; color: #4b5563; letter-spacing: 3.5px; text-transform: uppercase; margin-top: 4px;">LEARN &bull; BUILD &bull; GROW</div>
            </div>
        </div>';

        // --- EMBLEM & TITLE: LAUREL WREATH, CAP, REPORT CARD ---
        echo '<div class="report-title-wrap text-center my-3" style="position: relative; z-index: 2;">';
        echo '<div class="d-inline-flex align-items-center justify-content-center">';
        
        // Left laurel leaf branch
        echo '<svg width="48" height="68" viewBox="0 0 50 75" style="flex-shrink: 0;">
            <path d="M 42,72 Q 18,48 24,6" stroke="#d9232d" stroke-width="2.2" fill="none" stroke-linecap="round" />
            <path d="M 24,6 C 20,2 14,7 18,12 C 22,14 26,10 24,6 Z" fill="#d9232d" />
            <path d="M 28,14 C 36,9 41,17 35,22 C 30,24 27,19 28,14 Z" fill="#d9232d" />
            <path d="M 21,19 C 12,16 9,25 15,29 C 20,30 23,25 21,19 Z" fill="#d9232d" />
            <path d="M 26,30 C 35,26 39,35 33,40 C 28,41 24,35 26,30 Z" fill="#d9232d" />
            <path d="M 18,35 C 8,34 7,43 14,47 C 19,47 22,41 18,35 Z" fill="#d9232d" />
            <path d="M 28,47 C 37,44 40,53 34,57 C 29,58 25,53 28,47 Z" fill="#d9232d" />
            <path d="M 19,52 C 10,53 10,61 18,64 C 22,63 24,57 19,52 Z" fill="#d9232d" />
            <path d="M 33,62 C 42,61 43,70 37,74 C 32,74 30,68 33,62 Z" fill="#d9232d" />
            <path d="M 25,67 C 18,69 20,76 28,77 C 32,75 32,69 25,67 Z" fill="#d9232d" />
        </svg>';

        // Center: Red Graduation Cap sitting on top of Result Report Card
        echo '<div class="d-flex flex-column align-items-center mx-3">';
        echo '<svg width="44" height="30" viewBox="0 0 100 80" style="margin-bottom: 3px;">
            <polygon points="50,6 94,28 50,50 6,28" fill="#d9232d" />
            <path d="M 25,38 L 25,56 C 25,66 75,66 75,56 L 75,38 Q 50,50 25,38 Z" fill="#d9232d" />
            <circle cx="50" cy="28" r="2.5" fill="#d9232d" />
            <path d="M 50,28 L 18,35 L 14,56" stroke="#d9232d" stroke-width="2.5" fill="none" stroke-linecap="round" />
            <polygon points="11,56 17,56 16,68 12,68" fill="#d9232d" />
        </svg>';
        echo '<h2 style="font-size: 2.15rem; font-weight: 800; color: #111827; margin: 0; letter-spacing: 0.5px; line-height: 1.15;">Result Report Card</h2>';
        echo '</div>';

        // Right laurel leaf branch (mirrored)
        echo '<svg width="48" height="68" viewBox="0 0 50 75" style="flex-shrink: 0; transform: scaleX(-1);">
            <path d="M 42,72 Q 18,48 24,6" stroke="#d9232d" stroke-width="2.2" fill="none" stroke-linecap="round" />
            <path d="M 24,6 C 20,2 14,7 18,12 C 22,14 26,10 24,6 Z" fill="#d9232d" />
            <path d="M 28,14 C 36,9 41,17 35,22 C 30,24 27,19 28,14 Z" fill="#d9232d" />
            <path d="M 21,19 C 12,16 9,25 15,29 C 20,30 23,25 21,19 Z" fill="#d9232d" />
            <path d="M 26,30 C 35,26 39,35 33,40 C 28,41 24,35 26,30 Z" fill="#d9232d" />
            <path d="M 18,35 C 8,34 7,43 14,47 C 19,47 22,41 18,35 Z" fill="#d9232d" />
            <path d="M 28,47 C 37,44 40,53 34,57 C 29,58 25,53 28,47 Z" fill="#d9232d" />
            <path d="M 19,52 C 10,53 10,61 18,64 C 22,63 24,57 19,52 Z" fill="#d9232d" />
            <path d="M 33,62 C 42,61 43,70 37,74 C 32,74 30,68 33,62 Z" fill="#d9232d" />
            <path d="M 25,67 C 18,69 20,76 28,77 C 32,75 32,69 25,67 Z" fill="#d9232d" />
        </svg>';
        echo '</div>';

        // Red horizontal lines flanking test name
        echo '<div class="d-flex align-items-center justify-content-center mt-2">';
        echo '<div class="title-flank-line" style="height: 2px; width: 75px; background: #d9232d; border-radius: 1px;"></div>';
        echo '<span class="test-name-text mx-3 fw-bold" style="color: #1f2937; font-size: 1.05rem; letter-spacing: 0.5px;">' . htmlspecialchars($test_name) . '</span>';
        echo '<div class="title-flank-line" style="height: 2px; width: 75px; background: #d9232d; border-radius: 1px;"></div>';
        echo '</div>';
        echo '</div>';

        // --- STUDENT DETAILS BOX (2 COLUMNS) ---
        echo '<div class="student-info-card" style="border: 1px solid #d1d5db; border-radius: 12px; background: #ffffff; padding: 10px 18px; margin-bottom: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); position: relative; z-index: 2;">';
        echo '<div class="row align-items-center">';
        // Left Column
        echo '<div class="col-md-6 col-12 info-col-left">';
        echo '<div class="info-row d-flex align-items-center mb-2" style="font-size: 0.92rem;"><span class="info-label" style="width: 140px; font-weight: 700; color: #111827;">Student Name</span><span class="info-colon me-2 fw-bold">:</span><span class="info-value" style="font-weight: 600; color: #1f2937;">' . $student_name . '</span></div>';
        echo '<div class="info-row d-flex align-items-center mb-2" style="font-size: 0.92rem;"><span class="info-label" style="width: 140px; font-weight: 700; color: #111827;">Class</span><span class="info-colon me-2 fw-bold">:</span><span class="info-value" style="font-weight: 600; color: #1f2937;">' . htmlspecialchars($class_name) . '</span></div>';
        echo '<div class="info-row d-flex align-items-center mb-md-0 mb-2" style="font-size: 0.92rem;"><span class="info-label" style="width: 140px; font-weight: 700; color: #111827;">Guardian Name</span><span class="info-colon me-2 fw-bold">:</span><span class="info-value" style="font-weight: 600; color: #1f2937;">' . $guardian_name . '</span></div>';
        echo '</div>';
        // Right Column
        echo '<div class="col-md-6 col-12 info-col-right ps-md-4" style="border-left: 1px solid #e5e7eb;">';
        echo '<div class="info-row d-flex align-items-center mb-2" style="font-size: 0.92rem;"><span class="info-label" style="width: 130px; font-weight: 700; color: #111827;">Roll No.</span><span class="info-colon me-2 fw-bold">:</span><span class="info-value" style="font-weight: 600; color: #1f2937;">' . htmlspecialchars($student['rollno']) . '</span></div>';
        echo '<div class="info-row d-flex align-items-center" style="font-size: 0.92rem;"><span class="info-label" style="width: 130px; font-weight: 700; color: #111827;">Result Date</span><span class="info-colon me-2 fw-bold">:</span><span class="info-value" style="font-weight: 600; color: #1f2937;">' . $result_date . '</span></div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // --- MARKS TABLE ---
        echo '<div class="mobile-table-hint d-md-none text-muted mb-1 text-center" style="font-size: 0.74rem;"><i class="fa-solid fa-arrows-left-right me-1 text-primary"></i> Swipe table horizontally to view all columns</div>';
        echo '<div class="w-100 report-table-wrap" style="position: relative; z-index: 2; margin-top: 4px; margin-bottom: 8px;">';
        echo '<table class="table table-bordered mb-0 student-report-table" style="border: 1.5px solid #1e293b; font-size: 0.86rem; width: 100%; border-collapse: collapse;">';
        echo '<thead>';
        echo '<tr class="text-center align-middle" style="background-color: #1e293b; color: #ffffff;">';
        echo '<th style="width: 6%; background-color: #1e293b; color: #ffffff; padding: 7px 2px; white-space: nowrap !important;">S. No.</th>';

        if ($marks_display === 'separate') {
            echo '<th style="width: 26%; background-color: #1e293b; color: #ffffff; text-align: left; padding: 8px 6px;">Subject</th>';
            echo '<th style="width: 13%; background-color: #1e293b; color: #ffffff; padding: 7px 2px; white-space: nowrap !important;"><span style="white-space: nowrap !important; display: inline-block;">Theory</span><br><span style="font-size: 0.70rem; font-weight: 500; opacity: 0.9; white-space: nowrap !important;">(Max: ' . ($test_max_th + 0) . ')</span></th>';
            echo '<th style="width: 16%; background-color: #1e293b; color: #ffffff; padding: 7px 2px; white-space: nowrap !important;"><span style="white-space: nowrap !important; display: inline-block;">Practical</span><br><span style="font-size: 0.70rem; font-weight: 500; opacity: 0.9; white-space: nowrap !important;">(Max: ' . ($test_max_pr + 0) . ')</span></th>';
            echo '<th style="width: 13%; background-color: #1e293b; color: #ffffff; padding: 7px 2px; white-space: nowrap !important;"><span style="white-space: nowrap !important; display: inline-block;">Total</span><br><span style="font-size: 0.70rem; font-weight: 500; opacity: 0.9; white-space: nowrap !important;">(' . ($test_max_total + 0) . ')</span></th>';
            echo '<th class="col-percentage" style="width: 16%; background-color: #1e293b; color: #ffffff; padding: 8px 2px; white-space: nowrap !important;">Percentage</th>';
            echo '<th style="width: 10%; background-color: #1e293b; color: #ffffff; padding: 8px 2px; white-space: nowrap !important;">Grade</th>';
        } elseif ($marks_display === 'theory_only') {
            echo '<th style="width: 33%; background-color: #1e293b; color: #ffffff; text-align: left; padding: 8px 6px;">Subject</th>';
            echo '<th style="width: 18%; background-color: #1e293b; color: #ffffff; padding: 7px 3px; white-space: nowrap !important;"><span style="white-space: nowrap !important; display: inline-block;">Maximum</span><br><span style="font-size: 0.70rem; font-weight: 500; opacity: 0.9; white-space: nowrap !important;">(' . ($test_max_th + 0) . ')</span></th>';
            echo '<th style="width: 18%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Obtained</th>';
            echo '<th class="col-percentage" style="width: 14%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Percentage</th>';
            echo '<th style="width: 10%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Grade</th>';
        } elseif ($marks_display === 'practical_only') {
            echo '<th style="width: 33%; background-color: #1e293b; color: #ffffff; text-align: left; padding: 8px 6px;">Subject</th>';
            echo '<th style="width: 18%; background-color: #1e293b; color: #ffffff; padding: 7px 3px; white-space: nowrap !important;"><span style="white-space: nowrap !important; display: inline-block;">Maximum</span><br><span style="font-size: 0.70rem; font-weight: 500; opacity: 0.9; white-space: nowrap !important;">(' . ($test_max_pr + 0) . ')</span></th>';
            echo '<th style="width: 18%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Obtained</th>';
            echo '<th class="col-percentage" style="width: 14%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Percentage</th>';
            echo '<th style="width: 10%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Grade</th>';
        } else {
            // Combined mode
            $disp_combined_max = ($test_max_total > 0) ? ($test_max_total + 0) : 100;
            echo '<th style="width: 33%; background-color: #1e293b; color: #ffffff; text-align: left; padding: 8px 6px;">Subject</th>';
            echo '<th style="width: 18%; background-color: #1e293b; color: #ffffff; padding: 7px 3px; white-space: nowrap !important;"><span style="white-space: nowrap !important; display: inline-block;">Maximum</span><br><span style="font-size: 0.70rem; font-weight: 500; opacity: 0.9; white-space: nowrap !important;">(' . $disp_combined_max . ')</span></th>';
            echo '<th style="width: 18%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Obtained</th>';
            echo '<th class="col-percentage" style="width: 14%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Percentage</th>';
            echo '<th style="width: 10%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Grade</th>';
        }
        echo '</tr>';
        echo '</thead><tbody>';

        // Column accumulators for this test
        $col_th_obt = 0;
        $col_th_max = 0;
        $col_pr_obt = 0;
        $col_pr_max = 0;
        $col_tot_obt = 0;
        $col_tot_max = 0;

        $subject_counter = 1;
        $failed_subject_count = 0;

        foreach ($class_subjects as $subject_name) {
            $m_th_obt = 0;
            $m_th_max = 0;
            $m_pr_obt = 0;
            $m_pr_max = 0;
            $has_mark_record = false;

            if ($stmt_mark) {
                mysqli_stmt_bind_param($stmt_mark, "isi", $student_id, $subject_name, $t_code);
                mysqli_stmt_execute($stmt_mark);
                $res_m = mysqli_stmt_get_result($stmt_mark);
                if ($res_m && mysqli_num_rows($res_m) > 0) {
                    $has_mark_record = true;
                    $m_row = mysqli_fetch_assoc($res_m);
                    $m_th_obt = is_numeric($m_row['marks_obtained']) ? floatval($m_row['marks_obtained']) : 0;
                    $m_th_max = is_numeric($m_row['total_marks']) ? floatval($m_row['total_marks']) : 0;
                    $m_pr_obt = is_numeric($m_row['practical_marks']) ? floatval($m_row['practical_marks']) : 0;
                    $m_pr_max = is_numeric($m_row['total_practical']) ? floatval($m_row['total_practical']) : 0;
                }
            }

            // Fallback max marks if not recorded for this specific row
            if ($m_th_max == 0 && $test_max_th > 0) $m_th_max = $test_max_th;
            if ($m_pr_max == 0 && $test_max_pr > 0 && $m_pr_obt > 0) $m_pr_max = $test_max_pr;

            // Compute row values based on marks_display mode
            $row_item_obt = 0;
            $row_item_max = 0;

            if ($marks_display === 'theory_only') {
                $row_item_obt = $m_th_obt;
                $row_item_max = $m_th_max;
            } elseif ($marks_display === 'practical_only') {
                $row_item_obt = $m_pr_obt;
                $row_item_max = $m_pr_max;
            } else {
                $row_item_obt = $m_th_obt + $m_pr_obt;
                $row_item_max = $m_th_max + $m_pr_max;
            }

            $row_pct = ($row_item_max > 0) ? round(($row_item_obt / $row_item_max) * 100, 2) : 0;
            $row_grade = get_grade($row_pct);
            $row_is_fail = ($row_pct < PASS_PERCENTAGE * 100 && $row_item_max > 0);
            if ($row_is_fail) {
                $failed_subject_count++;
            }
            $row_status_lbl = $row_is_fail ? 'Compartment' : 'Pass';

            // Accumulate
            $col_th_obt += $m_th_obt;
            $col_th_max += $m_th_max;
            $col_pr_obt += $m_pr_obt;
            $col_pr_max += $m_pr_max;
            $col_tot_obt += $row_item_obt;
            $col_tot_max += $row_item_max;

            // Row styling: Entire row / all columns red for fail/back subject; clean white for pass
            if ($row_is_fail) {
                $row_cls = 'align-middle failed-row';
                $row_style = 'background-color: #fee2e2 !important; color: #b91c1c !important;';
                $cell_style = 'background-color: #fee2e2 !important; color: #b91c1c !important; border-color: #fca5a5 !important;';

                $subj_html = '<span class="fail-subject-text" style="color: #b91c1c; font-weight: 700;">' . htmlspecialchars($subject_name) . '</span>';
                $obt_th_html = '<span class="fail-mark-text" style="color: #b91c1c; font-weight: 800;">' . ($m_th_obt + 0) . '</span>';
                $obt_pr_html = ($m_pr_max > 0 || $m_pr_obt > 0) ? '<span class="fail-mark-text" style="color: #b91c1c; font-weight: 800;">' . ($m_pr_obt + 0) . '</span>' : '&mdash;';
                $obt_tot_html = '<span class="fail-mark-text" style="color: #b91c1c; font-weight: 800;">' . ($row_item_obt + 0) . '</span>';
                $pct_html = '<span class="fail-mark-text" style="color: #b91c1c; font-weight: 800; white-space: nowrap;">' . number_format($row_pct, 2) . ' %</span>';
                $grade_html = '<span class="badge fail-grade-badge" style="background-color: #dc2626; color: #ffffff; font-weight: 800; font-size: 0.82rem; padding: 2px 8px; border-radius: 4px;">' . $row_grade . '</span>';
            } else {
                $row_cls = 'align-middle passed-row';
                $row_style = 'background-color: #ffffff; color: #1e293b;';
                $cell_style = 'background-color: #ffffff; color: #1e293b; border-color: #cbd5e1;';

                $subj_html = '<span class="fw-semibold" style="color: #1e293b;">' . htmlspecialchars($subject_name) . '</span>';
                $obt_th_html = '<span style="color: #1e293b; font-weight: 600;">' . ($m_th_obt + 0) . '</span>';
                $obt_pr_html = ($m_pr_max > 0 || $m_pr_obt > 0) ? '<span style="color: #1e293b; font-weight: 600;">' . ($m_pr_obt + 0) . '</span>' : '&mdash;';
                $obt_tot_html = '<span style="color: #1e293b; font-weight: 700;">' . ($row_item_obt + 0) . '</span>';
                $pct_html = '<span style="color: #1e293b; font-weight: 600; white-space: nowrap;">' . number_format($row_pct, 2) . ' %</span>';
                $grade_html = '<span style="color: #1e293b; font-weight: 700;">' . $row_grade . '</span>';
            }

            echo '<tr class="' . $row_cls . '" style="' . $row_style . '">';
            echo '<td class="text-center" style="' . $cell_style . ' font-weight: 700;">' . $subject_counter . '</td>';
            echo '<td class="ps-2" style="' . $cell_style . '">' . $subj_html . '</td>';

            if ($marks_display === 'separate') {
                echo '<td class="text-center" style="' . $cell_style . '">' . $obt_th_html . '</td>';
                echo '<td class="text-center" style="' . $cell_style . '">' . $obt_pr_html . '</td>';
                echo '<td class="text-center" style="' . $cell_style . '">' . $obt_tot_html . '</td>';
                echo '<td class="text-center col-percentage" style="' . $cell_style . ' white-space: nowrap !important;">' . $pct_html . '</td>';
                echo '<td class="text-center" style="' . $cell_style . '">' . $grade_html . '</td>';
            } elseif ($marks_display === 'theory_only') {
                echo '<td class="text-center" style="' . $cell_style . '">' . ($m_th_max + 0) . '</td>';
                echo '<td class="text-center" style="' . $cell_style . '">' . $obt_th_html . '</td>';
                echo '<td class="text-center col-percentage" style="' . $cell_style . ' white-space: nowrap !important;">' . $pct_html . '</td>';
                echo '<td class="text-center" style="' . $cell_style . '">' . $grade_html . '</td>';
            } elseif ($marks_display === 'practical_only') {
                $pr_max_cell = ($m_pr_max > 0 || $m_pr_obt > 0) ? ($m_pr_max + 0) : '&mdash;';
                echo '<td class="text-center" style="' . $cell_style . '">' . $pr_max_cell . '</td>';
                echo '<td class="text-center" style="' . $cell_style . '">' . $obt_pr_html . '</td>';
                echo '<td class="text-center col-percentage" style="' . $cell_style . ' white-space: nowrap !important;">' . $pct_html . '</td>';
                echo '<td class="text-center" style="' . $cell_style . '">' . $grade_html . '</td>';
            } else {
                echo '<td class="text-center" style="' . $cell_style . '">' . ($row_item_max + 0) . '</td>';
                echo '<td class="text-center" style="' . $cell_style . '">' . $obt_tot_html . '</td>';
                echo '<td class="text-center col-percentage" style="' . $cell_style . ' white-space: nowrap !important;">' . $pct_html . '</td>';
                echo '<td class="text-center" style="' . $cell_style . '">' . $grade_html . '</td>';
            }

            echo '</tr>';
            $subject_counter++;
        }

        echo '</tbody>';

        // --- TABLE FOOTER: TOTAL ROW ---
        $overall_percentage = ($col_tot_max > 0) ? round(($col_tot_obt / $col_tot_max) * 100, 2) : 0;
        $overall_grade = get_grade($overall_percentage);
        $overall_is_fail = ($overall_percentage < PASS_PERCENTAGE * 100 && $col_tot_max > 0);
        $is_student_failed = ($overall_is_fail || $failed_subject_count > 0);
        $overall_final_status_text = $is_student_failed ? "Compartment" : "Pass";

        echo '<tfoot>';
        echo '<tr class="text-center align-middle" style="background-color: #f8fafc; color: #111827; font-weight: 800; border-top: 2px solid #1e293b;">';
        echo '<td colspan="2" class="text-center" style="font-weight: 800; font-size: 0.95rem; color: #111827;">Total</td>';

        if ($marks_display === 'separate') {
            echo '<td style="font-weight: 800; color: #111827;">' . ($col_th_obt + 0) . '</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . ($col_pr_obt > 0 ? ($col_pr_obt + 0) : '&mdash;') . '</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . ($col_tot_obt + 0) . '</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . number_format($overall_percentage, 2) . ' %</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . $overall_grade . '</td>';
        } elseif ($marks_display === 'theory_only') {
            echo '<td style="font-weight: 800; color: #111827;">' . ($col_th_max + 0) . '</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . ($col_th_obt + 0) . '</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . number_format($overall_percentage, 2) . ' %</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . $overall_grade . '</td>';
        } elseif ($marks_display === 'practical_only') {
            echo '<td style="font-weight: 800; color: #111827;">' . ($col_pr_max + 0) . '</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . ($col_pr_obt > 0 ? ($col_pr_obt + 0) : '&mdash;') . '</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . number_format($overall_percentage, 2) . ' %</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . $overall_grade . '</td>';
        } else {
            echo '<td style="font-weight: 800; color: #111827;">' . ($col_tot_max + 0) . '</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . ($col_tot_obt + 0) . '</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . number_format($overall_percentage, 2) . ' %</td>';
            echo '<td style="font-weight: 800; color: #111827;">' . $overall_grade . '</td>';
        }

        echo '</tr>';
        echo '</tfoot>';
        echo '</table>';
        echo '</div>'; // report-table-wrap

        // --- 4 SUMMARY CARDS ---
        echo '<div class="summary-cards-row row g-3 mt-2 mb-3 justify-content-between" style="position: relative; z-index: 2;">';

        // 1. Rank in Class
        echo '<div class="col-md-3 col-6">';
        echo '<div style="border: 1px solid #d1d5db; border-radius: 12px; background: #ffffff; padding: 14px 10px; text-align: center; height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
        echo '<div class="mb-1"><i class="fas fa-trophy" style="color: #d9232d; font-size: 1.6rem;"></i></div>';
        echo '<div class="summary-title" style="font-size: 0.82rem; font-weight: 700; color: #1f2937; margin-bottom: 6px;">Rank in Class</div>';
        echo '<div class="summary-val" style="font-size: 1.45rem; font-weight: 800; color: #d9232d; letter-spacing: -0.5px;">' . $rank_data['display'] . '</div>';
        echo '</div>';
        echo '</div>';

        // 2. Overall Percentage
        echo '<div class="col-md-3 col-6">';
        echo '<div style="border: 1px solid #d1d5db; border-radius: 12px; background: #ffffff; padding: 14px 10px; text-align: center; height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
        echo '<div class="mb-1"><i class="fas fa-chart-line" style="color: #d9232d; font-size: 1.6rem;"></i></div>';
        echo '<div class="summary-title" style="font-size: 0.82rem; font-weight: 700; color: #1f2937; margin-bottom: 2px;">Overall Percentage</div>';
        echo '<div class="summary-val" style="font-size: 1.45rem; font-weight: 800; color: #d9232d; letter-spacing: -0.5px;">' . number_format($overall_percentage, 2) . '%</div>';
        echo '<div class="summary-sub" style="font-size: 0.78rem; font-weight: 600; color: #4b5563;">(' . ($col_tot_obt + 0) . ' / ' . ($col_tot_max + 0) . ')</div>';
        echo '</div>';
        echo '</div>';

        // 3. Grade
        echo '<div class="col-md-3 col-6">';
        echo '<div style="border: 1px solid #d1d5db; border-radius: 12px; background: #ffffff; padding: 14px 10px; text-align: center; height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
        echo '<div class="mb-1"><i class="fas fa-award" style="color: #d9232d; font-size: 1.6rem;"></i></div>';
        echo '<div class="summary-title" style="font-size: 0.82rem; font-weight: 700; color: #1f2937; margin-bottom: 6px;">Grade</div>';
        echo '<div class="summary-val" style="font-size: 1.45rem; font-weight: 800; color: #d9232d; letter-spacing: -0.5px;">' . $overall_grade . '</div>';
        echo '</div>';
        echo '</div>';

        // 4. Result
        $res_badge_bg = $is_student_failed ? '#fee2e2' : '#dcfce7';
        $res_badge_color = $is_student_failed ? '#dc2626' : '#16a34a';
        $res_icon = $is_student_failed ? 'fa-triangle-exclamation' : 'fa-circle-check';
        $res_icon_color = $is_student_failed ? '#dc2626' : '#16a34a';
        echo '<div class="col-md-3 col-6">';
        echo '<div style="border: 1px solid #d1d5db; border-radius: 12px; background: #ffffff; padding: 14px 10px; text-align: center; height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">';
        echo '<div>';
        echo '<div class="mb-1"><i class="fa-solid ' . $res_icon . '" style="color: ' . $res_icon_color . '; font-size: 1.6rem;"></i></div>';
        echo '<div class="summary-title" style="font-size: 0.82rem; font-weight: 700; color: #1f2937; margin-bottom: 6px;">Result</div>';
        echo '</div>';
        echo '<div>';
        echo '<span class="summary-badge" style="background-color: ' . $res_badge_bg . '; color: ' . $res_badge_color . '; padding: 5px 14px; border-radius: 8px; font-weight: 800; font-size: 1.15rem; display: inline-block;">' . $overall_final_status_text . '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '</div>'; // summary cards row

        // --- SIGNATURES SECTION ---
        echo '<div class="signatures-row row align-items-center mb-2 pt-2" style="position: relative; z-index: 2; margin-top: auto !important;">';
        echo '<div class="col-5 text-center">';
        echo '<div style="border-top: 2px solid #222; width: 75%; margin: 0 auto 8px auto;"></div>';
        echo '<strong style="font-size: 0.95rem; color: #111;">Parent\'s Signature</strong>';
        echo '</div>';
        echo '<div class="col-2 text-center">';
        echo '<div style="width: 2px; height: 45px; background-color: #d9232d; margin: 0 auto;"></div>';
        echo '</div>';
        echo '<div class="col-5 text-center">';
        echo '<div style="border-top: 2px solid #222; width: 75%; margin: 0 auto 8px auto;"></div>';
        echo '<strong style="font-size: 0.95rem; color: #111;">Teacher\'s Signature</strong>';
        echo '</div>';
        echo '</div>';

        // --- BOTTOM-LEFT DECORATIVE CURVE ---
        echo '<svg class="bottom-corner-svg-left" viewBox="0 0 200 120" style="position: absolute; bottom: 0; left: 0; width: 160px; height: 100px; pointer-events: none; z-index: 1;">
            <path d="M0,20 Q40,110 150,120 L0,120 Z" fill="#111827" />
            <path d="M0,45 Q50,115 170,120 L0,120 Z" fill="#d9232d" />
            <path d="M0,70 Q70,120 190,120 L0,120 Z" fill="#ff2a3a" opacity="0.9" />
        </svg>';

        // --- BOTTOM-RIGHT DECORATIVE CURVE ---
        echo '<svg class="bottom-corner-svg-right" viewBox="0 0 200 120" style="position: absolute; bottom: 0; right: 0; width: 160px; height: 100px; pointer-events: none; z-index: 1;">
            <path d="M200,20 Q160,110 50,120 L200,120 Z" fill="#111827" />
            <path d="M200,45 Q150,115 30,120 L200,120 Z" fill="#d9232d" />
            <path d="M200,70 Q130,120 10,120 L200,120 Z" fill="#ff2a3a" opacity="0.9" />
        </svg>';

        echo '</div>'; // .report-card-page
    }

    if ($stmt_mark) {
        mysqli_stmt_close($stmt_mark);
    }

    echo '</div>'; // #report-card-print-area

?>
    <script>
        function switchStudent(newStudentId) {
            if (!newStudentId) return;
            var form = document.getElementById('reportFilterForm');
            if (form) {
                form.querySelector('input[name="student_id"]').value = newStudentId;
                form.submit();
            } else {
                location.href = 'print_report_card.php?student_id=' + encodeURIComponent(newStudentId);
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            var btnSelectAll = document.getElementById("btnSelectAll");
            var btnDeselectAll = document.getElementById("btnDeselectAll");
            var btnTermsOnly = document.getElementById("btnTermsOnly");
            var btnUnitsOnly = document.getElementById("btnUnitsOnly");
            var checkboxes = document.querySelectorAll(".test-checkbox");

            function updateCheckboxHighlight() {
                checkboxes.forEach(function(cb) {
                    var parent = cb.closest('.test-checkbox-wrap');
                    if (parent) {
                        if (cb.checked) {
                            parent.classList.add('is-checked');
                        } else {
                            parent.classList.remove('is-checked');
                        }
                    }
                });
            }

            checkboxes.forEach(function(cb) {
                cb.addEventListener('change', updateCheckboxHighlight);
            });

            if (btnSelectAll) {
                btnSelectAll.addEventListener("click", function() {
                    checkboxes.forEach(function(cb) { cb.checked = true; });
                    updateCheckboxHighlight();
                });
            }
            if (btnDeselectAll) {
                btnDeselectAll.addEventListener("click", function() {
                    checkboxes.forEach(function(cb) { cb.checked = false; });
                    updateCheckboxHighlight();
                });
            }
            if (btnTermsOnly) {
                btnTermsOnly.addEventListener("click", function() {
                    checkboxes.forEach(function(cb) {
                        cb.checked = (cb.value === "5" || cb.value === "6");
                    });
                    updateCheckboxHighlight();
                });
            }
            if (btnUnitsOnly) {
                btnUnitsOnly.addEventListener("click", function() {
                    checkboxes.forEach(function(cb) {
                        cb.checked = (cb.value === "1" || cb.value === "2" || cb.value === "3" || cb.value === "4");
                    });
                    updateCheckboxHighlight();
                });
            }

            // Radio mode highlight
            var radios = document.querySelectorAll('.marks-mode-radio');
            radios.forEach(function(r) {
                r.addEventListener('change', function() {
                    document.querySelectorAll('.radio-mode-wrap').forEach(function(rw) {
                        rw.classList.remove('is-checked');
                    });
                    var p = this.closest('.radio-mode-wrap');
                    if (p) p.classList.add('is-checked');
                });
            });

            var form = document.getElementById("reportFilterForm");
            if (form) {
                form.addEventListener("submit", function(e) {
                    var anyChecked = Array.from(checkboxes).some(function(cb) { return cb.checked; });
                    if (!anyChecked) {
                        e.preventDefault();
                        alert("Please select at least one test type to generate the report card.");
                        return false;
                    }
                });
            }
        });
    </script>

<?php
else:
    // If no student ID was provided in GET
?>
    <div class="pca-white-card">
        <div class="pca-card-header">
            <h1 class="pca-card-title">
                <i class="fa-solid fa-file-invoice"></i> Result Report Card Generator
            </h1>
            <a href="report_card_select.php" class="btn-back-link">
                <i class="fas fa-arrow-left me-1"></i> Student List
            </a>
        </div>
        <div class="pca-card-body text-center py-5">
            <div class="mx-auto mb-3" style="width: 60px; height: 60px; background: #eff6ff; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-user-graduate text-primary fa-xl"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">No Student Selected</h5>
            <p class="text-muted mb-4" style="font-size: 0.9rem;">Please select a student from the report card student list to preview and print their official report card.</p>
            <a href="report_card_select.php" class="btn-primary-action">
                <i class="fa-solid fa-list-check"></i> Select Student
            </a>
        </div>
    </div>
<?php
endif;
?>

</div> <!-- .report-page-wrap -->

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
