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
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="view-teacher.php">Teachers</a></li>
        <li class="breadcrumb-item active">Add Teachers</li>
    </ol>

    <!-- Page Header matching screenshot -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Add Teachers</h2>
                <p class="page-sub-heading mb-0">Add a new teacher with details and assign multiple classes.</p>
            </div>
        </div>
        <div>
            <a href="view-teacher.php" class="btn-outline-back">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <form action="code.php" method="POST" id="teacherForm">
        <div id="teacherContainer">
            <!-- Teacher Card Template -->
            <div class="teacher-card position-relative mb-4">
                <!-- Remove Teacher Button (visible when cloned) -->
                <button type="button" class="btn-remove-teacher position-absolute top-0 end-0 m-3 d-none remove-btn" onclick="removeTeacherRow(this)" title="Remove this teacher">
                    <i class="fas fa-times"></i>
                </button>

                <!-- Inputs Row: 3 columns matching screenshot -->
                <div class="row g-3 mb-2">
                    <div class="col-md-4">
                        <label class="form-label-custom">First Name <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="far fa-user input-icon"></i>
                            <input type="text" name="fname[]" class="form-control custom-input" placeholder="Enter first name" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-custom">Father Name <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="far fa-user input-icon"></i>
                            <input type="text" name="fathername[]" class="form-control custom-input" placeholder="Enter father name" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-custom">Phone No <span class="text-danger">*</span></label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-phone-alt input-icon"></i>
                            <input type="tel" name="phone[]" class="form-control custom-input"
                                pattern="[0-9]{10}" maxlength="10"
                                required
                                oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                                placeholder="Enter 10-digit phone number">
                        </div>
                    </div>
                </div>

                <!-- Assign Classes Sub-Box matching screenshot -->
                <div class="assign-classes-box class-box">
                    <div class="d-flex align-items-center gap-3">
                        <div class="assign-classes-icon-box">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div>
                            <h5 class="assign-classes-title mb-0">Assign Classes</h5>
                            <p class="assign-classes-subtitle mb-0">Select one or more classes for this teacher.</p>
                        </div>
                    </div>

                    <!-- Class Selectors Horizontal Container -->
                    <div class="class-selectors-container">
                        <div class="class-select-item">
                            <div class="class-dropdown-wrapper">
                                <i class="fas fa-graduation-cap dropdown-icon"></i>
                                <select name="class[0][]" class="form-select custom-class-select class-select" required>
                                    <option value="">-- Select Class --</option>
                                    <?php foreach ($classMapping as $c_id => $c_name): ?>
                                        <option value="<?= $c_id ?>"><?= htmlspecialchars($c_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="button" class="btn-remove-class" onclick="removeClassSelect(this)" title="Remove class">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Add Class Button -->
                    <button type="button" class="btn-add-class" onclick="addClassSelect(this)">
                        <i class="fas fa-plus me-1"></i> Add Class
                    </button>
                </div>
            </div>
        </div>

        <!-- Action Buttons matching screenshot -->
        <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
            <button type="button" class="btn-secondary-action" onclick="addTeacherRow()">
                <i class="fas fa-plus me-2"></i> Add Another Teacher
            </button>
            <a href="view-teacher.php" class="btn-secondary-action">
                <i class="fas fa-user-friends me-2"></i> View All Teachers
            </a>
        </div>

        <div>
            <button type="submit" name="add_multiple_teachers" class="btn-primary-action">
                <i class="far fa-save me-2"></i> Save All
            </button>
        </div>
    </form>
</div>

<script>
const classMapping = <?= json_encode($classMapping); ?>;

function updateTeacherClassDropdowns(classBox) {
    if (!classBox) return;

    const selects = classBox.querySelectorAll('.class-select');
    const selectedValues = Array.from(selects)
        .map(s => s.value)
        .filter(v => v !== '');

    selects.forEach(select => {
        const currentVal = select.value;
        select.innerHTML = '<option value="">-- Select Class --</option>';

        for (const [key, label] of Object.entries(classMapping)) {
            // Keep option if it is currently selected in this dropdown OR not selected in any other dropdown in this classBox
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

function addClassSelect(button) {
    let classBox = button.closest('.class-box');
    let teacherRow = classBox.closest('.teacher-card');
    let container = document.getElementById('teacherContainer');
    let teacherIndex = [...container.querySelectorAll('.teacher-card')].indexOf(teacherRow);

    let currentSelects = classBox.querySelectorAll('.class-select');
    let selectedValues = Array.from(currentSelects).map(s => s.value).filter(v => v !== '');

    if (selectedValues.length >= Object.keys(classMapping).length) {
        alert("All classes have already been assigned to this teacher.");
        return;
    }

    let flexDiv = document.createElement("div");
    flexDiv.className = "class-select-item";

    let dropWrapper = document.createElement("div");
    dropWrapper.className = "class-dropdown-wrapper";

    let icon = document.createElement("i");
    icon.className = "fas fa-graduation-cap dropdown-icon";

    let select = document.createElement("select");
    select.name = `class[${teacherIndex}][]`;
    select.className = "form-select custom-class-select class-select";
    select.required = true;

    dropWrapper.appendChild(icon);
    dropWrapper.appendChild(select);

    let removeBtn = document.createElement("button");
    removeBtn.type = "button";
    removeBtn.className = "btn-remove-class";
    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
    removeBtn.title = "Remove class";
    removeBtn.onclick = function () {
        removeClassSelect(this);
    };

    flexDiv.appendChild(dropWrapper);
    flexDiv.appendChild(removeBtn);

    let selectorsContainer = classBox.querySelector('.class-selectors-container');
    selectorsContainer.appendChild(flexDiv);

    updateTeacherClassDropdowns(classBox);
}

function removeClassSelect(button) {
    let selectItem = button.closest('.class-select-item');
    let classBox = button.closest('.class-box');
    let containerSelectors = classBox.querySelector('.class-selectors-container');
    let allItems = containerSelectors.querySelectorAll('.class-select-item');

    if (allItems.length > 1) {
        selectItem.remove();
    } else {
        let sel = selectItem.querySelector('select');
        if (sel) sel.value = '';
    }
    updateTeacherClassDropdowns(classBox);
}

function addTeacherRow() {
    let container = document.getElementById('teacherContainer');
    let firstRow = container.querySelector('.teacher-card');
    let newRow = firstRow.cloneNode(true);

    // Clear all text inputs
    newRow.querySelectorAll('input').forEach(el => el.value = '');

    // Reset Class selectors to only ONE dropdown item
    let classBox = newRow.querySelector('.class-box');
    let selectorsContainer = classBox.querySelector('.class-selectors-container');
    let items = selectorsContainer.querySelectorAll('.class-select-item');
    items.forEach((item, index) => {
        if (index > 0) item.remove();
    });

    let firstSelect = selectorsContainer.querySelector('select');
    if (firstSelect) firstSelect.value = '';

    // Show remove teacher button
    let removeBtn = newRow.querySelector('.remove-btn');
    if (removeBtn) {
        removeBtn.classList.remove('d-none');
    }

    container.appendChild(newRow);

    reindexTeacherRows();
    updateTeacherClassDropdowns(classBox);
}

function removeTeacherRow(button) {
    let row = button.closest('.teacher-card');
    row.remove();
    reindexTeacherRows();
}

function reindexTeacherRows() {
    let rows = document.querySelectorAll('#teacherContainer .teacher-card');
    rows.forEach((row, teacherIndex) => {
        row.querySelectorAll('.class-select').forEach(select => {
            select.name = `class[${teacherIndex}][]`;
        });
    });
}

document.addEventListener('change', function(e) {
    if (e.target && e.target.classList.contains('class-select')) {
        let classBox = e.target.closest('.class-box');
        if (classBox) {
            updateTeacherClassDropdowns(classBox);
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.class-box').forEach(classBox => {
        updateTeacherClassDropdowns(classBox);
    });
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
