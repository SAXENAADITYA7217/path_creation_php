<?php
// teacher_code.php
// Complete, cleaned and corrected version
// Assumptions:
// - authentication.php starts session and provides logged-in user checks
// - includes/connection.php sets $con = mysqli_connect(...)


include('authentication.php');

if (!isset($con) || !$con) {
    die("Database connection not available.");
}

// -------------------------------
// Exam type mapping
// -------------------------------
$exam_type_map = [
    'unit_test_1' => 1,
    'unit_test_2' => 2,
    'unit_test_3' => 3,
    'unit_test_4' => 4,
    'half_year'   => 5,
    'final'       => 6,
    'class_test'  => 7
];

// -------------------------------
// AJAX: Load subjects for a class
// -------------------------------
/*
if (isset($_POST['action']) && $_POST['action'] === 'load_subjects') {
    if (!isset($con) || !$con) {
        echo '<option value="">-- DB Connection Error! --</option>';
        exit();
    }

    $class_code = mysqli_real_escape_string($con, $_POST['class_code'] ?? '');
    $teacher_id = $_SESSION['auth_user']['user_id'];

    // Use correct table name class_subject_info and status = 1 (active)
    $query = "SELECT subject FROM class_subject_info WHERE class_code = ? AND status = 1 ORDER BY subject ASC";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $class_code);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $output = '<option value="">-- Select Subject --</option>';

    if ($res && mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            $subject_name = htmlspecialchars($row['subject']);
            $output .= "<option value=\"{$subject_name}\">{$subject_name}</option>";
        }
    } else {
        $output = '<option value="">-- No Subjects Found --</option>';
    }

    echo $output;
    exit();
}  */

if (isset($_POST['action']) && $_POST['action'] === 'load_subjects') {
    if (!isset($con) || !$con) {
        echo '<option value="">-- DB Connection Error! --</option>';
        exit();
    }

    $class_code = trim($_POST['class_code'] ?? '');
    $teacher_id = intval($_SESSION['auth_user']['user_id'] ?? 0);
    $exam_type = trim($_POST['exam_type'] ?? '');
    $mark_type = trim($_POST['mark_type'] ?? 'theory');
    $filter_submitted = !empty($_POST['filter_submitted']);

    if (empty($class_code) || $teacher_id <= 0) {
        echo '<option value="">-- Select Class First --</option>';
        exit();
    }

    // Fetch all distinct subjects assigned to this teacher for this class
    $query = "SELECT DISTINCT subject FROM teacher_subject WHERE class_code = ? AND teacher_id = ? ORDER BY subject ASC";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "si", $class_code, $teacher_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $assigned_subjects = [];
    if ($res && mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            $subj = trim($row['subject'] ?? '');
            if (!empty($subj) && !in_array($subj, $assigned_subjects)) {
                $assigned_subjects[] = $subj;
            }
        }
    }
    mysqli_stmt_close($stmt);

    if (empty($assigned_subjects)) {
        echo '<option value="">-- No Subjects Found --</option>';
        exit();
    }

    // If filtering already submitted marks for an exam (used by add-report.php and add-partical-marks.php)
    if ($filter_submitted && !empty($exam_type)) {
        $exam_type_map = [
            'unit_test_1' => 1,
            'unit_test_2' => 2,
            'unit_test_3' => 3,
            'unit_test_4' => 4,
            'half_year'   => 5,
            'final'       => 6,
            'class_test'  => 7
        ];
        $test_code = $exam_type_map[$exam_type] ?? 0;

        if ($test_code > 0) {
            $submitted_subjects = [];
            if ($mark_type === 'practical') {
                $sub_query = "SELECT DISTINCT subject FROM student_score 
                              WHERE class_code = ? AND test_code = ? 
                              AND (total_practical > 0 OR practical_marks IS NOT NULL)";
            } else {
                $sub_query = "SELECT DISTINCT subject FROM student_score 
                              WHERE class_code = ? AND test_code = ? 
                              AND (total_marks > 0 OR marks_obtained IS NOT NULL)";
            }

            $sub_stmt = mysqli_prepare($con, $sub_query);
            if ($sub_stmt) {
                mysqli_stmt_bind_param($sub_stmt, "si", $class_code, $test_code);
                mysqli_stmt_execute($sub_stmt);
                $sub_res = mysqli_stmt_get_result($sub_stmt);
                if ($sub_res) {
                    while ($s_row = mysqli_fetch_assoc($sub_res)) {
                        $s_subj = trim($s_row['subject'] ?? '');
                        if (!empty($s_subj) && !in_array($s_subj, $submitted_subjects)) {
                            $submitted_subjects[] = $s_subj;
                        }
                    }
                }
                mysqli_stmt_close($sub_stmt);
            }

            // Case-insensitive filtering of already submitted subjects
            $submitted_lower = array_map('strtolower', $submitted_subjects);
            $available_subjects = array_values(array_filter($assigned_subjects, function($s) use ($submitted_lower) {
                return !in_array(strtolower(trim($s)), $submitted_lower);
            }));

            if (empty($available_subjects)) {
                echo '<option value="">-- All assigned subjects already uploaded for this exam --</option>';
                exit();
            }

            $output = '<option value="">-- Select Subject --</option>';
            foreach ($available_subjects as $subject_name) {
                $sub_esc = htmlspecialchars($subject_name);
                $output .= "<option value=\"{$sub_esc}\">{$sub_esc}</option>";
            }
            echo $output;
            exit();
        }
    }

    // Default output: All assigned subjects (for view-report.php, view-partical-marks.php)
    $output = '<option value="">-- Select Subject --</option>';
    foreach ($assigned_subjects as $subject_name) {
        $sub_esc = htmlspecialchars($subject_name);
        $output .= "<option value=\"{$sub_esc}\">{$sub_esc}</option>";
    }

    echo $output;
    exit();
}


// -------------------------------
// AJAX: Load students for a class (for marks)
// -------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'load_students') {
    $class_code = mysqli_real_escape_string($con, $_POST['class_code'] ?? '');
    

    // Users table assumed to have: id, fname, lname, rollno, class, role_as
    $query = "SELECT id, fname, lname, rollno FROM users WHERE class = ? AND role_as = 0 ORDER BY rollno ASC";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $class_code);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res && mysqli_num_rows($res) > 0) {
        echo '<table class="table table-bordered mt-4">';
        echo '<thead><tr><th>Roll No</th><th>Name</th><th>Marks Obtained</th></tr></thead>';
        echo '<tbody>';
        while ($row = mysqli_fetch_assoc($res)) {
            $student_id = (int)$row['id'];
            $rollno = htmlspecialchars($row['rollno']);
            $name = htmlspecialchars($row['fname'] . ' ' . $row['lname']);
            echo '<tr>';
            echo "<td>{$rollno}</td>";
            echo "<td>{$name}</td>";
            echo "<td><input type=\"number\" name=\"marks[{$student_id}]\" class=\"form-control student-mark-input\" step=\"0.01\" min=\"0\" required></td>";
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo "<p class='alert alert-warning'>No students found for class ({$class_code}).</p>";
    }
    exit();
}

