<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];

$sql = "SELECT employee_id, employee_name FROM employee_data WHERE masterid = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $masterid);
$stmt->execute();
$result = $stmt->get_result();


?>

<?php include('message.php'); ?>

        <div class="card radius-10 overflow-hidden w-100">
            <div class="container mt-4">
                <h4>Add Employee Attendance</h4>
                <form action="attendance_code.php" method="POST">

                    <div class="row">
<div class="col-3 form-group">
    <label for="employeeSelect">Choose Employee:</label>
    <select class="form-control" id="employeeSelect" name="employee_id" required>
        <option value="">-- Select Employee --</option>

        <?php
        while ($row = $result->fetch_assoc()) {
            echo '<option value="'.$row['employee_id'].'">'.$row['employee_name'].'</option>';
        }
        ?>

    </select>
</div>


<div class="col-3 form-group mt-3">
    <label>Profile Preview:</label><br>
    <img id="profileImage" src="" alt="Profile Image"
         style="max-width:150px; display:none; border:1px solid #ccc; padding:5px;">
</div>

                        <div class="col-3">
                            <br>
                            <button type="button" class="btn btn-sm btn-primary" id="add-row-btn">Add Row</button>
                        </div>

                    </div>


<div id="attendanceRows">

    <div class="row attendance-row">
        <div class="col-3 form-group mt-3">
            <label>Select Date:</label>
            <input type="date" class="form-control" name="attendance_date[]" value="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="col-3 form-group mt-3">
            <label>Select Time:</label>
            <input type="time" class="form-control" name="attendance_time[]" required>
        </div>

        <div class="col-2 form-group mt-5">
            <button type="button" class="btn btn-danger btn-sm remove-row-btn" style="display:none;">Remove</button>
        </div>
    </div>

</div>

                    <button type="submit" name="add_attendance" class="btn btn-primary mt-3">Add Attendance</button>
                    
                </form>
            </div>
            <br>
        </div>


<script>
document.getElementById("employeeSelect").addEventListener("change", function () {
    const empId = this.value;
    const img = document.getElementById("profileImage");

    if (empId) {
        img.src = "get_employee_image.php?employee_id=" + empId;
        img.style.display = "block";
    } else {
        img.style.display = "none";
    }
});
</script>

<script>
document.getElementById("add-row-btn").addEventListener("click", function () {

    const container = document.getElementById("attendanceRows");

    const newRow = document.createElement("div");
    newRow.classList.add("row", "attendance-row");

    newRow.innerHTML = `
        <div class="col-3 form-group mt-3">
            <input type="date" class="form-control" name="attendance_date[]" required>
        </div>

        <div class="col-3 form-group mt-3">
            <input type="time" class="form-control" name="attendance_time[]" required>
        </div>

        <div class="col-2 form-group mt-3">
            <button type="button" class="btn btn-danger btn-sm remove-row-btn">Remove</button>
        </div>
    `;

    container.appendChild(newRow);
});

// Remove row
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("remove-row-btn")) {
        e.target.closest(".attendance-row").remove();
    }
});
</script>


<?php
include('includes/footer.php');
include('includes/scripts.php');
?>



