<?php
// FILE: view-study-material.php - Modern Study Material Management
include('authentication.php');
include('includes/header.php');

// Get logged-in teacher details
$teacher_name = $_SESSION['auth_user']['user_name'] ?? $_SESSION['auth_user']['fname'] ?? '';
$user_id = $_SESSION['auth_user']['user_id'] ?? $_SESSION['auth_user']['id'] ?? '';
$teacher_full_name = $teacher_name;
$teacher_fname = $teacher_name;

if (!empty($user_id)) {
    $u_res = mysqli_query($con, "SELECT fname, lname FROM users WHERE id='$user_id' LIMIT 1");
    if ($u_res && $u_row = mysqli_fetch_assoc($u_res)) {
        $teacher_fname = trim($u_row['fname']);
        $teacher_full_name = trim($u_row['fname'] . ' ' . ($u_row['lname'] ?? ''));
    }
}

// Class names mapping
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

// Material types
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

$selected_class = $_GET['class_name'] ?? '';
$selected_type  = $_GET['material_type'] ?? '';
?>

<!-- FontAwesome & Plus Jakarta Sans Font for exact theme match -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* Prevent global horizontal scroll */
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
    font-size: 1.18rem;
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

@media (max-width: 768px) {
    .pca-card-body {
        padding: 0.9rem;
    }
    .pca-card-header {
        padding: 1rem;
    }
}

/* Filter Box */
.filter-box-clean {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.2rem;
    margin-bottom: 1.5rem;
}

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

.btn-back-link {
    background: #ffffff;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.86rem;
    padding: 0.55rem 1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none !important;
    transition: all 0.2s ease;
    height: 42px;
}

.btn-back-link:hover {
    background: #f8fafc;
    color: #0f172a !important;
    border-color: #cbd5e1;
}

/* Badges & Pills */
.badge-ctx-pill {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #334155;
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.3rem 0.7rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
}

.type-pill {
    font-size: 0.76rem;
    font-weight: 700;
    padding: 0.28rem 0.65rem;
    border-radius: 6px;
    display: inline-block;
    white-space: nowrap;
}

