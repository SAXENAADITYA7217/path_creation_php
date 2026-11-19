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

// Grading scale matching the sample report card
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
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
}

/* Filter Box */
.filter-box-clean {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.25rem;
}

.form-label-clean {
    font-size: 0.82rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 0.4rem;
    display: block;
}

.form-control-clean {
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 0.55rem 0.9rem;
    font-size: 0.9rem;
    color: #1e293b;
    background: #ffffff;
    transition: all 0.2s ease;
}

.form-control-clean:focus {
    border-color: #5e48eb;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12);
    outline: none;
}

/* Buttons */
.btn-primary-action, .btn-print-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 0.58rem 1.25rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(94, 72, 235, 0.25);
    transition: all 0.2s ease;
    cursor: pointer;
    text-decoration: none !important;
}

.btn-primary-action:hover, .btn-print-action:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    box-shadow: 0 4px 14px rgba(94, 72, 235, 0.35);
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

/* Student Table in Selection View */
.pca-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.pca-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.85rem 1rem;
    border-bottom: 1.5px solid #e2e8f0;
}

.pca-table tbody td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 0.88rem;
}

.pca-table tbody tr:hover {
    background: #f8fafc;
}

.student-avatar-sm {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: linear-gradient(135deg, #5e48eb 0%, #4338ca 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.88rem;
    font-weight: 700;
    flex-shrink: 0;
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
    overflow: visible;
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
    table-layout: fixed;
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
    #layoutSidenav_nav,
    .sb-sidenav,
    .sb-topnav,
    footer,
    .print-button,
    .btn-print-action,
    .btn-back-link,
    .pca-white-card,
    .student-page-divider,
    .mobile-table-hint {
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

    #layoutSidenav,
    #layoutSidenav_content,
    main,
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
        max-width: 65% !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        text-align: left !important;
        position: relative !important;
        z-index: 5 !important;
    }
    .report-header-wrap .header-divider {
        display: block !important;
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
        border: none !important;
    }
    .student-report-table {
        min-width: 0 !important;
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
    .student-report-table tr.failed-row,
    .student-report-table tr.failed-row td {
        background-color: #fee2e2 !important;
        color: #b91c1c !important;
        border-color: #fca5a5 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
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

    /* 12. Signatures in Print */
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
}

/* ========================================================
   Mobile & Tablet Responsive Styles (Screen View Only)
   ======================================================== */
@media screen and (max-width: 768px) {
    .report-page-wrap {
        padding: 0 4px !important;
        overflow-x: hidden !important;
    }

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
}
</style>

<div class="report-page-wrap">

<?php
// Capture parameters
$selected_class = isset($_REQUEST['class']) ? trim($_REQUEST['class']) : '';
$selected_student_ids = isset($_REQUEST['student_ids']) && is_array($_REQUEST['student_ids']) ? array_map('intval', $_REQUEST['student_ids']) : [];

// If student_id single parameter is passed, wrap it in array
if (empty($selected_student_ids) && isset($_REQUEST['student_id']) && intval($_REQUEST['student_id']) > 0) {
    $selected_student_ids = [intval($_REQUEST['student_id'])];
}

// Tests selection
$selected_tests = [];
if (isset($_REQUEST['tests']) && is_array($_REQUEST['tests'])) {
    foreach ($_REQUEST['tests'] as $t) {
        $t_code = intval($t);
        if (isset($all_exam_types[$t_code])) {
            $selected_tests[] = $t_code;
        }
    }
} elseif (isset($_REQUEST['test_code']) && intval($_REQUEST['test_code']) > 0) {
    $t_code = intval($_REQUEST['test_code']);
    if (isset($all_exam_types[$t_code])) {
        $selected_tests = [$t_code];
    }
}

// Default to Unit Test 1 if none chosen
if (empty($selected_tests)) {
    $selected_tests = [1];
}

// Sort in canonical order
$canonical_order = array_keys($all_exam_types);
usort($selected_tests, function($a, $b) use ($canonical_order) {
    return array_search($a, $canonical_order) <=> array_search($b, $canonical_order);
});

// Marks display mode
$allowed_modes = ['separate', 'combined', 'theory_only', 'practical_only'];
$marks_display = isset($_REQUEST['marks_display']) && in_array($_REQUEST['marks_display'], $allowed_modes)
    ? $_REQUEST['marks_display']
    : 'separate';

// Result Date
$result_date = date('d-m-Y');

// Prepare Logo
$logo_data_uri = '';
$logo_file_admin = __DIR__ . '/assets/images/path_creation_logo.png';
$logo_file_teacher = dirname(__DIR__) . '/teacher-admin/assets/images/path_creation_logo.png';
if (file_exists($logo_file_admin)) {
    $logo_data_uri = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_file_admin));
} elseif (file_exists($logo_file_teacher)) {
    $logo_data_uri = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_file_teacher));
}

// =========================================================================
// MODE B: MULTI-STUDENT REPORT CARD PRINT VIEW
// If student_ids are selected and submitted to be printed:
// =========================================================================
if (!empty($selected_student_ids)):

    // Fetch details for all selected students in this class
    $placeholders = implode(',', array_fill(0, count($selected_student_ids), '?'));
    $types = str_repeat('i', count($selected_student_ids));
    $stmt_students = mysqli_prepare($con, "SELECT id, fname, lname, rollno, class, father_name FROM users WHERE id IN ($placeholders) ORDER BY CAST(rollno AS UNSIGNED) ASC, rollno ASC");
    mysqli_stmt_bind_param($stmt_students, $types, ...$selected_student_ids);
    mysqli_stmt_execute($stmt_students);
    $res_students = mysqli_stmt_get_result($stmt_students);

    $students_list = [];
    while ($s_row = mysqli_fetch_assoc($res_students)) {
        $students_list[] = $s_row;
    }
    mysqli_stmt_close($stmt_students);

    if (empty($students_list)):
