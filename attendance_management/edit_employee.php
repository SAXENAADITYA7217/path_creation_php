<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];

if (isset($_GET['id'])) {
    $emp_id = $_GET['id'];
    $query = "SELECT * FROM employee_data WHERE masterid = '$masterid' AND employee_id = '$emp_id' LIMIT 1";
    $query_run = mysqli_query($con, $query);

    if (mysqli_num_rows($query_run) > 0) {
        $employee = mysqli_fetch_assoc($query_run);
        
        // Fetch leave data from employee_leave table
        $leaveQuery = "SELECT employee_sl, employee_el, employee_cl 
                       FROM employee_leave WHERE masterid = '$masterid' AND employee_id = '{$employee['employee_id']}' LIMIT 1";
        $leaveResult = mysqli_query($con, $leaveQuery);

        if (mysqli_num_rows($leaveResult) > 0) {
            $leaveData = mysqli_fetch_assoc($leaveResult);
            $employee_sick_leave = $leaveData['employee_sl'];
            $employee_earned_leave = $leaveData['employee_el'];
            $employee_casual_leave = $leaveData['employee_cl'];
        } else {
            // No leave record found, set defaults to 0
            $employee_sick_leave = 0;
            $employee_earned_leave = 0;
            $employee_casual_leave = 0;
        }


    } else {
        echo "<h4>No Employee Found</h4>";
        exit();
    }
} else {
    echo "<h4>Invalid Request</h4>";
    exit();
}
?>

<div class="card radius-10">
    <div class="card-body">
        <h5>Edit Employee</h5>
        <hr>

        <form action="attendance_code.php" method="POST">
            <input type="hidden" name="id" value="<?= $employee['id'] ?>">
            
            <div class="row">

                <div class="col-md-10">
                    <div class="row">

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Employee ID</label>
                            <input type="text" name="employee_id" class="form-control form-control-sm"  value="<?= $employee['employee_id'] ?>" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Employee Name</label>
                            <input type="text" name="employee_name" class="form-control form-control-sm" value="<?= $employee['employee_name'] ?>" required>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Phone No</label>
                            <input type="text" name="phone_no" class="form-control form-control-sm" value="<?= $employee['phone_no'] ?>" required>
                        </div>


                        <div class="col-md-4 mb-3">
                            <label class="form-label">Designation</label>
                            <input type="text" name="designation" class="form-control form-control-sm" value="<?= $employee['designation'] ?>">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control form-control-sm" value="<?= $employee['start_time'] ?>">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control form-control-sm" value="<?= $employee['end_time'] ?>">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Worker Type</label>
                            <select name="worker_type" class="form-select form-select-sm">
                                <option value="overtime" <?= $employee['worker_type'] == 'overtime' ? 'selected' : '' ?>>Overtime</option>
                                <option value="non_overtime" <?= $employee['worker_type'] == 'non_overtime' ? 'selected' : '' ?>>Non Overtime</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Grace Time</label>
                            <input type="number" name="grace_time" class="form-control form-control-sm" value="<?= $employee['grace_time'] ?>">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Over Time</label>
                            <input type="number" name="over_time" class="form-control form-control-sm" value="<?= $employee['over_time'] ?>">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Status</label>
                            <select name="employee_status" class="form-select form-select-sm">
                                <option value="0" <?= $employee['employee_status'] == '0' ? 'selected' : '' ?>>Active</option>
                                <option value="1" <?= $employee['employee_status'] == '1' ? 'selected' : '' ?>>Blocked</option>
                            </select>
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Sick Leave</label>
                            <input type="number" name="employee_sick_leave" class="form-control form-control-sm" value="<?= $employee_sick_leave ?>">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Earned Leave</label>
                            <input type="number" name="employee_earned_leave" class="form-control form-control-sm" value="<?= $employee_earned_leave ?>">
                        </div>

                        <div class="col-md-2 mb-3">
                            <label class="form-label">Casual Leave</label>
                            <input type="number" name="employee_casual_leave" class="form-control form-control-sm" value="<?= $employee_casual_leave ?>">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Salary</label>
                            <input type="number" name="employee_salary" class="form-control form-control-sm" value="<?= $employee['employee_salary'] ?>">
                        </div>

                        
                    </div>
                </div>

                <div class="col-md-2 mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Employee Picture</label><br>
                        <?php
                            $profileImage = base64_encode($employee['employee_profile']);
                            echo '<img src="data:image/jpeg;base64,'.$profileImage.'" width="180" height="180" style="object-fit: cover; ">';
                        ?>
                    </div>
                </div>

            </div>   
            
            <hr>
            <h5>Employee Bank Details</h5>

            <div class="row">

                <div class="col-md-2 mb-3">
                    <label class="form-label">Salary Pay Type</label>
                    <select name="employee_salary_type" class="form-select form-select-sm">
                        <option value="CASH" <?= $employee['employee_salary_type'] == 'CASH' ? 'selected' : '' ?>>CASH</option>
                        <option value="ACCOUNT" <?= $employee['employee_salary_type'] == 'ACCOUNT' ? 'selected' : '' ?>>ACCOUNT</option>
                    </select>
                </div>


                <div class="col-md-4 mb-3">
                    <label class="form-label">Beneficiary Name</label>
                    <input type="text" name="beneficiary_name" class="form-control form-control-sm" value="<?= $employee['beneficiary_name'] ?>">
                </div>


                <div class="col-md-6 mb-3">
                    <label class="form-label">Beneficiary Address</label>
                    <input type="text" name="beneficiary_address" class="form-control form-control-sm" value="<?= $employee['beneficiary_address'] ?>">
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">Beneficiary Account Type</label>
                    <select name="beneficiary_account_type" class="form-select form-select-sm">
                        <option value="10-Saving Account" <?= $employee['beneficiary_account_type'] == '10-Saving Account' ? 'selected' : '' ?>>10-Saving Account</option>
                        <option value="11-Current Account" <?= $employee['beneficiary_account_type'] == '11-Current Account' ? 'selected' : '' ?>>11-Current Account</option>
                        <option value="13-Cash Credit Account" <?= $employee['beneficiary_account_type'] == '13-Cash Credit Account' ? 'selected' : '' ?>>13-Cash Credit Account</option>
                    </select>
                </div>


                <div class="col-md-4 mb-3">
                    <label class="form-label">Account Number</label>
                    <input type="text" 
                        name="beneficiary_account_no" 
                        class="form-control form-control-sm"
                        style="text-transform: uppercase;"
                        oninput="this.value = this.value.toUpperCase();"
                        value="<?= strtoupper($employee['beneficiary_account_no']) ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" 
                        name="beneficiary_ifsc_code" 
                        class="form-control form-control-sm"
                        style="text-transform: uppercase;"
                        oninput="this.value = this.value.toUpperCase();"
                        value="<?= strtoupper($employee['beneficiary_ifsc_code']) ?>">
                </div>


              
            </div>                

            <button type="submit" name="update_employee_btn" class="btn btn-primary">Update Employee</button>
       
        </form>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