.type-notes { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
.type-assignment { background: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff; }
.type-question_paper { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.type-syllabus { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.type-ppt { background: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }

/* Status Capsule */
.score-pill {
    font-weight: 700;
    font-size: 0.78rem;
    padding: 0.32rem 0.75rem;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap !important;
    line-height: 1.2;
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

/* File Pills */
.btn-file-pill {
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.3rem 0.7rem;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none !important;
    transition: all 0.15s ease;
    white-space: nowrap;
}

.btn-pdf {
    background: #fef2f2;
    color: #dc2626 !important;
    border: 1px solid #fecaca;
}
.btn-pdf:hover { background: #fee2e2; }

.btn-ppt {
    background: #fff7ed;
    color: #c2410c !important;
    border: 1px solid #ffedd5;
}
.btn-ppt:hover { background: #ffedd5; }

.btn-other {
    background: #f1f5f9;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
}
.btn-other:hover { background: #e2e8f0; }

/* Table Container with Native Smooth Touch Scrolling */
.table-responsive {
    overflow-x: auto !important;
    overflow-y: visible !important;
    width: 100% !important;
    border: 1px solid #edf0f5;
    border-radius: 12px;
    background: #ffffff;
    -webkit-overflow-scrolling: touch;
}

/* Custom Scrollbar for Smooth UX */
.table-responsive::-webkit-scrollbar {
    height: 7px;
}
.table-responsive::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Fluid Table (Never squishes, ample column width) */
.pca-table {
    margin-bottom: 0;
    width: 100% !important;
    min-width: 980px;
    border-collapse: collapse;
}

.pca-table thead th {
    background-color: #f8fafc !important;
    color: #475569 !important;
    font-size: 0.76rem !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.95rem 0.85rem !important;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top: none !important;
    white-space: nowrap !important;
    overflow: visible !important;
}

.pca-table tbody td {
    padding: 0.95rem 0.85rem !important;
    vertical-align: middle !important;
    border-bottom: 1px solid #f1f5f9 !important;
    font-size: 0.88rem;
    color: #1e293b;
    background: #ffffff;
    overflow: visible !important;
}

.pca-table tbody tr:hover td {
    background-color: #fbfbfe !important;
}

.pca-table tbody tr:last-child td {
    border-bottom: none !important;
}

/* Column Width Specifications (Guaranteed zero truncation) */
.th-class, .td-class {
    min-width: 110px;
    white-space: nowrap !important;
}

.th-subject, .td-subject {
    min-width: 110px;
    white-space: nowrap !important;
}

.th-type, .td-type {
    min-width: 115px;
    white-space: nowrap !important;
}

.th-title, .td-title {
    min-width: 170px;
    max-width: 250px;
}

.th-desc, .td-desc {
    min-width: 190px;
    max-width: 280px;
}

.th-file, .td-file {
    min-width: 90px;
    text-align: center !important;
    white-space: nowrap !important;
}

.th-status, .td-status {
    min-width: 110px;
    text-align: center !important;
    white-space: nowrap !important;
    overflow: visible !important;
}

.th-actions, .td-actions {
    min-width: 110px;
    text-align: center !important;
    white-space: nowrap !important;
    overflow: visible !important;
}

/* Action Icon Buttons */
.btn-icon-action {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none !important;
    flex-shrink: 0;
}

.btn-icon-action.btn-edit {
    background: #eff6ff;
    color: #2563eb !important;
    border: 1px solid #dbeafe;
}
.btn-icon-action.btn-edit:hover {
    background: #2563eb;
    color: #ffffff !important;
    box-shadow: 0 3px 8px rgba(37, 99, 235, 0.3);
    transform: translateY(-1px);
}

.btn-icon-action.btn-del {
    background: #fef2f2;
    color: #dc2626 !important;
    border: 1px solid #fee2e2;
}
.btn-icon-action.btn-del:hover {
    background: #dc2626;
    color: #ffffff !important;
    box-shadow: 0 3px 8px rgba(220, 38, 38, 0.3);
    transform: translateY(-1px);
}

/* Empty State */
.empty-state-clean {
    padding: 3.5rem 1.5rem;
    background: #fafbfe;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
}

.empty-icon-circle {
    width: 60px;
    height: 60px;
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
                    <i class="fa-solid fa-folder-open"></i>
                    My Study Materials
                </h1>
                <small class="text-muted d-block mt-1" style="font-size: 0.82rem;">
                    Upload, view, and organize learning resources for your assigned classes.
                </small>
            </div>
            <a href="studymaterial.php" class="btn-primary-action">
                <i class="fas fa-plus"></i> Add Material
            </a>
        </div>

        <div class="pca-card-body">

            <!-- Filter Form -->
            <form method="GET" class="filter-box-clean">
                <div class="row g-3 align-items-end">
                    <div class="col-lg-4 col-md-4 col-12">
                        <label class="form-label-clean">
                            <i class="fa-solid fa-graduation-cap text-primary me-1"></i> Class:
                        </label>
                        <select name="class_name" class="form-control form-control-clean">
                            <option value="">-- All Classes --</option>
                            <?php
                            foreach ($classes_to_show as $code) {
                                $name = $class_names[$code] ?? ('Class ' . $code);
                                $selected = ($selected_class == $code) ? 'selected' : '';
                                echo "<option value='{$code}' {$selected}>{$name}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-4 col-12">
                        <label class="form-label-clean">
                            <i class="fa-solid fa-layer-group text-primary me-1"></i> Material Type:
                        </label>
                        <select name="material_type" class="form-control form-control-clean">
                            <option value="">-- All Types --</option>
                            <?php
                            foreach ($types as $key => $val) {
                                $selected = ($selected_type == $key) ? 'selected' : '';
                                echo "<option value='{$key}' {$selected}>{$val}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-4 col-12">
                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn-primary-action flex-grow-1" style="white-space: nowrap !important;">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                            <?php if (!empty($selected_class) || !empty($selected_type)): ?>
                                <a href="view-study-material.php" class="btn-back-link text-nowrap" title="Reset Filters">
                                    <i class="fas fa-rotate-right me-1"></i> Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Responsive swipe hint on small viewports -->
            <div class="d-md-none text-muted mb-2 ps-1" style="font-size: 0.78rem;">
                <i class="fa-solid fa-arrows-left-right text-primary me-1"></i> Swipe table horizontally to view full details
            </div>

            <!-- Materials Table -->
            <div class="table-responsive">
                <table class="pca-table">
                    <thead>
                        <tr>
                            <th class="th-class">Class</th>
                            <th class="th-subject">Subject</th>
                            <th class="th-type">Type</th>
                            <th class="th-title">Title</th>
                            <th class="th-desc">Description</th>
                            <th class="th-file">File</th>
                            <th class="th-status">Status</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // Base query (matches teacher by user_name, full name, or first name)
                    $query = "SELECT * FROM study_materials WHERE (teacher_name = ? OR teacher_name = ? OR teacher_name = ?)";
                    $params = [$teacher_name, $teacher_full_name, $teacher_fname];
                    $types_str = "sss";

                    // Add optional filters
                    if (!empty($selected_class)) {
                        $query .= " AND class_name = ?";
                        $params[] = $selected_class;
                        $types_str .= "s";
                    }

                    if (!empty($selected_type)) {
                        $query .= " AND material_type = ?";
                        $params[] = $selected_type;
                        $types_str .= "s";
                    }

                    $query .= " ORDER BY id DESC";

                    $stmt = $con->prepare($query);
                    if ($stmt) {
                        if (count($params) > 0) {
                            $stmt->bind_param($types_str, ...$params);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();
                    } else {
                        $result = false;
                    }

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $class_display = $class_names[$row['class_name']] ?? $row['class_name'];
                            $type_key = $row['material_type'];
                            $type_display = $types[$type_key] ?? $type_key;
                            $filePath = htmlspecialchars($row['file_path']);
                            $type_css = 'type-' . strtolower(str_replace(' ', '_', $type_key));
                    ?>
                        <tr>
                            <td class="td-class">
                                <span class="badge-ctx-pill">
                                    <i class="fa-solid fa-graduation-cap text-primary me-1"></i> <?= htmlspecialchars($class_display) ?>
                                </span>
                            </td>
                            <td class="td-subject">
                                <span class="fw-semibold text-dark"><?= htmlspecialchars($row['subject']) ?></span>
                            </td>
                            <td class="td-type">
                                <span class="type-pill <?= $type_css ?>"><?= htmlspecialchars($type_display) ?></span>
                            </td>
                            <td class="td-title">
                                <strong class="text-dark d-block" style="font-size: 0.88rem; word-break: break-word;" title="<?= htmlspecialchars($row['title']) ?>">
                                    <?= htmlspecialchars($row['title']) ?>
                                </strong>
                            </td>
                            <td class="td-desc">
                                <span class="text-muted d-block" style="font-size: 0.82rem; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word;" title="<?= htmlspecialchars($row['description']) ?>">
                                    <?= htmlspecialchars($row['description']) ?: '&mdash;' ?>
                                </span>
                            </td>
                            <td class="td-file">
                                <?php if ($row['file_type'] == 'image'): ?>
                                    <a href="<?= $filePath ?>" target="_blank" class="d-inline-block" title="View Full Image">
                                        <img src="<?= $filePath ?>" width="40" height="40" style="object-fit:cover; border-radius:8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 4px rgba(0,0,0,0.06);">
                                    </a>
                                <?php elseif ($row['file_type'] == 'pdf'): ?>
                                    <a href="<?= $filePath ?>" target="_blank" class="btn-file-pill btn-pdf" title="Download / Open PDF">
                                        <i class="fa-solid fa-file-pdf"></i> PDF
                                    </a>
                                <?php elseif ($row['file_type'] == 'ppt'): ?>
                                    <a href="<?= $filePath ?>" target="_blank" class="btn-file-pill btn-ppt" title="Download / Open PPT">
                                        <i class="fa-solid fa-file-powerpoint"></i> PPT
                                    </a>
                                <?php else: ?>
                                    <a href="<?= $filePath ?>" target="_blank" class="btn-file-pill btn-other" title="View / Download File">
                                        <i class="fa-solid fa-download"></i> View
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="td-status">
                                <?php if ($row['status'] == 1): ?>
                                    <span class="score-pill score-pill-high">
                                        <i class="fa-solid fa-circle-check"></i> Active
                                    </span>
                                <?php else: ?>
                                    <span class="score-pill score-pill-low">
                                        <i class="fa-solid fa-circle-xmark"></i> Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="td-actions">
                                <div class="d-inline-flex align-items-center justify-content-center gap-2">
                                    <a href="edit-study-material.php?id=<?= $row['id'] ?>" class="btn-icon-action btn-edit" title="Edit Material">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="teacher_code.php?delete_material_id=<?= $row['id'] ?>"
                                       onclick="return confirm('Are you sure you want to delete this study material?');"
                                       class="btn-icon-action btn-del" title="Delete Material">
                                       <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                    ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state-clean text-center my-3">
                                    <div class="empty-icon-circle mx-auto mb-3">
                                        <i class="fa-solid fa-folder-open text-muted fa-lg"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">No study materials uploaded yet</h6>
                                    <p class="text-muted mb-3" style="font-size: 0.85rem;">Upload files, assignments, notes, or PPTs for your students.</p>
                                    <a href="studymaterial.php" class="btn-primary-action btn-sm">
                                        <i class="fas fa-plus"></i> Add Material
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
