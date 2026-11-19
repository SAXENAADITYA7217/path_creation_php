<?php
date_default_timezone_set('Asia/Kolkata');
include('authentication.php');
include('includes/header.php');

// Class Mapping
$classMapping = [
    '100' => 'Nursery',
    '101' => 'LKG',
    '102' => 'UKG',
    '1' => 'Class I',
    '2' => 'Class II',
    '3' => 'Class III',
    '4' => 'Class IV',
    '5' => 'Class V',
    '6' => 'Class VI',
    '7' => 'Class VII',
    '8' => 'Class VIII',
    '9' => 'Class IX',
    '10' => 'Class X',
    '11' => 'Class XI Sci (PCB)',
    '12' => 'Class XI Sci (PCM)',
    '13' => 'Class XI Comm. (with Math)',
    '14' => 'Class XI Comm. (without Math)',
    '15' => 'Class XI Arts',
    '16' => 'Class XII Sci (PCB)',
    '17' => 'Class XII Sci (PCM)',
    '18' => 'Class XII Comm. (with Math)',
    '19' => 'Class XII Comm. (without Math)',
    '20' => 'Class XII Arts'
];

$user_id = $_SESSION['auth_user']['user_id'] ?? '';
$user_code = $_SESSION['auth_user']['id'] ?? '';

// 1. Fetch assigned classes for this teacher
$classes = [];
$q_classes = mysqli_query($con, "SELECT DISTINCT class FROM class_teacher WHERE (user_id = '$user_code' OR user_id = '$user_id') AND (class_status = 0 OR class_status IS NULL)");
if ($q_classes) {
    while($row = mysqli_fetch_assoc($q_classes)) {
        if(!empty($row['class']) && !in_array((string)$row['class'], $classes)){
            $classes[] = (string)$row['class'];
        }
    }
}
if(empty($classes) && !empty($user_row['class'])){
    $classes[] = (string)$user_row['class'];
}

// 2. Class selection logic
if (isset($_GET['class']) && in_array((string)$_GET['class'], $classes)) {
    $selected_class = (string)$_GET['class'];
    $_SESSION['teacher_selected_class'] = $selected_class;
} elseif (isset($_SESSION['teacher_selected_class']) && in_array((string)$_SESSION['teacher_selected_class'], $classes)) {
    $selected_class = (string)$_SESSION['teacher_selected_class'];
} elseif (!empty($classes)) {
    $selected_class = (string)$classes[0];
    $_SESSION['teacher_selected_class'] = $selected_class;
} else {
    $selected_class = '';
}

$selected_class_name = $selected_class !== '' ? ($classMapping[$selected_class] ?? ('Class ' . $selected_class)) : 'None';
?>

<!-- FontAwesome & Plus Jakarta Sans Font for exact theme match -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* Exact Theme System matching the reference aesthetic */
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

.teacher-dashboard-wrap {
    font-family: var(--pca-font) !important;
    color: var(--pca-text-main);
    padding: 0.5rem 0.5rem 2.5rem 0.5rem;
}

/* Header */
.welcome-label {
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #6366f1;
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 6px;
}

.dashboard-main-title {
    font-size: 2rem;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
    margin-bottom: 0.2rem;
    line-height: 1.2;
}

.dashboard-sub-title {
    color: #64748b;
    font-size: 0.92rem;
    margin-bottom: 0;
}

.date-pill-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 0.55rem 1rem;
    font-size: 0.86rem;
    font-weight: 600;
    color: #334155;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.btn-back-link {
    background: #ffffff;
    color: #475569 !important;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 0.55rem 1.15rem;
    display: inline-flex;
    align-items: center;
    text-decoration: none !important;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
}

.btn-back-link:hover {
    background: #f8fafc;
    color: #0f172a !important;
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.btn-primary-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.88rem;
    padding: 0.58rem 1.25rem;
    box-shadow: 0 4px 12px rgba(94, 72, 235, 0.25);
    border: none;
    display: inline-flex;
    align-items: center;
    text-decoration: none !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
}

.btn-primary-action:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(94, 72, 235, 0.35);
    color: #ffffff !important;
}