?>
        <div class="pca-white-card print-button">
            <div class="pca-card-body text-center py-5">
                <i class="fa-solid fa-triangle-exclamation text-warning fa-3x mb-3"></i>
                <h5>No valid students selected</h5>
                <p class="text-muted">Please go back and select at least one student to print.</p>
                <a href="print_report_card.php?class=<?= urlencode($selected_class) ?>" class="btn-primary-action">
                    <i class="fa-solid fa-arrow-left"></i> Back to Student Selection
                </a>
            </div>
        </div>
<?php
    else:
        $num_students = count($students_list);
        $num_tests = count($selected_tests);
        $total_pages = $num_students * $num_tests;
        $class_display = isset($class_map[$selected_class]) ? $class_map[$selected_class] : ("Class " . $selected_class);
?>

    <!-- Sticky Top Action Bar for Quick Printing & Navigation (Hidden in Print) -->
    <div class="pca-white-card print-button mb-4" style="position: sticky; top: 10px; z-index: 100; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
        <div class="pca-card-header">
            <div>
                <h1 class="pca-card-title">
                    <i class="fa-solid fa-print"></i> Report Card Print Preview
                </h1>
                <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                    <span class="badge-ctx-pill">
                        <i class="fa-solid fa-graduation-cap me-1 text-primary"></i> <?= htmlspecialchars($class_display) ?>
                    </span>
                    <span class="badge-ctx-pill">
                        <i class="fa-solid fa-users me-1 text-primary"></i> <?= $num_students ?> Student<?= $num_students > 1 ? 's' : '' ?> Selected
                    </span>
                    <span class="badge-ctx-pill">
                        <i class="fa-solid fa-file-lines me-1 text-primary"></i> <?= $total_pages ?> Total A4 Page<?= $total_pages > 1 ? 's' : '' ?>
                    </span>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" onclick="window.print()" class="btn-print-action">
                    <i class="fa-solid fa-print"></i> Print All Report Cards (<?= $total_pages ?> Pages)
                </button>
                <a href="print_report_card.php?class=<?= urlencode($selected_class) ?>" class="btn-back-link">
                    <i class="fa-solid fa-arrow-left"></i> Back to Selection
                </a>
            </div>
        </div>
    </div>

    <!-- Main Print Container -->
    <div class="print-visible-area" id="report-card-print-area">
<?php
        // Loop through each student
        $student_page_index = 0;
        foreach ($students_list as $student):
            $student_id = intval($student['id']);
            $student_name = htmlspecialchars(trim($student['fname'] . ' ' . $student['lname']));
            $student_roll = htmlspecialchars($student['rollno']);
            $class_code = $student['class'];
            $class_name = isset($class_map[$class_code]) ? $class_map[$class_code] : "Class Code " . $class_code;
            $guardian_name = (!empty($student['father_name']) && trim($student['father_name']) !== '')
                ? htmlspecialchars(trim($student['father_name']))
                : '—';

            // Fetch subjects assigned to this class
            $class_subjects = [];
            $stmt_sub = mysqli_prepare($con, "SELECT DISTINCT subject FROM class_subject_info WHERE class_code=? ORDER BY subject ASC");
            if ($stmt_sub) {
                mysqli_stmt_bind_param($stmt_sub, "s", $class_code);
                mysqli_stmt_execute($stmt_sub);
                $res_sub = mysqli_stmt_get_result($stmt_sub);
                while ($s_row = mysqli_fetch_assoc($res_sub)) {
                    if (!empty($s_row['subject'])) {
                        $class_subjects[] = trim($s_row['subject']);
                    }
                }
                mysqli_stmt_close($stmt_sub);
            }
            if (empty($class_subjects)) {
                $stmt_fallback_sub = mysqli_prepare($con, "SELECT DISTINCT TRIM(subject) as subject FROM student_score WHERE class_code=? AND subject IS NOT NULL AND TRIM(subject) != '' ORDER BY subject ASC");
                if ($stmt_fallback_sub) {
                    mysqli_stmt_bind_param($stmt_fallback_sub, "s", $class_code);
                    mysqli_stmt_execute($stmt_fallback_sub);
                    $res_fb = mysqli_stmt_get_result($stmt_fallback_sub);
                    while ($fb_row = mysqli_fetch_assoc($res_fb)) {
                        $class_subjects[] = $fb_row['subject'];
                    }
                    mysqli_stmt_close($stmt_fallback_sub);
                }
            }

            // Loop through each selected test for this student
            foreach ($selected_tests as $t_code):
                $student_page_index++;
                $test_name = $all_exam_types[$t_code] ?? ("Test " . $t_code);

                // Fetch max marks for this test
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

                // Calculate Rank in Class
                $rank_data = get_student_rank_in_class($con, $student_id, $class_code, $t_code);
