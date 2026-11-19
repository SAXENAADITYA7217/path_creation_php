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

$selected_class = isset($_GET['class']) ? trim($_GET['class']) : '';
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="view-subject.php<?= ($selected_class !== '') ? '?class=' . urlencode($selected_class) : ''; ?>">Subjects</a></li>
        <li class="breadcrumb-item active">Add Subjects</li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-layer-group"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Add Subjects to Class</h2>
                <p class="page-sub-heading mb-0">Create and configure curriculum subjects for specific classes.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="view-subject.php<?= ($selected_class !== '') ? '?class=' . urlencode($selected_class) : ''; ?>" class="btn-outline-back">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
            <a href="view-subject.php<?= ($selected_class !== '') ? '?class=' . urlencode($selected_class) : ''; ?>" class="btn-secondary-action">
                <i class="fas fa-eye me-2"></i> View Subjects
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- Main Card -->
    <div class="teacher-card">
        <form action="code.php" method="POST" id="subjectForm">
            <!-- Select Class Section -->
            <div class="mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6 col-lg-5">
                        <label for="classSelect" class="form-label-custom mb-2">
                            <i class="fas fa-graduation-cap text-primary me-1"></i> Select Class <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative">
                            <i class="fas fa-school position-absolute dropdown-icon" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                            <select name="class" id="classSelect" class="form-select select-with-icon" style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;" required>
                                <option value="">-- Choose Target Class --</option>
                                <?php foreach ($classMapping as $c_id => $c_name): ?>
                                    <option value="<?= $c_id; ?>" <?= ($selected_class === (string)$c_id) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($c_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-7">
                        <div class="pt-md-4">
                            <div id="classInfoPill" class="d-none alert alert-info py-2 px-3 mb-0 d-inline-flex align-items-center gap-2" style="border-radius: 12px; font-size: 0.88rem; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af;">
                                <i class="fas fa-info-circle"></i>
                                <span id="classInfoText"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic New Subjects Section inside styled sub-box -->
            <div class="assign-classes-box mb-4">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        <div class="assign-classes-icon-box">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <div>
                            <h5 class="assign-classes-title mb-0">Curriculum Subjects</h5>
                            <p class="assign-classes-subtitle mb-0">Enter subject names in the 3-column grid below (3 subjects per row).</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-dark border px-3 py-2 fw-semibold shadow-xs" id="subjectCountBadge" style="border-radius: 10px; font-size: 0.85rem;">
                            <i class="fas fa-layer-group text-primary me-1"></i> <span id="subjectCountNum">3</span> Subjects
                        </span>
                        <button type="button" id="addSubjectBtn" class="btn-add-class">
                            <i class="fas fa-plus me-1"></i> Add Another Subject
                        </button>
                    </div>
                </div>

                <!-- 3 Columns Grid for Subjects -->
                <div id="subjectsWrapper" class="subjects-grid-container mb-3">
                    <!-- Subject 1 -->
                    <div class="subject-row">
                        <div class="d-flex align-items-center gap-2 w-100">
                            <div class="input-icon-wrapper flex-grow-1">
                                <i class="fas fa-book-open input-icon"></i>
                                <input type="text" name="subject[]" class="form-control custom-input subject-input" placeholder="e.g. Mathematics" required>
                            </div>
                            <button type="button" class="btn-remove-class remove-subject" title="Remove Subject">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Subject 2 -->
                    <div class="subject-row">
                        <div class="d-flex align-items-center gap-2 w-100">
                            <div class="input-icon-wrapper flex-grow-1">
                                <i class="fas fa-book-open input-icon"></i>
                                <input type="text" name="subject[]" class="form-control custom-input subject-input" placeholder="e.g. Physics">
                            </div>
                            <button type="button" class="btn-remove-class remove-subject" title="Remove Subject">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Subject 3 -->
                    <div class="subject-row">
                        <div class="d-flex align-items-center gap-2 w-100">
                            <div class="input-icon-wrapper flex-grow-1">
                                <i class="fas fa-book-open input-icon"></i>
                                <input type="text" name="subject[]" class="form-control custom-input subject-input" placeholder="e.g. Chemistry">
                            </div>
                            <button type="button" class="btn-remove-class remove-subject" title="Remove Subject">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Duplicate / Validation Warning Alert -->
                <div id="errorAlert" class="alert alert-danger d-none mt-3 py-2 px-3 align-items-center gap-2 mb-0" style="border-radius: 10px; font-size: 0.88rem; background: #fef2f2; border: 1px solid #fecaca; color: #dc2626;">
                    <i class="fas fa-exclamation-triangle flex-shrink-0"></i>
                    <span id="errorText"></span>
                </div>
            </div>

            <!-- Submit and Action Buttons -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2">
                <div class="d-flex align-items-center gap-2">
                    <button type="submit" name="add_multiple_subjects" class="btn-primary-action px-4">
                        <i class="far fa-save me-2"></i> Save All Subjects
                    </button>
                    <a href="view-subject.php<?= ($selected_class !== '') ? '?class=' . urlencode($selected_class) : ''; ?>" class="btn-outline-back text-decoration-none">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
                <div>
                    <span class="text-muted small">
                        <i class="fas fa-info-circle text-primary me-1"></i> Empty subject rows will be automatically skipped upon saving.
                    </span>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let existingSubjects = [];
const samplePlaceholders = [
    "e.g. Mathematics",
    "e.g. Physics",
    "e.g. Chemistry",
    "e.g. Biology",
    "e.g. English Literature",
    "e.g. Computer Science",
    "e.g. Social Studies",
    "e.g. History",
    "e.g. Geography",
    "e.g. Economics",
    "e.g. Accountancy",
    "e.g. Business Studies",
    "e.g. Environmental Science",
    "e.g. Physical Education",
    "e.g. Hindi",
    "e.g. Sanskrit"
];

function updateSubjectCount() {
    let rows = document.querySelectorAll('#subjectsWrapper .subject-row');
    let badge = document.getElementById('subjectCountNum');
    if (badge) {
        badge.textContent = rows.length;
    }
}

function fetchExistingForClass(classCode) {
    existingSubjects = [];
    let classInfoPill = document.getElementById('classInfoPill');
    let classInfoText = document.getElementById('classInfoText');
    let errorAlert = document.getElementById('errorAlert');
    if (errorAlert) {
        errorAlert.classList.add('d-none');
        errorAlert.classList.remove('d-flex');
    }

    if (!classCode) {
        if (classInfoPill) classInfoPill.classList.add('d-none');
        return;
    }

    let selectEl = document.getElementById('classSelect');
    let selectedText = selectEl.options[selectEl.selectedIndex] ? selectEl.options[selectEl.selectedIndex].text : 'Selected Class';

    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch-subjects.php?class=' + encodeURIComponent(classCode) + '&fetchNames=1', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                let data = JSON.parse(xhr.responseText);
                existingSubjects = data.map(function(s) { return String(s).trim().toLowerCase(); });
                
                if (classInfoPill && classInfoText) {
                    if (existingSubjects.length > 0) {
                        classInfoText.innerHTML = `<strong>${selectedText}</strong> currently has <strong>${existingSubjects.length}</strong> subject${existingSubjects.length > 1 ? 's' : ''} registered.`;
                    } else {
                        classInfoText.innerHTML = `No subjects registered yet for <strong>${selectedText}</strong>. Ready to configure!`;
                    }
                    classInfoPill.classList.remove('d-none');
                }
            } catch(e) {}
        }
    };
    xhr.send();
}

