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

if (isset($_GET['id'])) {
    $attendance_id = mysqli_real_escape_string($con, $_GET['id']);
    $query = "
        SELECT a.*, u.fname, u.lname, u.rollno, u.class 
        FROM attendance a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.id = '$attendance_id' 
        LIMIT 1
    ";
    $query_run = mysqli_query($con, $query);

    if ($query_run && mysqli_num_rows($query_run) > 0) {
        $attendance = mysqli_fetch_assoc($query_run);
    } else {
        $_SESSION['message'] = "No attendance record found.";
        header('Location: attendance-view.php');
        exit();
    }
} else {
    $_SESSION['message'] = "Invalid request.";
    header('Location: attendance-view.php');
    exit();
}

$retClass = isset($_GET['class']) ? trim($_GET['class']) : $attendance['class'];
$retDate  = isset($_GET['date']) ? trim($_GET['date']) : $attendance['date'];
$className = $classMapping[$attendance['class']] ?? ("Class " . $attendance['class']);
$fullName = trim($attendance['fname'] . ' ' . $attendance['lname']);
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="attendance-view.php?class=<?= urlencode($retClass); ?>&date=<?= urlencode($retDate); ?>">Attendance</a></li>
        <li class="breadcrumb-item active">Edit Attendance</li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-user-edit"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Edit Student Attendance</h2>
                <p class="page-sub-heading mb-0">Modify the individual attendance status or recorded date for this student.</p>
            </div>
        </div>
        <div>
            <a href="attendance-view.php?class=<?= urlencode($retClass); ?>&date=<?= urlencode($retDate); ?>" class="btn-outline-back text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i> Back to Attendance
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="teacher-card">
                <!-- Student Profile Banner -->
                <div class="d-flex align-items-center gap-3 p-3 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 52px; height: 52px; background: #ede9fe; font-size: 1.25rem; flex-shrink: 0;">
                        <?= strtoupper(substr($attendance['fname'], 0, 1)); ?>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($fullName); ?></h5>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge bg-light text-secondary border px-2 py-1" style="border-radius: 6px; font-size: 0.82rem;">
                                Roll No: <strong><?= htmlspecialchars($attendance['rollno'] ?: '-'); ?></strong>
                            </span>
                            <span class="badge-class-pill"><?= htmlspecialchars($className); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Update Form -->
                <form action="code.php" method="POST">
                    <input type="hidden" name="attendance_id" value="<?= $attendance['id']; ?>">
                    <input type="hidden" name="class" value="<?= htmlspecialchars($retClass); ?>">

                    <div class="row g-3 mb-4">
                        <!-- Date Input -->
                        <div class="col-md-6">
                            <label for="date" class="form-label-custom mb-2">
                                <i class="far fa-calendar-alt text-primary me-1"></i> Recorded Date <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative">
                                <i class="fas fa-calendar-day position-absolute dropdown-icon" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                                <input type="date" 
                                       name="date" 
                                       id="date" 
                                       value="<?= htmlspecialchars($attendance['date']); ?>" 
                                       class="form-control" 
                                       style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;" 
                                       required>
                            </div>
                        </div>

                        <!-- Status Selector -->
                        <div class="col-md-6">
                            <label class="form-label-custom mb-2">
                                <i class="fas fa-toggle-on text-primary me-1"></i> Attendance Status <span class="text-danger">*</span>
                            </label>
                            <div class="d-block pt-1">
                                <div class="att-toggle-group">
                                    <input type="radio" 
                                           class="att-radio" 
                                           name="status" 
                                           id="status_present" 
                                           value="1" 
                                           <?= ($attendance['status'] == 1) ? 'checked' : ''; ?>>
                                    <label class="att-toggle-label att-label-present" for="status_present" style="padding: 0.55rem 1.4rem;">
                                        <i class="fas fa-check"></i> Present
                                    </label>

                                    <input type="radio" 
                                           class="att-radio" 
                                           name="status" 
                                           id="status_absent" 
                                           value="0" 
                                           <?= ($attendance['status'] == 0) ? 'checked' : ''; ?>>
                                    <label class="att-toggle-label att-label-absent" for="status_absent" style="padding: 0.55rem 1.4rem;">
                                        <i class="fas fa-times"></i> Absent
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <button type="submit" name="update_attendance" class="btn-primary-action px-4">
                            <i class="far fa-save me-2"></i> Update Attendance
                        </button>
                        <a href="attendance-view.php?class=<?= urlencode($retClass); ?>&date=<?= urlencode($retDate); ?>" class="btn-outline-back text-decoration-none">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
