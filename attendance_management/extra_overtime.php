<?php
include('authentication.php');
include('includes/header.php');

  // Get selected month from URL or use current
    $currentMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

    // Generate previous and next months
    $timestamp = strtotime($currentMonth . '-01');
    $prevMonth = date('Y-m', strtotime('-1 month', $timestamp));
    $nextMonth = date('Y-m', strtotime('+1 month', $timestamp));
    $displayMonthName = date('F', $timestamp); // For example: July

    $masterid = $_SESSION['auth_user']['user_master_id'];
    $table_id = $_SESSION['auth_user']['user_master_id']; // -> ct0002
    $attendanceTable = 'z_attendance_' . $table_id;


?>

<div class="row">		
    <div class="col-12 col-lg-4">
        <?php include('message.php'); ?>
        <div class="card radius-10 overflow-hidden w-100">
            <div class="container mt-4">
                <h4>Extra Time</h4>
                <form action="attendance_code.php" method="POST">
                    <div class="form-group">
                        <label for="employeeSelect">Choose Employee:</label>
                        <select class="form-control" id="employeeSelect" name="employee_id" onchange="showProfileImage(this)" required>
                            <option value="">-- Select --</option>
                            <option value="all_select"> Select All Employee </option>
                            <option value="overtime"> Select Overtime Employee </option>
                            <option value="non_overtime"> Select Non Overtime Employee </option>
                            <?php
                            // Get today's date
                            $today = date('Y-m-d');

                                                        // Get today's date
                            $today = date('Y-m-d');

                            $attendanceQuery = "
                                SELECT DISTINCT employee_id 
                                FROM $attendanceTable
                                WHERE attendance_date = '$today'
                                AND attendance_status = 'Present'
                            ";
                            
                            $attendanceResult = mysqli_query($con, $attendanceQuery);
                            
                            while ($attRow = mysqli_fetch_assoc($attendanceResult)) {
                            
                                $empId = $attRow['employee_id'];
                            
                                $employeeQuery = "
                                    SELECT id, employee_id, employee_name, employee_profile
                                    FROM employee_data
                                    WHERE masterid = '$masterid'
                                    AND employee_id = '$empId'
                                    LIMIT 1
                                ";
                            
                                $employeeResult = mysqli_query($con, $employeeQuery);
                            
                                if ($row = mysqli_fetch_assoc($employeeResult)) {
                                    $id = $row['id'];
                                    $empName = $row['employee_name'];
                                    $empProfile = base64_encode($row['employee_profile']); // blob to base64
                            
                                    echo "<option value='{$empId}' data-image='{$empProfile}'>
                                            {$empId} - {$empName}
                                          </option>";
                                }
                            }

                            ?>
                        </select>
                    </div>

                    <div class="form-group mt-3">
                        <label>Profile Preview:</label><br>
                        <img id="profileImage" src="" alt="Profile Image" style="max-width: 150px; display:none; border:1px solid #ccc; padding:5px;">
                    </div>

                    <div class="form-group mt-3">
                        <label>Select Date:</label>
                        <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group mt-3">
                        <label>Extra Time:</label>
                        <input type="number" class="form-control" name="extra_time" required>
                    </div>

                    <button type="submit" name="extra_time_data" class="btn btn-primary mt-3">Extra Time</button>
                    
                </form>
            </div>
            <br>
        </div>
    </div>


    <div class="col-12 col-lg-8">

        <div class="card radius-10 overflow-hidden w-100">

            <div class="container mt-4">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="mb-0">Extra Overtime (<?= $displayMonthName ?>)</h5>
                    </div>
                    <div class="ms-auto d-flex align-items-center">
                        <form method="GET">
                            <input type="hidden" name="month" value="<?= $prevMonth ?>">
                            <button type="submit" class="btn btn-light btn-sm">&lt;</button>
                        </form>
                        <h5 class="mb-0 mx-2"><?= $displayMonthName ?></h5>
                        <form method="GET">
                            <input type="hidden" name="month" value="<?= $nextMonth ?>">
                            <button type="submit" class="btn btn-light btn-sm">&gt;</button>
                        </form>
                    </div>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Profile</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                           <?php
                                // Get current month in YYYY-MM format
                                $Select_month = $currentMonth;

                                $query = "SELECT * FROM employee_extra_work 
                                        WHERE masterid = '$masterid' AND DATE_FORMAT(extra_date, '%Y-%m') = '$Select_month' 
                                        ORDER BY id DESC";

                                $query_run = mysqli_query($con, $query);

                                if (mysqli_num_rows($query_run) > 0) {
                                    foreach ($query_run as $item) {
                                        $employee_id = $item['employee_id'];

                                        // Get employee details
                                        $employee_data_query = "SELECT * FROM employee_data WHERE masterid = '$masterid' AND employee_id = $employee_id";
                                        $employee_data_query_run = mysqli_query($con, $employee_data_query);

                                        if ($employee_item = mysqli_fetch_assoc($employee_data_query_run)) {
                            ?>
                            <tr>
                                <td><?= $employee_item['employee_id'] ?></td>
                                <td>
                                    <?php
                                        $profileImage = base64_encode($employee_item['employee_profile']);
                                        echo '<img src="data:image/jpeg;base64,'.$profileImage.'" width="50" height="50" style="object-fit: cover; border-radius: 50%;">';
                                    ?>
                                </td>
                                <td><?= $employee_item['employee_name'] ?></td>
                                <td><?= $item['extra_date'] ?></td>
                                <td><?= $item['extra_time'] ?> Minutes</td>
                                <td>
                                    <?php if ($item['server_update_status'] == 0): ?>
                                        <div class="badge rounded-pill bg-light-success text-success w-100">Sync</div>
                                    <?php else: ?>
                                        <div class="badge rounded-pill bg-light-danger text-danger w-100">Not Sync</div>
                                    <?php endif; ?>
                                </td>
                                
                            </tr>
                            <?php
                                        }
                                    }
                                } else {
                            ?>
                            <tr>
                                <td colspan="7" class="text-center">No Records Found</td>
                            </tr>
                            <?php
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>    


<script>
function showProfileImage(select) {
    const profileImg = document.getElementById('profileImage');
    const selectedOption = select.options[select.selectedIndex];
    const base64 = selectedOption.getAttribute('data-image');

    if (base64) {
        profileImg.src = "data:image/jpeg;base64," + base64;
        profileImg.style.display = 'block';
    } else {
        profileImg.src = "";
        profileImg.style.display = 'none';
    }
}
</script>



<?php
include('includes/footer.php');
include('includes/scripts.php');
?>


