<?php 
// Ensure these files include the database connection ($con) and session management
include('studentcode.php');
include('includes/header.php');

// --- 1. Fetch logged-in user details ---
if (!isset($_SESSION['auth_user']['user_id'])) {
    echo "<div class='alert alert-danger'>Authentication error. User session not found.</div>";
    include('includes/footer.php');
    include('includes/scripts.php');
    exit();
}

$user_id = $_SESSION['auth_user']['user_id'];

// Check for database connection before proceeding
if (!isset($con) || !$con) {
    echo "<div class='alert alert-danger'>Database connection failed.</div>";
    include('includes/footer.php');
    include('includes/scripts.php');
    exit();
}

$user_query = "SELECT class FROM users WHERE id='$user_id' LIMIT 1";
$user_result = mysqli_query($con, $user_query);

if (mysqli_num_rows($user_result) == 1) {
    $user_row = mysqli_fetch_assoc($user_result);
    $class = $user_row['class'];
} else {
    echo "<div class='alert alert-danger'>User details not found or invalid ID.</div>";
    include('includes/footer.php');
    include('includes/scripts.php');
    exit();
}

// --- 2. Exam Types ---
$all_exam_types = [
    1 => 'Unit Test 1',
    2 => 'Unit Test 2',
    5 => 'Half Year Exam',
    3 => 'Unit Test 3',
    4 => 'Unit Test 4',
    6 => 'Final Exam',
];
?>

<style>
/* 🎨 Enhanced Classical & Elegant Theme */

/* --- Custom Color Variables for Cohesion --- */
:root {
    --primary-classic: #6a0572; /* Deep Purple/Maroon for elegance */
    --accent-light: #f7e7f9; /* Very light purple/pink for soft background */
    --accent-lighter: #fffaff; /* Even lighter off-white with purple tint */
    --accent-dark: #3b0042;
    --progress-color: #9c27b0; /* A brighter purple for progress */
    --bg-main: #fcfcfc; /* Off-white, soft background */
    --card-shadow: rgba(0,0,0,0.1);
}

/* --- General & Container Styling --- */
.elegant-report-box {
    background: var(--bg-main);
    border-radius: 25px; 
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); 
    border: 3px solid var(--accent-light); 
    padding: 30px;
}

.report-title {
    font-family: 'Georgia', serif; 
    color: var(--primary-classic);
    border-bottom: 3px solid var(--accent-light);
    padding-bottom: 10px;
    margin-bottom: 25px !important;
}

/* --- Form and Input Styling --- */
.exam-selection-form {
    background-color: var(--accent-light); 
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 30px !important;
    box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.05);
}

.btn-primary {
    background-color: var(--primary-classic);
    border-color: var(--primary-classic);
    transition: background-color 0.3s, transform 0.2s;
}
.btn-primary:hover {
    background-color: var(--accent-dark);
    border-color: var(--accent-dark);
    transform: translateY(-2px);
}

/* --- Subject Card Styling (Updated Background) --- */
.subject-card {
    /* Updated to include light accent color */
    background: linear-gradient(145deg, var(--accent-lighter), #ffffff); 
    border-radius: 20px;
    box-shadow: 0 6px 20px var(--card-shadow);
    text-align: center;
    padding: 30px 20px;
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}
.subject-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px var(--card-shadow);
    border-color: var(--progress-color);
}
.subject-index {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--progress-color);
}
.subject-title {
    font-weight: 600;
    font-size: 1.3rem;
    color: var(--accent-dark);
}
.subject-marks {
    font-size: 1.4rem;
    font-weight: bold;
    color: #4b5563;
}

