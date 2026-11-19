<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];


?>

<div class="card radius-10">
    <div class="card-body">
        <?php include('message.php'); ?>

        <h5 class="mb-3">Add Employee Leave</h5>
        <hr>

        <form method="POST" action="attendance_code.php" enctype="multipart/form-data">

            <div class="row">

                <!-- Employee Select -->
                <div class="col-md-3 mb-3">
                    <label>Select Employee</label>
                    <select name="employee_id"  id="employee_id" class="form-control" required>
                        <option value="">-- Select Employee --</option>
                        <?php
                        $empQuery = "SELECT employee_id, employee_name 
                                     FROM employee_data 
                                     WHERE masterid='$masterid'";
                        $empRun = mysqli_query($con, $empQuery);
                        while ($emp = mysqli_fetch_assoc($empRun)) {
                            echo "<option value='{$emp['employee_id']}'>
                                    {$emp['employee_name']}
                                  </option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Leave Type -->
                <div class="col-md-3 mb-3">
                
                    <label class="d-flex justify-content-between align-items-center">
                        <span>Leave Type</span>
                        <span id="leave_balance" style="font-weight:600; color:green;"></span>
                    </label>
   
                    <select name="leave_type" id="leave_type" class="form-control" required>
                        <option value="">-- Select Leave Type --</option>
                        <option value="sick_leave">Sick Leave</option>
                        <option value="earned_leave">Earned Leave</option>
                        <option value="casual_leave">Casual Leave</option>
                    </select>
                </div>

                <!-- Leave Date -->
                <div class="col-md-3 mb-3">
                    <label>Leave Date</label>
                    <input type="date" name="leave_date" class="form-control" required>
                </div>

                

                <!-- Document Link -->
                <div class="col-md-3 mb-3">
                    <label>Document Link (Optional)</label>
                    <input type="file" name="document_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>

                <!-- Leave Reason -->
                <div class="col-md-12 mb-3">
                    <label>Leave Reason (Optional)</label>
                    <textarea name="leave_reason" class="form-control" rows="3"></textarea>
                </div>

                <!-- Submit -->
                <div class="col-md-12">
                    <button type="submit" name="save_leave" class="btn btn-primary">
                        Submit Leave Request
                    </button>
                </div>

            </div>

        </form>
    </div>
</div>

<script>
document.getElementById("employee_id").addEventListener("change", fetchLeaveBalance);
document.getElementById("leave_type").addEventListener("change", fetchLeaveBalance);

function fetchLeaveBalance() {

    var employeeId = document.getElementById("employee_id").value;
    var leaveType = document.getElementById("leave_type").value;

    if (employeeId !== "" && leaveType !== "") {

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "get_remaining_leave.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                document.getElementById("leave_balance").innerHTML =
                    "Remaining Leaves: " + this.responseText;
            }
        };

        xhr.send("employee_id=" + employeeId + "&leave_type=" + leaveType);
    }
}
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