?>
        <!-- Screen page divider badge -->
        <div class="student-page-divider print-button text-center my-3">
            <span class="badge shadow-sm" style="background: #1e293b; color: #fff; font-size: 0.85rem; padding: 0.45rem 1.1rem; border-radius: 20px; font-weight: 600;">
                <i class="fa-solid fa-file-invoice me-1 text-primary"></i> Page <?= $student_page_index ?> of <?= $total_pages ?>: <?= $student_name ?> (Roll #<?= $student_roll ?>) &mdash; <?= htmlspecialchars($test_name) ?>
            </span>
        </div>

        <!-- ==========================================
             SINGLE A4 REPORT CARD PAGE CONTAINER
             ========================================== -->
        <div class="report-card-page position-relative bg-white" style="border: none !important; padding: 24px 28px; margin: 0 auto 2rem auto; max-width: 860px; min-height: 1050px; display: flex; flex-direction: column; justify-content: flex-start; overflow: hidden !important; page-break-after: always; break-after: page;">

            <!-- TOP-LEFT GEOMETRIC CORNER (EXACT MIRROR OF TOP-RIGHT) -->
            <svg class="top-corner-svg-left" viewBox="0 0 100 75" style="position: absolute; top: 0; left: 0; width: 180px; height: 135px; pointer-events: none; z-index: 1;">
                <polygon points="62,0 0,0 0,58" fill="#111827" />
                <path d="M 98,0 L 68,0 L 2,66 L 7,70 L 29,43 Q 40,43 52,42 Q 57,40 62,36 Z" fill="#d9232d" />
            </svg>

            <!-- TOP-RIGHT GEOMETRIC CORNER -->
            <svg class="top-corner-svg-right" viewBox="0 0 100 75" style="position: absolute; top: 0; right: 0; width: 180px; height: 135px; pointer-events: none; z-index: 1;">
                <polygon points="38,0 100,0 100,58" fill="#111827" />
                <path d="M 2,0 L 32,0 L 98,66 L 93,70 L 71,43 Q 60,43 48,42 Q 43,40 38,36 Z" fill="#d9232d" />
            </svg>

            <!-- HEADER: LOGO & SCHOOL BRANDING -->
            <div class="report-header-wrap d-flex align-items-center mb-3" style="position: relative; z-index: 5;">
                <div class="header-logo-box" style="height: 60px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <img src="<?= $logo_data_uri ?>" alt="Path Creation Academy" style="height: 56px; width: auto; object-fit: contain; display: block;">
                </div>
                <div class="header-divider" style="width: 2px; height: 50px; background-color: #9ca3af; margin: 0 16px; flex-shrink: 0;"></div>
                <div class="brand-text-block" style="white-space: nowrap;">
                    <div class="brand-title" style="font-size: 1.85rem; font-weight: 800; color: #111827; line-height: 1.1; letter-spacing: -0.5px;">Path Creation <span style="color: #d9232d;">Academy</span></div>
                    <div class="brand-subtitle" style="font-size: 0.76rem; font-weight: 700; color: #4b5563; letter-spacing: 3.5px; text-transform: uppercase; margin-top: 4px;">LEARN &bull; BUILD &bull; GROW</div>
                </div>
            </div>

            <!-- EMBLEM & TITLE -->
            <div class="report-title-wrap text-center my-3" style="position: relative; z-index: 2;">
                <div class="d-inline-flex align-items-center justify-content-center">
                    <!-- Left Laurel Branch -->
                    <svg width="48" height="68" viewBox="0 0 50 75" style="flex-shrink: 0;">
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
                    </svg>

                    <!-- Center Cap & Title -->
                    <div class="d-flex flex-column align-items-center mx-3">
                        <svg width="44" height="30" viewBox="0 0 100 80" style="margin-bottom: 3px;">
                            <polygon points="50,6 94,28 50,50 6,28" fill="#d9232d" />
                            <path d="M 25,38 L 25,56 C 25,66 75,66 75,56 L 75,38 Q 50,50 25,38 Z" fill="#d9232d" />
                            <circle cx="50" cy="28" r="2.5" fill="#d9232d" />
                            <path d="M 50,28 L 18,35 L 14,56" stroke="#d9232d" stroke-width="2.5" fill="none" stroke-linecap="round" />
                            <polygon points="11,56 17,56 16,68 12,68" fill="#d9232d" />
                        </svg>
                        <h2 style="font-size: 2.15rem; font-weight: 800; color: #111827; margin: 0; letter-spacing: 0.5px; line-height: 1.15;">Result Report Card</h2>
                    </div>

                    <!-- Right Laurel Branch (Mirrored) -->
                    <svg width="48" height="68" viewBox="0 0 50 75" style="flex-shrink: 0; transform: scaleX(-1);">
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
                    </svg>
                </div>

                <!-- Test Name Line -->
                <div class="d-flex align-items-center justify-content-center mt-2">
                    <div class="title-flank-line" style="height: 2px; width: 75px; background: #d9232d; border-radius: 1px;"></div>
                    <span class="test-name-text mx-3 fw-bold" style="color: #1f2937; font-size: 1.05rem; letter-spacing: 0.5px;"><?= htmlspecialchars($test_name) ?></span>
                    <div class="title-flank-line" style="height: 2px; width: 75px; background: #d9232d; border-radius: 1px;"></div>
                </div>
            </div>

            <!-- STUDENT DETAILS BOX -->
            <div class="student-info-card" style="border: 1px solid #d1d5db; border-radius: 12px; background: #ffffff; padding: 10px 18px; margin-bottom: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); position: relative; z-index: 2;">
                <div class="row align-items-center">
                    <div class="col-md-6 col-12 info-col-left">
                        <div class="info-row d-flex align-items-center mb-2" style="font-size: 0.92rem;"><span class="info-label" style="width: 140px; font-weight: 700; color: #111827;">Student Name</span><span class="info-colon me-2 fw-bold">:</span><span class="info-value" style="font-weight: 600; color: #1f2937;"><?= $student_name ?></span></div>
                        <div class="info-row d-flex align-items-center mb-2" style="font-size: 0.92rem;"><span class="info-label" style="width: 140px; font-weight: 700; color: #111827;">Class</span><span class="info-colon me-2 fw-bold">:</span><span class="info-value" style="font-weight: 600; color: #1f2937;"><?= htmlspecialchars($class_name) ?></span></div>
                        <div class="info-row d-flex align-items-center mb-md-0 mb-2" style="font-size: 0.92rem;"><span class="info-label" style="width: 140px; font-weight: 700; color: #111827;">Guardian Name</span><span class="info-colon me-2 fw-bold">:</span><span class="info-value" style="font-weight: 600; color: #1f2937;"><?= $guardian_name ?></span></div>
                    </div>
                    <div class="col-md-6 col-12 info-col-right ps-md-4" style="border-left: 1px solid #e5e7eb;">
                        <div class="info-row d-flex align-items-center mb-2" style="font-size: 0.92rem;"><span class="info-label" style="width: 130px; font-weight: 700; color: #111827;">Roll No.</span><span class="info-colon me-2 fw-bold">:</span><span class="info-value" style="font-weight: 600; color: #1f2937;"><?= $student_roll ?></span></div>
                        <div class="info-row d-flex align-items-center" style="font-size: 0.92rem;"><span class="info-label" style="width: 130px; font-weight: 700; color: #111827;">Result Date</span><span class="info-colon me-2 fw-bold">:</span><span class="info-value" style="font-weight: 600; color: #1f2937;"><?= $result_date ?></span></div>
                    </div>
                </div>
            </div>

            <!-- MARKS TABLE -->
            <div class="mobile-table-hint d-md-none text-muted mb-1 text-center" style="font-size: 0.74rem;"><i class="fa-solid fa-arrows-left-right me-1 text-primary"></i> Swipe table horizontally to view all columns</div>
            <div class="w-100 report-table-wrap" style="position: relative; z-index: 2; margin-top: 4px; margin-bottom: 8px;">
                <table class="table table-bordered mb-0 student-report-table" style="border: 1.5px solid #1e293b; font-size: 0.86rem; width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr class="text-center align-middle" style="background-color: #1e293b; color: #ffffff;">
                            <th style="width: 6%; background-color: #1e293b; color: #ffffff; padding: 7px 2px; white-space: nowrap !important;">S. No.</th>
<?php
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
            $disp_combined_max = ($test_max_total > 0) ? ($test_max_total + 0) : 100;
            echo '<th style="width: 33%; background-color: #1e293b; color: #ffffff; text-align: left; padding: 8px 6px;">Subject</th>';
            echo '<th style="width: 18%; background-color: #1e293b; color: #ffffff; padding: 7px 3px; white-space: nowrap !important;"><span style="white-space: nowrap !important; display: inline-block;">Maximum</span><br><span style="font-size: 0.70rem; font-weight: 500; opacity: 0.9; white-space: nowrap !important;">(' . $disp_combined_max . ')</span></th>';
            echo '<th style="width: 18%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Obtained</th>';
            echo '<th class="col-percentage" style="width: 14%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Percentage</th>';
            echo '<th style="width: 10%; background-color: #1e293b; color: #ffffff; padding: 8px 3px; white-space: nowrap !important;">Grade</th>';
        }
