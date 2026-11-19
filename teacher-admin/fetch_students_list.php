<?php
// fetch_students_list.php (AJAX Target to List Students)
include('authentication.php');

if (isset($_POST['class_id'])) {
    $class_id = mysqli_real_escape_string($con, $_POST['class_id']);

    // Use prepared statement for fetching students
    $stmt = mysqli_prepare($con, "SELECT id, rollno, fname, lname FROM users WHERE role_as='0' AND class=? ORDER BY CAST(rollno AS UNSIGNED) ASC, rollno ASC");
    if (!$stmt) {
        echo '<div class="alert alert-danger my-3">Error preparing statement.</div>';
        exit;
    }
    mysqli_stmt_bind_param($stmt, "s", $class_id); 
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    if (mysqli_num_rows($result) > 0) {
        echo '<div class="table-responsive">';
        echo '<table class="pca-table">';
        echo '<colgroup>';
        echo '<col style="width: 15%;">';
        echo '<col style="width: 55%;">';
        echo '<col style="width: 30%;">';
        echo '</colgroup>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Roll No</th>';
        echo '<th>Student Name</th>';
        echo '<th class="text-end">Actions</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($row = mysqli_fetch_assoc($result)) {
            $name = htmlspecialchars(trim($row['fname'] . ' ' . ($row['lname'] ?? '')));
            $initial = strtoupper(substr(trim($row['fname'] ?? 'S'), 0, 1));
            $report_link = 'print_report_card.php?student_id=' . $row['id'];
            
            echo '<tr>';
            echo '<td><span class="badge-roll-pill">#' . htmlspecialchars($row['rollno']) . '</span></td>';
            echo '<td>';
            echo '<div class="d-flex align-items-center gap-2">';
            echo '<div class="student-avatar-sm">' . $initial . '</div>';
            echo '<span class="fw-bold text-dark">' . $name . '</span>';
            echo '</div>';
            echo '</td>';
            echo '<td class="text-end">';
            echo '<a href="' . $report_link . '" class="btn-print-link"><i class="fa-solid fa-file-invoice me-1"></i> View &amp; Print Report</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<div class="empty-state-clean text-center my-3">';
        echo '<div class="empty-icon-circle mx-auto mb-2"><i class="fa-solid fa-folder-open text-muted fa-lg"></i></div>';
        echo '<h6 class="fw-bold text-dark mb-1">No Students Found</h6>';
        echo '<p class="text-muted mb-0" style="font-size: 0.85rem;">No student records found enrolled in this class.</p>';
        echo '</div>';
    }
}
?>