// -------------------------------
// Save marks (from result-add.php)
// -------------------------------
/*
if (isset($_POST['save_marks'])) {
    // Basic sanitization and validation
    $class_code = mysqli_real_escape_string($con, $_POST['class_code'] ?? '');
    $subject = mysqli_real_escape_string($con, $_POST['subject'] ?? '');
    $exam_type_str = mysqli_real_escape_string($con, $_POST['exam_type'] ?? '');
    $total_marks_raw = $_POST['total_marks'] ?? '';
    $marks = $_POST['marks'] ?? [];

    // validate total_marks numeric
    if (!is_numeric($total_marks_raw) || (float)$total_marks_raw <= 0) {
        $_SESSION['message'] = "Total marks must be a positive number.";
        header("Location: result-add.php");
        exit();
    }
    $total_marks = (float)$total_marks_raw;

    $test_code_int = $exam_type_map[$exam_type_str] ?? null;
    if ($test_code_int === null) {
        $_SESSION['message'] = "Invalid exam type selected.";
        header("Location: result-add.php");
        exit();
    }

    // Insert each student's mark inside a transaction (optional)
    $teacher_id = $_SESSION['auth_user']['user_id'] ?? 0 ; 


    
    mysqli_begin_transaction($con);
    $success_count = 0;
    $error_found = false;

    $insert_query = "INSERT INTO student_score (student_id, teacher_id, class_code, subject, test_code, marks_obtained, total_marks) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($con, $insert_query);

    foreach ($marks as $student_id => $obtained_raw) {
        // cast and validate
        $student_id_int = (int)$student_id;
        if (!is_numeric($obtained_raw)) {
            $error_found = true;
            $_SESSION['message'] = "Invalid marks for student ID {$student_id_int}.";
            break;
        }
        $obtained = (float)$obtained_raw;
        if ($obtained < 0 || $obtained > $total_marks) {
            $error_found = true;
            $_SESSION['message'] = "Validation Error: Marks ({$obtained}) for student {$student_id_int} cannot exceed total marks ({$total_marks}).";
            break;
        }

        mysqli_stmt_bind_param($stmt, "iissidd", $student_id_int,  $teacher_id, $class_code, $subject, $test_code_int, $obtained, $total_marks);
        if (mysqli_stmt_execute($stmt)) {
            $success_count++;
        } else {
            $error_found = true;
            $_SESSION['message'] = "DB error while saving marks: " . mysqli_stmt_error($stmt);
            break;
        }
    }

    if ($error_found) {
        mysqli_rollback($con);
        header("Location: result-add.php");
        exit();
    } else {
        mysqli_commit($con);
        $_SESSION['message'] = "{$success_count} marks saved successfully!";
        header("Location: view-report.php");
        exit();
    }
}
*/
if (isset($_POST['save_marks'])) {
    $class_code = mysqli_real_escape_string($con, $_POST['class_code'] ?? '');
    $subject = mysqli_real_escape_string($con, $_POST['subject'] ?? '');
    $exam_type = $_POST['exam_type'] ?? '';
    $total_practical = floatval($_POST['total_marks'] ?? 0);
    $practical_marks = $_POST['marks'] ?? [];
    $teacher_id = $_SESSION['auth_user']['user_id'];
    $session = $_SESSION['active_session'] ?? date('Y'); // optional session tracking

    // --- Map exam type to code ---
    $exam_type_map = [
        'unit_test_1' => 1, 'unit_test_2' => 2, 'unit_test_3' => 3,
        'unit_test_4' => 4, 'half_year' => 5, 'final' => 6, 'class_test' => 7
    ];
    $test_code = $exam_type_map[$exam_type] ?? 0;

    mysqli_begin_transaction($con);
    $success_count = 0;
    $error_found = false;

    foreach ($practical_marks as $student_id => $mark) {
        $student_id = (int)$student_id;
        $mark = floatval($mark);

        if ($mark < 0 || $mark > $total_practical) {
            $_SESSION['error'] = "Marks for student ID {$student_id} cannot exceed maximum marks ({$total_practical}).";
            $error_found = true;
            break;
        }

        // --- 1. Check if record already exists ---
        $check_query = "SELECT id FROM student_score 
                        WHERE student_id=?  
                        AND class_code=? AND subject=? AND test_code=?";
        $check_stmt = mysqli_prepare($con, $check_query);
        mysqli_stmt_bind_param($check_stmt, "iisi", $student_id, $class_code, $subject, $test_code);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            // --- 2. If found, update existing record ---
            $update_query = "UPDATE student_score 
                             SET 	marks_obtained=?, total_marks=?, updated_at=NOW(), session=? 
                             WHERE student_id=? AND class_code=? AND subject=? AND test_code=?";
            $update_stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ddsiisi", $mark, $total_practical, $session, $student_id, $class_code, $subject, $test_code);
            if (!mysqli_stmt_execute($update_stmt)) {
                $_SESSION['error'] = "Error updating marks for student ID {$student_id}: " . mysqli_stmt_error($update_stmt);
                $error_found = true;
                break;
            } else {
                $success_count++;
            }
        } else {
            // --- 3. Else, insert new record ---
            $insert_query = "INSERT INTO student_score 
                             (student_id, teacher_id, class_code, subject, test_code, marks_obtained, total_marks, session)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "iiisidds", $student_id, $teacher_id, $class_code, $subject, $test_code, $mark, $total_practical, $session);
            if (!mysqli_stmt_execute($insert_stmt)) {
                $_SESSION['error'] = "Error inserting marks for student ID {$student_id}: " . mysqli_stmt_error($insert_stmt);
                $error_found = true;
                break;
            } else {
                $success_count++;
            }
        }
    }

    if ($error_found) {
        mysqli_rollback($con);
    } else {
        mysqli_commit($con);
        $_SESSION['success'] = "Marks saved successfully for {$success_count} student(s)!";
    }

    header("Location: view-report.php");
    exit();
}

// -------------------------------
// Delete a score
// -------------------------------
if (isset($_POST['delete_score'])) {
    $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
    $score_id = (int)$_POST['delete_score'];
    $stmt = mysqli_prepare($con, "DELETE FROM student_score WHERE id=? AND student_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $score_id, $student_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $_SESSION['success'] = "Score deleted successfully.";
    header("Location: view-report.php");
    exit();
}

// -------------------------------
// 1. Update single student's marks ONLY (from edit-report.php)
// -------------------------------
if (isset($_POST['update_single_student_marks']) || (isset($_POST['action']) && $_POST['action'] === 'update_single_student_marks')) {
    $updated_count = 0;
    $student_id = intval($_POST['student_id'] ?? 0);
    $redirect_class = $_POST['redirect_class'] ?? '';
    $redirect_subject = $_POST['redirect_subject'] ?? '';
    $redirect_exam_type = $_POST['exam_type'] ?? '';

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'marks_') === 0 && strpos($key, 'total_marks_') === false) {
            $score_id = (int)str_replace('marks_', '', $key);
            $marks_obtained = floatval($value);

            // Fetch current record's total marks for validation
            $stmt_info = mysqli_prepare($con, "SELECT total_marks FROM student_score WHERE id=? AND student_id=? LIMIT 1");
            mysqli_stmt_bind_param($stmt_info, "ii", $score_id, $student_id);
            mysqli_stmt_execute($stmt_info);
            $res_info = mysqli_stmt_get_result($stmt_info);
            $score_row = ($res_info && mysqli_num_rows($res_info) > 0) ? mysqli_fetch_assoc($res_info) : null;
            mysqli_stmt_close($stmt_info);

            if (!$score_row) continue;

            $total_marks = floatval($score_row['total_marks']);
            if ($total_marks > 0 && $marks_obtained > $total_marks) {
                $marks_obtained = $total_marks;
            }
            if ($marks_obtained < 0) {
                $marks_obtained = 0;
            }

            // Update ONLY this student's marks_obtained - total_marks and other students remain untouched!
            $stmt = mysqli_prepare($con, "UPDATE student_score SET marks_obtained=?, updated_at=NOW() WHERE id=? AND student_id=?");
            mysqli_stmt_bind_param($stmt, "dii", $marks_obtained, $score_id, $student_id);
            if (mysqli_stmt_execute($stmt)) $updated_count++;
            mysqli_stmt_close($stmt);
        }
    }

    $_SESSION['success'] = "Student marks updated successfully!";
    $redirect_url = "view-report.php?class_code=" . urlencode($redirect_class) .
        "&subject=" . urlencode($redirect_subject) .
        (!empty($redirect_exam_type) ? "&exam_type=" . urlencode($redirect_exam_type) : "");
    header("Location: " . $redirect_url);
    exit();
}