?>
                        </tr>
                    </thead>
                    <tbody>
<?php
        $col_th_obt = 0;
        $col_th_max = 0;
        $col_pr_obt = 0;
        $col_pr_max = 0;
        $col_tot_obt = 0;
        $col_tot_max = 0;
        $subject_counter = 1;
        $failed_subject_count = 0;

        $stmt_mark = mysqli_prepare($con, "SELECT marks_obtained, practical_marks, total_marks, total_practical 
                                           FROM student_score 
                                           WHERE student_id=? AND TRIM(subject)=? AND test_code=? LIMIT 1");

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

            if ($m_th_max == 0 && $test_max_th > 0) $m_th_max = $test_max_th;
            if ($m_pr_max == 0 && $test_max_pr > 0 && $m_pr_obt > 0) $m_pr_max = $test_max_pr;

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

            $col_th_obt += $m_th_obt;
            $col_th_max += $m_th_max;
            $col_pr_obt += $m_pr_obt;
            $col_pr_max += $m_pr_max;
            $col_tot_obt += ($m_th_obt + $m_pr_obt);
            $col_tot_max += ($m_th_max + $m_pr_max);

            $subject_percentage = ($row_item_max > 0) ? round(($row_item_obt / $row_item_max) * 100, 2) : 0;
            $subject_grade = get_grade($subject_percentage);

            $is_subject_failed = false;
            if ($row_item_max > 0 && ($row_item_obt / $row_item_max) < PASS_PERCENTAGE) {
                $is_subject_failed = true;
                $failed_subject_count++;
            }

            $row_cls = $is_subject_failed ? 'failed-row' : 'passed-row';
            $row_style = $is_subject_failed 
                ? 'background-color: #fee2e2 !important; color: #b91c1c !important;' 
                : 'background-color: #ffffff; color: #1e293b;';
            $cell_style = $is_subject_failed 
                ? 'background-color: #fee2e2 !important; color: #b91c1c !important; border-color: #fca5a5 !important;' 
                : 'border-color: #cbd5e1;';

            if ($is_subject_failed) {
                $subj_html = '<span class="fail-subject-text fw-bold text-danger">' . htmlspecialchars($subject_name) . '</span>';
                $obt_th_html = '<span class="fail-mark-text fw-bold text-danger">' . ($m_th_obt + 0) . '</span>';
                $obt_pr_html = ($m_pr_obt > 0 || $m_pr_max > 0) ? ('<span class="fail-mark-text fw-bold text-danger">' . ($m_pr_obt + 0) . '</span>') : '&mdash;';
                $obt_tot_html = '<span class="fail-mark-text fw-bold text-danger">' . ($m_th_obt + $m_pr_obt) . '</span>';
                $pct_html = '<span class="fail-mark-text fw-bold text-danger">' . number_format($subject_percentage, 2) . ' %</span>';
                $grade_html = '<span class="fail-grade-badge">F</span>';
            } else {
                $subj_html = htmlspecialchars($subject_name);
                $obt_th_html = ($m_th_obt + 0);
                $obt_pr_html = ($m_pr_obt > 0 || $m_pr_max > 0) ? ($m_pr_obt + 0) : '&mdash;';
                $obt_tot_html = ($m_th_obt + $m_pr_obt);
                $pct_html = number_format($subject_percentage, 2) . ' %';
                $grade_html = '<strong>' . $subject_grade . '</strong>';
            }
?>
                        <tr class="<?= $row_cls ?>" style="<?= $row_style ?>">
                            <td class="text-center" style="<?= $cell_style ?> font-weight: 700;"><?= $subject_counter ?></td>
                            <td class="ps-2" style="<?= $cell_style ?>"><?= $subj_html ?></td>
<?php
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
?>
                        </tr>
<?php
            $subject_counter++;
        }
        if ($stmt_mark) mysqli_stmt_close($stmt_mark);

        $overall_percentage = ($col_tot_max > 0) ? round(($col_tot_obt / $col_tot_max) * 100, 2) : 0;
        $overall_grade = get_grade($overall_percentage);
        $overall_is_fail = ($overall_percentage < PASS_PERCENTAGE * 100 && $col_tot_max > 0);
        $is_student_failed = ($overall_is_fail || $failed_subject_count > 0);
        $overall_final_status_text = $is_student_failed ? "Compartment" : "Pass";
