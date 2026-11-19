<?php
include('authentication.php');

// --- Exam Types & Class Mapping ---
$exam_types = [
    'unit_test_1' => ['name' => 'Unit Test 1', 'code' => 1],
    'unit_test_2' => ['name' => 'Unit Test 2', 'code' => 2],
    'unit_test_3' => ['name' => 'Unit Test 3', 'code' => 3],
    'unit_test_4' => ['name' => 'Unit Test 4', 'code' => 4],
    'half_year'   => ['name' => 'Half Year Exam', 'code' => 5],
    'final'       => ['name' => 'Final Exam', 'code' => 6],
    'class_test'  => ['name' => 'Class Test', 'code' => 7]
];

$full_class_options = [
    100 => 'Nursery', 101 => 'LKG', 102 => 'UKG', 1 => 'Class 1', 2 => 'Class 2', 
    3 => 'Class 3', 4 => 'Class 4', 5 => 'Class 5', 6 => 'Class 6', 7 => 'Class 7', 
    8 => 'Class 8', 9 => 'Class 9', 10 => 'Class 10', 11 => 'Class 11 Sci (PCB)', 
    12 => 'Class 11 Sci (PCM)', 13 => 'Class 11 Comm.', 14 => 'Class 11 Arts', 
    15 => 'Class 12 Sci (PCB)', 16 => 'Class 12 Sci (PCM)', 17 => 'Class 12 Comm.', 
    18 => 'Class 12 Arts'
];

if(isset($_POST['student_id'])) {
    $student_id = mysqli_real_escape_string($con, $_POST['student_id']);

    $student_info = mysqli_fetch_assoc(mysqli_query($con, "SELECT fname, lname, class FROM users WHERE id='$student_id'"));
    $student_class_name = $full_class_options[$student_info['class']] ?? 'Unknown Class';

    $query = "SELECT test_code, subject, marks_obtained, total_marks, DATE(created_at) as date_added
              FROM student_score WHERE student_id='$student_id' ORDER BY created_at DESC";
    $result = mysqli_query($con, $query);

    $output = '';
    if(mysqli_num_rows($result) > 0) {
        $output .= '<div class="table-responsive"><table class="table table-striped table-bordered table-sm">';
        $output .= '<thead>
                        <tr class="table-primary">
                            <th>Date Added</th>
                            <th>Subject</th>
                            <th>Test Type</th>
                            <th>Marks (Obtained/Total)</th>
                            <th>Percentage</th>
                        </tr>
                    </thead><tbody>';
        while($row = mysqli_fetch_assoc($result)) {
            $test_name = 'Unknown Test';
            foreach($exam_types as $map) {
                if($map['code'] == $row['test_code']) { $test_name = $map['name']; break; }
            }
            $percentage = ($row['total_marks'] > 0) ? number_format(($row['marks_obtained'] / $row['total_marks'])*100,2).'%' : 'N/A';
            $output .= '<tr>
                            <td>'.htmlspecialchars($row['date_added']).'</td>
                            <td>'.htmlspecialchars($row['subject']).'</td>
                            <td>'.htmlspecialchars($test_name).'</td>
                            <td>'.htmlspecialchars($row['marks_obtained']).' / '.htmlspecialchars($row['total_marks']).'</td>
                            <td>'.$percentage.'</td>
                        </tr>';
        }
        $output .= '</tbody></table></div>';
    } else {
        $output = '<p class="alert alert-info">No test history found for this student.</p>';
    }

    echo json_encode([
        'student_name' => htmlspecialchars($student_info['fname'].' '.$student_info['lname']),
        'student_class' => htmlspecialchars($student_class_name),
        'html' => $output
    ]);
    exit;
}