// -------------------------------
// 2. Update Maximum Marks & All Student Marks (from edit-all-report.php or batch)
// -------------------------------
if (isset($_POST['update_all_marks']) || isset($_POST['update_marks']) || (isset($_POST['action']) && in_array($_POST['action'], ['edit_report', 'update_all_marks']))) {
    $updated_count = 0;
    $student_id = intval($_POST['student_id'] ?? 0);
    $redirect_class = $_POST['redirect_class'] ?? '';
    $redirect_subject = $_POST['redirect_subject'] ?? '';
    $redirect_exam_type = $_POST['exam_type'] ?? '';
    $teacher_id = $_SESSION['auth_user']['user_id'] ?? 0;
    $global_max = (isset($_POST['global_max_marks']) && is_numeric($_POST['global_max_marks']) && floatval($_POST['global_max_marks']) > 0)
        ? floatval($_POST['global_max_marks'])
        : null;

    $updated_all_tests = [];

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'marks_') === 0 && strpos($key, 'total_marks_') === false) {
            $score_id = (int)str_replace('marks_', '', $key);
            $marks_obtained = floatval($value);

            // Fetch score record details
            $stmt_info = mysqli_prepare($con, "SELECT student_id, test_code, class_code, subject, total_marks FROM student_score WHERE id=? LIMIT 1");
            mysqli_stmt_bind_param($stmt_info, "i", $score_id);
            mysqli_stmt_execute($stmt_info);
            $res_info = mysqli_stmt_get_result($stmt_info);
            $score_row = ($res_info && mysqli_num_rows($res_info) > 0) ? mysqli_fetch_assoc($res_info) : null;
            mysqli_stmt_close($stmt_info);

            if (!$score_row) continue;

            $total_key = 'total_marks_' . $score_id;
            if ($global_max !== null) {
                $total_marks = $global_max;
            } elseif (isset($_POST[$total_key]) && is_numeric($_POST[$total_key]) && floatval($_POST[$total_key]) > 0) {
                $total_marks = floatval($_POST[$total_key]);
            } else {
                $total_marks = floatval($score_row['total_marks']);
            }

            // Remove violation: marks cannot exceed maximum marks
            if ($total_marks > 0 && $marks_obtained > $total_marks) {
                $marks_obtained = $total_marks;
            }
            if ($marks_obtained < 0) {
                $marks_obtained = 0;
            }

            // Update this student's score
            if ($total_marks > 0) {
                $stmt = mysqli_prepare($con, "UPDATE student_score SET marks_obtained=?, total_marks=?, updated_at=NOW() WHERE id=?");
                mysqli_stmt_bind_param($stmt, "ddi", $marks_obtained, $total_marks, $score_id);
            } else {
                $stmt = mysqli_prepare($con, "UPDATE student_score SET marks_obtained=?, updated_at=NOW() WHERE id=?");
                mysqli_stmt_bind_param($stmt, "di", $marks_obtained, $score_id);
            }

            if (mysqli_stmt_execute($stmt)) $updated_count++;
            mysqli_stmt_close($stmt);

            // Maximum marks reflects to all students in this test, and clamp any marks exceeding total_marks to remove violation
            $s_test = intval($score_row['test_code']);
            $s_class = $score_row['class_code'];
            $s_subj = $score_row['subject'];
            $test_key = $s_class . '_' . $s_subj . '_' . $s_test;

            if ($total_marks > 0 && !in_array($test_key, $updated_all_tests)) {
                if ($teacher_id) {
                    $stmt_all = mysqli_prepare($con, "UPDATE student_score 
                        SET total_marks=?, 
                            marks_obtained = CASE WHEN marks_obtained > ? THEN ? ELSE marks_obtained END, 
                            updated_at=NOW() 
                        WHERE class_code=? AND subject=? AND test_code=? AND teacher_id=?");
                    mysqli_stmt_bind_param($stmt_all, "dddssii", $total_marks, $total_marks, $total_marks, $s_class, $s_subj, $s_test, $teacher_id);
                } else {
                    $stmt_all = mysqli_prepare($con, "UPDATE student_score 
                        SET total_marks=?, 
                            marks_obtained = CASE WHEN marks_obtained > ? THEN ? ELSE marks_obtained END, 
                            updated_at=NOW() 
                        WHERE class_code=? AND subject=? AND test_code=?");
                    mysqli_stmt_bind_param($stmt_all, "dddssi", $total_marks, $total_marks, $total_marks, $s_class, $s_subj, $s_test);
                }
                mysqli_stmt_execute($stmt_all);
                mysqli_stmt_close($stmt_all);
                $updated_all_tests[] = $test_key;
            }
        }
    }

    $all_msg = !empty($updated_all_tests) ? " Maximum marks ({$global_max}) was updated for all students in this test." : "";
    $_SESSION['success'] = "Theory marks updated successfully!" . $all_msg;

    $redirect_url = "view-report.php?class_code=" . urlencode($redirect_class) .
        "&subject=" . urlencode($redirect_subject) .
        (!empty($redirect_exam_type) ? "&exam_type=" . urlencode($redirect_exam_type) : "");
    header("Location: " . $redirect_url);
    exit();
}

