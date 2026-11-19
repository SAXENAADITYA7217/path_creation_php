<?php
include('authentication.php');
include('includes/header.php');

$classMapping = [
    '100' => 'Nursery', '101' => 'LKG', '102' => 'UKG',
    '1' => 'Class 1', '2' => 'Class 2', '3' => 'Class 3', '4' => 'Class 4', '5' => 'Class 5',
    '6' => 'Class 6', '7' => 'Class 7', '8' => 'Class 8', '9' => 'Class 9', '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)', '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm. (With math)', '14' => 'Class 11 Comm. (Without math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)', '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (With math)', '19' => 'Class 12 Comm. (Without math)',
    '20' => 'Class 12 Arts'
];

$preselected_teacher_id = isset($_GET['teacher_id']) ? intval($_GET['teacher_id']) : 0;
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;

$edit_record = null;
$is_edit_mode = false;

if ($edit_id > 0) {
    $stmt = mysqli_prepare($con, "SELECT id, teacher_id, class_code, subject FROM teacher_subject WHERE id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && mysqli_num_rows($res) > 0) {
            $edit_record = mysqli_fetch_assoc($res);
            $is_edit_mode = true;
            $preselected_teacher_id = intval($edit_record['teacher_id']);
        }
        mysqli_stmt_close($stmt);
    }
}

