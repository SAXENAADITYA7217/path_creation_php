<?php

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

$user_id = $_SESSION['auth_user']['user_id'] ?? $_SESSION['auth_user']['id'] ?? '';
$classes = [];

if (!empty($user_id)) {
    // Check teacher_subject first
    $q_ts = "SELECT DISTINCT class_code FROM teacher_subject WHERE teacher_id = ? ORDER BY CAST(class_code AS UNSIGNED) ASC, class_code ASC";
    $stmt_ts = mysqli_prepare($con, $q_ts);
    if ($stmt_ts) {
        mysqli_stmt_bind_param($stmt_ts, "i", $user_id);
        mysqli_stmt_execute($stmt_ts);
        $res_ts = mysqli_stmt_get_result($stmt_ts);
        while ($r = mysqli_fetch_assoc($res_ts)) {
            $classes[] = strval($r['class_code']);
        }
        mysqli_stmt_close($stmt_ts);
    }

    // Fallback to class_teacher if teacher_subject has no entries
    if (empty($classes)) {
        $query_class = "SELECT DISTINCT class FROM class_teacher WHERE user_id = '$user_id'";
        $result = mysqli_query($con, $query_class);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $classes[] = strval($row['class']);
            }
        }
    }
}

// Convert to comma-separated string
$class_ids = implode(", ", $classes);

// Convert class IDs to text labels
$display_classes = [];
foreach($classes as $c){
    if(isset($classMapping[$c])){
        $display_classes[] = $classMapping[$c];
    }
}

// If teacher selected class in GET or session
if (isset($_GET['class']) && in_array($_GET['class'], $classes)) {
    $selected_class = $_GET['class'];
    $_SESSION['teacher_selected_class'] = $selected_class;
} elseif (isset($_SESSION['teacher_selected_class']) && in_array($_SESSION['teacher_selected_class'], $classes)) {
    $selected_class = $_SESSION['teacher_selected_class'];
} elseif (!empty($classes)) {
    $selected_class = $classes[0]; // auto select first assigned class
    $_SESSION['teacher_selected_class'] = $selected_class;
} else {
    $selected_class = '';
}

?>