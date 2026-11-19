<?php
include('authentication.php');
include('includes/header.php');

// Fetch classes
$academy_class_query = "SELECT * FROM erp_class_data WHERE class_status = '0' ORDER BY class_id ASC";
$result_academy_class = mysqli_query($con, $academy_class_query);

// Fetch sessions
$session_query = "SELECT * FROM erp_session WHERE session_status = '0' ORDER BY session_id ASC";
$result_session = mysqli_query($con, $session_query);

// GET selected values from dropdown
$selected_class = $_GET['class_id'] ?? '';
$selected_session = $_GET['session_id'] ?? '';
// Auto-select current session based on today's date
if ($selected_session == '') {

    $today = date('Y-m-d');

    $default_session_q = mysqli_query(
        $con,
        "SELECT session_id 
         FROM erp_session 
         WHERE session_status='0'
         AND '$today' BETWEEN session_start_date AND session_end_date
         LIMIT 1"
    );

    if (mysqli_num_rows($default_session_q) > 0) {
        $selected_session = mysqli_fetch_assoc($default_session_q)['session_id'];
    }
}


/* If no session selected, set default latest active session
if ($selected_session == '') {
    $default_session_q = mysqli_query($con, "SELECT session_id FROM erp_session WHERE session_status='0' ORDER BY session_id DESC LIMIT 1");
    $default_session = mysqli_fetch_assoc($default_session_q)['session_id'] ?? '';

    $selected_session = $default_session;
}
*/
?>

<?php include('message.php'); ?>


<div class="container-fluid px-1">
    <div class="row">
        <div class="col-md-12">

            <div class="card">

                <div class="card-header mb-0">
                    <div class="row align-items-center mb-0">

                        <div class="col-md-4">
                            <h5 class="mb-0">Class Fee</h5>
                        </div>

                        <div class="col-md-8 text-end">

                            <div class="d-inline-flex gap-2">

                                <!-- Class Filter -->
                                <select id="class_id" class="form-control form-control-sm" style="width:180px;">
                                    <option value="">All Class</option>
                                    <?php while ($row_class = mysqli_fetch_assoc($result_academy_class)) { ?>
                                        <option value="<?= $row_class['class_id']; ?>"
                                            <?= ($selected_class == $row_class['class_id']) ? 'selected' : ''; ?>>
                                            <?= $row_class['class_name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <!-- Session Filter -->
                                <select id="session_id" class="form-control form-control-sm" style="width:180px;">
                                    <?php while ($row_session = mysqli_fetch_assoc($result_session)) { ?>
                                        <option value="<?= $row_session['session_id']; ?>"
                                            <?= ($selected_session == $row_session['session_id']) ? 'selected' : ''; ?>>
                                            <?= $row_session['session_name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <a href="add_student_detail.php" class="btn btn-danger btn-sm">
                                    Add Student
                                </a>

                            </div>
                        </div>

                    </div>
                </div>


                <div class="card-body mb-0">
                    <div class="table-responsive" style="font-size: 12px; overflow-x: auto;">

                        <table id="example_sort_data" class="table align-middle mb-0" style="font-size: 12px; border-collapse: collapse;">

                            <thead class="table-light">
                                <tr>
                                    <th>Student Id</th>
                                    <th>Class</th>
                                    <th>Student Name</th>
                                    <th>Father Name</th>
                                    <th>Phone No 1</th>
                                    <th>Phone No 2</th>
                                    <th>Aadhaar No</th>
                                    <th>PEN No</th>
                                    <th>Session</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // BUILD MAIN QUERY
                                $query = "SELECT * FROM erp_student_class_data WHERE class_status = '0'";

                                if ($selected_class != "") {
                                    $query .= " AND class_id = '$selected_class'";
                                }
                                if ($selected_session != "") {
                                    $query .= " AND session_id = '$selected_session'";
                                }

                                $query .= " ORDER BY class_id ASC";

                                $result_class_student = mysqli_query($con, $query);

                                if (mysqli_num_rows($result_class_student) > 0) {
                                   
                                    while ($row_class_student = mysqli_fetch_assoc($result_class_student)) {

                                        $student_id = $row_class_student['student_id'];
                                        $session_id = $row_class_student['session_id'];
                                        $class_id = $row_class_student['class_id'];
                                        // Class Name
                                        $class_q = mysqli_query($con, "SELECT class_name FROM erp_class_data WHERE class_id='$class_id' LIMIT 1");
                                        $class_name = mysqli_fetch_assoc($class_q)['class_name'] ?? 'N/A';

                                        // Session Name
                                        $ses_q = mysqli_query($con, "SELECT session_name FROM erp_session WHERE session_id='$session_id' LIMIT 1");
                                        $session_name = mysqli_fetch_assoc($ses_q)['session_name'] ?? 'N/A';

                                        // Fetch student data
                                        $student_query = "SELECT * FROM erp_student_detail WHERE student_id = '$student_id' AND student_status = '0' LIMIT 1";
                                        $result_student_data = mysqli_query($con, $student_query);

                                        $row_student_data = mysqli_fetch_assoc($result_student_data);
                                ?>

                                    
                                        <tr>
                                            <td><?= $row_student_data['student_id']; ?></td>
                                            <td><?= $class_name; ?></td>
                                            <td><?= $row_student_data['student_name']; ?></td>
                                            <td><?= $row_student_data['student_father_name']; ?></td>
                                            <td><?= $row_student_data['student_phone_no']; ?></td>
                                            <td><?= $row_student_data['student_phone_no_2']; ?></td>
                                            <td><?= $row_student_data['aadhaar_card_no']; ?></td>
                                            <td><?= $row_student_data['student_pen_no']; ?></td>
                                            <td><?= $session_name; ?></td>

                                            <td>
                                                <a href="edit_student_detail.php?id=<?= urlencode($student_id) ?>&session_id=<?= urlencode($session_id) ?>&class_id=<?= urlencode($class_id) ?>"
                                                    class="btn btn-primary btn-sm px-2 py-0">
                                                    Edit
                                                </a>

                                                <a href="delete_class_fee_data.php?id=<?= $row_class_fee['class_fee_id']; ?>" class="btn btn-danger btn-sm px-2 py-0">
                                                    Delete
                                                </a>
                                            </td>
                                        </tr>
                                    
                                <?php
                                    }
                                } else { ?>

                                    No Fee Records Found

                                <?php } ?>
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>


<!-- FILTER SCRIPT -->
<script>
    document.getElementById('class_id').addEventListener('change', function () {
        let class_id = this.value;
        let session_id = document.getElementById('session_id').value;
        window.location.href = "?class_id=" + class_id + "&session_id=" + session_id;
    });

    document.getElementById('session_id').addEventListener('change', function () {
        let session_id = this.value;
        let class_id = document.getElementById('class_id').value;
        window.location.href = "?class_id=" + class_id + "&session_id=" + session_id;
    });
</script>




<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