?>
                    </tbody>
                    <tfoot>
                        <tr class="text-center align-middle" style="background-color: #f8fafc; color: #111827; font-weight: 800; border-top: 2px solid #1e293b;">
                            <td colspan="2" class="text-center" style="font-weight: 800; font-size: 0.95rem; color: #111827;">Total</td>
<?php
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
?>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- 4 SUMMARY CARDS -->
            <div class="summary-cards-row row g-2 g-md-3 mt-2 mb-3 justify-content-between" style="position: relative; z-index: 2;">
                <!-- 1. Rank in Class -->
                <div class="col-md-3 col-6">
                    <div style="border: 1px solid #d1d5db; border-radius: 12px; background: #ffffff; padding: 14px 10px; text-align: center; height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="mb-1"><i class="fas fa-trophy" style="color: #d9232d; font-size: 1.6rem;"></i></div>
                        <div class="summary-title" style="font-size: 0.82rem; font-weight: 700; color: #1f2937; margin-bottom: 6px;">Rank in Class</div>
                        <div class="summary-val" style="font-size: 1.45rem; font-weight: 800; color: #d9232d; letter-spacing: -0.5px;"><?= $rank_data['display'] ?></div>
                    </div>
                </div>

                <!-- 2. Overall Percentage -->
                <div class="col-md-3 col-6">
                    <div style="border: 1px solid #d1d5db; border-radius: 12px; background: #ffffff; padding: 14px 10px; text-align: center; height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="mb-1"><i class="fas fa-chart-line" style="color: #d9232d; font-size: 1.6rem;"></i></div>
                        <div class="summary-title" style="font-size: 0.82rem; font-weight: 700; color: #1f2937; margin-bottom: 2px;">Overall Percentage</div>
                        <div class="summary-val" style="font-size: 1.45rem; font-weight: 800; color: #d9232d; letter-spacing: -0.5px;"><?= number_format($overall_percentage, 2) ?>%</div>
                        <div class="summary-sub" style="font-size: 0.78rem; font-weight: 600; color: #4b5563;">(<?= ($col_tot_obt + 0) ?> / <?= ($col_tot_max + 0) ?>)</div>
                    </div>
                </div>

                <!-- 3. Grade -->
                <div class="col-md-3 col-6">
                    <div style="border: 1px solid #d1d5db; border-radius: 12px; background: #ffffff; padding: 14px 10px; text-align: center; height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="mb-1"><i class="fas fa-award" style="color: #d9232d; font-size: 1.6rem;"></i></div>
                        <div class="summary-title" style="font-size: 0.82rem; font-weight: 700; color: #1f2937; margin-bottom: 6px;">Grade</div>
                        <div class="summary-val" style="font-size: 1.45rem; font-weight: 800; color: #d9232d; letter-spacing: -0.5px;"><?= $overall_grade ?></div>
                    </div>
                </div>

                <!-- 4. Result -->
<?php
                $res_badge_bg = $is_student_failed ? '#fee2e2' : '#dcfce7';
                $res_badge_color = $is_student_failed ? '#dc2626' : '#16a34a';
                $res_icon = $is_student_failed ? 'fa-triangle-exclamation' : 'fa-circle-check';
                $res_icon_color = $is_student_failed ? '#dc2626' : '#16a34a';
