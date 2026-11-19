<?php
include('authentication.php');
include('includes/header.php');

date_default_timezone_set("Asia/Kolkata");
$today = date('Y-m-d');

/* ================= CURRENT SESSION (BY DATE) ================= */
$current_session_q = "
    SELECT session_id 
    FROM erp_session 
    WHERE session_status='0'
      AND session_start_date <= '$today'
      AND session_end_date >= '$today'
    LIMIT 1
";
$current_session_r = mysqli_query($con, $current_session_q);
$current_session_d = mysqli_fetch_assoc($current_session_r);
$current_session_id = $current_session_d['session_id'] ?? '';

/* ================= FETCH CLASSES ================= */
$class_q = "SELECT * FROM erp_class_data WHERE class_status='0' ORDER BY class_id ASC";
$class_r = mysqli_query($con, $class_q);

/* ================= FETCH SESSIONS ================= */
$session_q = "SELECT * FROM erp_session WHERE session_status='0' ORDER BY session_id DESC";
$session_r = mysqli_query($con, $session_q);

/* ================= FETCH STUDENTS ================= */
$student_r = null;
if (isset($_GET['class_id'])) {

    $class_id   = mysqli_real_escape_string($con, $_GET['class_id']);
    $session_id = mysqli_real_escape_string(
        $con,
        $_GET['session_id'] ?? $current_session_id
    );

    $student_q = "
        SELECT s.student_id, s.student_name, s.student_father_name
        FROM erp_student_class_data c
        JOIN erp_student_detail s ON s.student_id = c.student_id
        WHERE c.class_id='$class_id'
          AND c.session_id='$session_id'
          AND c.class_status='0'
        ORDER BY s.student_name
    ";
    $student_r = mysqli_query($con, $student_q);
}
?>

<?php include('message.php'); ?>

<div class="container-fluid px-2">
    <div class="card">
        <div class="card-header">
            <h5>Promote Students to Next Class / Session</h5>
        </div>

        <div class="card-body">

            <!-- ================= FILTER ================= -->
            <form method="GET" class="row g-2 mb-3">

                <div class="col-md-3">
                    <label>Current Class</label>
                    <select name="class_id" class="form-control form-control-sm" required>
                        <option value="">Select</option>
                        <?php while($c = mysqli_fetch_assoc($class_r)){ ?>
                            <option value="<?= $c['class_id'] ?>"
                                <?= (@$_GET['class_id'] == $c['class_id']) ? 'selected' : '' ?>>
                                <?= $c['class_name'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Current Session</label>
                    <select name="session_id" class="form-control form-control-sm" required>
                        <?php
                        mysqli_data_seek($session_r, 0);
                        while($s = mysqli_fetch_assoc($session_r)){ ?>
                            <option value="<?= $s['session_id'] ?>"
                                <?= (
                                    (@$_GET['session_id'] == $s['session_id']) ||
                                    (!isset($_GET['session_id']) && $s['session_id'] == $current_session_id)
                                ) ? 'selected' : '' ?>>
                                <?= $s['session_name'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-2 align-self-end">
                    <button class="btn btn-primary btn-sm">Load Students</button>
                </div>

            </form>

            <?php if ($student_r && mysqli_num_rows($student_r) > 0) { ?>

            <!-- ================= PROMOTION FORM ================= -->
            <form action="master_code.php" method="POST">

                <input type="hidden" name="old_class_id" value="<?= $class_id ?>">
                <input type="hidden" name="old_session_id" value="<?= $session_id ?>">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Next Class</label>
                        <select name="new_class_id" class="form-control form-control-sm" required>
                            <?php
                            mysqli_data_seek($class_r, 0);
                            while($c = mysqli_fetch_assoc($class_r)){ ?>
                                <option value="<?= $c['class_id'] ?>">
                                    <?= $c['class_name'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Next Session</label>
                        <select name="new_session_id" class="form-control form-control-sm" required>
                            <?php
                            mysqli_data_seek($session_r, 0);
                            while($s = mysqli_fetch_assoc($session_r)){
                                if ($s['session_id'] == $session_id) continue; // BLOCK SAME SESSION
                            ?>
                                <option value="<?= $s['session_id'] ?>">
                                    <?= $s['session_name'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">
                                <input type="checkbox" onclick="toggleAll(this)">
                            </th>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Father Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($student_r)){ ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="students[]" value="<?= $row['student_id'] ?>">
                                </td>
                                <td><?= $row['student_id'] ?></td>
                                <td><?= $row['student_name'] ?></td>
                                <td><?= $row['student_father_name'] ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <button type="submit" name="promote_students"
                        class="btn btn-success btn-sm">
                    Promote Selected Students
                </button>

            </form>

            <?php } elseif(isset($_GET['class_id'])) { ?>
                <p class="text-danger">No students found for selected class & session.</p>
            <?php } ?>

        </div>
    </div>
</div>

<script>
function toggleAll(source){
    document.querySelectorAll('input[name="students[]"]').forEach(cb => cb.checked = source.checked);
}
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
