<?php
include('authentication.php');
include('includes/header.php');

/* ================= VALIDATE INPUT ================= */
if (
    !isset($_GET['id']) ||
    !isset($_GET['session_id']) ||
    !isset($_GET['class_id'])
) {
    $_SESSION['message'] = "Invalid Request";
    header("Location: student_list.php");
    exit();
}

$student_id = mysqli_real_escape_string($con, $_GET['id']);
$session_id = mysqli_real_escape_string($con, $_GET['session_id']);
$class_id   = mysqli_real_escape_string($con, $_GET['class_id']);

/* ================= FETCH STUDENT + FEE DATA ================= */
$query = "
SELECT 
    s.student_id,
    s.student_name,
    s.student_father_name,
    s.student_phone_no,
    s.student_phone_no_2,
    s.aadhaar_card_no,
    s.student_pen_no,
    f.student_previous_fee,
    f.student_discount_fee,
    f.student_advance_fee
FROM erp_student_detail s
LEFT JOIN erp_student_fee_detail f 
    ON s.student_id = f.student_id
    AND f.class_id = '$class_id'
    AND f.session_id = '$session_id'
WHERE s.student_id = '$student_id'
LIMIT 1
";

$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['message'] = "Student Record Not Found";
    header("Location: student_list.php");
    exit();
}

$student = mysqli_fetch_assoc($result);

/* ================= FETCH CLASS LIST ================= */
$class_query = "SELECT * FROM erp_class_data WHERE class_status='0' ORDER BY class_id ASC";
$class_result = mysqli_query($con, $class_query);

/* ================= FETCH SESSION LIST ================= */
$session_query = "SELECT * FROM erp_session WHERE session_status='0' ORDER BY session_id DESC";
$session_result = mysqli_query($con, $session_query);
?>

<?php include('message.php'); ?>

<div class="container-fluid px-1">
    <div class="row mt-1">
        <div class="col-md-12">
            <div class="card">

                <div class="card-header">
                    <h5>
                        Edit Student Detail
                        <a href="student_list.php" class="btn btn-primary btn-sm float-end">Back</a>
                    </h5>
                </div>

                <div class="card-body">
                    <form action="master_code.php" method="POST">

                        <!-- Hidden Keys -->
                        <input type="hidden" name="student_id" value="<?= $student_id ?>">
                        <input type="hidden" name="old_class_id" value="<?= $class_id ?>">
                        <input type="hidden" name="old_session_id" value="<?= $session_id ?>">

                        <div class="row">

                            <div class="col-md-3 mb-3">
                                <label>Student Name</label>
                                <input type="text" name="student_name" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($student['student_name']) ?>" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Father Name</label>
                                <input type="text" name="student_father_name" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($student['student_father_name']) ?>" required>
                            </div>

                            <div class="col-md-1 mb-3">
                                <label>Class</label>
                                <select name="class_id" class="form-control form-control-sm">
                                    <?php while ($row = mysqli_fetch_assoc($class_result)) { ?>
                                        <option value="<?= $row['class_id'] ?>"
                                            <?= ($row['class_id'] == $class_id) ? 'selected' : '' ?>>
                                            <?= $row['class_name'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-1 mb-3">
                                <label>Session</label>
                                <select name="session_id" class="form-control form-control-sm">
                                    <?php while ($row = mysqli_fetch_assoc($session_result)) { ?>
                                        <option value="<?= $row['session_id'] ?>"
                                            <?= ($row['session_id'] == $session_id) ? 'selected' : '' ?>>
                                            <?= $row['session_name'] ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label>Phone No</label>
                                <input type="text" name="student_phone" class="form-control form-control-sm"
                                       value="<?= $student['student_phone_no'] ?>" required>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label>Second Phone</label>
                                <input type="text" name="student_second_phone" class="form-control form-control-sm"
                                       value="<?= $student['student_phone_no_2'] ?>" required>
                            </div>

                            <div class="col-md-3 mb-2">
                                <label>Aadhaar No</label>
                                <input type="text" name="student_aadhaar_number" class="form-control form-control-sm"
                                       value="<?= $student['aadhaar_card_no'] ?>">
                            </div>

                            <div class="col-md-3 mb-2">
                                <label>PEN No</label>
                                <input type="text" name="student_pen_number" class="form-control form-control-sm"
                                       value="<?= $student['student_pen_no'] ?>">
                            </div>

                            <div class="col-md-2 mb-2">
                                <label>Previous Balance</label>
                                <input type="text" name="previous_balance" class="form-control form-control-sm"
                                       value="<?= $student['student_previous_fee'] ?>">
                            </div>

                            <div class="col-md-2 mb-2">
                                <label>Fee Discount</label>
                                <input type="text" name="fee_discount" class="form-control form-control-sm"
                                       value="<?= $student['student_discount_fee'] ?>">
                            </div>

                            <div class="col-md-2 mb-2">
                                <label>Advance Fee</label>
                                <input type="text" name="advance_fee" class="form-control form-control-sm"
                                       value="<?= $student['student_advance_fee'] ?>">
                            </div>

                            <div class="col-md-3 mt-4">
                                <button type="submit" name="update_student_detail"
                                        class="btn btn-success btn-sm">
                                    Update Student
                                </button>
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