?>
                <div class="col-md-3 col-6">
                    <div style="border: 1px solid #d1d5db; border-radius: 12px; background: #ffffff; padding: 14px 10px; text-align: center; height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div class="mb-1"><i class="fa-solid <?= $res_icon ?>" style="color: <?= $res_icon_color ?>; font-size: 1.6rem;"></i></div>
                            <div class="summary-title" style="font-size: 0.82rem; font-weight: 700; color: #1f2937; margin-bottom: 6px;">Result</div>
                        </div>
                        <div>
                            <span class="summary-badge" style="background-color: <?= $res_badge_bg ?>; color: <?= $res_badge_color ?>; padding: 5px 14px; border-radius: 8px; font-weight: 800; font-size: 1.15rem; display: inline-block;"><?= $overall_final_status_text ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SIGNATURES SECTION -->
            <div class="signatures-row row align-items-center mb-2 pt-2" style="position: relative; z-index: 2; margin-top: auto !important;">
                <div class="col-5 text-center">
                    <div style="border-top: 2px solid #222; width: 75%; margin: 0 auto 8px auto;"></div>
                    <strong style="font-size: 0.95rem; color: #111;">Parent's Signature</strong>
                </div>
                <div class="col-2 text-center">
                    <div style="width: 2px; height: 45px; background-color: #d9232d; margin: 0 auto;"></div>
                </div>
                <div class="col-5 text-center">
                    <div style="border-top: 2px solid #222; width: 75%; margin: 0 auto 8px auto;"></div>
                    <strong style="font-size: 0.95rem; color: #111;">Teacher's Signature</strong>
                </div>
            </div>

            <!-- BOTTOM-LEFT DECORATIVE CURVE -->
            <svg class="bottom-corner-svg-left" viewBox="0 0 200 120" style="position: absolute; bottom: 0; left: 0; width: 160px; height: 100px; pointer-events: none; z-index: 1;">
                <path d="M0,20 Q40,110 150,120 L0,120 Z" fill="#111827" />
                <path d="M0,45 Q50,115 170,120 L0,120 Z" fill="#d9232d" />
                <path d="M0,70 Q70,120 190,120 L0,120 Z" fill="#ff2a3a" opacity="0.9" />
            </svg>

            <!-- BOTTOM-RIGHT DECORATIVE CURVE -->
            <svg class="bottom-corner-svg-right" viewBox="0 0 200 120" style="position: absolute; bottom: 0; right: 0; width: 160px; height: 100px; pointer-events: none; z-index: 1;">
                <path d="M200,20 Q160,110 50,120 L200,120 Z" fill="#111827" />
                <path d="M200,45 Q150,115 30,120 L200,120 Z" fill="#d9232d" />
                <path d="M200,70 Q130,120 10,120 L200,120 Z" fill="#ff2a3a" opacity="0.9" />
            </svg>

        </div><!-- /.report-card-page -->
<?php
            endforeach; // foreach test
        endforeach; // foreach student
?>
    </div><!-- /#report-card-print-area -->

<?php
    endif; // empty students_list check

// =========================================================================
// MODE A: CLASS SELECTION & STUDENT CHECKLIST VIEW
// =========================================================================
else:
?>

    <div class="pca-white-card">
        <!-- Card Header -->
        <div class="pca-card-header">
            <div>
                <h1 class="pca-card-title">
                    <i class="fa-solid fa-file-invoice"></i> Result Report Card Printing
                </h1>
                <span class="text-muted" style="font-size: 0.82rem;">Select class, pick students with checkboxes, and generate official A4 report cards in bulk or individually</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="result-show.php" class="btn-back-link">
                    <i class="fa-solid fa-chart-bar text-primary"></i> Show Results Table
                </a>
                <a href="result-view.php" class="btn-back-link">
                    <i class="fa-solid fa-table-list text-primary"></i> View Results
                </a>
            </div>
        </div>

        <div class="pca-card-body">
            <!-- Form to select class, exams, marks display, and student checkboxes -->
            <form method="GET" action="print_report_card.php" id="adminReportCardForm">
                
                <!-- Filter Box: Class, Exams & Marks Display -->
                <div class="filter-box-clean">
                    <div class="row g-3">
                        <!-- 1. Class Selection -->
                        <div class="col-lg-4 col-md-6 col-12">
                            <label for="classSelect" class="form-label-clean">
                                <i class="fa-solid fa-graduation-cap text-primary me-1"></i> 1. Select Class: *
                            </label>
                            <select name="class" id="classSelect" class="form-control form-control-clean" onchange="this.form.submit();" required>
                                <option value="">-- Choose Class --</option>
                                <?php foreach ($class_map as $c_code => $c_name): ?>
                                    <option value="<?= htmlspecialchars($c_code) ?>" <?= ($selected_class === strval($c_code)) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c_name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted" style="font-size: 0.76rem;">Changing class reloads students instantly.</div>
                        </div>

                        <!-- 2. Test Selection -->
                        <div class="col-lg-5 col-md-6 col-12">
                            <label class="form-label-clean">
                                <i class="fa-solid fa-list-check text-primary me-1"></i> 2. Select Test(s):
                            </label>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2 p-2 bg-white rounded border">
                                <?php foreach ($all_exam_types as $code => $name): 
                                    $is_checked = in_array($code, $selected_tests);
                                    $theme = $test_theme_colors[$code] ?? ['badge_bg' => '#475569'];
                                ?>
                                    <label class="test-checkbox-wrap <?= $is_checked ? 'is-checked' : '' ?>" for="test_<?= $code ?>">
                                        <input class="form-check-input test-checkbox m-0" type="checkbox" name="tests[]" id="test_<?= $code ?>" value="<?= $code ?>" <?= $is_checked ? 'checked' : '' ?>>
                                        <span class="badge shadow-sm" style="background-color: <?= $theme['badge_bg'] ?>; color: #fff; font-size: 0.78rem; padding: 4px 8px; font-weight: 600;">
                                            <?= htmlspecialchars($name) ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="quick-pill-btn" id="btnSelectAllTests">All</button>
                                <button type="button" class="quick-pill-btn" id="btnUnitsOnly">Unit Tests</button>
                                <button type="button" class="quick-pill-btn" id="btnHalfFinalOnly">Half &amp; Final</button>
                            </div>
                        </div>

                        <!-- 3. Marks Display Format -->
                        <div class="col-lg-3 col-md-12 col-12">
                            <label class="form-label-clean">
                                <i class="fa-solid fa-sliders text-primary me-1"></i> 3. Marks Format:
                            </label>
                            <div class="d-flex flex-column gap-2 p-2 bg-white rounded border">
                                <label class="radio-mode-wrap <?= ($marks_display === 'separate') ? 'is-checked' : '' ?>" for="md_sep">
                                    <input class="form-check-input m-0" type="radio" name="marks_display" id="md_sep" value="separate" <?= ($marks_display === 'separate') ? 'checked' : '' ?>>
                                    <span style="font-size: 0.82rem; font-weight: 600; color: #1e293b;">Theory &amp; Practical (Separate)</span>
                                </label>
                                <label class="radio-mode-wrap <?= ($marks_display === 'combined') ? 'is-checked' : '' ?>" for="md_comb">
                                    <input class="form-check-input m-0" type="radio" name="marks_display" id="md_comb" value="combined" <?= ($marks_display === 'combined') ? 'checked' : '' ?>>
                                    <span style="font-size: 0.82rem; font-weight: 600; color: #1e293b;">Combined Total Marks</span>
                                </label>
                                <label class="radio-mode-wrap <?= ($marks_display === 'theory_only') ? 'is-checked' : '' ?>" for="md_th">
                                    <input class="form-check-input m-0" type="radio" name="marks_display" id="md_th" value="theory_only" <?= ($marks_display === 'theory_only') ? 'checked' : '' ?>>
                                    <span style="font-size: 0.82rem; font-weight: 600; color: #1e293b;">Theory Only</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

