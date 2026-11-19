<?php
include('authentication.php');
include('includes/header.php');

// Ensure user logged in
if (!isset($_SESSION['auth_user']['user_id']) && !isset($_SESSION['auth_user']['id'])) {
    die("User not logged in.");
}

// Fetch teacher name
$user_id = $_SESSION['auth_user']['user_id'] ?? $_SESSION['auth_user']['id'];
$query = "SELECT fname, lname FROM users WHERE id = '$user_id' LIMIT 1";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
$teacher_name = $row ? trim($row['fname'] . ' ' . ($row['lname'] ?? '')) : 'Unknown';
if (empty($teacher_name) && $row) {
    $teacher_name = $row['fname'];
}

// Full class options mapping
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
    '13' => 'Class 11 Comm. (With math)',
    '14' => 'Class 11 Comm. (Without math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)',
    '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (With math)',
    '19' => 'Class 12 Comm. (Without math)',
    '20' => 'Class 12 Arts'
];

// Fetch only classes that have assigned subjects for this teacher in teacher_subject
$assigned_classes = [];
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

// Classes to show in dropdown (locks to assigned classes)
$classes_to_show = !empty($assigned_classes) ? $assigned_classes : array_keys($full_class_options);
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
    padding: 1.5rem;
}

/* Controls */
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
    background: #f8fafc !important;
    min-height: 42px;
    transition: all 0.2s ease;
    width: 100%;
}

.form-control-clean:focus {
    background: #ffffff !important;
    border-color: #5e48eb !important;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12) !important;
    outline: none;
}

.form-control-clean[readonly] {
    background-color: #f1f5f9 !important;
    color: #475569 !important;
    cursor: default;
}

/* File upload zone */
.file-upload-box {
    background: #fbfbfe;
    border: 1.5px dashed #cbd5e1;
    border-radius: 12px;
    padding: 1rem 1.2rem;
    transition: all 0.2s ease;
}

.file-upload-box:hover {
    border-color: #5e48eb;
    background: #fafaff;
}

/* Primary Action Button */
.btn-primary-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.6rem 1.8rem;
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
    text-decoration: none !important;
    transition: all 0.2s ease;
}

.btn-view-link:hover {
    background: #f8fafc;
    color: #0f172a !important;
    border-color: #cbd5e1;
}

/* Switch styling */
.form-check-input {
    width: 2.2em !important;
    height: 1.2em !important;
    margin-top: 0.15em !important;
    cursor: pointer;
}

.form-check-input:checked {
    background-color: #10b981 !important;
    border-color: #10b981 !important;
}

@media (max-width: 768px) {
    .pca-card-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem;
    }
    .pca-card-header .btn-primary-action,
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
</style>