document.getElementById('classSelect').addEventListener('change', function() {
    fetchExistingForClass(this.value);
    validateSubjects();
});

// If class is already selected on load (e.g. from GET parameter)
if (document.getElementById('classSelect').value !== "") {
    fetchExistingForClass(document.getElementById('classSelect').value);
}

// Add new subject row into the 3-column grid
document.getElementById('addSubjectBtn').addEventListener('click', function() {
    let wrapper = document.getElementById('subjectsWrapper');
    let currentRows = wrapper.querySelectorAll('.subject-row').length;
    let placeholder = samplePlaceholders[currentRows % samplePlaceholders.length];

    let div = document.createElement('div');
    div.className = 'subject-row';
    div.innerHTML = `
        <div class="d-flex align-items-center gap-2 w-100">
            <div class="input-icon-wrapper flex-grow-1">
                <i class="fas fa-book-open input-icon"></i>
                <input type="text" name="subject[]" class="form-control custom-input subject-input" placeholder="${placeholder}">
            </div>
            <button type="button" class="btn-remove-class remove-subject" title="Remove Subject">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    wrapper.appendChild(div);
    updateSubjectCount();
    
    // Focus the newly added input
    let newInput = div.querySelector('.subject-input');
    if (newInput) newInput.focus();
});

// Remove subject row
document.addEventListener('click', function(e) {
    let removeBtn = e.target.closest('.remove-subject');
    if (removeBtn) {
        let row = removeBtn.closest('.subject-row');
        let allRows = document.querySelectorAll('#subjectsWrapper .subject-row');
        if (allRows.length > 1) {
            row.remove();
        } else {
            let input = row.querySelector('.subject-input');
            if (input) input.value = '';
        }
        updateSubjectCount();
        validateSubjects();
    }
});

// Real-time duplicate validation
function validateSubjects() {
    let errorAlert = document.getElementById('errorAlert');
    let errorText = document.getElementById('errorText');
    let allInputs = document.querySelectorAll('.subject-input');
    let seenValues = {};
    let errorFound = "";

    // Reset styles
    allInputs.forEach(function(inp) {
        inp.style.borderColor = "";
    });

    for (let i = 0; i < allInputs.length; i++) {
        let inp = allInputs[i];
        let val = inp.value.trim().toLowerCase();
        if (!val) continue;

        // 1. Check against DB subjects for selected class
        if (existingSubjects.includes(val)) {
            errorFound = `Subject "<strong>${inp.value.trim()}</strong>" already exists in this class!`;
            inp.style.borderColor = "#ef4444";
            break;
        }

        // 2. Check for intra-form duplicate
        if (seenValues[val]) {
            errorFound = `Subject "<strong>${inp.value.trim()}</strong>" is entered more than once!`;
            inp.style.borderColor = "#ef4444";
            seenValues[val].style.borderColor = "#ef4444";
            break;
        }

        seenValues[val] = inp;
    }

    if (errorFound) {
        errorText.innerHTML = errorFound;
        errorAlert.classList.remove('d-none');
        errorAlert.classList.add('d-flex');
        return false;
    } else {
        errorAlert.classList.add('d-none');
        errorAlert.classList.remove('d-flex');
        return true;
    }
}

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('subject-input')) {
        validateSubjects();
    }
});

// Form submit interception
document.getElementById('subjectForm').addEventListener('submit', function(e) {
    let classVal = document.getElementById('classSelect').value;
    if (!classVal) {
        e.preventDefault();
        alert('Please select a target class first.');
        document.getElementById('classSelect').focus();
        return false;
    }

    let allInputs = document.querySelectorAll('.subject-input');
    let filledCount = 0;
    allInputs.forEach(function(inp) {
        if (inp.value.trim() !== '') filledCount++;
    });

    if (filledCount === 0) {
        e.preventDefault();
        alert('Please enter at least one subject name.');
        if (allInputs[0]) allInputs[0].focus();
        return false;
    }

    if (!validateSubjects()) {
        e.preventDefault();
        return false;
    }
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