// -------------------------------
// Attendance: update single record
// -------------------------------
if (isset($_POST['update_attendance'])) {
    $attendance_id = isset($_POST['attendance_id']) ? (int)$_POST['attendance_id'] : 0;
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 0;

    $updateQuery = "UPDATE attendance SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $updateQuery);
    mysqli_stmt_bind_param($stmt, "ii", $status, $attendance_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['message'] = $ok ? "Attendance updated successfully!" : "Failed to update attendance: " . mysqli_error($con);

    if (!empty($_POST['class'])) {
        $_SESSION['teacher_selected_class'] = trim($_POST['class']);
    }

    // Preserve filters for redirect
    $redirectUrl = "attendance-view.php?";
    $params = [];
    if (!empty($_POST['class'])) $params[] = "class=" . urlencode($_POST['class']);
    if (!empty($_POST['student'])) $params[] = "student=" . urlencode($_POST['student']);
    $dateVal = $_POST['date'] ?? $_POST['attendance_date'] ?? '';
    if (!empty($dateVal)) $params[] = "date=" . urlencode($dateVal);

    if (!empty($params)) {
        $redirectUrl .= implode('&', $params);
    } else {
        $redirectUrl = "attendance-view.php";
    }

    header("Location: {$redirectUrl}");
    exit();
}

// -------------------------------
// Add multiple students (bulk add)
// -------------------------------
/*
if (isset($_POST['add_student'])) {
    // Expect arrays: fname[], fathername[], phone[], class[], rollno[]
    $added = 0;

    $insertQuery = "INSERT INTO users (fname, father_name, email, password, role_as, status, phone_no, class, rollno) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insertQuery);

    foreach ($_POST['rollno'] as $key => $rollno) {
        $fname = trim($_POST['fname'][$key] ?? '');
        $fathername = trim($_POST['fathername'][$key] ?? '');
        $phone_no = trim($_POST['phone'][$key] ?? '');
        $class = trim($_POST['class'][$key] ?? '');
        $rollno = trim($rollno);

        if ($fname === '' || $phone_no === '') continue;

        $last4 = substr($phone_no, -4);
        $email = strtolower(preg_replace('/\s+/', '', $fname)) . $last4 . "@pathcreationacademy.com";
        $password = $phone_no; // password = phone number
        $role_as = 0; // student
        $status = 0; // default inactive

        mysqli_stmt_bind_param($stmt, "ssssiiiss", $fname, $fathername, $email, $password, $role_as, $status, $phone_no, $class, $rollno);
        if (mysqli_stmt_execute($stmt)) $added++;
    }

    mysqli_stmt_close($stmt);
    $_SESSION['message'] = "{$added} students added successfully.";
    header('Location: get-student.php');
    exit();
}  */

if (isset($_POST['add_student'])) {
    // Expect arrays: fname[], fathername[], phone[], class[], rollno[]
    $added = 0;

    // STEP 1: Prepare Insert Query (with user_id included)
    $insertQuery = "INSERT INTO users 
        (user_id, fname, father_name, email, password, role_as, status, phone_no, class, rollno)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insertQuery);

    foreach ($_POST['rollno'] as $key => $rollno) {
        $fname = trim($_POST['fname'][$key] ?? '');
        $fathername = trim($_POST['fathername'][$key] ?? '');
        $phone_no = trim($_POST['phone'][$key] ?? '');
        $class = trim($_POST['class'][$key] ?? '');
        $rollno = trim($rollno);

        if ($fname === '' || $phone_no === '') continue;

        // STEP 2: Generate new user_id like P000001
        $prefix = 'P';
        $query = "SELECT user_id FROM users WHERE user_id LIKE '{$prefix}%' ORDER BY user_id DESC LIMIT 1";
        $result = mysqli_query($con, $query);

        if ($row = mysqli_fetch_assoc($result)) {
            // Get numeric part, increment by 1
            $lastNum = intval(substr($row['user_id'], 1)) + 1;
        } else {
            $lastNum = 1; // First ID if table empty
        }

        // Format with leading zeros (6 digits)
        $new_user_id = $prefix . str_pad($lastNum, 6, '0', STR_PAD_LEFT);

        // STEP 3: Other values
        $last4 = substr($phone_no, -4);
        $email = strtolower(preg_replace('/\s+/', '', $fname)) . $last4 . "@pathcreationacademy.com";
        $password = $phone_no; // password = phone number
        $role_as = 0; // student
        $status = 0; // default inactive

        // STEP 4: Bind & Execute
        mysqli_stmt_bind_param(
            $stmt,
            "ssssiiisss",
            $new_user_id,
            $fname,
            $fathername,
            $email,
            $password,
            $role_as,
            $status,
            $phone_no,
            $class,
            $rollno
        );

        if (mysqli_stmt_execute($stmt)) {
            $added++;
        }
    }

    mysqli_stmt_close($stmt);
    $_SESSION['message'] = "{$added} students added successfully.";
    header('Location: get-student.php');
    exit();
}


// -------------------------------
// Update a student
// -------------------------------


if (isset($_POST['update_student'])) {
    $student_id = (int)$_POST['id'];
    $fname = trim($_POST['fname']);
    $father_name = trim($_POST['father_name']);
    $phone_no = trim($_POST['phone_no']); // ✅ corrected key
    $rollno = (int)$_POST['rollno'];
    $class = (int)$_POST['class'];
    $status = isset($_POST['status']) ? 1 : 0;

    // Basic validation
    if ($fname === '' || $phone_no === '') {
        $_SESSION['message'] = "❌ First Name and Phone Number are required!";
        header('Location: edit-student.php?id=' . $student_id);
        exit();
    }

    // Auto-generate email
    $last4 = substr($phone_no, -4);
    $email = strtolower(preg_replace('/\s+/', '', $fname)) . $last4 . "@pathcreationacademy.com";

    // Fetch current phone number (to detect changes)
    $check_query = mysqli_query($con, "SELECT phone_no FROM users WHERE id='$student_id' LIMIT 1");
    $old_data = mysqli_fetch_assoc($check_query);
    $old_phone_no = $old_data['phone_no'];

    // Check if password field is filled
    $new_password_input = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Case 1: Teacher entered new password manually
    if ($new_password_input !== '') {
        $hashed_password = $phone_no;
    }
    // Case 2: Phone number changed → auto-update password to phone number
    elseif ($old_phone_no !== $phone_no) {
        $hashed_password = $phone_no;
    }
    // Case 3: No password change at all
    else {
        $hashed_password = null;
    }

    // Build SQL based on whether password will be updated
    if ($hashed_password !== null) {
        $update_query = "
            UPDATE users 
            SET fname=?, father_name=?, email=?, password=?, phone_no=?, class=?, rollno=?, status=? 
            WHERE id=? AND role_as=0
        ";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param(
            $stmt,
            "ssssssiii",
            $fname,
            $father_name,
            $email,
            $hashed_password,
            $phone_no,
            $class,
            $rollno,
            $status,
            $student_id
        );
    } else {
        $update_query = "
            UPDATE users 
            SET fname=?, father_name=?, email=?, phone_no=?, class=?, rollno=?, status=? 
            WHERE id=? AND role_as=0
        ";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param(
            $stmt,
            "ssssiiii",
            $fname,
            $father_name,
            $email,
            $phone_no,
            $class,
            $rollno,
            $status,
            $student_id
        );
    }

    // Execute
    $ok = mysqli_stmt_execute($stmt);

    if (!$ok) {
        $_SESSION['message'] = "❌ SQL Error: " . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        header('Location: edit-student.php?id=' . $student_id);
        exit();
    }

    mysqli_stmt_close($stmt);
    $_SESSION['message'] = "✅ Student Updated Successfully!";
    if (!empty($class)) {
        header('Location: get-student.php?class=' . urlencode($class));
    } else {
        header('Location: get-student.php');
    }
    exit();
}