<div class="report-page-wrap">

    <!-- Flash message -->
    <?php include('message.php'); ?>

    <div class="pca-white-card">
        <!-- Header -->
        <div class="pca-card-header">
            <h1 class="pca-card-title">
                <i class="fa-solid fa-folder-open"></i>
                Add Study Material
            </h1>
            <a href="view-study-material.php" class="btn-view-link">
                <i class="fas fa-eye me-1"></i> View Materials
            </a>
        </div>

        <div class="pca-card-body">

            <form action="teacher_code.php" method="POST" enctype="multipart/form-data">
                <div class="row g-3">

                    <!-- Teacher Name -->
                    <div class="col-md-6 col-12">
                        <label class="form-label-clean">
                            <i class="fa-solid fa-user-tie text-primary me-1"></i> Teacher Name:
                        </label>
                        <input type="text" name="teacher_name" class="form-control form-control-clean"
                               value="<?php echo htmlspecialchars($teacher_name); ?>" readonly>
                    </div>

                    <!-- Class -->
                    <div class="col-md-6 col-12">
                        <label for="class_name" class="form-label-clean">
                            <i class="fa-solid fa-graduation-cap text-primary me-1"></i> Target Class: *
                        </label>
                        <select name="class_name" id="class_name" required class="form-control form-control-clean">
                            <option value="">-- Select Class --</option>
                            <?php 
                            $auto_select = (count($assigned_classes) === 1);
                            foreach ($classes_to_show as $code): 
                                $c_name = $full_class_options[$code] ?? ('Class ' . $code);
                                $is_selected = $auto_select ? 'selected' : '';
                            ?>
                                <option value="<?= htmlspecialchars($code) ?>" <?= $is_selected ?>><?= htmlspecialchars($c_name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Subject -->
                    <div class="col-md-6 col-12">
                        <label for="subject" class="form-label-clean">
                            <i class="fa-solid fa-book-open text-primary me-1"></i> Subject: *
                        </label>
                        <select name="subject" id="subject" required class="form-control form-control-clean">
                            <option value="">-- Select Class First --</option>
                        </select>
                    </div>

                    <!-- Material Type -->
                    <div class="col-md-6 col-12">
                        <label for="material_type" class="form-label-clean">
                            <i class="fa-solid fa-layer-group text-primary me-1"></i> Material Type: *
                        </label>
                        <select name="material_type" id="material_type" required class="form-control form-control-clean">
                            <option value="">-- Select Type --</option>
                            <option value="assignment">Assignment</option>
                            <option value="syllabus">Syllabus</option>
                            <option value="notes">Notes</option>
                            <option value="question_paper">Question Paper</option>
                            <option value="ppt">Daily PPT</option>
                        </select>
                    </div>

                    <!-- Title -->
                    <div class="col-12">
                        <label for="title" class="form-label-clean">
                            <i class="fa-solid fa-heading text-primary me-1"></i> Material Title: *
                        </label>
                        <input type="text" name="title" id="title" required class="form-control form-control-clean" placeholder="e.g. Chapter 4 - Organic Chemistry Notes">
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label for="description" class="form-label-clean">
                            <i class="fa-solid fa-align-left text-primary me-1"></i> Description (Optional):
                        </label>
                        <textarea name="description" id="description" class="form-control form-control-clean" rows="3"
                                  placeholder="Provide any instructions or summary about this material..."></textarea>
                    </div>

                    <!-- File Upload -->
                    <div class="col-12">
                        <label for="file" class="form-label-clean">
                            <i class="fa-solid fa-file-arrow-up text-primary me-1"></i> Upload File: *
                        </label>
                        <div class="file-upload-box">
                            <input type="file" name="file" id="file" accept=".pdf,.jpg,.jpeg,.png,.ppt,.pptx" required class="form-control form-control-clean">
                            <small class="text-muted d-block mt-2">
                                <i class="fa-solid fa-circle-info me-1"></i> Allowed file formats: <strong>PDF, JPG, JPEG, PNG, PPT, PPTX</strong>
                            </small>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-12">
                        <label class="form-label-clean">
                            <i class="fa-solid fa-toggle-on text-primary me-1"></i> Visibility Status:
                        </label>
                        <div class="form-check form-switch d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" name="status" id="status" checked>
                            <label class="form-check-label fw-semibold text-dark" for="status" style="cursor: pointer;">
                                Active (Visible to students)
                            </label>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="col-12 mt-4 pt-2 border-top">
                        <button type="submit" name="upload_study_material" class="btn-primary-action">
                            <i class="fas fa-cloud-arrow-up me-1"></i> Upload Study Material
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>

<!-- jQuery for AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function loadSubjectsForClass(class_code) {
        if (class_code !== '') {
            $('#subject').html('<option value="">Loading subjects...</option>');
            $.ajax({
                url: 'fetch_subjects_formaterial.php',
                type: 'POST',
                data: { 
                    action: 'load_subjects', 
                    class_code: class_code 
                },
                success: function(response) {
                    $('#subject').html(response);
                },
                error: function() {
                    alert('Error loading subjects. Please try again.');
                    $('#subject').html('<option value="">-- Error Loading Subjects --</option>');
                }
            });
        } else {
            $('#subject').html('<option value="">-- Select Class First --</option>');
        }
    }

    $('#class_name').on('change', function() {
        var class_code = $(this).val();
        loadSubjectsForClass(class_code);
    });

    // If a class is auto-selected on page load, load its subjects immediately
    var currentClass = $('#class_name').val();
    if (currentClass) {
        loadSubjectsForClass(currentClass);
    }
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
