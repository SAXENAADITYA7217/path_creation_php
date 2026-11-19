<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];


?>

<?php include('message.php'); ?>

        <div class="card radius-10 overflow-hidden w-100">
            <div class="container mt-4">
                <h4>Add Employee Attendance</h4>
                <form action="attendance_code.php" method="POST">

                    <div class="row">

                        <div class="col-3 form-group">
                            <label for="select_leave">Choose Employee:</label>
                            <select class="form-control"  name="select_leave" required>
                                <option value="">-- Select leave --</option>
                            </select>
                        </div>

                        
                        <div class="col-3 form-group">
                            <label>Select Date:</label>
                            <input type="date" class="form-control" name="leave_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>



                    </div>

                    <button type="submit" name="add_attendance" class="btn btn-primary mt-3">Add Attendance</button>
                    
                </form>
            </div>
            <br>
        </div>




<?php
include('includes/footer.php');
include('includes/scripts.php');
?>



