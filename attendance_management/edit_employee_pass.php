<?php
ob_start();
include('authentication.php');
include('includes/header.php');

if (!isset($_GET['id'])) {
    header('Location: employee_pass.php'); // redirect if no ID
    exit();
}

$employeeId = $_GET['id'];
$passId = $_GET['pass_id'];
$today = date('Y-m-d');

// Check if the pass is valid for editing
$query = "SELECT * FROM employee_pass_info 
          WHERE employee_id = '$employeeId' 
          AND employee_pass_date = '$today' 
          AND employee_pass_status = 0
          LIMIT 1";

$result = mysqli_query($con, $query);

if (mysqli_num_rows($result) == 0) {
    // No valid record found: redirect back
    $_SESSION['message'] = "Invalid or used pass.";
    header("Location: employee_pass.php"); // or your main list page
    exit(0);
}

// Continue with the rest of the page if the record is valid
$passData = mysqli_fetch_assoc($result);

ob_end_flush();

$masterid = $_SESSION['auth_user']['user_master_id'];
$table_id = strtolower($_SESSION['auth_user']['user_master_id']); // -> ct0002
$attendanceTable = 'z_attendance_' . $table_id;

?>



<div class="row">		
    <div class="col-12 col-lg-4">
        <?php include('message.php'); ?>
        <div class="card radius-10 overflow-hidden w-100">
            <div class="container mt-4">
                <h4>Generate Employee Pass</h4>
                <form action="attendance_code.php" method="POST">
                    <div class="form-group">
                        <label for="employeeSelect">Choose Employee:</label>
                        <select class="form-control" id="employeeSelect" name="employee_id" onchange="showProfileImage(this)" required>
                            <?php
                                $today = date('Y-m-d');
                                $userId = isset($_GET['id']) ? $_GET['id'] : null;

                                // Only run query if $userId is set
                                if ($userId) {
                                    $query = "
                                        SELECT ed.id, ed.employee_id, ed.employee_name, ed.employee_profile 
                                        FROM employee_data ed
                                        INNER JOIN $attendanceTable am ON ed.employee_id = am.employee_id
                                        WHERE ed.employee_id = '$employeeId'
                                        AND am.attendance_date = '$today'
                                        AND am.attendance_status = 'Present'
                                        AND ed.masterid = '$masterid'
                                        LIMIT 1
                                    ";

                                    $result = mysqli_query($con, $query);

                                    if (mysqli_num_rows($result) > 0) {
                                        $row = mysqli_fetch_assoc($result);
                                        $id = $row['id'];
                                        $empId = $row['employee_id'];
                                        $empName = $row['employee_name'];
                                        $empProfile = base64_encode($row['employee_profile']);
                                        echo "<option value='{$empId}' data-image='{$empProfile}' selected>{$empId} - {$empName}</option>";
                                    } else {
                                        echo "<option value=''>Employee not available today or not present</option>";
                                    }
                                } else {
                                    echo "<option value=''>Invalid Access</option>";
                                }
                            ?>
                        </select>
                    </div>

                    
                    <input type="hidden" name="pass_id" value="<?php echo $passId ?>">

                    <div class="form-group mt-3">
                        <label>Profile Preview:</label><br>
                        <img id="profileImage" src="" alt="Profile Image" style="max-width: 150px; display:none; border:1px solid #ccc; padding:5px;">
                    </div>

                    <div class="form-group mt-3">
                        <label>Select Date:</label>
                        <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                    </div>

                    <div class="form-group mt-3">
                        <label>Select Time:</label>
                        <input type="time" class="form-control" name="pass_time" id="passTimeInput" required>
                    </div>

                    <button type="submit" name="update_generate_pass" class="btn btn-primary mt-3">Update Pass</button>
                    
                </form>
            </div>
            <br>
        </div>
    </div> 
</div>




<!-- JS -->
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

// 👇 Auto-run on page load to show selected employee image
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('employeeSelect');
    showProfileImage(select);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const passTimeInput = document.getElementById('passTimeInput');

    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const currentTime = `${hours}:${minutes}`;

    passTimeInput.value = currentTime;
    passTimeInput.min = currentTime;
});
</script>



<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
