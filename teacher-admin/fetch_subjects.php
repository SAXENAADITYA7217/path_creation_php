<?php
include('authentication.php'); // include DB connection

if(isset($_POST['class_code'])){
    $class_code = mysqli_real_escape_string($con, $_POST['class_code']);

    $query = "SELECT subject_name FROM class_subject_info WHERE class_code='$class_code' AND status=1 ORDER BY subject_name ASC";
    $result = mysqli_query($con, $query);

    if(mysqli_num_rows($result) > 0){
        echo '<option value="">-- Select Subject --</option>';
        while($row = mysqli_fetch_assoc($result)){
            $sub = htmlspecialchars($row['subject_name']);
            echo "<option value='$sub'>$sub</option>";
        }
    } else {
        echo '<option value="">No Subjects Found</option>';
    }
}
?>

<!-- this is basically used to view-report.php for autoslect the subject when the teacher slect class  -->