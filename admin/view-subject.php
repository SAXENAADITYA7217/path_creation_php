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

$selected_class = isset($_GET['class']) ? trim($_GET['class']) : '';
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item active">Class Subjects</li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-book-open"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Class Subjects</h2>
                <p class="page-sub-heading mb-0">View, filter, and manage configured curriculum subjects by class.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="add-subject.php<?= ($selected_class !== '') ? '?class=' . urlencode($selected_class) : ''; ?>" class="btn-primary-action text-decoration-none">
                <i class="fas fa-plus me-2"></i> Add Subjects
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- Class Selector Card -->
    <div class="teacher-card mb-4">
        <label for="classSelect" class="form-label-custom">Select Class <span class="text-danger">*</span></label>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="position-relative" style="width: 420px; max-width: 100%;">
                <i class="fas fa-graduation-cap position-absolute dropdown-icon" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                <select id="classSelect" class="form-select select-with-icon" style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;">
                    <option value="">-- Select Class --</option>
                    <?php foreach ($classMapping as $c_id => $c_name): ?>
                        <?php $selected = ($selected_class === (string)$c_id) ? 'selected' : ''; ?>
                        <option value="<?= $c_id; ?>" <?= $selected; ?>><?= htmlspecialchars($c_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Subjects Container (AJAX Loaded) -->
    <div id="subjectsContainer">
        <div class="teacher-card text-center py-5">
            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: #ede9fe; color: var(--pca-primary); font-size: 1.5rem;">
                <i class="fas fa-layer-group"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">Please Select a Class Above</h5>
            <p class="text-muted small mb-0">Choose a class from the dropdown to preview and manage all configured subjects.</p>
        </div>
    </div>
</div>

<script>
document.getElementById('classSelect').addEventListener('change', function() {
    let classCode = this.value;
    let container = document.getElementById('subjectsContainer');

    if (classCode === "") {
        container.innerHTML = `
            <div class="teacher-card text-center py-5">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: #ede9fe; color: var(--pca-primary); font-size: 1.5rem;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Please Select a Class Above</h5>
                <p class="text-muted small mb-0">Choose a class from the dropdown to preview and manage all configured subjects.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = `
        <div class="teacher-card text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="text-muted small mb-0">Loading subjects for selected class...</p>
        </div>
    `;

    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch-subjects.php?class=' + encodeURIComponent(classCode), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                container.innerHTML = xhr.responseText;
            } else {
                container.innerHTML = `
                    <div class="teacher-card text-center py-4 text-danger">
                        <i class="fas fa-exclamation-triangle fs-2 mb-2 d-block"></i>
                        Failed to load subjects. Please try again.
                    </div>
                `;
            }
        }
    };
    xhr.send();
});

// Auto-trigger if class parameter is present in URL
window.addEventListener('DOMContentLoaded', function() {
    let classVal = document.getElementById('classSelect').value;
    if (classVal !== '') {
        document.getElementById('classSelect').dispatchEvent(new Event('change'));
    }
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