// -------------------------------
// Delete a student
// -------------------------------
if (isset($_POST['delete_student'])) {
    $student_id = (int)$_POST['delete_student'];
    $class = isset($_POST['class']) ? trim($_POST['class']) : '';

    // If class not provided in POST, query student's class before deleting
    if ($class === '') {
        $get_class_q = mysqli_query($con, "SELECT class FROM users WHERE id = '$student_id' LIMIT 1");
        if ($get_class_q && mysqli_num_rows($get_class_q) > 0) {
            $c_row = mysqli_fetch_assoc($get_class_q);
            $class = $c_row['class'];
        }
    }

    $delete_query = "DELETE FROM users WHERE id = ? AND role_as = 0";
    $stmt = mysqli_prepare($con, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['message'] = $ok ? "✅ Student Deleted Successfully" : "❌ Failed to Delete Student: " . mysqli_error($con);

    if ($class !== '') {
        header('Location: get-student.php?class=' . urlencode($class));
    } else {
        header('Location: get-student.php');
    }
    exit();
}



// -------------------------------
// Fetch students for attendance (AJAX GET request)
// -------------------------------
if (isset($_GET['selectedClass']) && isset($_GET['date'])) {
    $selectedClass = mysqli_real_escape_string($con, $_GET['selectedClass']);
    $date = mysqli_real_escape_string($con, $_GET['date']);

    $query = "SELECT id, fname, lname, rollno FROM users WHERE class = ? AND role_as = 0 ORDER BY rollno ASC";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $selectedClass);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res && mysqli_num_rows($res) > 0) {
        echo "<table class='table table-bordered'>";
        echo "<thead><tr><th>Roll No</th><th>Name</th><th>Status</th></tr></thead><tbody>";
        while ($student = mysqli_fetch_assoc($res)) {
            $uid = (int)$student['id'];
            $roll = htmlspecialchars($student['rollno']);
            $name = htmlspecialchars($student['fname'] . ' ' . $student['lname']);

            // Check if attendance already exists
            $checkQuery = "SELECT status FROM attendance WHERE user_id = ? AND date = ? LIMIT 1";
            $chStmt = mysqli_prepare($con, $checkQuery);
            mysqli_stmt_bind_param($chStmt, "is", $uid, $date);
            mysqli_stmt_execute($chStmt);
            $chRes = mysqli_stmt_get_result($chStmt);

            $status = 1; // default present
            if ($chRes && mysqli_num_rows($chRes) > 0) {
                $r = mysqli_fetch_assoc($chRes);
                $status = (int)$r['status']; // 1=Present, 0=Absent, 2=Holiday
            }
            mysqli_stmt_close($chStmt);

            echo "<tr>
                    <td>{$roll}</td>
                    <td>{$name}</td>
                    <td>
                        <select name='attendance_status[{$uid}]' class='form-select form-select-sm'>
                            <option value='1' " . ($status === 1 ? 'selected' : '') . ">Present</option>
                            <option value='0' " . ($status === 0 ? 'selected' : '') . ">Absent</option>
                            <option value='2' " . ($status === 2 ? 'selected' : '') . ">Holiday</option>
                        </select>
                    </td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No students found in this class.</p>";
    }
    exit();
}

// -------------------------------
// Add multiple attendance (bulk insert/update)
// -------------------------------
if (isset($_POST['add_multiple_attendance'])) {
    $date = $_POST['date'] ?? '';
    $class = $_POST['class'] ?? '';
    $isHoliday = isset($_POST['markHoliday']) ? 1 : 0;

    // --- If marked as Holiday ---
    if ($isHoliday) {
        $query = "SELECT id FROM users WHERE class = ? AND role_as = 0";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $class);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        while ($student = mysqli_fetch_assoc($res)) {
            $uid = (int)$student['id'];

            // Check if already exists
            $checkQuery = "SELECT id FROM attendance WHERE user_id = ? AND date = ? LIMIT 1";
            $chStmt = mysqli_prepare($con, $checkQuery);
            mysqli_stmt_bind_param($chStmt, "is", $uid, $date);
            mysqli_stmt_execute($chStmt);
            mysqli_stmt_store_result($chStmt);

            if (mysqli_stmt_num_rows($chStmt) > 0) {
                $updateQuery = "UPDATE attendance SET status = 2 WHERE user_id = ? AND date = ?";
                $upStmt = mysqli_prepare($con, $updateQuery);
                mysqli_stmt_bind_param($upStmt, "is", $uid, $date);
                mysqli_stmt_execute($upStmt);
                mysqli_stmt_close($upStmt);
            } else {
                $insertQuery = "INSERT INTO attendance (user_id, date, status) VALUES (?, ?, 2)";
                $inStmt = mysqli_prepare($con, $insertQuery);
                mysqli_stmt_bind_param($inStmt, "is", $uid, $date);
                mysqli_stmt_execute($inStmt);
                mysqli_stmt_close($inStmt);
            }
            mysqli_stmt_close($chStmt);
        }

        if (!empty($class)) {
            $_SESSION['teacher_selected_class'] = $class;
        }
        $redirUrl = 'attendance-view.php';
        $params = [];
        if (!empty($class)) $params[] = 'class=' . urlencode($class);
        if (!empty($date)) $params[] = 'date=' . urlencode($date);
        if (!empty($params)) $redirUrl .= '?' . implode('&', $params);

        $_SESSION['message'] = "Marked as Holiday for all students";
        header('Location: ' . $redirUrl);
        exit();
    }

    // --- Normal Present/Absent/Holiday ---
    $attendances = $_POST['attendance_status'] ?? [];
    if (empty($date) || empty($attendances)) {
        if (!empty($class)) {
            $_SESSION['teacher_selected_class'] = $class;
        }
        $redirUrl = 'attendance-view.php';
        $params = [];
        if (!empty($class)) $params[] = 'class=' . urlencode($class);
        if (!empty($date)) $params[] = 'date=' . urlencode($date);
        if (!empty($params)) $redirUrl .= '?' . implode('&', $params);

        $_SESSION['message'] = "Date or attendance missing.";
        header('Location: ' . $redirUrl);
        exit();
    }

    foreach ($attendances as $user_id => $status) {
        $user_id_int = (int)$user_id;
        $status_int = (int)$status; // ensure numeric

        // Check if exists
        $checkQuery = "SELECT id FROM attendance WHERE user_id = ? AND date = ? LIMIT 1";
        $chStmt = mysqli_prepare($con, $checkQuery);
        mysqli_stmt_bind_param($chStmt, "is", $user_id_int, $date);
        mysqli_stmt_execute($chStmt);
        mysqli_stmt_store_result($chStmt);

        if (mysqli_stmt_num_rows($chStmt) > 0) {
            $updateQuery = "UPDATE attendance SET status = ? WHERE user_id = ? AND date = ?";
            $upStmt = mysqli_prepare($con, $updateQuery);
            mysqli_stmt_bind_param($upStmt, "iis", $status_int, $user_id_int, $date);
            mysqli_stmt_execute($upStmt);
            mysqli_stmt_close($upStmt);
        } else {
            $insertQuery = "INSERT INTO attendance (user_id, date, status) VALUES (?, ?, ?)";
            $inStmt = mysqli_prepare($con, $insertQuery);
            mysqli_stmt_bind_param($inStmt, "isi", $user_id_int, $date, $status_int);
            mysqli_stmt_execute($inStmt);
            mysqli_stmt_close($inStmt);
        }
        mysqli_stmt_close($chStmt);
    }

    if (!empty($class)) {
        $_SESSION['teacher_selected_class'] = $class;
    }
    $redirUrl = 'attendance-view.php';
    $params = [];
    if (!empty($class)) $params[] = 'class=' . urlencode($class);
    if (!empty($date)) $params[] = 'date=' . urlencode($date);
    if (!empty($params)) $redirUrl .= '?' . implode('&', $params);

    $_SESSION['message'] = "Attendance saved successfully.";
    header('Location: ' . $redirUrl);
    exit();
}



// -------------------------------
// Upload study material
// -------------------------------


