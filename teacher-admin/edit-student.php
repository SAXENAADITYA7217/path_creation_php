<?php
include('authentication.php');
include('includes/header.php');

// Class Mapping
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
    '13' => 'Class 11 Comm. (with Math)',
    '14' => 'Class 11 Comm. (without Math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)',
    '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (with Math)',
    '19' => 'Class 12 Comm. (without Math)',
    '20' => 'Class 12 Arts'
];

$user_id = $_SESSION['auth_user']['user_id'] ?? '';
$user_code = $_SESSION['auth_user']['id'] ?? '';

// Check if either id or user_id is passed
if (isset($_GET['id']) || isset($_GET['user_id'])) {
    $identifier = isset($_GET['id']) ? 'id' : 'user_id';
    $value = mysqli_real_escape_string($con, $_GET[$identifier]);

    // Fetch student record from users table
    $query = "SELECT * FROM users WHERE $identifier = '$value' AND role_as = 0 LIMIT 1";
    $query_run = mysqli_query($con, $query) or die("Query Failed: " . mysqli_error($con));

    if ($query_run && mysqli_num_rows($query_run) > 0) {
        $student = mysqli_fetch_assoc($query_run);
    } else {
        echo "<script>alert('Student not found!'); window.location.href='get-student.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('No student identifier provided!'); window.location.href='get-student.php';</script>";
    exit;
}

// Fetch assigned classes for this teacher
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
// Always ensure student's own class is in dropdown
if(!empty($student['class']) && !in_array((string)$student['class'], $classes)){
    $classes[] = (string)$student['class'];
}
?>

<!-- FontAwesome & Plus Jakarta Sans Font for clean aesthetic -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --pca-font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    --pca-primary: #5e48eb;
    --pca-primary-hover: #4d38d6;
}

.edit-student-wrapper {
    font-family: var(--pca-font) !important;
    padding: 0.5rem 0.5rem 2rem 0.5rem;
}

/* Monolithic Clean Card */
.pca-white-card {
    background: #ffffff;
    border: 1px solid #edf0f5;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
    overflow: hidden;
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
    color: #0f172a;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pca-card-body {
    padding: 1.5rem;
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
    padding: 0.6rem 0.9rem !important;
    font-size: 0.9rem !important;
    color: #1e293b !important;
    background: #f8fafc !important;
    height: 44px;
    transition: all 0.2s ease;
}

.form-control-clean:focus {
    background: #ffffff !important;
    border-color: #5e48eb !important;
    box-shadow: 0 0 0 3px rgba(94, 72, 235, 0.12) !important;
    outline: none;
}

.btn-back-link {
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

.btn-back-link:hover {
    background: #f8fafc;
    color: #0f172a !important;
    border-color: #cbd5e1;
}

.btn-primary-action {
    background: linear-gradient(135deg, #5e48eb 0%, #4f46e5 100%);
    color: #ffffff !important;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 0.6rem 1.6rem;
    border: none;
    box-shadow: 0 3px 10px rgba(94, 72, 235, 0.25);
    display: inline-flex;
    align-items: center;
    transition: all 0.2s ease;
    cursor: pointer;
}

.btn-primary-action:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    box-shadow: 0 5px 14px rgba(94, 72, 235, 0.35);
    transform: translateY(-1px);
}
/* Mobile Responsive Optimization */
@media (max-width: 768px) {
    .edit-student-wrapper {
        padding: 0.5rem 0.25rem 2rem 0.25rem !important;
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
    .btn-back-link {
        width: 100% !important;
        justify-content: center !important;
    }
    .student-actions-row {
        flex-direction: column !important;
        gap: 0.75rem !important;
    }
    .student-actions-row button,
    .student-actions-row a,
    .student-actions-row .actions-right-group {
        width: 100% !important;
        justify-content: center !important;
    }
    .student-actions-row .actions-right-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<div class="edit-student-wrapper">

    <!-- Flash message -->
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert" style="background: #eef2ff; color: #4338ca; border-left: 4px solid #6366f1 !important; padding: 0.9rem 1.2rem;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-info fs-5"></i>
                <div class="fw-medium"><?= htmlspecialchars($_SESSION['message']); ?></div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <!-- Monolithic Card -->
    <div class="pca-white-card">
        
        <div class="pca-card-header">
            <h4 class="pca-card-title">
                <i class="fa-solid fa-user-pen text-primary"></i> Edit Student
            </h4>
            <a href="get-student.php<?= !empty($student['class']) ? '?class=' . urlencode($student['class']) : '' ?>" class="btn-back-link">
                <i class="fa-solid fa-arrow-left me-1"></i> BACK
            </a>
        </div>

        <div class="pca-card-body">
            <form action="teacher_code.php" method="POST">
                <!-- Exact Hidden fields required -->
                <input type="hidden" name="id" value="<?= htmlspecialchars($student['id']); ?>">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($student['user_id'] ?? ''); ?>">

                <div class="row align-items-end g-3">
                    
                    <!-- Roll No -->
                    <div class="col-12 col-md-2 col-lg-1">
                        <label class="form-label-clean">Roll No.</label>
                        <input type="text" name="rollno" value="<?= htmlspecialchars($student['rollno']); ?>" class="form-control form-control-clean" required>
                    </div>

                    <!-- First Name -->
                    <div class="col-12 col-md-5 col-lg-3">
                        <label class="form-label-clean">First Name</label>
                        <input type="text" name="fname" value="<?= htmlspecialchars($student['fname']); ?>" class="form-control form-control-clean" required>
                    </div>

                    <!-- Father Name -->
                    <div class="col-12 col-md-5 col-lg-3">
                        <label class="form-label-clean">Father Name</label>
                        <input type="text" name="father_name" value="<?= htmlspecialchars($student['father_name']); ?>" class="form-control form-control-clean" required>
                    </div>

                    <!-- Phone No -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label-clean">Phone No</label>
                        <input type="tel" name="phone_no" value="<?= htmlspecialchars($student['phone_no']); ?>" class="form-control form-control-clean"
                               pattern="[0-9]{10}" maxlength="10" required
                               oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                               placeholder="10-digit number">
                    </div>

                    <!-- Class -->
                    <div class="col-12 col-md-6 col-lg-2">
                        <label class="form-label-clean">Class</label>
                        <select name="class" class="form-select form-control-clean" required>
                            <option value="">-- Select Class --</option>
                            <?php foreach($classes as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>" <?= ($student['class'] == $c) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($classMapping[$c] ?? ('Class ' . $c)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <!-- Action Buttons -->
                <div class="d-flex align-items-center justify-content-between pt-4 mt-4 border-top student-actions-row">
                    <!-- Delete Button on Left -->
                    <button type="submit" name="delete_student" value="<?= htmlspecialchars($student['id']); ?>"
                            class="btn btn-outline-danger"
                            style="border-radius: 10px; font-weight: 600; padding: 0.55rem 1.3rem;"
                            onclick="return confirm('Are you sure you want to delete this student?');">
                        <i class="fa-solid fa-trash-can me-1"></i> Delete
                    </button>

                    <!-- Update Button on Right -->
                    <div class="d-flex align-items-center gap-2 actions-right-group">
                        <a href="get-student.php<?= !empty($student['class']) ? '?class=' . urlencode($student['class']) : '' ?>" 
                           class="btn btn-light border" style="border-radius: 10px; font-weight: 600; padding: 0.55rem 1.25rem; color: #475569;">
                            Cancel
                        </a>
                        <button type="submit" name="update_student" class="btn-primary-action">
                            <i class="fa-solid fa-check me-1"></i> Update
                        </button>
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
