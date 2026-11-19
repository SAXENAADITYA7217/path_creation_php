<?php
// ranking.php (Student Ranking Page with Tie Handling and All Exams Option)
include('authentication.php'); 
include('includes/header.php'); 

// --- Class Name Mapping (Must match the existing report card map) ---
$class_map = [
    "100" => "Nursery", "101" => "LKG", "102" => "UKG", "1" => "Class 1", "2" => "Class 2", "3" => "Class 3",
    "4" => "Class 4", "5" => "Class 5", "6" => "Class 6", "7" => "Class 7", "8" => "Class 8", "9" => "Class 9",
    "10" => "Class 10", "11" => "Class 11 Sci (PCB)", "14" => "Class 11 Arts", "15" => "Class 12 Sci (PCB)", 
    "12" => "Class 11 Sci (PCM)", "13" => "Class 11 Comm.", 
    "16" => "Class 12 Sci (PCM)", "17" => "Class 12 Comm.", "18" => "Class 12 Arts"
];

// Define ALL exam types (Must match the existing report card map)
$all_exam_types = [
    1 => 'Unit Test 1', 
    2 => 'Unit Test 2',
    5 => 'Half Year Exam',
    3 => 'Unit Test 3',
    4 => 'Unit Test 4', 
    6 => 'Final Exam', 
];

$teacher_id = $_SESSION['auth_user']['user_id'] ?? $_SESSION['auth_user']['id'] ?? '';
$assigned_classes = [];
if (!empty($teacher_id)) {
    $q_assigned = "SELECT DISTINCT class_code FROM teacher_subject WHERE teacher_id = ? ORDER BY CAST(class_code AS UNSIGNED) ASC, class_code ASC";
    $stmt_assigned = mysqli_prepare($con, $q_assigned);
    if ($stmt_assigned) {
        mysqli_stmt_bind_param($stmt_assigned, "i", $teacher_id);
        mysqli_stmt_execute($stmt_assigned);
        $res_assigned = mysqli_stmt_get_result($stmt_assigned);
        while ($ac_row = mysqli_fetch_assoc($res_assigned)) {
            $assigned_classes[] = strval($ac_row['class_code']);
        }
        mysqli_stmt_close($stmt_assigned);
    }

    if (empty($assigned_classes)) {
        $q_ct = mysqli_query($con, "SELECT DISTINCT class FROM class_teacher WHERE user_id = '$teacher_id'");
        if ($q_ct && mysqli_num_rows($q_ct) > 0) {
            while ($ct_row = mysqli_fetch_assoc($q_ct)) {
                $assigned_classes[] = strval($ct_row['class']);
            }
        }
    }
}
$classes_to_show = !empty($assigned_classes) ? $assigned_classes : array_keys($class_map);

// Passing percentage (33%)
define('PASS_PERCENTAGE', 0.33);

// Fetch selections from POST, use 0 as default for All Exams if not yet posted
$selected_class_code = $_POST['class_code'] ?? '';
// Ensure test code is treated as an integer (0 for All Exams)
$selected_test_code = isset($_POST['test_code']) ? intval($_POST['test_code']) : ''; 
$students_data = [];
$total_students = 0;

if (!isset($con) || !$con) {
    die("<div class='container mt-4'><p class='alert alert-danger'>Database connection failed.</p></div>");
}

// --- Data Processing (when form is submitted) ---
if (!empty($selected_class_code) && $selected_test_code !== '') {
    
    // --- IMPORTANT: Marks Aggregation Logic ---
    // The previous logic (from print_report_card.php) indicated marks were stored across columns.
    // Obtained Marks = ss.marks_obtained (Practical) + ss.practical_marks (Theory)
    // Max Marks = ss.total_marks (Practical Max) + ss.total_practical (Theory Max)
    // This query sums all these fields for a student across all relevant subject/test records.
    
    $base_query = "
        SELECT 
            u.id AS student_id, u.fname, u.lname, u.rollno,
            SUM(ss.marks_obtained + ss.practical_marks) AS total_obtained,
            SUM(ss.total_marks + ss.total_practical) AS total_max
        FROM student_score ss
        JOIN users u ON ss.student_id = u.id
        WHERE u.class = ? ";

    $ranking_query = "";
    $stmt = null;

    if ($selected_test_code > 0) {
        // Case 1: Specific Test Selected
        $ranking_query = $base_query . " AND ss.test_code = ? ";
        $ranking_query .= " GROUP BY u.id, u.fname, u.lname, u.rollno
                            ORDER BY total_obtained DESC, u.rollno ASC";
        
        $stmt = mysqli_prepare($con, $ranking_query);
        mysqli_stmt_bind_param($stmt, "si", $selected_class_code, $selected_test_code);

    } else {
        // Case 2: All Exams Selected (test_code = 0) - Calculate overall rank
        $ranking_query = $base_query;
        $ranking_query .= " GROUP BY u.id, u.fname, u.lname, u.rollno
                            ORDER BY total_obtained DESC, u.rollno ASC";

        $stmt = mysqli_prepare($con, $ranking_query);
        // Note: Only the class code is bound when fetching all exams
        mysqli_stmt_bind_param($stmt, "s", $selected_class_code);
    }
    
    if ($stmt) {
        // Execute the statement and fetch results
        if (!mysqli_stmt_execute($stmt)) {
            // Handle execution error
            error_log("SQL Error during ranking query execution: " . mysqli_stmt_error($stmt));
            echo "<div class='container mt-4'><p class='alert alert-danger'>Error fetching student ranks. Check database logs.</p></div>";
            mysqli_stmt_close($stmt);
            // Re-fetch the current form selections to maintain the UI state
            $selected_class_code = $_POST['class_code'] ?? '';
            $selected_test_code = isset($_POST['test_code']) ? intval($_POST['test_code']) : '';
        } else {
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $students_data[] = $row;
            }
            mysqli_stmt_close($stmt);

            // 2. Apply Ranking Logic (Dense Ranking)
            if (!empty($students_data)) {
                $rank = 0;
                $prev_score = -1;
                $rank_counter = 0; 
                $total_students = count($students_data);

                foreach ($students_data as $i => &$student) {
                    $rank_counter++;
                    // If the current score is different from the previous one, update the rank
                    if ($student['total_obtained'] != $prev_score) {
                        $rank = $rank_counter;
                        $prev_score = $student['total_obtained'];
                    }
                    $student['rank'] = $rank;
                    $student['percentage'] = ($student['total_max'] > 0) ? 
                                              round(($student['total_obtained'] / $student['total_max']) * 100, 2) : 0;
                }
                unset($student); // Break the reference
            }
        }
    } else {
        echo "<div class='container mt-4'><p class='alert alert-danger'>Database query preparation failed: " . mysqli_error($con) . "</p></div>";
    }
}
?>

