<?php
include('authentication.php');

$class_id   = mysqli_real_escape_string($con, $_POST['class_id']);
$session_id = mysqli_real_escape_string($con, $_POST['session_id']);

$sql = "
SELECT 
    sd.student_id,
    sd.student_name
FROM erp_student_class_data sc
INNER JOIN erp_student_detail sd 
    ON sd.student_id = sc.student_id
WHERE sc.class_id = '$class_id'
  AND sc.session_id = '$session_id'
  AND sc.class_status = '0'
  AND sd.student_status = '0'
ORDER BY sd.student_name ASC
";

$res = mysqli_query($con, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}

echo json_encode($data);
