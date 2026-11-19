<?php
include('authentication.php');
include('includes/header.php');

$id = $_GET['id'] ?? '';
if($id == ''){
    $_SESSION['message'] = "Invalid subject ID!";
    header("Location: view-subject.php");
    exit();
}

$id_esc = mysqli_real_escape_string($con, $id);
$res = mysqli_query($con, "SELECT * FROM class_subject_info WHERE id='$id_esc' LIMIT 1");
if(mysqli_num_rows($res) == 0){
    $_SESSION['message'] = "Subject not found!";
    header("Location: view-subject.php");
    exit();
}

$subjectData = mysqli_fetch_assoc($res);

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

$className = $classMapping[$subjectData['class_code']] ?? ("Class " . $subjectData['class_code']);
?>

<div class="dashboard-container">
    <!-- Breadcrumb -->
    <ol class="breadcrumb mb-3">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="view-subject.php">Subjects</a></li>
        <li class="breadcrumb-item active">Edit Subject</li>
    </ol>

    <!-- Page Header matching modern SaaS theme -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="user-header-icon-box">
                <i class="fas fa-pencil-alt"></i>
            </div>
            <div>
                <h2 class="page-main-heading mb-1">Edit Subject</h2>
                <p class="page-sub-heading mb-0">Modify the subject details or visibility status for this class.</p>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="view-subject.php?class=<?= urlencode($subjectData['class_code']); ?>" class="btn-outline-back">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- Edit Subject Card -->
    <div class="teacher-card" style="max-width: 600px;">
        <form action="code.php" method="POST">
            <input type="hidden" name="subject_id" value="<?= $subjectData['id'] ?>">
            <input type="hidden" name="class_code" value="<?= $subjectData['class_code'] ?>">

            <!-- Class (Disabled/Readonly) -->
            <div class="mb-4">
                <label class="form-label-custom">Class</label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-graduation-cap input-icon"></i>
                    <input type="text" class="form-control custom-input" value="<?= htmlspecialchars($className) ?>" disabled style="background-color: #f8fafc; font-weight: 600;">
                </div>
            </div>

            <!-- Subject Name -->
            <div class="mb-4">
                <label class="form-label-custom">Subject Name <span class="text-danger">*</span></label>
                <div class="input-icon-wrapper">
                    <i class="fas fa-book-open input-icon"></i>
                    <input type="text" name="subject" class="form-control custom-input" value="<?= htmlspecialchars($subjectData['subject']) ?>" required>
                </div>
            </div>

            <!-- Status -->
            <div class="mb-4">
                <label class="form-label-custom">Visibility Status <span class="text-danger">*</span></label>
                <div class="position-relative">
                    <i class="fas fa-toggle-on position-absolute dropdown-icon" style="left: 15px; top: 50%; transform: translateY(-50%); color: var(--pca-primary); font-size: 0.95rem; pointer-events: none; z-index: 5;"></i>
                    <select name="status" class="form-select select-with-icon" style="padding-left: 42px !important; border-radius: 12px; height: 46px; border: 1.5px solid #dcdff0; background-color: #fff; font-size: 0.9rem; font-weight: 500;">
                        <option value="1" <?= $subjectData['status'] == 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $subjectData['status'] == 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <button type="submit" name="update_subject" class="btn-primary-action">
                    <i class="far fa-save me-2"></i> Update Subject
                </button>
                <a href="view-subject.php?class=<?= urlencode($subjectData['class_code']); ?>" class="btn-secondary-action">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
