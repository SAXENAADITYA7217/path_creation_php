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
$selected_date = isset($_GET['date']) && !empty($_GET['date']) ? trim($_GET['date']) : date('Y-m-d');
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="attendance-view.php<?= (!empty($selected_class)) ? '?class=' . urlencode($selected_class) . '&date=' . urlencode($selected_date) : ''; ?>">Attendance</a></li>
        <li class="breadcrumb-item active">Mark Attendance</li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Mark Student Attendance</h2>
                <p class="page-sub-heading mb-0">Record daily present/absent statuses or update existing logs by class and date.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="attendance-view.php<?= (!empty($selected_class)) ? '?class=' . urlencode($selected_class) . '&date=' . urlencode($selected_date) : ''; ?>" class="btn-outline-back text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
            <a href="attendance-view.php<?= (!empty($selected_class)) ? '?class=' . urlencode($selected_class) . '&date=' . urlencode($selected_date) : ''; ?>" class="btn-secondary-action text-decoration-none">
                <i class="fas fa-eye me-2"></i> View Records
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <form action="code.php" method="POST" id="attendanceForm">
        <!-- Target Class & Date Configuration Card -->
        <div class="teacher-card mb-4">
            <div class="row g-3 align-items-end">
                <!-- Class Selector -->
                <div class="col-md-6">
                    <label for="classSelect" class="form-label-custom mb-2">
                        <i class="fas fa-graduation-cap text-primary me-1"></i> Target Class <span class="text-danger">*</span>
                    </label>
                    <div class="position-relative">
                        <i class="fas fa-school position-absolute dropdown-icon" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                        <select name="class" id="classSelect" class="form-select select-with-icon" style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;" required>
                            <option value="">-- Choose Class --</option>
                            <?php foreach ($classMapping as $c_id => $c_name): ?>
                                <option value="<?= $c_id; ?>" <?= ($selected_class === (string)$c_id) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($c_name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Date Picker -->
                <div class="col-md-6">
                    <label for="attendanceDate" class="form-label-custom mb-2">
                        <i class="far fa-calendar-alt text-primary me-1"></i> Attendance Date <span class="text-danger">*</span>
                    </label>
                    <div class="position-relative">
                        <i class="fas fa-calendar-day position-absolute dropdown-icon" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                        <input type="date" 
                               name="date" 
                               id="attendanceDate" 
                               value="<?= htmlspecialchars($selected_date); ?>" 
                               class="form-control" 
                               style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;" 
                               required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Attendance Roster Container (AJAX loaded) -->
        <div id="attendanceRosterContainer">
            <div class="teacher-card text-center py-5">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: #ede9fe; color: var(--pca-primary); font-size: 1.4rem;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Select a Class Above</h5>
                <p class="text-muted small mb-0">Choose a class from the dropdown above to load the student roster for attendance.</p>
            </div>
        </div>
    </form>
</div>

<script>
function loadAttendanceRoster() {
    let classVal = document.getElementById('classSelect').value;
    let dateVal = document.getElementById('attendanceDate').value;
    let container = document.getElementById('attendanceRosterContainer');

    if (!classVal) {
        container.innerHTML = `
            <div class="teacher-card text-center py-5">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: #ede9fe; color: var(--pca-primary); font-size: 1.4rem;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Select a Class Above</h5>
                <p class="text-muted small mb-0">Choose a class from the dropdown above to load the student roster for attendance.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = `
        <div class="teacher-card text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status"></div>
            <p class="text-muted small mb-0">Loading student roster for Class & Date...</p>
        </div>
    `;

    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'attendance-fetch-students.php?class=' + encodeURIComponent(classVal) + '&date=' + encodeURIComponent(dateVal), true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                container.innerHTML = xhr.responseText;
                updateLiveAttendanceCounts();
            } else {
                container.innerHTML = `
                    <div class="teacher-card text-center py-4 text-danger">
                        <i class="fas fa-exclamation-triangle fs-2 mb-2 d-block"></i>
                        Failed to load students. Please try again.
                    </div>
                `;
            }
        }
    };
    xhr.send();
}

function updateLiveAttendanceCounts() {
    let presentRadios = document.querySelectorAll('.att-radio[value="1"]:checked');
    let absentRadios = document.querySelectorAll('.att-radio[value="0"]:checked');

    let presEl = document.getElementById('livePresentCount');
    let absEl = document.getElementById('liveAbsentCount');

    if (presEl) presEl.textContent = presentRadios.length;
    if (absEl) absEl.textContent = absentRadios.length;
}

function markAllStatus(status) {
    let radios = document.querySelectorAll('.att-radio[value="' + status + '"]');
    radios.forEach(function(r) {
        r.checked = true;
    });
    updateLiveAttendanceCounts();
}

document.getElementById('classSelect').addEventListener('change', loadAttendanceRoster);
document.getElementById('attendanceDate').addEventListener('change', loadAttendanceRoster);

// Auto-trigger if class is already selected on page load
window.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('classSelect').value !== '') {
        loadAttendanceRoster();
    }
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
