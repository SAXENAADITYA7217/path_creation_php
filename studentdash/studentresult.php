<?php 
include('studentcode.php');
include('includes/header.php');

// --- Fetch logged-in user details ---
$user_id = $_SESSION['auth_user']['user_id'];

$user_query = "SELECT * FROM users WHERE id='$user_id'";
$user_result = mysqli_query($con, $user_query);

if (mysqli_num_rows($user_result) == 1) {
    $user_row = mysqli_fetch_assoc($user_result);
    $full_name = $user_row['fname'] . ' ' . $user_row['lname'];
    $email = $user_row['email'];
    $class = $user_row['class'];
} else {
    echo "<div class='alert alert-danger'>User not found.</div>";
    include('includes/footer.php');
    include('includes/scripts.php');
    exit();
}

// --- Exam Types ---
$all_exam_types = [
    1 => 'Unit Test 1',
    2 => 'Unit Test 2',
    5 => 'Half Year Exam',
    3 => 'Unit Test 3',
    4 => 'Unit Test 4',
    6 => 'Final Exam',
];

// --- Fetch subjects for student's class ---
$subject_query = "SELECT subject FROM class_subject_info WHERE class_code='$class' AND status=1";
$subject_result = mysqli_query($con, $subject_query);
?>

<style>
/* 🎨 Classical and Refined Layout */
.report-card {
    max-width: 650px;
    width: 100%;
    border-radius: 15px;
    transition: all 0.3s ease-in-out;
    /* Richer shadow and subtle border for a classical feel */
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15), 0 0 0 5px var(--bs-primary);
    border: none; /* Removing default border */
}

.report-card:hover {
    transform: translateY(-5px); /* Lift effect */
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2), 0 0 0 5px var(--bs-primary);
}

.form-select, .btn {
    border-radius: 10px !important;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column !important;
    }
    .form-row > div {
        width: 100% !important;
    }
}

/* Header and Detail Styling */
.card-header-custom {
    background-color: var(--bs-primary);
    color: white;
    font-size: 1.5rem;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    padding: 1.5rem;
    font-weight: bold;
}

.detail-row {
    padding: 8px 0;
    border-bottom: 1px dashed #e9ecef; /* Subtle separation */
}

.detail-row:last-child {
    border-bottom: none;
}

/* Progress Bar Enhancement */
.progress {
    height: 20px; /* Thicker progress bar */
    border-radius: 15px;
    background-color: #e9ecef;
    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}

.progress-bar {
    font-size: 12px;
    line-height: 20px;
    font-weight: bold;
}