<?php
    // If class is selected, fetch and display all students with checkboxes
    if (!empty($selected_class)):
        $stmt_cls_students = mysqli_prepare($con, "SELECT id, fname, lname, rollno, father_name FROM users WHERE class=? AND role_as=0 ORDER BY CAST(rollno AS UNSIGNED) ASC, rollno ASC");
        mysqli_stmt_bind_param($stmt_cls_students, "s", $selected_class);
        mysqli_stmt_execute($stmt_cls_students);
        $res_cls_students = mysqli_stmt_get_result($stmt_cls_students);

        $roster = [];
        while ($st = mysqli_fetch_assoc($res_cls_students)) {
            $roster[] = $st;
        }
        mysqli_stmt_close($stmt_cls_students);

        $roster_count = count($roster);
        $class_display_title = isset($class_map[$selected_class]) ? $class_map[$selected_class] : ("Class " . $selected_class);
?>

                <!-- Student Roster & Checkbox Selection Section -->
                <div class="mt-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-users text-primary"></i> <?= htmlspecialchars($class_display_title) ?> Students
                                <span class="badge bg-primary rounded-pill" style="font-size: 0.8rem;"><?= $roster_count ?> Total</span>
                            </h5>
                            <span class="text-muted" style="font-size: 0.82rem;">Select which students to include in the bulk print output</span>
                        </div>

                        <!-- Checkbox Controls & Search -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge-ctx-pill">
                                <i class="fa-solid fa-check-double text-success me-1"></i> Selected: <strong id="selectedCountText" class="ms-1 text-primary"><?= $roster_count ?></strong> / <?= $roster_count ?>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelectAllStudents">
                                <i class="fa-solid fa-check-square me-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAllStudents">
                                <i class="fa-regular fa-square me-1"></i> Deselect All
                            </button>
                            <div style="min-width: 200px;">
                                <input type="text" id="studentSearchInput" class="form-control form-control-sm form-control-clean" placeholder="Search name or roll #...">
                            </div>
                        </div>
                    </div>

<?php if ($roster_count > 0): ?>
                    <!-- Student Checklist Table -->
                    <div class="table-responsive bg-white rounded border" style="max-height: 520px; overflow-y: auto;">
                        <table class="pca-table" id="studentRosterTable">
                            <thead>
                                <tr>
                                    <th style="width: 50px; text-align: center;">
                                        <input type="checkbox" id="masterCheckbox" class="form-check-input m-0" checked title="Select / Deselect All">
                                    </th>
                                    <th style="width: 120px;">Roll No.</th>
                                    <th>Student Name</th>
                                    <th>Guardian / Father Name</th>
                                    <th class="text-end" style="width: 160px;">Individual Action</th>
                                </tr>
                            </thead>
                            <tbody>
<?php 
            foreach ($roster as $student_row):
                $st_id = $student_row['id'];
                $st_name = htmlspecialchars(trim($student_row['fname'] . ' ' . $student_row['lname']));
                $st_roll = htmlspecialchars($student_row['rollno']);
                $st_initial = strtoupper(substr(trim($student_row['fname'] ?? 'S'), 0, 1));
                $st_guardian = (!empty($student_row['father_name']) && trim($student_row['father_name']) !== '')
                    ? htmlspecialchars(trim($student_row['father_name']))
                    : '—';