// Pre-load subjects for initial class in edit mode
$edit_subjects = [];
if ($is_edit_mode && !empty($edit_record['class_code'])) {
    $cc_esc = mysqli_real_escape_string($con, $edit_record['class_code']);
    $res_sub = mysqli_query($con, "SELECT subject FROM class_subject_info WHERE class_code = '$cc_esc' AND status = 1 ORDER BY subject ASC");
    if ($res_sub) {
        while ($s_row = mysqli_fetch_assoc($res_sub)) {
            $edit_subjects[] = $s_row['subject'];
        }
    }
    if (!in_array($edit_record['subject'], $edit_subjects)) {
        $edit_subjects[] = $edit_record['subject'];
    }
}
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="view-teacher.php">Teachers</a></li>
        <li class="breadcrumb-item"><a href="view-assign-subject.php<?= ($preselected_teacher_id > 0) ? '?teacher_id=' . $preselected_teacher_id : ''; ?>">Assigned Subjects</a></li>
        <li class="breadcrumb-item active"><?= $is_edit_mode ? 'Edit Subject' : 'Assign Subjects'; ?></li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="<?= $is_edit_mode ? 'fas fa-pencil-alt' : 'fas fa-book-open'; ?>"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1"><?= $is_edit_mode ? 'Edit Assigned Subject' : 'Assign Teacher Subjects'; ?></h2>
                <p class="page-sub-heading mb-0">
                    <?= $is_edit_mode ? 'Modify the assigned class or subject for this teacher.' : 'Assign multiple classes and subjects to a teacher.'; ?>
                </p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="view-assign-subject.php<?= ($preselected_teacher_id > 0) ? '?teacher_id=' . $preselected_teacher_id : ''; ?>" class="btn-outline-back">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
            <?php if (!$is_edit_mode): ?>
                <button type="button" class="btn-secondary-action" id="addRowBtn">
                    <i class="fas fa-plus me-1"></i> Add Row
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php include('message.php'); ?>

    <?php if ($is_edit_mode): ?>
        <!-- ==========================================
             EDIT MODE: Single Subject Assignment Form
             ========================================== -->
        <div class="teacher-card">
            <form action="code.php" method="POST" id="editTeacherSubjectForm">
                <input type="hidden" name="assign_id" value="<?= $edit_record['id']; ?>">
                <input type="hidden" name="teacher_id" value="<?= $edit_record['teacher_id']; ?>">

                <!-- Teacher Display (Readonly) -->
                <div class="mb-4">
                    <label class="form-label-custom">Teacher</label>
                    <div class="input-icon-wrapper" style="max-width: 500px;">
                        <i class="far fa-user input-icon"></i>
                        <?php
                        $t_stmt = mysqli_prepare($con, "SELECT fname, lname FROM users WHERE id = ? LIMIT 1");
                        $t_name = "Teacher #" . $edit_record['teacher_id'];
                        if ($t_stmt) {
                            mysqli_stmt_bind_param($t_stmt, "i", $edit_record['teacher_id']);
                            mysqli_stmt_execute($t_stmt);
                            $t_res = mysqli_stmt_get_result($t_stmt);
                            if ($t_res && ($t_row = mysqli_fetch_assoc($t_res))) {
                                $t_name = trim($t_row['fname'] . ' ' . $t_row['lname']);
                            }
                            mysqli_stmt_close($t_stmt);
                        }
                        ?>
                        <input type="text" class="form-control custom-input" value="<?= htmlspecialchars($t_name); ?>" readonly style="background-color: #f8fafc; font-weight: 600;">
                    </div>
                </div>

                <div class="assign-classes-box mb-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="assign-classes-icon-box">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <h5 class="assign-classes-title mb-0">Class & Subject</h5>
                            <p class="assign-classes-subtitle mb-0">Select the updated class and corresponding subject.</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Class Select -->
                        <div class="col-md-6">
                            <label class="form-label-custom">Select Class <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <i class="fas fa-graduation-cap position-absolute dropdown-icon" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); z-index: 5;"></i>
                                <select name="class_code" id="editClassCode" class="form-select custom-class-select" required>
                                    <option value="">-- Select Class --</option>
                                    <?php foreach ($classMapping as $c_id => $c_name): ?>
                                        <?php $c_sel = ((string)$c_id === (string)$edit_record['class_code']) ? 'selected' : ''; ?>
                                        <option value="<?= $c_id; ?>" <?= $c_sel; ?>><?= htmlspecialchars($c_name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Subject Select -->
                        <div class="col-md-6">
                            <label class="form-label-custom">Select Subject <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <i class="fas fa-book position-absolute dropdown-icon" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); z-index: 5;"></i>
                                <select name="subject" id="editSubjectSelect" class="form-select custom-class-select" required>
                                    <option value="">-- Select Subject --</option>
                                    <?php foreach ($edit_subjects as $s_item): ?>
                                        <?php $s_sel = ($s_item === $edit_record['subject']) ? 'selected' : ''; ?>
                                        <option value="<?= htmlspecialchars($s_item); ?>" <?= $s_sel; ?>><?= htmlspecialchars($s_item); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" name="update_subject_teacher" class="btn-primary-action">
                        <i class="far fa-save me-2"></i> Update Subject
                    </button>
                    <a href="view-assign-subject.php?teacher_id=<?= $edit_record['teacher_id']; ?>" class="btn-secondary-action">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

    <?php else: ?>
        <!-- ==========================================
             ADD MODE: Multiple Subject Assignment Form
             ========================================== -->
        <div class="teacher-card">
            <form action="code.php" method="POST" id="teacherForm">
                <!-- Teacher Select -->
                <div class="mb-4">
                    <label class="form-label-custom">Select Teacher <span class="text-danger">*</span></label>
                    <div class="input-icon-wrapper" style="max-width: 450px;">
                        <i class="far fa-user input-icon"></i>
                        <select name="teacher_id" class="form-select select-with-icon" id="teacherSelect" style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;" required>
                            <option value="">-- Select Teacher --</option>
                            <?php
                            $teacher_query = "SELECT id, fname, lname, email FROM users WHERE role_as = '2' ORDER BY fname ASC";
                            $teacher_result = mysqli_query($con, $teacher_query);

                            if ($teacher_result && mysqli_num_rows($teacher_result) > 0) {
                                while ($teacher = mysqli_fetch_assoc($teacher_result)) {
                                    $t_full = trim(($teacher['fname'] ?? '') . ' ' . ($teacher['lname'] ?? ''));
                                    if (empty($t_full)) {
                                        $t_full = !empty($teacher['email']) ? $teacher['email'] : ('Teacher #' . $teacher['id']);
                                    }
                                    $selected = ($preselected_teacher_id === intval($teacher['id'])) ? 'selected' : '';
                                    echo "<option value='{$teacher['id']}' {$selected}>" . htmlspecialchars($t_full) . "</option>";
                                }
                            } else {
                                echo "<option value=''>No Teachers Found</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Already Assigned Subjects Section -->
                <div id="alreadyAssignedBox" class="mb-4 d-none">
                    <div class="assign-classes-box p-3" style="background: #fbfbfe; border: 1.5px dashed #c7d2fe;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-book-reader text-primary fs-5"></i>
                                <span class="fw-bold text-dark" style="font-size: 0.92rem;">Already Assigned Subjects for this Teacher</span>
                            </div>
                            <span id="assignedCountBadge" class="badge bg-light text-primary border" style="font-size: 0.82rem; font-weight: 700;">0 Subject(s)</span>
                        </div>
                        <div id="alreadyAssignedContent">
                            <div class="text-center py-2 text-muted small">
                                <i class="fas fa-spinner fa-spin me-1"></i> Loading assigned subjects...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Rows Container -->
                <div id="teacherContainer" class="mb-4">
                    <!-- Class & Subject Row Template -->
                    <div class="teacher-row assign-classes-box mb-3 position-relative">
                        <button type="button" class="btn-remove-teacher position-absolute top-0 end-0 m-3 remove-btn d-none" title="Remove Row">
                            <i class="fas fa-times"></i>
                        </button>

                        <div class="row g-3">
                            <!-- Class -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Select Class <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <i class="fas fa-graduation-cap position-absolute dropdown-icon" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); z-index: 5;"></i>
                                    <select name="class_code[]" class="form-select custom-class-select class_code" required>
                                        <option value="">-- Select Class --</option>
                                        <?php foreach ($classMapping as $c_id => $c_name): ?>
                                            <option value="<?= $c_id; ?>"><?= htmlspecialchars($c_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Subject -->
                            <div class="col-md-6">
                                <label class="form-label-custom">Select Subject <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <i class="fas fa-book position-absolute dropdown-icon" style="left: 14px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); z-index: 5;"></i>
                                    <select name="subject[]" class="form-select custom-class-select subject" required disabled>
                                        <option value="">-- Select Class First --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <button type="submit" name="add_subject_teachers" class="btn-primary-action">
                        <i class="far fa-save me-2"></i> Save All
                    </button>
                    <a href="view-assign-subject.php<?= ($preselected_teacher_id > 0) ? '?teacher_id=' . $preselected_teacher_id : ''; ?>" class="btn-secondary-action" id="viewAssignedBtn">
                        <i class="fas fa-eye me-2"></i> View Assigned Subjects
                    </a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    let isEditMode = <?= $is_edit_mode ? 'true' : 'false'; ?>;

    if (isEditMode) {
        // Edit Mode Class Change Listener
        $("#editClassCode").on("change", function () {
            let class_code = $(this).val();
            let teacherId = "<?= $edit_record ? $edit_record['teacher_id'] : 0; ?>";
            let $subSelect = $("#editSubjectSelect");

            if (!class_code) {
                $subSelect.html('<option value="">-- Select Class First --</option>').prop("disabled", true);
                return;
            }

            $subSelect.html('<option value="">Loading Subjects...</option>').prop("disabled", true);

            $.ajax({
                url: "load_subject.php",
                type: "POST",
                dataType: "json",
                data: {
                    action: "load_subjects",
                    class_code: class_code,
                    teacher_id: teacherId
                },
                success: function (data) {
                    if (data && data.status === 'success' && data.all_subjects && data.all_subjects.length > 0) {
                        let html = '<option value="">-- Select Subject --</option>';
                        data.all_subjects.forEach(function (subj) {
                            html += '<option value="' + $('<div>').text(subj).html() + '">' + $('<div>').text(subj).html() + '</option>';
                        });
                        $subSelect.html(html).prop("disabled", false);
                    } else {
                        $subSelect.html('<option value="">-- No Subjects in this Class --</option>').prop("disabled", true);
                    }
                },
                error: function () {
                    $subSelect.html('<option value="">-- Error Loading Subjects --</option>').prop("disabled", true);
                }
            });
        });

        return; // Don't bind Add Mode scripts in Edit mode
    }

    // ==========================================
    // ADD MODE SCRIPTS
    // ==========================================
    let classSubjectsCache = {};
    let teacherDbCache = {};

    function getTeacherId() {
        return $("#teacherSelect").val() || 0;
    }

    function loadTeacherAssignedSubjects() {
        let teacherId = getTeacherId();
        let $box = $("#alreadyAssignedBox");
        let $content = $("#alreadyAssignedContent");
        let $badge = $("#assignedCountBadge");
        let $viewBtn = $("#viewAssignedBtn");

        if (!teacherId || teacherId == "0") {
            $box.addClass("d-none");
            $viewBtn.attr("href", "view-assign-subject.php");
            teacherDbCache = {};
            updateAllSubjectDropdowns();
            return;
        }

        $viewBtn.attr("href", "view-assign-subject.php?teacher_id=" + encodeURIComponent(teacherId));
        $box.removeClass("d-none");
        $content.html('<div class="text-center py-2 text-muted small"><i class="fas fa-spinner fa-spin me-1"></i> Loading assigned subjects...</div>');

        $.ajax({
            url: "load_subject.php",
            type: "POST",
            dataType: "json",
            data: {
                action: "get_teacher_assigned_subjects",
                teacher_id: teacherId
            },
            success: function (data) {
                teacherDbCache = {};
                if (data && data.status === 'success' && data.assigned && data.assigned.length > 0) {
                    $badge.text(data.assigned.length + " Subject(s)");

                    data.assigned.forEach(function (item) {
                        let c = String(item.class_code);
                        if (!teacherDbCache[c]) teacherDbCache[c] = [];
                        if (!teacherDbCache[c].includes(item.subject)) {
                            teacherDbCache[c].push(item.subject);
                        }
                    });

                    let html = '<div class="table-responsive" style="max-height: 220px; overflow-y: auto;">';
                    html += '<table class="table table-sm table-bordered mb-0 text-center align-middle" style="background:#fff;">';
                    html += '<thead class="users-table-header sticky-top"><tr><th style="width: 50px;">#</th><th>Class</th><th>Assigned Subject</th></tr></thead><tbody>';

                    data.assigned.forEach(function (item, index) {
                        html += '<tr class="users-table-row">';
                        html += '<td class="fw-bold text-muted">' + (index + 1) + '</td>';
                        html += '<td><span class="badge-class-pill text-nowrap">' + $('<div>').text(item.class_name).html() + '</span></td>';
                        html += '<td class="fw-bold text-dark">' + $('<div>').text(item.subject).html() + '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    $content.html(html);
                } else {
                    $badge.text("0 Subjects");
                    $content.html('<div class="text-muted p-2 text-center small">No subjects currently assigned to this teacher yet.</div>');
                }

                updateAllSubjectDropdowns();
            },
            error: function () {
                $content.html('<div class="text-danger p-2 text-center small">Failed to load assigned subjects.</div>');
            }
        });
    }

    function updateAllSubjectDropdowns() {
        $(".teacher-row").each(function () {
            let $row = $(this);
            let class_code = $row.find(".class_code").val();
            let $subSelect = $row.find(".subject");
            let currentSubject = $subSelect.val();

            if (!class_code) {
                $subSelect.html('<option value="">-- Select Class First --</option>').prop("disabled", true);
                return;
            }

            let allSubjects = classSubjectsCache[class_code];
            let assignedInDb = teacherDbCache[String(class_code)] || [];

            if (!allSubjects) {
                loadSubjectsForClass(class_code, $row);
                return;
            }

            let takenInOtherRows = [];
            $(".teacher-row").not($row).each(function () {
                let otherClass = $(this).find(".class_code").val();
                let otherSub = $(this).find(".subject").val();
                if (otherClass === class_code && otherSub) {
                    takenInOtherRows.push(otherSub);
                }
            });

            let optionsHtml = '<option value="">-- Select Subject --</option>';
            let availableCount = 0;

            allSubjects.forEach(function (subj) {
                let isDbAssigned = assignedInDb.includes(subj);
                let isOtherRowTaken = takenInOtherRows.includes(subj);

                if (subj === currentSubject || (!isDbAssigned && !isOtherRowTaken)) {
                    let isSelected = (subj === currentSubject) ? 'selected' : '';
                    optionsHtml += '<option value="' + $('<div>').text(subj).html() + '" ' + isSelected + '>' + $('<div>').text(subj).html() + '</option>';
                    availableCount++;
                }
            });

            if (allSubjects.length === 0) {
                optionsHtml = '<option value="">-- No Subjects in this Class --</option>';
            } else if (availableCount === 0) {
                optionsHtml = '<option value="">-- All Subjects in this Class Assigned --</option>';
            }

            $subSelect.html(optionsHtml);
            $subSelect.prop("disabled", availableCount === 0 && !currentSubject);
            if (currentSubject) {
                $subSelect.val(currentSubject);
            }
        });
    }

    function loadSubjectsForClass(class_code, $triggerRow) {
        let teacherId = getTeacherId();
        let $subSelect = $triggerRow.find(".subject");

        $subSelect.html('<option value="">Loading Subjects...</option>').prop("disabled", true);

        $.ajax({
            url: "load_subject.php",
            type: "POST",
            dataType: "json",
            data: {
                action: "load_subjects",
                class_code: class_code,
                teacher_id: teacherId
            },
            success: function (data) {
                if (data && data.status === 'success') {
                    classSubjectsCache[class_code] = data.all_subjects || [];
                    if (data.assigned_in_db) {
                        let c = String(class_code);
                        if (!teacherDbCache[c]) teacherDbCache[c] = [];
                        data.assigned_in_db.forEach(function(s) {
                            if (!teacherDbCache[c].includes(s)) teacherDbCache[c].push(s);
                        });
                    }
                } else {
                    classSubjectsCache[class_code] = [];
                }
                updateAllSubjectDropdowns();
            },
            error: function () {
                $subSelect.html('<option value="">-- Error Loading Subjects --</option>').prop("disabled", true);
            }
        });
    }

    $("#teacherSelect").on("change", function () {
        loadTeacherAssignedSubjects();
    });

    $(document).on("change", ".class_code", function () {
        let class_code = $(this).val();
        let $row = $(this).closest(".teacher-row");
        $row.find(".subject").val("");

        if (class_code) {
            if (classSubjectsCache[class_code]) {
                updateAllSubjectDropdowns();
            } else {
                loadSubjectsForClass(class_code, $row);
            }
        } else {
            $row.find(".subject").html('<option value="">-- Select Class First --</option>').prop("disabled", true);
            updateAllSubjectDropdowns();
        }
    });

    $(document).on("change", ".subject", function () {
        updateAllSubjectDropdowns();
    });

    $("#addRowBtn").on("click", function () {
        let $clone = $(".teacher-row:first").clone();
        $clone.find("select").val("");
        $clone.find(".subject").html('<option value="">-- Select Class First --</option>').prop("disabled", true);
        $clone.find(".remove-btn").removeClass("d-none");
        $("#teacherContainer").append($clone);
        updateAllSubjectDropdowns();
    });

    $(document).on("click", ".remove-btn", function () {
        $(this).closest(".teacher-row").remove();
        updateAllSubjectDropdowns();
    });

    if ($("#teacherSelect").val()) {
        loadTeacherAssignedSubjects();
    }
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
