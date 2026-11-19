<?php
include('authentication.php');
include('includes/header.php');
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-12">
            <?php include('message.php'); ?>
            <div class="card">
                <div class="card-header">
                    <h4>Form Details</h4>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="form-group mb-3">
                            <label for="classSelect">Select Class:</label>
                            <select class="form-control" id="classSelect" name="selected_class">
                                <option value="">-- Select Class --</option>
                                <option value="3" <?= (isset($_GET['selected_class']) && $_GET['selected_class'] == '3') ? 'selected' : '' ?>>Class 3rd</option>
                                <option value="4" <?= (isset($_GET['selected_class']) && $_GET['selected_class'] == '4') ? 'selected' : '' ?>>Class 4th</option>
                                <option value="5" <?= (isset($_GET['selected_class']) && $_GET['selected_class'] == '5') ? 'selected' : '' ?>>Class 5th</option>
                                <option value="6" <?= (isset($_GET['selected_class']) && $_GET['selected_class'] == '6') ? 'selected' : '' ?>>Class 6th</option>
                                <option value="7" <?= (isset($_GET['selected_class']) && $_GET['selected_class'] == '7') ? 'selected' : '' ?>>Class 7th</option>
                                <option value="8" <?= (isset($_GET['selected_class']) && $_GET['selected_class'] == '8') ? 'selected' : '' ?>>Class 8th</option>
                                <option value="9" <?= (isset($_GET['selected_class']) && $_GET['selected_class'] == '9') ? 'selected' : '' ?>>Class 9th</option>
                                <option value="10" <?= (isset($_GET['selected_class']) && $_GET['selected_class'] == '10') ? 'selected' : '' ?>>Class 10th</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                    <hr>
                    <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>#</th>
                                <th>Form ID</th>
                                <th>Student Name</th>
                                <th>Father's Name</th>
                                <th>Date of Birth</th>
                                <th>Class</th>
                                <th>Phone No 1</th>
                                <th>Phone No 2</th>
                                <th>Coaching Name</th>
                                <th>School Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include('config/dbcon.php'); // your DB connection

                            if (isset($_GET['selected_class']) && !empty($_GET['selected_class'])) {
                                $selected_class = mysqli_real_escape_string($con, $_GET['selected_class']);
                                $query = "SELECT * FROM form_data WHERE student_class = '$selected_class' ORDER BY id DESC";
                            } else {
                                $query = "SELECT * FROM form_data ORDER BY id DESC";
                            }

                            $query_run = mysqli_query($con, $query);
                            $sr_no = 1;

                            if (mysqli_num_rows($query_run) > 0) {
                                foreach ($query_run as $row) {
                            ?>
                                    <tr>
                                        <td><?= $sr_no++; ?></td>
                                        <td><?= htmlspecialchars($row['form_id']); ?></td>
                                        <td><?= htmlspecialchars($row['student_name']); ?></td>
                                        <td><?= htmlspecialchars($row['student_father_name']); ?></td>
                                        <td><?= htmlspecialchars($row['student_dob']); ?></td>
                                        <td><?= htmlspecialchars($row['student_class']); ?></td>
                                        <td><?= htmlspecialchars($row['phone_no_1']); ?></td>
                                        <td><?= htmlspecialchars($row['phone_no_2']); ?></td>
                                        <td><?= htmlspecialchars($row['student_coaching_name']); ?></td>
                                        <td><?= htmlspecialchars($row['student_school_name']); ?></td>
                                    </tr>
                            <?php
                                }
                            } else {
                                echo '<tr><td colspan="10" class="text-center">No Record Found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
