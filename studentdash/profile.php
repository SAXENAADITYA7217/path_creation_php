<?php
include('studentcode.php');
include('includes/header.php');

// ✅ Class Mapping
$class_names = [
    '100' => 'Nursery', '101' => 'LKG', '102' => 'UKG',
    '1' => 'Class 1', '2' => 'Class 2', '3' => 'Class 3', '4' => 'Class 4',
    '5' => 'Class 5', '6' => 'Class 6', '7' => 'Class 7', '8' => 'Class 8',
    '9' => 'Class 9', '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)', '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm. (with Math)', '14' => 'Class 11 Comm. (without Math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)', '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (with Math)', '19' => 'Class 12 Comm. (without Math)',
    '20' => 'Class 12 Arts'
];

// ✅ Ensure login
if (!isset($_SESSION['auth_user']['user_id'])) {
    $_SESSION['message'] = "Please login to continue.";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['auth_user']['user_id'];

// ✅ Fetch Student Info
$user_query = "SELECT id,fname,lname,email,father_name,phone_no,user_photo,class FROM users WHERE id='$user_id' LIMIT 1";
$user_result = mysqli_query($con, $user_query);
$user = mysqli_fetch_assoc($user_result);
$class_code = $user['class'];

// ✅ Profile Photo (new photo after upload)
$photo_source = !empty($user['user_photo']) ? "uploads/profile_image/" . $user['user_photo'] : "uploads/profile_image/default.png";

// ✅ Fetch Subjects
$subjects_query = "SELECT subject FROM class_subject_info WHERE class_code='$class_code'";
$subjects_result = mysqli_query($con, $subjects_query);
$class_subjects = [];
while ($row = mysqli_fetch_assoc($subjects_result)) $class_subjects[] = $row['subject'];

// ✅ Fetch Teachers
$teachers_query = "SELECT ts.subject, u.fname, u.lname
                   FROM teacher_subject ts
                   JOIN users u ON ts.teacher_id = u.id
                   WHERE ts.class_code='$class_code'";
$teachers_result = mysqli_query($con, $teachers_query);
$teacher_subjects = [];
while ($row = mysqli_fetch_assoc($teachers_result)) {
    $teacher_subjects[$row['subject']][] = $row['fname'] . " " . $row['lname'];
}

$border_colors = ['#1cc88a', '#4e73df', '#f6c23e', '#e74a3b', '#36b9cc', '#6f42c1']; // Adjusted for a more vibrant dashboard look
$color_index = 0;
// PHP variable to hold the full class name
$full_class_name = htmlspecialchars($class_names[$class_code] ?? 'Unknown Class');
?>

<style>
body {
    /* Soft Gradient Background */
    background: linear-gradient(135deg, #f0f4f8 0%, #e0e7ee 100%);
    font-family: 'Poppins', sans-serif;
}
.main-card {
    border-radius: 20px;
    /* Stronger, more modern shadow */
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s, box-shadow 0.3s;
}
.main-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}
.profile-banner {
    height: 150px; /* Slightly taller banner */
    /* Vibrant Gradient Banner */
    background: linear-gradient(120deg, #4e73df 0%, #36b9cc 100%);
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
}
.profile-img-container {
    position: relative;
    width: 150px;
    height: 150px;
    margin: -75px auto 0;
    border-radius: 50%;
    background-color: white;
    padding: 6px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    transition: all 0.5s ease-in-out;
}
.profile-img {
    width: 138px !important;
    height: 138px !important;
    object-fit: cover;
    /* "Glowing" circular border */
    border: 4px solid #f6c23e;
    transition: all 0.5s ease-in-out;
}
.profile-img-container:hover .profile-img {
    transform: scale(1.05) rotate(3deg);
    box-shadow: 0 0 15px 5px rgba(246, 194, 62, 0.7);
}
.subject-card {
    border-radius: 15px;
    /* Lighter, softer card background */
    background: #ffffff;
    padding: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    transition: all 0.3s;
    overflow: hidden;
}
.subject-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
.info-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.05);
}
.list-group-item {
    border-color: #f0f4f8;
    padding: 12px 20px;
    background: transparent;
    transition: background-color 0.2s;
}
.list-group-item:hover {
    background-color: #f7f9fb;
}
.status-card {
    border-radius: 15px;
    background: linear-gradient(45deg, #1cc88a, #4e73df); /* Dual-color gradient for prominence */
    color: white;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    text-align: center;
}
/* Fade-in animation for content */
.fade-in {
    animation: fadeIn 1s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="container py-5">
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-info alert-dismissible fade show text-center fade-in" role="alert">
            <?= htmlspecialchars($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <h2 class="text-center mb-5 fw-bolder text-dark fade-in">
                <i class="fas fa-school me-2 text-primary"></i>Student Portal Dashboard
            </h2>

            <div class="row justify-content-center mb-4 fade-in" style="animation-delay: 0.05s;">
                <div class="col-md-10 col-lg-8">
                    <div class="status-card">
                        <h4 class="mb-0 fw-bold">
                            <i class="fas fa-graduation-cap me-2 text-warning"></i> Current Class: **<?= $full_class_name ?>**
                        </h4>
                    </div>
                </div>
            </div>
            <div class="card main-card fade-in">
                <div class="profile-banner"></div>

                <div class="card-body p-4 pt-0">
                    <div class="text-center mb-5">
                        <div class="profile-img-container">
                            <img src="<?= htmlspecialchars($photo_source) ?>" class="rounded-circle profile-img" alt="Profile Photo">
                        </div>

                        <button type="button" class="btn btn-primary fw-bold mt-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-camera me-1"></i> Update Photo
                        </button>

                        <h3 class="fw-bolder mb-1 mt-3 text-dark"><?= htmlspecialchars($user['fname'].' '.$user['lname']) ?></h3>
                        <p class="text-muted"><i class="fas fa-user-graduate me-2 text-success"></i>Student</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-lg-6 fade-in" style="animation-delay: 0.1s;">
                            <div class="card info-card h-100">
                                <div class="card-header bg-primary text-white border-0" style="border-radius: 15px 15px 0 0;">
                                    <h5 class="fw-bold mb-0"><i class="fas fa-id-badge me-2"></i>Personal Details</h5>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <i class="fas fa-user-circle me-3 text-secondary"></i>
                                            <strong>Full Name:</strong> <?= htmlspecialchars($user['fname'].' '.$user['lname']) ?>
                                        </li>
                                        <li class="list-group-item">
                                            <i class="fas fa-male me-3 text-secondary"></i>
                                            <strong>Father's Name:</strong> <?= htmlspecialchars($user['father_name']) ?>
                                        </li>
                                        <li class="list-group-item">
                                            <i class="fas fa-envelope me-3 text-secondary"></i>
                                            <strong>Email:</strong> <?= htmlspecialchars($user['email']) ?>
                                        </li>
                                        <li class="list-group-item">
                                            <i class="fas fa-phone-alt me-3 text-secondary"></i>
                                            <strong>Phone:</strong> +91 <?= htmlspecialchars($user['phone_no']) ?>
                                        </li>
                                        <li class="list-group-item bg-light fw-bold">
                                            <i class="fas fa-chalkboard me-3 text-primary"></i>
                                            <strong class="text-primary">Class:</strong> <?= $full_class_name ?>
                                        </li>
                                        </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 fade-in" style="animation-delay: 0.2s;">
                            <div class="card info-card h-100">
                                <div class="card-header bg-success text-white border-0" style="border-radius: 15px 15px 0 0;">
                                    <h5 class="fw-bold mb-0"><i class="fas fa-chalkboard-teacher me-2"></i>Subjects & Teachers</h5>
                                </div>
                                <div class="card-body pt-3">
                                    <?php if(!empty($class_subjects)): ?>
                                        <div class="row row-cols-1 g-3">
                                            <?php foreach($class_subjects as $subject): ?>
                                                <?php
                                                    $current_color = $border_colors[$color_index++ % count($border_colors)];
                                                ?>
                                                <div class="col">
                                                    <div class="subject-card position-relative" style="border-left:6px solid <?= $current_color ?>;">
                                                         <h6 class="fw-bold mb-2 text-dark">
                                                            <i class="fas fa-book-open me-2" style="color: <?= $current_color ?>;"></i>
                                                            <?= htmlspecialchars($subject) ?>
                                                        </h6>
                                                        <p class="text-muted mb-0">
                                                            <small class="fw-bold d-block mb-1">Teacher(s):</small>
                                                            <?php
                                                            if(isset($teacher_subjects[$subject])) {
                                                                foreach($teacher_subjects[$subject] as $t_name) {
                                                                    // Use a lighter, more vibrant badge color
                                                                    echo '<span class="badge bg-primary me-2 mb-1 fw-normal shadow-sm">
                                                                        <i class="fas fa-user-tie me-1"></i> '.htmlspecialchars($t_name).'
                                                                    </span>';
                                                                }
                                                            } else {
                                                                echo '<span class="badge bg-warning text-dark fw-normal">No teacher assigned</span>';
                                                            }
                                                            ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning text-center fw-bold">
                                            <i class="fas fa-exclamation-triangle me-2"></i>No subjects assigned for this class.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                <div class="modal-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h5 class="modal-title"><i class="fas fa-image me-2"></i>Upload Profile Photo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="studentcode.php" enctype="multipart/form-data">
                    <div class="modal-body p-4">
                        <label for="user_photo_input" class="form-label fw-bold">Select New Photo:</label>
                        <input type="file" id="user_photo_input" name="user_photo" accept=".jpg,.jpeg,.png,.gif" class="form-control form-control-lg" required>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i> Allowed formats: JPG, JPEG, PNG, GIF (max 5MB)
                        </small>
                    </div>
                    <div class="modal-footer justify-content-center border-0 p-3">
                        <button type="submit" name="upload_photo" class="btn btn-success btn-lg fw-bold w-100 shadow">
                            <i class="fas fa-cloud-upload-alt me-1"></i> Save Photo
                        </button>
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