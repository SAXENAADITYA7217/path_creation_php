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

/* If no session selected, set default latest active session
if ($selected_session == '') {
    $default_session_q = mysqli_query($con, "SELECT session_id FROM erp_session WHERE session_status='0' ORDER BY session_id DESC LIMIT 1");
    $default_session = mysqli_fetch_assoc($default_session_q)['session_id'] ?? '';

    $selected_session = $default_session;
}*/

// If no session selected, auto-select session based on current date
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

$current_month = date("Y-m");


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
                                    <?php }
                                        
                                        $session_year_query = mysqli_query($con, "SELECT session_start_date, session_end_date FROM erp_session WHERE session_id='$selected_session' LIMIT 1");
                                        $session_year_data = mysqli_fetch_assoc($session_year_query);

                                        $start_year = date("Y", strtotime($session_year_data['session_start_date']));
                                        $end_year = date("Y", strtotime($session_year_data['session_end_date']));

                                       // ---------- GET SELECTED MONTH ----------
                                       // $selected_month_full = $_GET['current_month'] ?? date("Y-m");

                                       $today_month = date("Y-m");
                                        $selected_month_full = $_GET['current_month'] ?? $today_month;

                                        // Make sure selected month lies within session range
                                        $session_start_month_full = date("Y-m", strtotime($session_year_data['session_start_date']));
                                        $session_end_month_full   = date("Y-m", strtotime($session_year_data['session_end_date']));

                                        if ($selected_month_full < $session_start_month_full) {
                                            $selected_month_full = $session_start_month_full;
                                        }
                                        if ($selected_month_full > $session_end_month_full) {
                                            $selected_month_full = $session_end_month_full;
                                        }

                                        $selected_year = intval(substr($selected_month_full, 0, 4));
                                        $selected_month = intval(substr($selected_month_full, 5, 2));

                                        // SESSION START YEAR / MONTH
                                        $session_start_year = intval(date("Y", strtotime($session_year_data['session_start_date'])));
                                        $session_start_month = intval(date("m", strtotime($session_year_data['session_start_date'])));

                                        // CALCULATE MONTH DIFFERENCE
                                        $months_passed = (($selected_year - $session_start_year) * 12) + ($selected_month - $session_start_month + 1);
                                        if ($months_passed < 0) $months_passed = 1;

                                    ?>
                                </select>

                                <!-- Session Filter -->
                                <select id="current_month" class="form-control form-control-sm" style="width:200px;">

                                <?php 
                                for ($year = $start_year; $year <= $end_year; $year++) {
                                    for ($m = 1; $m <= 12; $m++) {

                                        if ($year == $start_year && $m < date("m", strtotime($session_year_data['session_start_date']))) {
                                            continue;
                                        }

                                        if ($year == $end_year && $m > date("m", strtotime($session_year_data['session_end_date']))) {
                                            continue;
                                        }

                                        $month_num = str_pad($m, 2, "0", STR_PAD_LEFT);
                                        $month_name = date("F", mktime(0, 0, 0, $m, 1));

                                        $value = $year . "-" . $month_num;
                                        $selected = ($selected_month_full == $value) ? "selected" : "";

                                        echo "<option value='$value' $selected>$year - $month_name</option>";
                                    }
                                }
                                ?>
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

                        <table id="example_sort_data" class="table table-bordered align-middle mb-0" style="font-size: 12px; border-collapse: collapse;">

                            <thead class="table-light">
                                <tr>
                                    <th>Student Id</th>
                                    <th>Class</th>
                                    <th>Student Name</th>
                                    <th>Father Name</th>
                                    <th>Store Fee</th>
                                    <th>Yearly Fee</th>
                                    <th>Monthly Fee</th>
                                    <th>Total Monthly Fee</th>
                                    <th>Previous Fee</th>
                                    <th>Discount</th>
                                    <th>Advance Fee</th>
                                    <th>Total Fee</th>
                                    <th>Phone No 1</th>
                                    <th>Phone No 2</th>
                                    <th>Aadhaar No</th>
                                    <th>PEN No</th>
                                    <th>Session</th>
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

                                        // Fetch student data
                                        $student_fee_query = "SELECT * FROM erp_student_fee_detail WHERE student_id = '$student_id' AND class_id = '$class_id' AND session_id = '$session_id' AND student_fee_status = '0' LIMIT 1";
                                        $result_student_fee_data = mysqli_query($con, $student_fee_query);

                                        $row_student_fee_data = mysqli_fetch_assoc($result_student_fee_data);

                                        $student_previous_fee = $row_student_fee_data['student_previous_fee'];
                                        $student_discount_fee = $row_student_fee_data['student_discount_fee'];
                                        $student_advance_fee = $row_student_fee_data['student_advance_fee'];

                                        //Fee Detail Data
                                        $fee_query = "SELECT * FROM erp_class_fee WHERE class_id = '$class_id' AND session_id = '$session_id' AND class_fee_status = '0'";
                                        $result_fee_data = mysqli_query($con, $fee_query);
                                      
                                        $total_fee = 0;
                                        $store_fee = 0;
                                        $monthly_fee = 0;

                                        if (mysqli_num_rows($result_fee_data) > 0) {

                                            while ($row_fee = mysqli_fetch_assoc($result_fee_data)) {

                                                $fee_type_id = $row_fee['fee_type_id'];
                                                $class_fee_amount = $row_fee['class_fee_amount'];

                                                // Fetch Fee Type
                                                $fee_type_query = "SELECT fee_detail_type_status FROM erp_fee_type_detail WHERE fee_type_id='$fee_type_id' LIMIT 1";
                                                $result_fee_type = mysqli_query($con, $fee_type_query);
                                                $row_fee_type = mysqli_fetch_assoc($result_fee_type);

                                                // Make sure it exists
                                                if (!$row_fee_type) continue;

                                                $status = $row_fee_type['fee_detail_type_status'];  // string ("1","2","3")

                                                // Convert to integer for safety
                                                $status = (int)$status;

                                                if ($status === 1) {
                                                    $total_fee += $class_fee_amount;   // Yearly Fee
                                                } 
                                                else if ($status === 2) {
                                                    $store_fee += $class_fee_amount;   // Store Fee
                                                } 
                                                else if ($status === 3) {
                                                    $monthly_fee += $class_fee_amount; // Monthly Fee (always add)
                                                }
                                            }
                                        }

                                        // Total Monthly Fee
                                        $total_monthly_fee = $monthly_fee * $months_passed; 
                                        $grand_total_fee = ($total_fee + $total_monthly_fee + $student_previous_fee) - ($student_discount_fee + $student_advance_fee);
                                ?>

                                    
                                        <tr>
                                            <td><?= $row_student_data['student_id']; ?></td>
                                            <td><?= $class_name; ?></td>
                                            <td><?= $row_student_data['student_name']; ?></td>
                                            <td><?= $row_student_data['student_father_name']; ?></td>
                                            <td><?= $store_fee; ?></td>
                                            <td><?= $total_fee; ?></td>
                                            <td><?= $monthly_fee; ?></td>
                                            <td><?= $total_monthly_fee; ?></td>
                                            <td><?= $student_previous_fee; ?></td>
                                            <td><?= $student_discount_fee; ?></td>
                                            <td><?= $student_advance_fee; ?></td>
                                            <td><?= $grand_total_fee; ?></td>
                                            <td><?= $row_student_data['student_phone_no']; ?></td>
                                            <td><?= $row_student_data['student_phone_no_2']; ?></td>
                                            <td><?= $row_student_data['aadhaar_card_no']; ?></td>
                                            <td><?= $row_student_data['student_pen_no']; ?></td>
                                            <td><?= $session_name; ?></td>
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
        let current_month = document.getElementById('current_month').value;

        window.location.href = "?class_id=" + class_id + "&session_id=" + session_id + "&current_month=" + current_month;
    });

    document.getElementById('session_id').addEventListener('change', function () {
        let session_id = this.value;
        let class_id = document.getElementById('class_id').value;
        let current_month = document.getElementById('current_month').value;

        window.location.href = "?class_id=" + class_id + "&session_id=" + session_id + "&current_month=" + current_month;
    });

    document.getElementById('current_month').addEventListener('change', function () {
        let class_id = document.getElementById('class_id').value;
        let session_id = document.getElementById('session_id').value;
        let current_month = this.value;

        window.location.href = "?class_id=" + class_id + "&session_id=" + session_id + "&current_month=" + current_month;
    });

</script>




<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