if (isset($_POST['upload_study_material'])) {
    $teacher_name  = mysqli_real_escape_string($con, $_POST['teacher_name']);
    $class_name    = mysqli_real_escape_string($con, $_POST['class_name']);
    $subject       = mysqli_real_escape_string($con, $_POST['subject']);
    $material_type = mysqli_real_escape_string($con, $_POST['material_type']);
    $title         = mysqli_real_escape_string($con, $_POST['title']);
    $description   = mysqli_real_escape_string($con, $_POST['description']);
    $status        = isset($_POST['status']) ? 1 : 0;

    // Folder where files will be stored
    $uploadDir = "uploads/study_materials/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $fileType = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'ppt', 'pptx'];

        if (in_array($fileType, $allowed)) {
            $fileName = time() . "_" . preg_replace('/\s+/', '_', $_FILES['file']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                // Identify file type
                if ($fileType == 'pdf') $fileTypeForDb = 'pdf';
                elseif (in_array($fileType, ['jpg', 'jpeg', 'png'])) $fileTypeForDb = 'image';
                elseif (in_array($fileType, ['ppt', 'pptx'])) $fileTypeForDb = 'ppt';
                else $fileTypeForDb = 'other';

                // ✅ Insert record in DB (save path, not file data)
                $query = "INSERT INTO study_materials 
                          (teacher_name, class_name, subject, material_type, title, description, file_path, file_type, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($con, $query);
                mysqli_stmt_bind_param($stmt, "ssssssssi",
                    $teacher_name, $class_name, $subject, $material_type,
                    $title, $description, $targetPath, $fileTypeForDb, $status
                );

                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['message'] = "✅ Study Material Uploaded Successfully!";
                } else {
                    $_SESSION['message'] = "❌ Upload Failed: " . mysqli_stmt_error($stmt);
                }

                mysqli_stmt_close($stmt);
            } else {
                $_SESSION['message'] = "❌ Failed to move uploaded file.";
            }
        } else {
            $_SESSION['message'] = "❌ Invalid file type.";
        }
    } else {
        $_SESSION['message'] = "❌ No file selected or upload error.";
    }

    header("Location: view-study-material.php");
    exit();
}

// -------------------------------
// Update study material

if (isset($_POST['update_material'])) {
    $id = isset($_POST['material_id']) ? (int)$_POST['material_id'] : 0;
    $teacher_name  = mysqli_real_escape_string($con, $_POST['teacher_name'] ?? '');
    $class_name    = mysqli_real_escape_string($con, $_POST['class_name'] ?? '');
    $subject       = mysqli_real_escape_string($con, $_POST['subject'] ?? '');
    $material_type = mysqli_real_escape_string($con, $_POST['material_type'] ?? '');
    $title         = mysqli_real_escape_string($con, $_POST['title'] ?? '');
    $description   = mysqli_real_escape_string($con, $_POST['description'] ?? '');
    $status        = isset($_POST['status']) ? 1 : 0;

    $uploadDir = "uploads/study_materials/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $newFilePath = null;
    $fileTypeForDb = null;

    // ✅ Step 1: Check if new file uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $fileType = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'ppt', 'pptx'];

        if (in_array($fileType, $allowed)) {
            $fileName = time() . "_" . preg_replace('/\s+/', '_', $_FILES['file']['name']);
            $targetPath = $uploadDir . $fileName;

            // Move file to upload folder
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                $newFilePath = $targetPath;

                // Determine file type for DB
                if ($fileType == 'pdf') $fileTypeForDb = 'pdf';
                elseif (in_array($fileType, ['jpg', 'jpeg', 'png'])) $fileTypeForDb = 'image';
                elseif (in_array($fileType, ['ppt', 'pptx'])) $fileTypeForDb = 'ppt';
                else $fileTypeForDb = 'other';

                // ✅ Step 2: Delete old file (if exists)
                $getOldFile = mysqli_prepare($con, "SELECT file_path FROM study_materials WHERE id=? LIMIT 1");
                mysqli_stmt_bind_param($getOldFile, "i", $id);
                mysqli_stmt_execute($getOldFile);
                $res = mysqli_stmt_get_result($getOldFile);
                if ($res && $old = mysqli_fetch_assoc($res)) {
                    if (file_exists($old['file_path'])) {
                        @unlink($old['file_path']); // delete old file safely
                    }
                }
                mysqli_stmt_close($getOldFile);
            }
        }
    }

    // ✅ Step 3: Update record
    if ($newFilePath !== null) {
        // If new file uploaded, update file info too
        $query = "UPDATE study_materials 
                  SET teacher_name=?, class_name=?, subject=?, material_type=?, title=?, description=?, file_path=?, file_type=?, status=? 
                  WHERE id=?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ssssssssii",
            $teacher_name, $class_name, $subject, $material_type,
            $title, $description, $newFilePath, $fileTypeForDb, $status, $id
        );
    } else {
        // No new file — keep old file path
        $query = "UPDATE study_materials 
                  SET teacher_name=?, class_name=?, subject=?, material_type=?, title=?, description=?, status=? 
                  WHERE id=?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ssssssii",
            $teacher_name, $class_name, $subject, $material_type, $title, $description, $status, $id
        );
    }

    // ✅ Step 4: Execute and show message
    if (isset($stmt) && mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "✅ Material Updated Successfully!";
    } else {
        $_SESSION['message'] = "❌ Update Failed: " . (isset($stmt) ? mysqli_stmt_error($stmt) : 'Unknown error');
    }

    if (isset($stmt)) mysqli_stmt_close($stmt);

    header("Location: view-study-material.php");
    exit();
}



// -------------------------------
// Delete study material by GET id
// -------------------------------
if (isset($_GET['delete_material_id'])) {
    $id = (int)$_GET['delete_material_id'];

    $stmt = mysqli_prepare($con, "DELETE FROM study_materials WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "✅ Material Deleted Successfully!";
    } else {
        $_SESSION['message'] = "❌ Delete Failed: " . mysqli_stmt_error($stmt);
    }
    mysqli_stmt_close($stmt);

    header("Location: view-study-material.php");
    exit();
}






// -------------------------------
// AJAX: Load students for Practical Marks
// -------------------------------
if(isset($_POST['action']) && $_POST['action'] === 'load_students_practical') {
    $class_code = mysqli_real_escape_string($con, $_POST['class_code'] ?? '');
    $teacher_id = $_SESSION['auth_user']['user_id'];

    $query = "SELECT id, fname, lname, rollno FROM users WHERE class = ? AND role_as = 0 ORDER BY rollno ASC";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $class_code);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if($res && mysqli_num_rows($res) > 0){
        echo '<table class="table table-bordered mt-4">';
        echo '<thead><tr><th>Roll No</th><th>Name</th><th>Practical Marks</th></tr></thead>';
        echo '<tbody>';
        while($row = mysqli_fetch_assoc($res)){
            $student_id = (int)$row['id'];
            $rollno = htmlspecialchars($row['rollno']);
            $name = htmlspecialchars($row['fname'] . ' ' . $row['lname']);
            echo '<tr>';
            echo "<td>{$rollno}</td>";
            echo "<td>{$name}</td>";
            echo "<td><input type='number' name='practical_marks[{$student_id}]' class='form-control student-practical-mark-input' step='0.01' min='0'></td>";
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo "<p class='alert alert-warning'>No students found for class ({$class_code}).</p>";
    }
    exit();
}

