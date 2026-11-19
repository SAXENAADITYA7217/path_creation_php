<?php
include('authentication.php');
include('includes/header.php');

// Fetch classes
$academy_class_query = "SELECT * FROM erp_class_data WHERE class_status = '0' ORDER BY class_id ASC";
$result_academy_class = mysqli_query($con, $academy_class_query);

// Store class options
$class_options = "";
mysqli_data_seek($result_academy_class, 0);
while ($row_class = mysqli_fetch_assoc($result_academy_class)) {
    $class_options .= "<option value='{$row_class['class_id']}'>{$row_class['class_name']}</option>";
}

// Fetch sessions
$session_query = "SELECT * FROM erp_session WHERE session_status = '0' ORDER BY session_id DESC";
$result_session = mysqli_query($con, $session_query);

// Session options
$session_options = "";
mysqli_data_seek($result_session, 0);
while ($row_sessions = mysqli_fetch_assoc($result_session)) {
    $session_options .= "<option value='{$row_sessions['session_id']}'>{$row_sessions['session_name']}</option>";
}

?>

<?php include('message.php'); ?>

<div class="container-fluid px-1">
    <div class="row mt-1">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header mb-0">
                    <h5>Add Student
                        <a href="#" class="btn btn-primary btn-sm float-end" id="addButton">Back</a>
                    </h5>
                </div>
                <div class="card-body mb-0">
                    <form action="master_code.php" method="POST">

                        <div class="row">


                            <div class="col-md-3 mb-3">
                                <label for="">Student Name</label>
                                <input type="text" name="student_name" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="">Student Father Name</label>
                                <input type="text" name="student_father_name" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-1 mb-3">
                                <label for="">Class</label>
                                <select name="class_id" class="form-control form-control-sm fee-select class-select">
                                    <option value="">-- Select --</option>
                                    <?= $class_options; ?>
                                </select>
                            </div>

                            <div class="col-md-1 mb-3">
                                <label for="">Session</label>
                                <select name="session_id" class="form-control form-control-sm fee-select class-select">
                                    <?= $session_options; ?>
                                </select>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="">Phone No</label>
                                <input type="text" name="student_phone" class="form-control form-control-sm"
                                    required minlength="10" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label for="">Second Phone No</label>
                                <input type="text" name="student_second_phone" class="form-control form-control-sm"
                                    required minlength="10" maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                            </div>

                            <div class="col-md-3 mb-1">
                                <label for="">Aadhar No</label>
                                <input type="text" name="student_aadhaar_number" id="aadhaarInput"
                                    class="form-control form-control-sm" maxlength="14">
                            </div>

                            <div class="col-md-3 mb-1">
                                <label for="">Pen No</label>
                                <input type="text" name="student_pen_number" id="penInput"
                                    class="form-control form-control-sm" maxlength="14">
                            </div>

                            <div class="col-md-2 mb-1">
                                <label for="">Previous Balance</label>
                                <input type="text" name="previous_balance" class="form-control form-control-sm"
                                    required value = "0" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                            </div>

                            <div class="col-md-2 mb-1">
                                <label for="">Fee Discount</label>
                                <input type="text" name="fee_discount" class="form-control form-control-sm"
                                    required value = "0" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                            </div>

                            <div class="col-md-2 mb-1">
                                <label for="">Advance Fee</label>
                                <input type="text" name="advance_fee" class="form-control form-control-sm"
                                    required value = "0" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                            </div>

                            <div class="col-md-3 mb-1">
                                <br>
                                <button type="submit" name="add_student_detail" class="btn btn-primary btn-sm">Add Student Detail</button>
                            </div> 

                        </div>    

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>




<script>
document.getElementById("aadhaarInput").addEventListener("input", function () {
    formatNumber(this);
});

document.getElementById("penInput").addEventListener("input", function () {
    formatNumber(this);
});

function formatNumber(inputField) {
    let value = inputField.value.replace(/\D/g, ""); // only digits
    value = value.substring(0, 12); // max 12 digits

    if (value.length > 4 && value.length <= 8) {
        value = value.replace(/(\d{4})(\d+)/, "$1 $2");
    } 
    else if (value.length > 8) {
        value = value.replace(/(\d{4})(\d{4})(\d+)/, "$1 $2 $3");
    }

    inputField.value = value;
}

</script>


<script>
document.getElementById("aadhaarInput").addEventListener("input", function () {
    let inputField = this;
    formatNumber(inputField);

    let cleanAadhaar = inputField.value.replace(/\s+/g, ""); 

    if(cleanAadhaar.length === 12){
        checkAadhaar(cleanAadhaar);
    } else {
        resetAadhaarField();
    }
});

function checkAadhaar(aadhaarNumber){
    $.ajax({
        url: "check_aadhaar.php",
        type: "POST",
        data: { check_aadhaar: 1, aadhaar: aadhaarNumber },
        success: function(response){

            if(response.trim() === "exists"){
                // red border
                $("#aadhaarInput").css("border", "2px solid red");

                // show error
                if(!$("#aadhaarError").length){
                    $("#aadhaarInput").after("<small id='aadhaarError' style='color:red;'>Aadhaar already exists!</small>");
                }

                // disable submit
                $("button[name='add_student_detail']").prop("disabled", true);
            } 
            else {
                resetAadhaarField();
            }
        }
    });
}

function resetAadhaarField(){
    $("#aadhaarInput").css("border", "1px solid #ced4da");
    $("#aadhaarError").remove();
    $("button[name='add_student_detail']").prop("disabled", false);
}
</script>


<?php
include('includes/footer.php');
include('includes/scripts.php');
?>