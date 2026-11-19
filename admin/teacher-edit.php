<?php
include('authentication.php'); 
include('includes/header.php'); 

$classMapping = [
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

$teacher = null;
$assigned_classes = [];

if (isset($_GET['id'])) {
    $teacher_id = mysqli_real_escape_string($con, $_GET['id']);
    $query = "SELECT * FROM users WHERE id='$teacher_id' AND role_as='2' LIMIT 1";
    $query_run = mysqli_query($con, $query);

    if ($query_run && mysqli_num_rows($query_run) > 0) {
        $teacher = mysqli_fetch_assoc($query_run);

        $t_user_id = $teacher['user_id'] ?? '';
        $t_pk = $teacher['id'] ?? '';

        if (!empty($t_user_id)) {
            $class_query = "SELECT class FROM class_teacher WHERE user_id = '$t_user_id'";
            $class_run = mysqli_query($con, $class_query);
            if ($class_run && mysqli_num_rows($class_run) > 0) {
                while ($c_row = mysqli_fetch_assoc($class_run)) {
                    $assigned_classes[] = (string)$c_row['class'];
                }
            }
        }

        if (empty($assigned_classes) && !empty($t_pk)) {
            $class_query = "SELECT class FROM class_teacher WHERE user_id = '$t_pk'";
            $class_run = mysqli_query($con, $class_query);
            if ($class_run && mysqli_num_rows($class_run) > 0) {
                while ($c_row = mysqli_fetch_assoc($class_run)) {
                    $assigned_classes[] = (string)$c_row['class'];
                }
            }
        }

        if (empty($assigned_classes) && !empty($teacher['class'])) {
            $assigned_classes[] = (string)$teacher['class'];
        }
    }
}
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="view-teacher.php">Teachers</a></li>
        <li class="breadcrumb-item active">Edit Teacher</li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-user-edit"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Edit Teacher Details</h2>
                <p class="page-sub-heading mb-0">Update teacher personal information and manage assigned classes.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="view-teacher.php" class="btn-outline-back">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
            <?php if ($teacher): ?>
                <a href="view-assign-subject.php?teacher_id=<?= $teacher['id']; ?>" class="btn-secondary-action">
                    <i class="fas fa-book-open me-2"></i> View Assigned Subjects
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php include('message.php'); ?>

    <?php if ($teacher): ?>
        <!-- Main Form Card -->
        <div class="teacher-card">
            <form action="code.php" method="POST">
                <input type="hidden" name="teacher_id" value="<?= $teacher['id']; ?>">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($teacher['user_id'] ?? ''); ?>">

                <!-- Teacher Personal Details (3 Columns) -->
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">First Name <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="far fa-user input-icon"></i>
                            <input type="text" name="fname" value="<?= htmlspecialchars($teacher['fname']); ?>" class="form-control custom-input" placeholder="Enter first name" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-custom">Father Name <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="far fa-user input-icon"></i>
                            <input type="text" name="father_name" value="<?= htmlspecialchars($teacher['father_name']); ?>" class="form-control custom-input" placeholder="Enter father name" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-custom">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-phone-alt input-icon"></i>
                            <input type="text" name="phone_no" value="<?= htmlspecialchars($teacher['phone_no']); ?>" class="form-control custom-input" placeholder="Enter 10-digit phone" required>
                        </div>
                    </div>
                </div>

                <!-- Account Status -->
                <div class="mb-4">
                    <div class="form-check form-switch d-inline-flex align-items-center">
                        <input class="form-check-input" type="checkbox" name="status" id="teacherStatus" style="width: 46px; height: 23px; cursor: pointer;" <?= $teacher['status'] == '1' ? 'checked' : ''; ?>>
                        <label class="form-check-label ms-2 fw-semibold text-dark" for="teacherStatus" style="cursor: pointer; font-size: 0.9rem;">
                            Inactive Account <span class="text-muted small fw-normal">(Checked = Inactive / Disable Access)</span>
                        </label>
                    </div>
                </div>

                <!-- Assigned Classes Section (3 Columns per Row) -->
                <div class="assign-classes-box class-box mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="assign-classes-icon-box">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h5 class="assign-classes-title mb-0">Assigned Classes</h5>
                            <p class="assign-classes-subtitle mb-0">Manage the classes assigned to this teacher (3 classes per row).</p>
                        </div>
                    </div>

                    <!-- 3-Column Responsive Grid Container -->
                    <div id="classContainer" class="class-selectors-container">
                        <?php if (!empty($assigned_classes)): ?>
                            <?php foreach ($assigned_classes as $current_class): ?>
                                <div class="class-select-item">
                                    <div class="class-dropdown-wrapper">
                                        <i class="fas fa-graduation-cap dropdown-icon"></i>
                                        <select name="class[]" class="form-select custom-class-select class-select" required>
                                            <option value="">-- Select Class --</option>
                                            <?php foreach ($classMapping as $c_id => $c_name): ?>
                                                <?php if ((string)$current_class === (string)$c_id || !in_array((string)$c_id, $assigned_classes)): ?>
                                                    <option value="<?= $c_id; ?>" <?= ((string)$current_class === (string)$c_id) ? 'selected' : ''; ?>>
                                                        <?= htmlspecialchars($c_name); ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <button type="button" class="btn-remove-class" onclick="removeClassRow(this)" title="Remove class">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="class-select-item">
                                <div class="class-dropdown-wrapper">
                                    <i class="fas fa-graduation-cap dropdown-icon"></i>
                                    <select name="class[]" class="form-select custom-class-select class-select">
                                        <option value="">-- Select Class --</option>
                                        <?php foreach ($classMapping as $c_id => $c_name): ?>
                                            <option value="<?= $c_id; ?>"><?= htmlspecialchars($c_name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="button" class="btn-remove-class" onclick="removeClassRow(this)" title="Remove class">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Add Class Button -->
                    <button type="button" class="btn-add-class" onclick="addClassRow()">
                        <i class="fas fa-plus me-1"></i> Add Class
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" name="update_teacher" class="btn-primary-action">
                            <i class="far fa-save me-2"></i> Update Teacher
                        </button>
                        <a href="view-teacher.php" class="btn-secondary-action">
                            Cancel
                        </a>
                    </div>
                    <div>
                        <button type="submit" name="delete_teacher" class="btn-action-delete text-nowrap" style="height: 46px; padding: 0 1.5rem !important; width: auto;" onclick="return confirm('Are you sure you want to permanently delete this teacher?');">
                            <i class="far fa-trash-alt me-2"></i> Delete Teacher
                        </button>
                    </div>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="teacher-card text-center py-5">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: #fee2e2; color: #dc2626; font-size: 1.5rem;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Teacher Not Found</h5>
            <p class="text-muted small mb-3">No teacher was found matching the provided identifier.</p>
            <a href="view-teacher.php" class="btn-primary-action text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> Return to Teachers List
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
const classMapping = <?= json_encode($classMapping); ?>;

function updateClassDropdowns() {
    const container = document.getElementById('classContainer');
    if (!container) return;

    const selects = container.querySelectorAll('.class-select');
    const selectedValues = Array.from(selects)
        .map(s => s.value)
        .filter(v => v !== '');

    selects.forEach(select => {
        const currentVal = select.value;
        select.innerHTML = '<option value="">-- Select Class --</option>';

        for (const [key, label] of Object.entries(classMapping)) {
            if (key === currentVal || !selectedValues.includes(key)) {
                const opt = document.createElement('option');
                opt.value = key;
                opt.textContent = label;
                if (key === currentVal) {
                    opt.selected = true;
                }
                select.appendChild(opt);
            }
        }
        select.value = currentVal;
    });
}

function addClassRow() {
    const container = document.getElementById('classContainer');
    const selects = container.querySelectorAll('.class-select');
    const selectedValues = Array.from(selects).map(s => s.value).filter(v => v !== '');

    if (selectedValues.length >= Object.keys(classMapping).length) {
        alert("All classes have already been assigned to this teacher.");
        return;
    }

    const flexDiv = document.createElement('div');
    flexDiv.className = 'class-select-item';

    const dropWrapper = document.createElement('div');
    dropWrapper.className = 'class-dropdown-wrapper';

    const icon = document.createElement('i');
    icon.className = 'fas fa-graduation-cap dropdown-icon';

    const select = document.createElement('select');
    select.name = 'class[]';
    select.className = 'form-select custom-class-select class-select';
    select.required = true;

    dropWrapper.appendChild(icon);
    dropWrapper.appendChild(select);

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'btn-remove-class';
    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
    removeBtn.title = 'Remove class';
    removeBtn.onclick = function() {
        removeClassRow(this);
    };

    flexDiv.appendChild(dropWrapper);
    flexDiv.appendChild(removeBtn);
    container.appendChild(flexDiv);

    updateClassDropdowns();
}

function removeClassRow(button) {
    const row = button.closest('.class-select-item');
    const container = document.getElementById('classContainer');
    const allRows = container.querySelectorAll('.class-select-item');

    if (allRows.length > 1) {
        if (row) row.remove();
    } else {
        const select = row.querySelector('.class-select');
        if (select) select.value = '';
    }
    updateClassDropdowns();
}

document.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('class-select')) {
        updateClassDropdowns();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    updateClassDropdowns();
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