?>
                                <tr class="student-row" data-name="<?= strtolower($st_name) ?>" data-roll="<?= strtolower($st_roll) ?>">
                                    <td class="text-center">
                                        <input type="checkbox" name="student_ids[]" value="<?= $st_id ?>" class="form-check-input student-cb" checked>
                                    </td>
                                    <td>
                                        <span class="badge-roll-pill">#<?= $st_roll ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="student-avatar-sm"><?= $st_initial ?></div>
                                            <span class="fw-bold text-dark"><?= $st_name ?></span>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <?= $st_guardian ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="print_report_card.php?class=<?= urlencode($selected_class) ?>&student_id=<?= $st_id ?>" class="btn btn-sm btn-outline-primary" style="font-size: 0.78rem; font-weight: 600; padding: 0.25rem 0.65rem;" title="View and print this student's report card only">
                                            <i class="fa-solid fa-print me-1"></i> Print Single
                                        </a>
                                    </td>
                                </tr>
<?php       endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Action Bar -->
                    <div class="d-flex align-items-center justify-content-between mt-3 p-3 bg-light rounded border flex-wrap gap-2">
                        <div class="text-muted" style="font-size: 0.86rem;">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i> Each selected student will print on their own full-page A4 report card with full school branding, marks, and signatures.
                        </div>
                        <button type="submit" id="submitPrintBtn" class="btn-primary-action btn-lg">
                            <i class="fa-solid fa-print me-2"></i> Generate &amp; Print Report Cards (<span id="btnCountText"><?= $roster_count ?></span> Students)
                        </button>
                    </div>

<?php else: ?>
                    <div class="text-center py-5 bg-white rounded border">
                        <i class="fa-solid fa-folder-open text-muted fa-3x mb-2"></i>
                        <h6 class="fw-bold text-dark">No Students Enrolled</h6>
                        <p class="text-muted mb-0" style="font-size: 0.85rem;">No student records found enrolled in <?= htmlspecialchars($class_display_title) ?>.</p>
                    </div>
<?php endif; ?>

                </div><!-- /.mt-4 -->

<?php else: ?>
                <!-- No Class Selected State -->
                <div class="text-center py-5 bg-white rounded border my-2">
                    <div class="mx-auto mb-3" style="width: 60px; height: 60px; background: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-graduation-cap text-primary fa-xl"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Please Select a Class Above</h5>
                    <p class="text-muted mb-0" style="font-size: 0.88rem;">Choose any class from the dropdown to load the student roster with individual print checkboxes.</p>
                </div>
<?php endif; ?>

            </form>
        </div>
    </div>

<?php endif; ?>

</div><!-- /.report-page-wrap -->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Quick test selection helpers
    $('#btnSelectAllTests').on('click', function() {
        $('.test-checkbox').prop('checked', true).closest('.test-checkbox-wrap').addClass('is-checked');
    });
    $('#btnUnitsOnly').on('click', function() {
        $('.test-checkbox').each(function() {
            var val = parseInt($(this).val());
            var isUnit = [1, 2, 3, 4].includes(val);
            $(this).prop('checked', isUnit).closest('.test-checkbox-wrap').toggleClass('is-checked', isUnit);
        });
    });
    $('#btnHalfFinalOnly').on('click', function() {
        $('.test-checkbox').each(function() {
            var val = parseInt($(this).val());
            var isTerm = [5, 6].includes(val);
            $(this).prop('checked', isTerm).closest('.test-checkbox-wrap').toggleClass('is-checked', isTerm);
        });
    });

    // Checkbox styling toggle
    $('.test-checkbox').on('change', function() {
        $(this).closest('.test-checkbox-wrap').toggleClass('is-checked', $(this).is(':checked'));
    });
    $('.radio-mode-wrap input[type="radio"]').on('change', function() {
        $('.radio-mode-wrap').removeClass('is-checked');
        if ($(this).is(':checked')) {
            $(this).closest('.radio-mode-wrap').addClass('is-checked');
        }
    });

    // Student Checkbox Counter Update
    function updateStudentCounter() {
        var total = $('.student-cb').length;
        var checked = $('.student-cb:checked').length;
        $('#selectedCountText').text(checked);
        $('#btnCountText').text(checked);
        $('#masterCheckbox').prop('checked', total > 0 && checked === total);
        
        if (checked === 0) {
            $('#submitPrintBtn').prop('disabled', true).addClass('opacity-50');
        } else {
            $('#submitPrintBtn').prop('disabled', false).removeClass('opacity-50');
        }
    }

    // Master Checkbox Toggle
    $('#masterCheckbox').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.student-cb').prop('checked', isChecked);
        updateStudentCounter();
    });

    // Select All / Deselect All Buttons
    $('#btnSelectAllStudents').on('click', function() {
        $('.student-cb').prop('checked', true);
        updateStudentCounter();
    });
    $('#btnDeselectAllStudents').on('click', function() {
        $('.student-cb').prop('checked', false);
        updateStudentCounter();
    });

    // Individual Checkbox Click
    $(document).on('change', '.student-cb', function() {
        updateStudentCounter();
    });

    // Fast Student Search / Filter
    $('#studentSearchInput').on('keyup', function() {
        var term = $(this).val().toLowerCase().trim();
        $('.student-row').each(function() {
            var name = $(this).data('name') || '';
            var roll = String($(this).data('roll') || '');
            if (term === '' || name.indexOf(term) > -1 || roll.indexOf(term) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Prevent submission if 0 students selected
    $('#adminReportCardForm').on('submit', function(e) {
        if ($('.student-cb').length > 0 && $('.student-cb:checked').length === 0) {
            e.preventDefault();
            alert('Please select at least one student checkbox before generating report cards.');
            return false;
        }
    });
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