// -------------------------------
// Save Practical Marks (Add)
// -------------------------------
if (isset($_POST['save_practical_marks'])) {
    $class_code = mysqli_real_escape_string($con, $_POST['class_code'] ?? '');
    $subject = mysqli_real_escape_string($con, $_POST['subject'] ?? '');
    $exam_type = $_POST['exam_type'] ?? '';
    $total_practical = floatval($_POST['total_practical'] ?? 0);
    $practical_marks = $_POST['practical_marks'] ?? [];
    $teacher_id = $_SESSION['auth_user']['user_id'];
    $session = $_SESSION['active_session'] ?? date('Y'); // optional session tracking

    // --- Map exam type to code ---
    $exam_type_map = [
        'unit_test_1' => 1, 'unit_test_2' => 2, 'unit_test_3' => 3,
        'unit_test_4' => 4, 'half_year' => 5, 'final' => 6, 'class_test' => 7
    ];
    $test_code = $exam_type_map[$exam_type] ?? 0;

    mysqli_begin_transaction($con);
    $success_count = 0;
    $error_found = false;

    foreach ($practical_marks as $student_id => $mark) {
        $student_id = (int)$student_id;
        $mark = floatval($mark);

        if ($mark < 0 || $mark > $total_practical) {
            $_SESSION['error'] = "Invalid practical marks for student ID {$student_id}.";
            $error_found = true;
            break;
        }

        // --- 1. Check if record already exists ---
        $check_query = "SELECT id FROM student_score 
                        WHERE student_id=? 
                        AND class_code=? AND subject=? AND test_code=?";
        $check_stmt = mysqli_prepare($con, $check_query);
        mysqli_stmt_bind_param($check_stmt, "iisi", $student_id, $class_code, $subject, $test_code);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            // --- 2. If found, update existing record ---
            $update_query = "UPDATE student_score 
                             SET practical_marks=?, total_practical=?, updated_at=NOW(), session=? 
                             WHERE student_id=?  AND class_code=? AND subject=? AND test_code=?";
            $update_stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ddsiisi", $mark, $total_practical, $session, $student_id,  $class_code, $subject, $test_code);
            if (!mysqli_stmt_execute($update_stmt)) {
                $_SESSION['error'] = "Error updating marks for student ID {$student_id}: " . mysqli_stmt_error($update_stmt);
                $error_found = true;
                break;
            } else {
                $success_count++;
            }
        } else {
            // --- 3. Else, insert new record ---
            $insert_query = "INSERT INTO student_score 
                             (student_id, teacher_id, class_code, subject, test_code, practical_marks, total_practical, session)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($con, $insert_query);
            mysqli_stmt_bind_param($insert_stmt, "iiisidds", $student_id, $teacher_id, $class_code, $subject, $test_code, $mark, $total_practical, $session);
            if (!mysqli_stmt_execute($insert_stmt)) {
                $_SESSION['error'] = "Error inserting marks for student ID {$student_id}: " . mysqli_stmt_error($insert_stmt);
                $error_found = true;
                break;
            } else {
                $success_count++;
            }
        }
    }

    if ($error_found) {
        mysqli_rollback($con);
    } else {
        mysqli_commit($con);
        $_SESSION['success'] = "{$success_count} practical marks saved successfully!";
    }

    header("Location: view-partical-marks.php");
    exit();
}

// update marks 

// 1. Update single student's practical marks ONLY (from edit_partical_marks.php)
if (isset($_POST['update_single_practical_marks']) || (isset($_POST['action']) && $_POST['action'] === 'update_single_practical_marks')) {
    $student_id = intval($_POST['student_id'] ?? 0);
    $redirect_class = $_POST['redirect_class'] ?? '';
    $redirect_subject = $_POST['redirect_subject'] ?? '';
    $redirect_exam_type = $_POST['exam_type'] ?? '';
    $teacher_id = $_SESSION['auth_user']['user_id'] ?? 0;

    if (!$teacher_id) {
        $_SESSION['error'] = "Unauthorized teacher.";
        header("Location: view-partical-marks.php");
        exit();
    }

    $updated_count = 0;

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'practical_') === 0 && strpos($key, 'total_practical_') === false) {
            $score_id = intval(str_replace('practical_', '', $key));
            $marks = floatval($value);

            // Fetch record to check max practical
            $stmt = mysqli_prepare($con, "SELECT total_practical FROM student_score WHERE id=? AND student_id=? AND teacher_id=? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "iii", $score_id, $student_id, $teacher_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $score_row = ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);

            if (!$score_row) continue;

            $total = floatval($score_row['total_practical']);
            if ($total > 0 && $marks > $total) {
                $marks = $total;
            }
            if ($marks < 0) {
                $marks = 0;
            }

            // Update ONLY this student's practical marks
            $stmt = mysqli_prepare($con, "UPDATE student_score SET practical_marks=?, updated_at=NOW() WHERE id=? AND student_id=? AND teacher_id=?");
            mysqli_stmt_bind_param($stmt, "diii", $marks, $score_id, $student_id, $teacher_id);
            if (mysqli_stmt_execute($stmt)) {
                $updated_count++;
            }
            mysqli_stmt_close($stmt);
        }
    }

    $_SESSION['success'] = "Practical marks updated successfully!";
    $redirect_url = "view-partical-marks.php?class_code=" . urlencode($redirect_class) .
        "&subject=" . urlencode($redirect_subject) .
        (!empty($redirect_exam_type) ? "&exam_type=" . urlencode($redirect_exam_type) : "");
    header("Location: " . $redirect_url);
    exit();
}

// 2. Update Total Practical Marks & All Student Practical Marks (from edit-all-practical-marks.php or batch)
if (isset($_POST['update_all_practical_marks']) || isset($_POST['update_practical_marks']) || (isset($_POST['action']) && in_array($_POST['action'], ['update_all_practical_marks', 'edit_partical_marks']))) {
    $student_id = intval($_POST['student_id'] ?? 0);
    $redirect_class = $_POST['redirect_class'] ?? '';
    $redirect_subject = $_POST['redirect_subject'] ?? '';
    $redirect_exam_type = $_POST['exam_type'] ?? '';

    // ✅ Get logged-in teacher ID
    $teacher_id = $_SESSION['auth_user']['user_id'] ?? 0;

    if (!$teacher_id) {
        $_SESSION['error'] = "Unauthorized teacher.";
        header("Location: view-partical-marks.php");
        exit();
    }

    $global_total_practical = (isset($_POST['global_total_practical']) && is_numeric($_POST['global_total_practical']) && floatval($_POST['global_total_practical']) > 0)
        ? floatval($_POST['global_total_practical'])
        : null;

    $updated_all_tests = [];
    $updated_count = 0;

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'practical_') === 0 && strpos($key, 'total_practical_') === false) {
            $score_id = intval(str_replace('practical_', '', $key));
            $marks = floatval($value);

            // Fetch record details
            $stmt = mysqli_prepare($con, "SELECT test_code, class_code, subject, total_practical FROM student_score WHERE id=? AND teacher_id=? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "ii", $score_id, $teacher_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $score_row = ($res && mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res) : null;
            mysqli_stmt_close($stmt);

            if (!$score_row) {
                continue;
            }

            // Check if global or row total_practical was posted
            $total_key = 'total_practical_' . $score_id;
            if ($global_total_practical !== null) {
                $total = $global_total_practical;
            } elseif (isset($_POST[$total_key]) && is_numeric($_POST[$total_key]) && floatval($_POST[$total_key]) > 0) {
                $total = floatval($_POST[$total_key]);
            } else {
                $total = floatval($score_row['total_practical']);
            }

            // Remove violation: practical marks cannot exceed total practical marks
            if ($total > 0 && $marks > $total) {
                $marks = $total;
            }
            if ($marks < 0) {
                $marks = 0;
            }

            // ✅ Update practical marks AND total_practical for this student
            $stmt = mysqli_prepare($con, "UPDATE student_score SET practical_marks=?, total_practical=?, updated_at=NOW() WHERE id=? AND teacher_id=?");
            mysqli_stmt_bind_param($stmt, "ddii", $marks, $total, $score_id, $teacher_id);
            if (mysqli_stmt_execute($stmt)) {
                $updated_count++;
            }
            mysqli_stmt_close($stmt);

            // Automatically update maximum practical marks for all students in this test and clamp any marks exceeding total to remove violation
            $s_test = intval($score_row['test_code']);
            $s_class = $score_row['class_code'];
            $s_subj = $score_row['subject'];
            $test_key = $s_class . '_' . $s_subj . '_' . $s_test;

            if ($total > 0 && !in_array($test_key, $updated_all_tests)) {
                $stmt_all = mysqli_prepare($con, "UPDATE student_score 
                    SET total_practical=?, 
                        practical_marks = CASE WHEN practical_marks > ? THEN ? ELSE practical_marks END, 
                        updated_at=NOW() 
                    WHERE class_code=? AND subject=? AND test_code=? AND teacher_id=?");
                mysqli_stmt_bind_param($stmt_all, "dddssii", $total, $total, $total, $s_class, $s_subj, $s_test, $teacher_id);
                mysqli_stmt_execute($stmt_all);
                mysqli_stmt_close($stmt_all);
                $updated_all_tests[] = $test_key;
            }
        }
    }

    $all_msg = !empty($updated_all_tests) ? " Maximum practical marks ({$global_total_practical}) was automatically updated for all students in this test." : "";
    $_SESSION['success'] = "Practical marks updated successfully!" . $all_msg;

    $redirect_url = "view-partical-marks.php?class_code=" . urlencode($redirect_class) .
        "&subject=" . urlencode($redirect_subject) .
        (!empty($redirect_exam_type) ? "&exam_type=" . urlencode($redirect_exam_type) : "");
    header("Location: " . $redirect_url);
    exit();
}


