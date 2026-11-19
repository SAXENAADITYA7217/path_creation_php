<?php
include('authentication.php');
include('includes/header.php');

$teacher_id = $_SESSION['auth_user']['user_id'] ?? $_SESSION['auth_user']['id'] ?? '';

$full_class_options = [
    '100' => 'Nursery', '101' => 'LKG', '102' => 'UKG',
    '1' => 'Class 1', '2' => 'Class 2', '3' => 'Class 3', '4' => 'Class 4', '5' => 'Class 5',
    '6' => 'Class 6', '7' => 'Class 7', '8' => 'Class 8', '9' => 'Class 9', '10' => 'Class 10',
    '11' => 'Class 11 Sci (PCB)', '12' => 'Class 11 Sci (PCM)',
    '13' => 'Class 11 Comm.', '14' => 'Class 11 Arts',
    '15' => 'Class 12 Sci (PCB)', '16' => 'Class 12 Sci (PCM)',
    '17' => 'Class 12 Comm.', '18' => 'Class 12 Arts'
];

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
$classes_to_show = !empty($assigned_classes) ? $assigned_classes : array_keys($full_class_options);
?>

<style>
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }
    .card-header h4 {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    .card-header h4 a {
        float: none !important;
        width: 100%;
        text-align: center;
    }
}
</style>

<div class="container-fluid px-4">
    <div class="row mt-4">
        <div class="col-md-12">

            <?php include('message.php'); ?>

            <div class="card">
                <div class="card-header">
                    <h4>Add Test
                        <a href="test-view.php" class="btn btn-danger float-end">View Test</a>
                    </h4>
                </div>
                <div class="card-body">

                    <form action="Teacher_code.php" method="POST" enctype="multipart/form-data">
                        <div class="row">

                            <!-- Teacher Name (manual input) -->
                            <div class="col-md-6 mb-3">
                                <label for="teacher">Teacher Name</label>
                                <input type="text" name="teacher_name" required class="form-control" placeholder="Enter Your Name">
                            </div>

                            <!-- Class Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="class">Class</label>
                                <select name="class_code" required class="form-control">
                                    <option value="">Select Class</option>
                                    <?php
                                    foreach ($classes_to_show as $code) {
                                        $c_name = $full_class_options[$code] ?? ('Class ' . $code);
                                        echo "<option value='{$code}'>{$c_name}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <!-- Subject (manual input) -->
                            <div class="col-md-6 mb-3">
                                <label for="subject">Subject</label>
                                <input type="text" name="subject" required class="form-control" placeholder="Enter Subject manually">
                            </div>

                            <!-- Chapter/Title -->
                            <div class="col-md-6 mb-3">
                                <label for="chapter">Chapter/Title (Optional)</label>
                                <input type="text" name="chapter_title" class="form-control" placeholder="Enter Chapter or Topic">
                            </div>

                            <!-- Test Name -->
                            <div class="col-md-6 mb-3">
                                <label for="test_name">Test Name</label>
                                <input type="text" name="test_name" required class="form-control" placeholder="Enter Test Name">
                            </div>

                            <!-- Google Form Link -->
                            <div class="col-md-6 mb-3">
                                <label for="google_form_link">Google Form Link</label>
                                <input type="url" name="google_form_link" required class="form-control" placeholder="Enter Google Form URL">
                            </div>

                            <!-- Description -->
                            <div class="col-md-12 mb-3">
                                <label for="description">Description</label>
                                <textarea name="description" class="form-control" rows="4" placeholder="Optional Description"></textarea>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status">Status</label>
                                <input type="checkbox" name="status" value="1"/>
                            </div>

                            <!-- Submit Button -->
                            <div class="col-md-12 mb-3">
                                <button type="submit" name="test_add" class="btn btn-primary">Save Test</button>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>





<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
