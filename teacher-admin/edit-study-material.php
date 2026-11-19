<?php
include('authentication.php');
include('includes/header.php');

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "❌ Material ID missing!";
    header("Location: view-study-material.php");
    exit();
}

$id = intval($_GET['id']);
$user_id = $_SESSION['auth_user']['user_id'] ?? $_SESSION['auth_user']['id'] ?? '';

// Fetch existing data
$stmt = $con->prepare("SELECT * FROM study_materials WHERE id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows != 1) {
    $_SESSION['message'] = "❌ Material not found!";
    header("Location: view-study-material.php");
    exit();
}
$material = $result->fetch_assoc();

// Class and material type arrays
$class_names = [
    '100' => 'Nursery', '101' => 'LKG', '102' => 'UKG',
    '1' => 'Class 1', '2' => 'Class 2', '3' => 'Class 3', '4' => 'Class 4', '5' => 'Class 5',
    '6' => 'Class 6', '7' => 'Class 7', '8' => 'Class 8', '9' => 'Class 9', '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)', '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm. (with Math)', '14' => 'Class 11 Comm. (without Math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)', '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (with Math)', '19' => 'Class 12 Comm. (without Math)',
    '20' => 'Class 12 Arts'
];

$types = [
    'assignment' => 'Assignment',
    'notes' => 'Notes',
    'question_paper' => 'Question Paper',
    'ppt' => 'Daily PPT',
    'syllabus' => 'Syllabus'
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
$classes_to_show = !empty($assigned_classes) ? $assigned_classes : array_keys($class_names);

// Ensure the current material's class is preserved in the dropdown even if not in assigned
if (!empty($material['class_name']) && !in_array(strval($material['class_name']), $classes_to_show)) {
    $classes_to_show[] = strval($material['class_name']);
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
    padding: 1.5rem;
}

/* Form Controls */
.form-label-clean {
    font-size: 0.85rem;
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
    font-size: 0.9rem;
    font-weight: 500;
    color: #1e293b;
    padding: 0.6rem 0.9rem;
    height: auto;
    transition: all 0.2s ease;
    width: 100%;
}

.form-control-clean:focus {
    border-color: #5e48eb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12);
    background: #ffffff;
}

/* Primary Action Button */
.btn-primary-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.65rem 1.8rem;
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

.btn-cancel-link {
    background: #ffffff;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 0.65rem 1.4rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    transition: all 0.2s ease;
    gap: 6px;
}

.btn-cancel-link:hover {
    background: #f8fafc;
    color: #0f172a !important;
    border-color: #cbd5e1;
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

/* File Upload Box */
.file-upload-box {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 1.25rem;
    background: #f8fafc;
    transition: all 0.2s ease;
}

.file-upload-box:hover {
    border-color: #5e48eb;
    background: #fafaff;
}

/* Custom switch */
.form-check-input {
    width: 2.2em;
    height: 1.2em;
    cursor: pointer;
}
.form-check-input:checked {
    background-color: #5e48eb;
    border-color: #5e48eb;
}

@media (max-width: 768px) {
    .pca-card-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem;
    }
    .pca-card-header .btn-view-link,
    .btn-primary-action,
    .btn-cancel-link {
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
                <i class="fa-solid fa-pen-to-square"></i>
                Edit Study Material
            </h1>
            <a href="view-study-material.php" class="btn-view-link">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Materials
            </a>
        </div>

        <div class="pca-card-body">
            <form action="teacher_code.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="material_id" value="<?= htmlspecialchars($material['id']) ?>">

                <div class="row g-3">
                    <!-- Teacher Name -->
                    <div class="col-md-6 col-12">
                        <label for="teacher_name" class="form-label-clean">
                            <i class="fa-solid fa-user-tie text-primary me-1"></i> Teacher Name: *
                        </label>
                        <input type="text" name="teacher_name" id="teacher_name" value="<?= htmlspecialchars($material['teacher_name']) ?>" class="form-control form-control-clean" required>
                    </div>

                    <!-- Target Class -->
                    <div class="col-md-6 col-12">
                        <label for="class_name" class="form-label-clean">
                            <i class="fa-solid fa-graduation-cap text-primary me-1"></i> Class: *
                        </label>
                        <select name="class_name" id="class_name" class="form-control form-control-clean" required>
                            <option value="">-- Select Class --</option>
                            <?php
                            foreach ($classes_to_show as $code) {
                                $c_name = $class_names[$code] ?? ('Class ' . $code);
                                $selected = ($material['class_name'] == $code) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($code) . "' {$selected}>" . htmlspecialchars($c_name) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Subject -->
                    <div class="col-md-6 col-12">
                        <label for="subject" class="form-label-clean">
                            <i class="fa-solid fa-book-open text-primary me-1"></i> Subject: *
                        </label>
                        <input type="text" name="subject" id="subject" value="<?= htmlspecialchars($material['subject']) ?>" class="form-control form-control-clean" required>
                    </div>

                    <!-- Material Type -->
                    <div class="col-md-6 col-12">
                        <label for="material_type" class="form-label-clean">
                            <i class="fa-solid fa-layer-group text-primary me-1"></i> Material Type: *
                        </label>
                        <select name="material_type" id="material_type" class="form-control form-control-clean" required>
                            <?php
                            foreach ($types as $key => $val) {
                                $selected = ($material['material_type'] == $key) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($key) . "' {$selected}>" . htmlspecialchars($val) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Title -->
                    <div class="col-12">
                        <label for="title" class="form-label-clean">
                            <i class="fa-solid fa-heading text-primary me-1"></i> Material Title: *
                        </label>
                        <input type="text" name="title" id="title" value="<?= htmlspecialchars($material['title']) ?>" class="form-control form-control-clean" required>
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <label for="description" class="form-label-clean">
                            <i class="fa-solid fa-align-left text-primary me-1"></i> Description:
                        </label>
                        <textarea name="description" id="description" class="form-control form-control-clean" rows="3"><?= htmlspecialchars($material['description']) ?></textarea>
                    </div>

                    <!-- File Upload -->
                    <div class="col-12">
                        <label for="file" class="form-label-clean">
                            <i class="fa-solid fa-file-arrow-up text-primary me-1"></i> Replace File (Optional):
                        </label>
                        <div class="file-upload-box">
                            <input type="file" name="file" id="file" accept=".pdf,.jpg,.jpeg,.png,.ppt,.pptx" class="form-control form-control-clean mb-2">
                            <?php if (!empty($material['file_path'])): ?>
                                <div class="d-flex align-items-center gap-2 mt-2">
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <i class="fa-solid fa-paperclip text-muted me-1"></i> Current File:
                                    </span>
                                    <a href="<?= htmlspecialchars($material['file_path']) ?>" target="_blank" class="fw-semibold text-primary text-decoration-none">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Existing Document
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-12">
                        <div class="form-check form-switch ps-0 d-flex align-items-center gap-3">
                            <input class="form-check-input ms-0" type="checkbox" name="status" id="materialStatus" value="1" <?= ($material['status'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold text-dark mb-0" for="materialStatus">
                                Active (Visible to students)
                            </label>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="col-12 pt-3 border-top d-flex align-items-center gap-2">
                        <button type="submit" name="update_material" class="btn-primary-action">
                            <i class="fa-solid fa-floppy-disk"></i> Update Material
                        </button>
                        <a href="view-study-material.php" class="btn-cancel-link">
                            <i class="fa-solid fa-xmark"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
