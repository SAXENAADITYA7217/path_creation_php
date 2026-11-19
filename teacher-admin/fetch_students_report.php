<?php
include('authentication.php');

if(isset($_POST['class_id'])){
    // SECURITY: Sanitize input for class_id
    $class_id = mysqli_real_escape_string($con, $_POST['class_id']);

    // SECURITY: Use prepared statement for fetching students
    $stmt = mysqli_prepare($con, "SELECT id, rollno, fname, lname FROM users WHERE role_as='0' AND class=? ORDER BY rollno ASC");
    if (!$stmt) {
        die("Statement preparation failed: " . mysqli_error($con));
    }
    // Assuming 'class' column is a string type (e.g., VARCHAR)
    mysqli_stmt_bind_param($stmt, "s", $class_id); 
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0){
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr><th>Roll No</th><th>Name</th><th>Action</th></tr></thead><tbody>';
        while($row = mysqli_fetch_assoc($result)){
            $name = htmlspecialchars($row['fname'].' '.$row['lname']);
            // *** IMPORTANT CHANGE HERE: Use an <a> tag instead of a button/modal ***
            // The link points to the new print_report_card.php with the student ID
            $report_link = 'print_report_card.php?student_id=' . $row['id'];
            
            echo '<tr>';
            echo '<td>'.htmlspecialchars($row['rollno']).'</td>';
            echo '<td>'.$name.'</td>';
            // Changed from button to anchor tag
            echo '<td><a href="'.$report_link.'" class="btn btn-sm btn-info" target="_self">View & Print Report</a></td>'; 
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p class="alert alert-warning">No students found in this class.</p>';
    }
    mysqli_stmt_close($stmt);
}
?>