/* Monolithic White Card */
.pca-white-card {
    background: #ffffff;
    border: 1px solid #edf0f5;
    border-radius: 18px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.pca-card-header {
    padding: 1.2rem 1.4rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f3f7;
    background: #ffffff;
    flex-wrap: wrap;
    gap: 1rem;
}

.pca-card-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pca-card-body {
    padding: 1.4rem;
    flex-grow: 1;
}

/* Horizontal Scroll Container for Student Rows */
.student-fields-scroll-wrap {
    overflow-x: auto;
    padding-bottom: 4px;
}

/* Student Form Row Card - Grid with Delete in Same Row */
.student-row-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1rem 1.15rem;
    margin-bottom: 0.85rem;
    transition: all 0.2s ease;
    min-width: 860px;
}

.student-row-card:hover {
    border-color: #cbd5e1;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
}

.student-row-grid {
    display: grid;
    grid-template-columns: 42px 90px 1.8fr 1.6fr 1.35fr 1.35fr 42px;
    gap: 12px;
    align-items: flex-end;
}

.row-number-pill {
    background: #f0f1ff;
    color: #5e48eb;
    font-weight: 800;
    font-size: 0.85rem;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.form-label-custom {
    font-size: 0.76rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.35rem;
    display: block;
    white-space: nowrap;
}

/* Input with Icon */
.input-with-icon {
    position: relative;
}

.input-with-icon .input-icon {
    position: absolute;
    left: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.85rem;
    pointer-events: none;
    z-index: 2;
}

.form-control-clean {
    border-radius: 10px !important;
    border: 1px solid #e2e8f0 !important;
    padding: 0.55rem 0.85rem 0.55rem 2.3rem !important;
    font-size: 0.88rem !important;
    color: #1e293b !important;
    background: #f8fafc !important;
    height: 42px;
    transition: all 0.2s ease;
}

.form-control-clean:focus {
    background: #ffffff !important;
    border-color: #5e48eb !important;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12) !important;
    outline: none;
}

/* Action Delete Button in Same Row */
.btn-action-delete {
    background: #fef2f2 !important;
    color: #dc2626 !important;
    border: 1px solid #fee2e2 !important;
    border-radius: 10px !important;
    width: 42px;
    height: 42px;
    padding: 0 !important;
    font-size: 0.95rem !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    cursor: pointer;
    flex-shrink: 0;
}

.btn-action-delete:hover {
    background: #fee2e2 !important;
    color: #b91c1c !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
}
/* Mobile Responsive Optimization */
@media (max-width: 768px) {
    .teacher-dashboard-wrap {
        padding: 0.5rem 0.25rem 2rem 0.25rem !important;
    }
    .dashboard-main-title {
        font-size: 1.45rem !important;
    }
    .pca-card-header {
        flex-direction: column !important;
        align-items: stretch !important;
        gap: 0.75rem !important;
        padding: 1rem !important;
    }
    .pca-card-body {
        padding: 1rem !important;
    }
    .btn-back-link, .btn-primary-action {
        width: 100% !important;
        justify-content: center !important;
    }
    .date-pill-badge {
        width: 100% !important;
        justify-content: center !important;
    }
}
</style>

<div class="teacher-dashboard-wrap">

    <!-- Top Greeting & Header Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="welcome-label">
                <i class="fa-solid fa-user-plus"></i> Student Enrollment
            </div>
            <h1 class="dashboard-main-title">Add New Students</h1>
            <p class="dashboard-sub-title">Register one or multiple students into your assigned class sections.</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="get-student.php<?= $selected_class !== '' ? '?class=' . urlencode($selected_class) : '' ?>" class="btn-back-link">
                <i class="fa-solid fa-arrow-left me-2"></i> Back to Students
            </a>
            <div class="date-pill-badge">
                <i class="fa-regular fa-calendar-days text-primary"></i>
                <span><?= date('D, M d, Y') ?></span>
                <span class="text-muted">•</span>
                <span id="liveClockDisplay"><?= date('h:i:s A') ?></span>
            </div>
        </div>
    </div>

    <!-- Session Flash Message -->
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert" style="background: #eef2ff; color: #4338ca; border-left: 5px solid #6366f1 !important; padding: 1rem 1.3rem;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-info fs-5"></i>
                <div class="fw-medium"><?= htmlspecialchars($_SESSION['message']); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- Monolithic White Card Container -->
    <div class="pca-white-card">
        
        <!-- Form Header Toolbar -->
        <div class="pca-card-header">
            <div class="d-flex align-items-center gap-2">
                <h5 class="pca-card-title">
                    <i class="fa-solid fa-user-graduate text-primary"></i>
                    Student Details Entry
                </h5>
                <span class="badge rounded-pill" id="rowCountBadge" style="background: #f0f1ff; color: #5e48eb; font-weight: 700; font-size: 0.8rem; padding: 0.35rem 0.8rem;">
                    1 Student Row
                </span>
            </div>

            <!-- Toolbar Controls -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="d-inline-flex align-items-center gap-2 bg-light px-3 py-1 rounded-3 border">
                    <i class="fa-solid fa-sliders text-muted" style="font-size: 0.85rem;"></i>
                    <span class="text-muted small fw-semibold">Default Class:</span>
                    <select id="defaultClassSelect" class="form-select form-select-sm border-0 bg-transparent fw-semibold text-primary" style="cursor: pointer; width: auto;">
                        <?php foreach($classes as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= ($selected_class == $c) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($classMapping[$c] ?? ('Class ' . $c)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="button" class="btn-primary-action" id="addRowTopBtn">
                    <i class="fa-solid fa-plus me-1"></i> Add Another Row
                </button>
            </div>
        </div>

        <!-- Form Body -->
        <div class="pca-card-body">

            <!-- Student Submission Form -->
            <form action="teacher_code.php" method="POST" id="studentForm">
                <div class="student-fields-scroll-wrap">
                    <div id="studentFieldsContainer">

                        <!-- First Student Row (Row #1) - Exactly 1 Row with Delete Button at the end -->
                        <div class="student-row-card" data-student-row>
                            <div class="student-row-grid">
                                
                                <!-- Index Badge -->
                                <div>
                                    <span class="row-number-pill">#1</span>
                                </div>

                                <!-- Roll No -->
                                <div>
                                    <label class="form-label-custom">Roll No</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-hashtag input-icon"></i>
                                        <input type="text" name="rollno[]" class="form-control form-control-clean rollno-input" placeholder="Roll No" required>
                                    </div>
                                </div>

                                <!-- Student Name -->
                                <div>
                                    <label class="form-label-custom">Student Full Name <span class="text-danger">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fa-regular fa-user input-icon"></i>
                                        <input type="text" name="fname[]" class="form-control form-control-clean fname-input" placeholder="e.g. Rahul Sharma" required>
                                    </div>
                                </div>

                                <!-- Father's Name -->
                                <div>
                                    <label class="form-label-custom">Father's Name</label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-user-tie input-icon"></i>
                                        <input type="text" name="fathername[]" class="form-control form-control-clean" placeholder="e.g. Ramesh Sharma">
                                    </div>
                                </div>

                                <!-- Phone Number -->
                                <div>
                                    <label class="form-label-custom">Phone Number <span class="text-danger">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-phone input-icon"></i>
                                        <input type="tel" name="phone[]" class="form-control form-control-clean phone-input" 
                                               pattern="[0-9]{10}" maxlength="10" required
                                               oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                                               placeholder="10-digit mobile">
                                    </div>
                                </div>

                                <!-- Class -->
                                <div>
                                    <label class="form-label-custom">Class <span class="text-danger">*</span></label>
                                    <div class="input-with-icon">
                                        <i class="fa-solid fa-graduation-cap input-icon"></i>
                                        <select name="class[]" class="form-select form-control-clean class-select-field" required>
                                            <option value="">-- Class --</option>
                                            <?php foreach($classes as $c): ?>
                                                <option value="<?= htmlspecialchars($c) ?>" <?= ($selected_class == $c) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($classMapping[$c] ?? ('Class ' . $c)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Delete Button (Same Row) -->
                                <div>
                                    <button type="button" class="btn-action-delete removeRowBtn" title="Remove this student">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <!-- Form Bottom Actions -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top mt-4">
                    <button type="button" class="btn btn-light border px-4 py-2" id="addRowBottomBtn" style="border-radius: 12px; font-weight: 600; font-size: 0.9rem; color: #475569;">
                        <i class="fa-solid fa-plus me-2 text-primary"></i> Add Another Student Row
                    </button>

                    <div class="d-flex align-items-center gap-2">
                        <a href="get-student.php<?= $selected_class !== '' ? '?class=' . urlencode($selected_class) : '' ?>" class="btn btn-outline-secondary px-4 py-2" style="border-radius: 12px; font-weight: 600; font-size: 0.9rem;">
                            Cancel
                        </a>
                        <button type="submit" name="add_student" class="btn-primary-action px-4 py-2" style="font-size: 0.92rem;">
                            <i class="fa-solid fa-check me-2"></i> Submit & Enroll Students
                        </button>
                    </div>
                </div>
            </form>

        </div>

    </div>

</div>

<script>
// Live Clock
function updateClock() {
    const el = document.getElementById('liveClockDisplay');
    if (!el) return;
    const now = new Date();
    el.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
}
setInterval(updateClock, 1000);

document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('studentFieldsContainer');
    const defaultClassSelect = document.getElementById('defaultClassSelect');
    const rowCountBadge = document.getElementById('rowCountBadge');

    // Update row counts & badges
    function updateRowIndices() {
        const rows = container.querySelectorAll('[data-student-row]');
        rows.forEach((row, index) => {
            const badge = row.querySelector('.row-number-pill');
            if (badge) {
                badge.textContent = '#' + (index + 1);
            }
        });

        const count = rows.length;
        if (rowCountBadge) {
            rowCountBadge.textContent = count + (count === 1 ? ' Student Row' : ' Student Rows');
        }
    }

    // Attach remove event handler
    function attachRemoveEvents() {
        container.querySelectorAll('.removeRowBtn').forEach(btn => {
            btn.onclick = function () {
                const allRows = container.querySelectorAll('[data-student-row]');
                if (allRows.length > 1) {
                    const row = this.closest('[data-student-row]');
                    row.style.opacity = '0';
                    row.style.transform = 'translateY(-10px)';
                    row.style.transition = 'all 0.2s ease';
                    setTimeout(() => {
                        row.remove();
                        updateRowIndices();
                    }, 200);
                } else {
                    alert("You must have at least one student row.");
                }
            };
        });
    }

    // Function to add a new row
    function addNewStudentRow() {
        const firstRow = container.querySelector('[data-student-row]');
        if (!firstRow) return;

        const newRow = firstRow.cloneNode(true);
        newRow.style.opacity = '1';
        newRow.style.transform = 'none';

        // Clear input values
        newRow.querySelectorAll('input').forEach(input => {
            input.value = '';
        });

        // Set class to default class selector if available
        if (defaultClassSelect && defaultClassSelect.value) {
            const classSelect = newRow.querySelector('.class-select-field');
            if (classSelect) {
                classSelect.value = defaultClassSelect.value;
            }
        }

        container.appendChild(newRow);
        updateRowIndices();
        attachRemoveEvents();

        // Focus the rollno input of the new row
        const newRollInput = newRow.querySelector('.rollno-input');
        if (newRollInput) {
            newRollInput.focus();
        }
    }

    // Default class dropdown change handler
    if (defaultClassSelect) {
        defaultClassSelect.addEventListener('change', function() {
            const chosen = this.value;
            container.querySelectorAll('.class-select-field').forEach(sel => {
                sel.value = chosen;
            });
        });
    }

    // Button event listeners
    const addTopBtn = document.getElementById('addRowTopBtn');
    if (addTopBtn) addTopBtn.addEventListener('click', addNewStudentRow);

    const addBottomBtn = document.getElementById('addRowBottomBtn');
    if (addBottomBtn) addBottomBtn.addEventListener('click', addNewStudentRow);

    // Initial event bindings
    attachRemoveEvents();
    updateRowIndices();
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
