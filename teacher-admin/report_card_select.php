<?php
// report_card_select.php (Main Class Selection & Student Report Cards Page)
include('authentication.php');
include('includes/header.php');

$user_id = $_SESSION['auth_user']['user_id'] ?? $_SESSION['auth_user']['id'] ?? '';
$teacher_class = isset($_SESSION['auth_user']['user_class']) ? $_SESSION['auth_user']['user_class'] : '';

// Define class options
$full_class_options = [
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

// Fetch only classes that have assigned subjects for this teacher in teacher_subject
$assigned_classes = [];
if (!empty($user_id)) {
    $q_assigned = "SELECT DISTINCT class_code FROM teacher_subject WHERE teacher_id = ? ORDER BY CAST(class_code AS UNSIGNED) ASC, class_code ASC";
    $stmt_assigned = mysqli_prepare($con, $q_assigned);
    if ($stmt_assigned) {
        mysqli_stmt_bind_param($stmt_assigned, "i", $user_id);
        mysqli_stmt_execute($stmt_assigned);
        $res_assigned = mysqli_stmt_get_result($stmt_assigned);
        while ($ac_row = mysqli_fetch_assoc($res_assigned)) {
            $assigned_classes[] = strval($ac_row['class_code']);
        }
        mysqli_stmt_close($stmt_assigned);
    }

    // Fallback to class_teacher table if no teacher_subject rows found
    if (empty($assigned_classes)) {
        $q_ct = mysqli_query($con, "SELECT DISTINCT class FROM class_teacher WHERE user_id = '$user_id'");
        if ($q_ct && mysqli_num_rows($q_ct) > 0) {
            while ($ct_row = mysqli_fetch_assoc($q_ct)) {
                $assigned_classes[] = strval($ct_row['class']);
            }
        }
    }
}
$classes_to_show = !empty($assigned_classes) ? $assigned_classes : array_keys($full_class_options);

// Determine preselected class
$preselected_class = '';
if (!empty($teacher_class) && in_array(strval($teacher_class), $classes_to_show)) {
    $preselected_class = strval($teacher_class);
} elseif (count($assigned_classes) === 1) {
    $preselected_class = strval($assigned_classes[0]);
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

/* Filter Controls Box */
.filter-box-clean {
    background: #f8fafc;
    border: 1px solid #edf0f5;
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.25rem;
}

.form-label-clean {
    font-size: 0.84rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 0.4rem;
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
    padding: 0.55rem 0.85rem;
    height: auto;
    transition: all 0.2s ease;
}

.form-control-clean:focus {
    border-color: #5e48eb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12);
}

.btn-view-link {
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

.btn-view-link:hover {
    background: #f8fafc;
    color: #0f172a !important;
    border-color: #cbd5e1;
}

/* Table styling for student list */
.table-responsive {
    overflow-x: auto !important;
    overflow-y: visible !important;
    width: 100% !important;
    border: 1px solid #edf0f5;
    border-radius: 12px;
    background: #ffffff;
}

.pca-table {
    margin-bottom: 0;
    width: 100% !important;
    min-width: 580px;
    table-layout: fixed !important;
    border-collapse: collapse;
}

@media (max-width: 768px) {
    .pca-card-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem;
    }
    .pca-card-header .btn-view-link {
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
}

.pca-table tbody td {
    padding: 0.85rem 1rem !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #f1f5f9 !important;
    font-size: 0.88rem;
    color: #1e293b;
    background: #ffffff;
}

.pca-table tbody tr:hover td {
    background-color: #fbfbfe !important;
}

.pca-table tbody tr:last-child td {
    border-bottom: none !important;
}

.badge-roll-pill {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
    font-weight: 700;
    font-size: 0.82rem;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    display: inline-block;
}

.student-avatar-sm {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%);
    color: #4338ca;
    border: 1px solid #c7d2fe;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.84rem;
    font-weight: 800;
    flex-shrink: 0;
}

.btn-print-link {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.84rem;
    padding: 0.45rem 1.1rem;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none !important;
    box-shadow: 0 2px 6px rgba(94, 72, 235, 0.2);
    transition: all 0.2s ease;
}

.btn-print-link:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    box-shadow: 0 4px 12px rgba(94, 72, 235, 0.32);
    transform: translateY(-1px);
}

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

.loading-box-clean {
    padding: 2.5rem 1rem;
    text-align: center;
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
                    <i class="fa-solid fa-file-invoice"></i>
                    Student Report Cards
                </h1>
                <span class="text-muted" style="font-size: 0.82rem;">Select class to view students and generate official result report cards</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="view-report.php" class="btn-view-link">
                    <i class="fa-regular fa-chart-bar text-primary"></i> View Theory Marks
                </a>
                <a href="view-partical-marks.php" class="btn-view-link">
                    <i class="fa-solid fa-flask text-primary"></i> View Practical Marks
                </a>
            </div>
        </div>

        <div class="pca-card-body">
            <!-- Filter Box -->
            <div class="filter-box-clean">
                <div class="row g-3 align-items-end">
                    <!-- Class Selection -->
                    <div class="col-lg-5 col-md-6 col-12">
                        <label for="class_select" class="form-label-clean">
                            <i class="fa-solid fa-graduation-cap text-primary me-1"></i> Select Class: *
                        </label>
                        <select id="class_select" class="form-control form-control-clean">
                            <option value="">-- Choose Class --</option>
                            <?php 
                            foreach ($classes_to_show as $id):
                                $c_name = $full_class_options[$id] ?? ('Class ' . $id);
                                $is_selected = ($preselected_class == $id) ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($id) ?>" <?= $is_selected ?>>
                                    <?= htmlspecialchars($c_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Search Input for fast filtering -->
                    <div class="col-lg-5 col-md-6 col-12">
                        <label for="student_search_input" class="form-label-clean">
                            <i class="fa-solid fa-magnifying-glass text-primary me-1"></i> Filter Students:
                        </label>
                        <input type="text" id="student_search_input" class="form-control form-control-clean" placeholder="Type student name or roll no to search...">
                    </div>

                    <!-- Class Info Pill -->
                    <div class="col-lg-2 col-12 text-lg-end text-start">
                        <span id="student_count_badge" class="badge-ctx-pill d-none">
                            <i class="fa-solid fa-users text-primary me-1"></i> <span id="student_count_text">0 Students</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Student List Container -->
            <div id="student_list">
                <div class="empty-state-clean text-center my-2">
                    <div class="empty-icon-circle mx-auto mb-3">
                        <i class="fa-solid fa-user-graduate text-primary fa-lg"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Select a Class Above</h6>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Choose an assigned class from the dropdown to load student records and print report cards.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

    function loadStudents(class_id){
        $('#student_list').html(`
            <div class="loading-box-clean">
                <div class="spinner-border text-primary mb-2" role="status" style="width: 2.2rem; height: 2.2rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-muted fw-semibold" style="font-size: 0.88rem;">Loading student roster...</div>
            </div>
        `);

        $.ajax({
            url: 'fetch_students_list.php',
            type: 'POST',
            data: {class_id: class_id},
            success: function(data){
                $('#student_list').html(data);

                // Update count badge
                let rowCount = $('#student_list tbody tr').length;
                if(rowCount > 0 && $('#student_list table').length > 0) {
                    $('#student_count_text').text(rowCount + (rowCount === 1 ? ' Student' : ' Students'));
                    $('#student_count_badge').removeClass('d-none');
                } else {
                    $('#student_count_badge').addClass('d-none');
                }

                // Reset search box
                $('#student_search_input').val('');
            },
            error: function(){
                $('#student_list').html(`
                    <div class="alert alert-danger text-center my-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-1"></i> Error loading students. Please try again.
                    </div>
                `);
                $('#student_count_badge').addClass('d-none');
            }
        });
    }

    // Auto-load students if preselected
    let defaultClass = $('#class_select').val();
    if(defaultClass){
        loadStudents(defaultClass);
    }

    // Load students when class is changed
    $('#class_select').on('change', function(){
        let class_id = $(this).val();
        if(class_id){
            loadStudents(class_id);
        } else {
            $('#student_count_badge').addClass('d-none');
            $('#student_list').html(`
                <div class="empty-state-clean text-center my-2">
                    <div class="empty-icon-circle mx-auto mb-3">
                        <i class="fa-solid fa-user-graduate text-primary fa-lg"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">Select a Class Above</h6>
                    <p class="text-muted mb-0" style="font-size: 0.85rem;">Choose an assigned class from the dropdown to load student records and print report cards.</p>
                </div>
            `);
        }
    });

    // Real-time client-side filter
    $('#student_search_input').on('keyup', function(){
        let query = $(this).val().toLowerCase().trim();
        $('#student_list tbody tr').each(function(){
            let text = $(this).text().toLowerCase();
            if(text.indexOf(query) > -1){
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
