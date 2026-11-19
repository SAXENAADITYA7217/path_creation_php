<?php
include('authentication.php');
include('includes/header.php');

$selected_teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : 0;

// Class Code to Name Mapping
$class_map = [
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

$teacher_name = '';
$total_assigned = 0;
$assigned_result = null;

if ($selected_teacher_id > 0) {
    // Fetch Teacher details
    $teacher_sql = "SELECT id, user_id, fname, lname FROM users WHERE id = '$selected_teacher_id' LIMIT 1";
    $teacher_res = mysqli_query($con, $teacher_sql);
    if ($teacher_res && mysqli_num_rows($teacher_res) > 0) {
        $teacher_info = mysqli_fetch_assoc($teacher_res);
        $teacher_name = trim($teacher_info['fname'] . ' ' . $teacher_info['lname']);

        $user_id_val = $teacher_info['user_id'] ?? '';
        $whereClause = "teacher_id = '$selected_teacher_id'";
        if (!empty($user_id_val) && $user_id_val !== (string)$selected_teacher_id) {
            $user_id_esc = mysqli_real_escape_string($con, $user_id_val);
            $whereClause .= " OR teacher_id = '$user_id_esc'";
        }

        $query = "SELECT id, subject, class_code FROM teacher_subject WHERE $whereClause ORDER BY class_code ASC, id ASC";
        $assigned_result = mysqli_query($con, $query);
        $total_assigned = ($assigned_result) ? mysqli_num_rows($assigned_result) : 0;
    } else {
        $teacher_name = "Unknown Teacher";
    }
}
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="view-teacher.php">Teachers</a></li>
        <li class="breadcrumb-item active">Assigned Subjects</li>
    </ol>

    <!-- Page Header matching reference screenshot -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-book-open"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Teacher Assigned Subjects</h2>
                <p class="page-sub-heading mb-0">View and manage subjects assigned to a teacher.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="view-teacher.php" class="btn-outline-back">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
            <a href="add-teacher-subject.php<?= ($selected_teacher_id > 0) ? '?teacher_id=' . $selected_teacher_id : ''; ?>" class="btn-primary-action text-decoration-none">
                <i class="fas fa-plus me-2"></i> Assign Subjects
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- Teacher Selection Card matching screenshot -->
    <div class="teacher-card mb-4">
        <label for="teacher_id" class="form-label-custom">Select Teacher <span class="text-danger">*</span></label>
        <form method="GET" action="" class="d-flex flex-wrap align-items-center gap-3">
            <div class="position-relative" style="width: 420px; max-width: 100%;">
                <i class="far fa-user position-absolute" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                <select name="teacher_id" id="teacher_id" class="form-select select-with-icon" style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;" required onchange="this.form.submit()">
                    <option value="">-- Select Teacher --</option>
                    <?php
                    $teacher_query = "SELECT id, fname, lname, email FROM users WHERE role_as = '2' ORDER BY fname ASC";
                    $teacher_res = mysqli_query($con, $teacher_query);

                    if ($teacher_res && mysqli_num_rows($teacher_res) > 0) {
                        while ($t_row = mysqli_fetch_assoc($teacher_res)) {
                            $t_full = trim(($t_row['fname'] ?? '') . ' ' . ($t_row['lname'] ?? ''));
                            if (empty($t_full)) {
                                $t_full = !empty($t_row['email']) ? $t_row['email'] : ('Teacher #' . $t_row['id']);
                            }
                            $selected = ($selected_teacher_id == $t_row['id']) ? 'selected' : '';
                            echo "<option value='{$t_row['id']}' {$selected}>" . htmlspecialchars($t_full) . "</option>";
                        }
                    } else {
                        echo "<option value=''>No Teachers Found</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn-primary-action" style="height: 46px; padding: 0 1.6rem; border-radius: 12px;">
                <i class="far fa-eye me-2"></i> View
            </button>
        </form>
    </div>

    <!-- Assigned Subjects Lower Card matching screenshot -->
    <?php if ($selected_teacher_id > 0): ?>
        <div class="teacher-card p-0 overflow-hidden mb-4">
            <!-- Header Banner inside Card -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-4 border-bottom" style="background: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background: #ede9fe; color: var(--pca-primary); font-size: 1.2rem; flex-shrink: 0;">
                        <i class="far fa-user"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold" style="color: #0f1222; font-size: 1.15rem;">
                            Assigned Subjects for: <span style="color: var(--pca-primary); font-weight: 800;"><?= htmlspecialchars($teacher_name); ?></span>
                        </h5>
                        <p class="text-muted small mb-0 mt-1">Total <?= $total_assigned; ?> subjects assigned</p>
                    </div>
                </div>
                <div>
                    <a href="add-teacher-subject.php?teacher_id=<?= $selected_teacher_id; ?>" class="btn-outline-back" style="padding: 0.55rem 1.3rem; font-size: 0.88rem;">
                        <i class="fas fa-plus me-1"></i> Assign More Subjects
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table mb-0 text-nowrap align-middle">
                    <thead>
                        <tr>
                            <th class="users-table-header text-nowrap" style="width: 60px;">#</th>
                            <th class="users-table-header text-nowrap">SUBJECT</th>
                            <th class="users-table-header text-nowrap">CLASS</th>
                            <th class="users-table-header text-center text-nowrap" style="width: 200px;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($assigned_result && mysqli_num_rows($assigned_result) > 0) {
                            $count = 1;
                            while ($row = mysqli_fetch_assoc($assigned_result)) {
                                $class_name = isset($class_map[$row['class_code']]) ? $class_map[$row['class_code']] : "Class " . $row['class_code'];
                                ?>
                                <tr class="users-table-row">
                                    <td class="fw-bold text-muted text-nowrap"><?= $count++; ?></td>
                                    <td class="text-nowrap">
                                        <div class="d-flex align-items-center">
                                            <div class="subject-icon-box me-3">
                                                <i class="fas fa-book-open"></i>
                                            </div>
                                            <span class="fw-bold text-dark" style="font-size: 0.95rem;"><?= htmlspecialchars($row['subject']); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-nowrap">
                                        <span class="badge-class-pill text-nowrap" style="padding: 0.45rem 1rem; font-size: 0.85rem;">
                                            <i class="fas fa-layer-group me-1" style="font-size: 0.8rem;"></i>
                                            <?= htmlspecialchars($class_name); ?>
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <a href="add-teacher-subject.php?edit_id=<?= $row['id']; ?>&teacher_id=<?= $selected_teacher_id; ?>" class="btn-action-pill-edit text-nowrap" title="Edit Subject Assignment">
                                                <i class="fas fa-pencil-alt"></i> Edit
                                            </a>
                                            <form method="POST" action="code.php" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to delete this subject assignment?');">
                                                <input type="hidden" name="subject_id" value="<?= htmlspecialchars($row['id']); ?>">
                                                <input type="hidden" name="teacher_id" value="<?= htmlspecialchars($selected_teacher_id); ?>">
                                                <button type="submit" name="delete_assign-subject" class="btn-action-pill-delete text-nowrap" title="Delete Subject Assignment">
                                                    <i class="far fa-trash-alt"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fas fa-book-open fs-2 mb-3 d-block opacity-50"></i>
                                    <h6 class="fw-bold">No Subjects Assigned</h6>
                                    <p class="small mb-3">No subjects currently assigned to <strong><?= htmlspecialchars($teacher_name); ?></strong>.</p>
                                    <a href="add-teacher-subject.php?teacher_id=<?= $selected_teacher_id; ?>" class="btn-primary-action text-decoration-none">
                                        <i class="fas fa-plus me-1"></i> Assign Subjects
                                    </a>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="teacher-card text-center py-5">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: #ede9fe; color: var(--pca-primary); font-size: 1.5rem;">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Select a Teacher Above</h5>
            <p class="text-muted small mb-0">Choose a teacher from the dropdown to view and manage their assigned subjects.</p>
        </div>
    <?php endif; ?>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