///////////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] === UPLOAD_ERR_OK) {

    $user_id = $_SESSION['auth_user']['user_id'];

    // Allowed types and max size
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $file_tmp = $_FILES['user_photo']['tmp_name'];
    $file_name = $_FILES['user_photo']['name'];
    $mime_type = mime_content_type($file_tmp);
    $file_size = $_FILES['user_photo']['size'];

    // ✅ Validate type and size
    if (!in_array($mime_type, $allowed_types)) {
        $_SESSION['message'] = "🚫 Invalid file type. Only JPG, PNG, GIF allowed.";
        header("Location: profile.php");
        exit();
    }
    if ($file_size > $max_size) {
        $_SESSION['message'] = "🚫 File too large. Max size 5MB.";
        header("Location: profile.php");
        exit();
    }

    // ✅ Generate a unique file name
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;

    // ✅ Set upload path
    $upload_dir = 'uploads/profile_image/';
    $upload_path = $upload_dir . $new_filename;

    // ✅ Get old photo from DB
    $query = "SELECT user_photo FROM users WHERE id = ?";
    $stmt_old = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt_old, "s", $user_id);
    mysqli_stmt_execute($stmt_old);
    mysqli_stmt_bind_result($stmt_old, $old_photo);
    mysqli_stmt_fetch($stmt_old);
    mysqli_stmt_close($stmt_old);

    // ✅ Move file to uploads directory
    if (move_uploaded_file($file_tmp, $upload_path)) {

        // ✅ Delete old photo (if exists and not default)
        if (!empty($old_photo)) {
            $old_path = $upload_dir . $old_photo;
            if (file_exists($old_path)) {
                unlink($old_path); // Delete old image file
            }
        }

        // ✅ Update DB with new filename
        $stmt = mysqli_prepare($con, "UPDATE users SET user_photo = ?, user_photo_type = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sss", $new_filename, $mime_type, $user_id);
        $executed = mysqli_stmt_execute($stmt);

        if ($executed) {
            $_SESSION['message'] = "✅ Profile photo uploaded successfully!";
        } else {
            $_SESSION['message'] = "🚫 Database error: " . mysqli_error($con);
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['message'] = "🚫 Failed to move uploaded file.";
    }

    header("Location: profile.php");
    exit();
}


if (isset($_POST['solve_complaint_btn'])) {
    $complaint_id = mysqli_real_escape_string($con, $_POST['complaint_id']);

    // ✅ Delete only that specific complaint
    $delete_query = "DELETE FROM complaints WHERE id = '$complaint_id'";
    $delete_result = mysqli_query($con, $delete_query);

    if ($delete_result) {
        $_SESSION['message'] = "✅ Complaint solved and deleted successfully.";
    } else {
        $_SESSION['message'] = "❌ Failed to delete complaint. Try again.";
    }

    header("Location: viewcomplaints.php");
    exit();
}

// ✅ Update Teacher Profile Details
if (isset($_POST['update_teacher_profile'])) {
    $user_id = $_SESSION['auth_user']['user_id'];
    $phone_no = mysqli_real_escape_string($con, trim($_POST['phone_no'] ?? ''));
    $email = mysqli_real_escape_string($con, trim($_POST['email'] ?? ''));
    $father_name = mysqli_real_escape_string($con, trim($_POST['father_name'] ?? ''));

    if (empty($phone_no) || empty($email)) {
        $_SESSION['message'] = "⚠️ Phone number and email cannot be empty.";
        header("Location: profile.php");
        exit();
    }

    $update_q = "UPDATE users SET phone_no = '$phone_no', email = '$email', father_name = '$father_name' WHERE id = '$user_id'";
    $res = mysqli_query($con, $update_q);

    if ($res) {
        $_SESSION['auth_user']['email'] = $email;
        $_SESSION['message'] = "✅ Profile updated successfully!";
    } else {
        $_SESSION['message'] = "❌ Failed to update profile: " . mysqli_error($con);
    }

    header("Location: profile.php");
    exit();
}

// ✅ Change Teacher Password
if (isset($_POST['change_teacher_password'])) {
    $user_id = $_SESSION['auth_user']['user_id'];
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password)) {
        $_SESSION['message'] = "⚠️ All password fields are required.";
        header("Location: profile.php");
        exit();
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['message'] = "⚠️ New password and confirmation do not match.";
        header("Location: profile.php");
        exit();
    }

    if (strlen($new_password) < 4) {
        $_SESSION['message'] = "⚠️ Password must be at least 4 characters.";
        header("Location: profile.php");
        exit();
    }

    // Verify current password
    $check_q = mysqli_query($con, "SELECT password FROM users WHERE id = '$user_id' LIMIT 1");
    if ($check_q && mysqli_num_rows($check_q) > 0) {
        $u_row = mysqli_fetch_assoc($check_q);
        if ($u_row['password'] !== $current_password) {
            $_SESSION['message'] = "❌ Current password is incorrect.";
            header("Location: profile.php");
            exit();
        }

        $new_pwd_esc = mysqli_real_escape_string($con, $new_password);
        $upd_pwd = mysqli_query($con, "UPDATE users SET password = '$new_pwd_esc' WHERE id = '$user_id'");
        if ($upd_pwd) {
            $_SESSION['message'] = "✅ Password changed successfully!";
        } else {
            $_SESSION['message'] = "❌ Failed to update password: " . mysqli_error($con);
        }
    } else {
        $_SESSION['message'] = "❌ User record not found.";
    }

    header("Location: profile.php");
    exit();
}

// Fallback: If any request reaches the end of teacher_code.php without matching an action, redirect safely to view-report.php
if (!headers_sent()) {
    header("Location: view-report.php");
    exit();
}

?>