<style>
.table-responsive {
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch;
}
.table-responsive table {
    min-width: 650px;
}
@media (max-width: 768px) {
    .container {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }
    .card-body {
        padding: 1rem !important;
    }
}
</style>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow border-dark">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0"><i class="fas fa-list-ol"></i> Class Performance Ranking</h4>
                </div>
                <div class="card-body">

                    <!-- Filter Form -->
                    <form method="POST" action="ranking.php" class="mb-4 p-3 border rounded bg-light">
                        <div class="form-row align-items-end">
                            <!-- Class Selection -->
                            <div class="form-group col-md-5 mb-2">
                                <label for="class_code" class="font-weight-bold text-dark">Select Class:</label>
                                <select name="class_code" id="class_code" class="form-control" required>
                                    <option value="">-- Choose Class --</option>
                                    <?php 
                                    foreach ($classes_to_show as $code) {
                                        $name = $class_map[$code] ?? ('Class ' . $code);
                                        $selected = ($selected_class_code == $code) ? 'selected' : '';
                                        echo '<option value="' . $code . '" ' . $selected . '>' . htmlspecialchars($name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Exam Type Selection -->
                            <div class="form-group col-md-5 mb-2">
                                <label for="test_code" class="font-weight-bold text-dark">Select Exam Type:</label>
                                <select name="test_code" id="test_code" class="form-control" required>
                                    <option value="0" <?= ($selected_test_code === 0) ? 'selected' : '' ?>>All Exams (Overall Rank)</option>
                                    <?php 
                                    foreach ($all_exam_types as $code => $name) {
                                        $selected = ($selected_test_code == $code) ? 'selected' : '';
                                        echo '<option value="' . $code . '" ' . $selected . '>' . htmlspecialchars($name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group col-md-2 mb-2">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Show Ranking</button>
                            </div>
                        </div>
                    </form>

                    <!-- Ranking Results -->
                    <?php if (!empty($selected_class_code) && $selected_test_code !== ''): ?>
                        <?php 
                        $class_display = $class_map[$selected_class_code] ?? "Unknown Class";
                        $test_display = ($selected_test_code === 0) ? "All Exams (Overall)" : ($all_exam_types[$selected_test_code] ?? "Unknown Test");
                        ?>
                        <div class="alert alert-info text-center">
                            <h5 class="mb-0">Ranking for **<?= htmlspecialchars($class_display) ?>** in **<?= htmlspecialchars($test_display) ?>** (Total Students: <?= $total_students ?>)</h5>
                            <small class="text-muted">Students with the same total score share the same rank.</small>
                        </div>

                        <?php if (empty($students_data)): ?>
                            <div class="alert alert-warning text-center">No results found for the selected criteria.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover mt-3">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Rank</th>
                                            <th>Roll No.</th>
                                            <th>Student Name</th>
                                            <th>Marks Obtained</th>
                                            <th>Max Marks</th>
                                            <th>Percentage (%)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students_data as $student): ?>
                                            <?php
                                            $percentage = $student['percentage'];
                                            $is_pass = ($percentage >= PASS_PERCENTAGE * 100);
                                            $status_class = $is_pass ? 'badge badge-success' : 'badge badge-danger';
                                            $status_text = $is_pass ? 'Pass' : 'Fail';
                                            $row_class = $is_pass ? '' : 'table-danger';
                                            ?>
                                            <tr class="<?= $row_class ?>">
                                                <td class="font-weight-bold h5 text-center align-middle">
                                                    <?= $student['rank'] ?>
                                                </td>
                                                <td><?= htmlspecialchars($student['rollno']) ?></td>
                                                <!-- Link to print report card to verify marks -->
                                                <td><a href="print_report_card.php?student_id=<?= $student['student_id'] ?>&test_code=<?= $selected_test_code ?>" target="_blank" class="text-primary font-weight-bold"><?= htmlspecialchars($student['fname'] . ' ' . $student['lname']) ?> <i class="fas fa-external-link-alt fa-xs"></i></a></td>
                                                <td class="text-center"><?= $student['total_obtained'] ?></td>
                                                <td class="text-center"><?= $student['total_max'] ?></td>
                                                <td class="text-center font-weight-bold">
                                                    <?= $percentage ?>%
                                                </td>
                                                <td class="text-center">
                                                    <span class="<?= $status_class ?>"><?= $status_text ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include necessary footer and scripts
include('includes/footer.php');
include('includes/scripts.php');
?>