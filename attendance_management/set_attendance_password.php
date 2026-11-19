<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];
?>

<form action="attendance_code.php" method="POST">
    
    <h3>Set Attendance Edit Password</h3>

    <label>New Password</label>
    <input type="password" name="edit_password" required class="form-control">

    <br><br>

    <button type="submit" name="save_attendance_password" class="btn btn-primary">
        Save Password
    </button>

</form>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>