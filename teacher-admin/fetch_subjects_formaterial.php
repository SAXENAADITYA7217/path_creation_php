<?php
include('authentication.php');

if (!isset($_SESSION['auth_user']['user_id']) && !isset($_SESSION['auth_user']['id'])) {
    echo '<option value="">Session expired. Please login again.</option>';
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'load_subjects') {
    $class_code = $_POST['class_code'] ?? '';
    $teacher_id = $_SESSION['auth_user']['user_id'] ?? $_SESSION['auth_user']['id'] ?? '';

    if ($class_code != '' && $teacher_id != '') {
        $query = "SELECT DISTINCT subject FROM teacher_subject WHERE teacher_id = ? AND class_code = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ii", $teacher_id, $class_code);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            echo '<option value="">--Select Subject--</option>';
            while ($row = mysqli_fetch_assoc($result)) {
                $subject = htmlspecialchars($row['subject']);
                echo "<option value='$subject'>$subject</option>";
            }
        } else {
            echo '<option value="">No subjects assigned for this class</option>';
        }

        mysqli_stmt_close($stmt);
    } else {
        echo '<option value="">Invalid request</option>';
    }
} else {
    echo '<option value="">Unauthorized access</option>';
}
?>
