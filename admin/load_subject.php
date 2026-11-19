<?php
include('authentication.php');

$class_map = [
    '100' => 'Nursery', '101' => 'LKG', '102' => 'UKG',
    '1' => 'Class 1', '2' => 'Class 2', '3' => 'Class 3', '4' => 'Class 4', '5' => 'Class 5',
    '6' => 'Class 6', '7' => 'Class 7', '8' => 'Class 8', '9' => 'Class 9', '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)', '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm. (with Math)', '14' => 'Class 11 Comm. (without Math)',
    '15' => 'Class 11 Arts',
    '16' => 'Class 12 Sci (PCB)', '17' => 'Class 12 Sci (PCM)',
    '18' => 'Class 12 Comm. (with Math)', '19' => 'Class 12 Comm. (without Math)',
    '20' => 'Class 12 Arts'
];

if (isset($_POST['action'])) {

    // Action 1: Load active subjects for a class + already assigned for this teacher in this class
    if ($_POST['action'] === 'load_subjects') {
        $class_code = mysqli_real_escape_string($con, $_POST['class_code'] ?? '');
        $teacher_id = intval($_POST['teacher_id'] ?? 0);

        // Fetch active subjects for this class
        $query = "SELECT subject FROM class_subject_info WHERE class_code = ? AND status = 1 ORDER BY subject ASC";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "s", $class_code);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        $all_subjects = [];
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $all_subjects[] = $row['subject'];
            }
        }
        mysqli_stmt_close($stmt);

        // Fetch subjects already assigned to this teacher in this class from database
        $assigned_in_db = [];
        if ($teacher_id > 0) {
            $q_assigned = "SELECT subject FROM teacher_subject WHERE teacher_id = ? AND class_code = ?";
            $stmt_a = mysqli_prepare($con, $q_assigned);
            mysqli_stmt_bind_param($stmt_a, "is", $teacher_id, $class_code);
            mysqli_stmt_execute($stmt_a);
            $res_a = mysqli_stmt_get_result($stmt_a);
            if ($res_a) {
                while ($row_a = mysqli_fetch_assoc($res_a)) {
                    $assigned_in_db[] = $row_a['subject'];
                }
            }
            mysqli_stmt_close($stmt_a);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'all_subjects' => $all_subjects,
            'assigned_in_db' => $assigned_in_db
        ]);
        exit;
    }

    // Action 2: Get all subjects assigned to a teacher across all classes
    if ($_POST['action'] === 'get_teacher_assigned_subjects') {
        $teacher_id = intval($_POST['teacher_id'] ?? 0);

        $assigned = [];
        if ($teacher_id > 0) {
            $query = "SELECT id, class_code, subject FROM teacher_subject WHERE teacher_id = ? ORDER BY class_code ASC, subject ASC";
            $stmt = mysqli_prepare($con, $query);
            mysqli_stmt_bind_param($stmt, "i", $teacher_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $c_code = (string)$row['class_code'];
                    $c_name = $class_map[$c_code] ?? ("Class " . $c_code);
                    $assigned[] = [
                        'id' => $row['id'],
                        'class_code' => $c_code,
                        'class_name' => $c_name,
                        'subject' => $row['subject']
                    ];
                }
            }
            mysqli_stmt_close($stmt);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'assigned' => $assigned
        ]);
        exit;
    }
}
?>