.status-badge-custom {
    font-size: 1rem;
    padding: .5em 1em;
    border-radius: .25rem;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.bg-danger-custom {
    background-color: #dc3545 !important;
    color: white;
}

.bg-success-custom {
    background-color: #28a745 !important;
    color: white;
}
</style>

<div class="container-fluid pt-4 px-4">
    <div class="bg-light text-center rounded p-4 shadow">
        <h4 class="mb-4 fw-bold text-primary">📘 Student Report</h4>

        <form method="POST" class="row g-3 justify-content-center align-items-end form-row mb-5">
            <div class="col-md-4 col-sm-12">
                <label class="form-label fw-bold">Select Subject</label>
                <select name="subject" class="form-select" required>
                    <option value="">-- Select Subject --</option>
                    <?php 
                    if (mysqli_num_rows($subject_result) > 0) {
                        while ($row = mysqli_fetch_assoc($subject_result)) {
                            $selected = (isset($_POST['subject']) && $_POST['subject'] == $row['subject']) ? 'selected' : '';
                            echo "<option value='{$row['subject']}' $selected>{$row['subject']}</option>";
                        }
                    } else {
                        echo "<option disabled>No subjects found</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4 col-sm-12">
                <label class="form-label fw-bold">Select Exam Type</label>
                <select name="exam_type" class="form-select" required>
                    <option value="">-- Select Exam Type --</option>
                    <?php 
                    foreach ($all_exam_types as $key => $exam_name) {
                        $selected = (isset($_POST['exam_type']) && $_POST['exam_type'] == $key) ? 'selected' : '';
                        echo "<option value='$key' $selected>$exam_name</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-2 col-sm-12">
                <button type="submit" name="view_result" class="btn btn-primary w-100 fw-bold mt-2">View Result</button>
            </div>
        </form>

        <?php
        if (isset($_POST['view_result'])) {
            $selected_subject = mysqli_real_escape_string($con, $_POST['subject']);
            $selected_exam = mysqli_real_escape_string($con, $_POST['exam_type']);

            $marks_query = "
                SELECT * FROM student_score 
                WHERE student_id='$user_id' 
                AND class_code='$class'
                AND subject='$selected_subject'
                AND test_code='$selected_exam'
            ";

            $marks_result = mysqli_query($con, $marks_query);

            if (mysqli_num_rows($marks_result) > 0) {
                $row = mysqli_fetch_assoc($marks_result);

                // ✅ Calculation
                $exam_name = $all_exam_types[$selected_exam];
                $show_practical = in_array($exam_name, ['Half Year Exam', 'Final Exam']);

                $theory_obtained = $row['marks_obtained'];
                $theory_total = $row['total_marks'];
                $practical_obtained = $row['practical_marks'];
                $practical_total = $row['total_practical'];

                $total_obtained = $theory_obtained;
                $total_marks = $theory_total;

                if ($show_practical) {
                    $total_obtained += $practical_obtained;
                    $total_marks += $practical_total;
                }

                $percentage = $total_marks > 0 ? round(($total_obtained / $total_marks) * 100, 2) : 0;
                $status_text = ($percentage < 33) ? 'FAIL' : 'PASS';
                $status_class = ($percentage < 33) ? 'bg-danger-custom' : 'bg-success-custom';
                $status_text_color = ($percentage < 33) ? 'text-danger' : 'text-success';

                // ✅ Card UI - MODIFIED
                echo "
                <div class='mt-5 d-flex justify-content-center'>
                    <div class='card text-start shadow-lg report-card'>
                        <div class='card-header-custom text-center'>
                            📊 **Result: {$exam_name}**
                        </div>
                        <div class='card-body p-4'>
                            <div class='d-flex justify-content-between mb-3 detail-row'>
                                <span class='fw-bold text-dark'>Subject:</span> 
                                <span class='fw-bold text-primary'>{$row['subject']}</span>
                            </div>

                            <div class='d-flex justify-content-between mb-3 detail-row'>
                                <span class='fw-bold text-dark'>Exam Type:</span> 
                                <span class='text-primary'>{$exam_name}</span>
                            </div>

                            <h6 class='mt-4 mb-3 text-muted border-bottom pb-2'>Theory Marks</h6>

                            <div class='d-flex justify-content-between mb-2 detail-row'>
                                <span>Marks Obtained:</span> 
                                <span class='fw-bold'>{$theory_obtained}</span>
                            </div>

                            <div class='d-flex justify-content-between mb-4 detail-row'>
                                <span>Maximum Marks:</span> 
                                <span class='fw-bold'>{$theory_total}</span>
                            </div>";

                if ($show_practical) {
                    echo "
                            <h6 class='mt-4 mb-3 text-muted border-bottom pb-2'>Practical Marks</h6>

                            <div class='d-flex justify-content-between mb-2 detail-row'>
                                <span>Marks Obtained:</span> 
                                <span class='fw-bold'>{$practical_obtained}</span>
                            </div>
                            <div class='d-flex justify-content-between mb-4 detail-row'>
                                <span>Maximum Marks:</span> 
                                <span class='fw-bold'>{$practical_total}</span>
                            </div>";
                }

                echo "
                            <div class='bg-light p-3 rounded mb-4 mt-4 border-start border-3 border-primary'>
                                <div class='d-flex justify-content-between mb-2'>
                                    <span class='fw-bold text-dark'>🎯 Grand Total Obtained:</span> 
                                    <span class='fw-bold text-primary fs-5'>{$total_obtained}</span>
                                </div>
                                <div class='d-flex justify-content-between'>
                                    <span class='fw-bold text-dark'>🧾 Grand Total Maximum:</span> 
                                    <span class='fw-bold text-primary fs-5'>{$total_marks}</span>
                                </div>
                            </div>
                            
                            <h6 class='mt-4 mb-3 text-muted border-bottom pb-2'>Final Result</h6>

                            <div class='d-flex justify-content-between mb-3'>
                                <span class='fw-bold text-dark'>Percentage:</span> 
                                <span class='fw-bold {$status_text_color}'>{$percentage}%</span>
                            </div>

                            <div class='progress mb-4'>
                                <div class='progress-bar {$status_class}' role='progressbar' style='width: {$percentage}%;' aria-valuenow='{$percentage}' aria-valuemin='0' aria-valuemax='100'>{$percentage}%</div>
                            </div>

                            <div class='text-center'>
                                <span class='status-badge-custom {$status_class}'>{$status_text}</span>
                            </div>
                        </div>
                    </div>
                </div>";
            } else {
                echo "<div class='alert alert-warning mt-4'>No marks found for this subject and exam type.</div>";
            }
        }
        ?>
    </div>
</div>

<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>