/* --- Circular Bar (Progress Circle) Styling --- */
.progress-circle {
    position: relative;
    width: 110px; 
    height: 110px;
    margin: 0 auto 10px auto;
    background: conic-gradient(var(--progress-color) var(--progress), #e2e8f0 var(--progress));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.6s ease;
    border: 3px solid #f0f0f0; 
}
.progress-circle::after {
    content: attr(data-percent) '%';
    position: absolute;
    font-weight: 800; 
    color: var(--progress-color);
    background: #ffffff; 
    border-radius: 50%;
    width: 90px;
    height: 90px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    box-shadow: inset 0 0 8px rgba(0,0,0,0.1);
}

/* Dark Card (Alternating style - Updated Background) */
.dark-card {
    /* Updated to use a slightly deeper light accent color */
    background: linear-gradient(145deg, #f7f7f9, var(--accent-light));
}
.dark-card .subject-title { color: var(--primary-classic); }
.dark-card .subject-index { color: var(--primary-classic); }
.dark-card .progress-circle {
    background: conic-gradient(var(--primary-classic) var(--progress), #cfd8dc var(--progress)); 
}
.dark-card .progress-circle::after {
    background: #ffffff;
    color: var(--primary-classic);
}
</style>

<div class="container-fluid pt-4 px-4">
    <div class="elegant-report-box text-center p-4 shadow"> 
        <h4 class="mb-4 fw-bold report-title">📘 Student Report</h4>

        <form method="POST" class="row justify-content-center exam-selection-form">
            <div class="col-md-4 col-sm-12 mb-3 mb-md-0">
                <label class="form-label fw-bold" style="color: var(--accent-dark);">Select Exam Type</label>
                <select name="exam_type" class="form-select" required>
                    <option value="">-- Select Exam Type --</option>
                    <?php 
                    foreach ($all_exam_types as $key => $exam_name) {
                        $selected = (isset($_POST['exam_type']) && $_POST['exam_type'] == $key) ? 'selected' : '';
                        echo "<option value='".intval($key)."' $selected>$exam_name</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-2 col-sm-12 d-flex align-items-end">
                <button type="submit" name="view_result" class="btn btn-primary w-100 fw-bold">View Results</button>
            </div>
        </form>

        <?php
        if (isset($_POST['view_result']) && !empty($_POST['exam_type'])) {
            // Sanitize the input
            $selected_exam = mysqli_real_escape_string($con, $_POST['exam_type']);
            $exam_name = $all_exam_types[intval($selected_exam)] ?? "Unknown Exam";

            echo "<h5 class='text-start mb-4 fw-bold result-header'>Showing Results for <span style='color: var(--progress-color);'>$exam_name</span></h5>";
            echo "<div class='row g-4 justify-content-center'>";

            // --- Fetch Subjects for the Class ---
            $subject_query = "SELECT subject FROM class_subject_info WHERE class_code='$class' AND status=1 ORDER BY subject ASC";
            $subject_result = mysqli_query($con, $subject_query);
            $subject_found = mysqli_num_rows($subject_result) > 0;

            if ($subject_found) {
                $count = 0;
                $index_letter = 'A';
                $results_displayed = false;

                while ($sub_row = mysqli_fetch_assoc($subject_result)) {
                    $subject = mysqli_real_escape_string($con, $sub_row['subject']);

                    // --- Fetch Marks for Subject and Exam Type ---
                    $marks_query = "
                        SELECT * FROM student_score 
                        WHERE student_id='$user_id' 
                        AND class_code='$class'
                        AND subject='$subject'
                        AND test_code='$selected_exam'
                        LIMIT 1
                    ";
                    $marks_result = mysqli_query($con, $marks_query);

                    if (mysqli_num_rows($marks_result) > 0) {
                        $row = mysqli_fetch_assoc($marks_result);
                        $results_displayed = true;

                        // Determine if practical marks should be included (keys 5 and 6)
                        $show_practical = in_array(intval($selected_exam), [5, 6]); 

                        // Extract and convert scores
                        $theory_obtained = floatval($row['marks_obtained']);
                        $theory_total = floatval($row['total_marks']);
                        $practical_obtained = floatval($row['practical_marks']);
                        $practical_total = floatval($row['total_practical']);

                        $total_obtained = $theory_obtained;
                        $total_marks = $theory_total;

                        if ($show_practical) {
                            $total_obtained += $practical_obtained;
                            $total_marks += $practical_total;
                        }

                        // Calculate percentage
                        $percentage = $total_marks > 0 ? round(($total_obtained / $total_marks) * 100) : 0;
                        
                        // Format marks cleanly (removes trailing zeros)
                        $total_obtained_display = rtrim(rtrim(number_format($total_obtained, 2, '.', ''), '0'), '.');
                        $total_marks_display = rtrim(rtrim(number_format($total_marks, 2, '.', ''), '0'), '.');
                        
                        $dark_class = ($count % 2 != 0) ? 'dark-card' : '';

                        // --- Display Card ---
                        echo "
                        <div class='col-lg-3 col-md-4 col-sm-6'>
                            <div class='subject-card $dark_class'>
                                <div class='subject-index'>$index_letter.</div>
                                <div class='subject-title'>{$sub_row['subject']}</div>
                                <div class='subject-marks'>{$total_obtained_display}/{$total_marks_display}</div>
                                <div class='progress-circle' style='--progress: {$percentage}%;' data-percent='{$percentage}'></div>
                                <div class='mt-3 text-muted small'>Score Percentage</div>
                            </div>
                        </div>";
                        $count++;
                        $index_letter = chr(ord($index_letter) + 1);
                    }
                }

                echo "</div>"; // Close row g-4
                
                // Show a message if subjects were found but no marks were entered for the specific exam
                if (!$results_displayed) {
                     echo "<div class='alert alert-info mt-4'>Results for the **$exam_name** are not yet available.</div>";
                }

            } else {
                echo "<div class='alert alert-warning mt-4'>No subjects found configured for your class.</div>";
            }
        } elseif (isset($_POST['view_result']) && empty($_POST['exam_type'])) {
             echo "<div class='alert alert-warning mt-4'>Please select an exam type to view results.</div>";
        }
        ?>
    </div>
</div>

<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>