<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];
$query = "SELECT * FROM employee_data WHERE masterid='$masterid'";
$query_run = mysqli_query($con, $query);

?>

<?php include('message.php'); ?>


<div class="card shadow-sm radius-10 border-0">
    <div class="card-body">
        <h4 class="mb-3 text-primary">Add Employee Money Detail</h4>
        <hr>

        <form action="attendance_code.php" method="POST">
            <div class="row align-items-center">

                <!-- LEFT SIDE FORM -->
                <div class="col-lg-8">

                    <div class="row g-3">

                        <!-- Employee Selector -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Employee</label>
                            <select name="employee_id" id="employee_select" class="form-select" required>
                                <option value="">-- Select Employee --</option>
                                <?php while($row = mysqli_fetch_assoc($query_run)) { ?>
                                    <option 
                                        value="<?= $row['employee_id']; ?>"
                                        data-name="<?= $row['employee_name']; ?>"
                                        data-image="<?= base64_encode($row['employee_profile']); ?>">
                                        <?= $row['employee_name']; ?> (<?= $row['employee_id']; ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <input type="hidden" name="employee_name" id="employee_name">

                        <!-- Money Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Money Type</label>
                            <select name="money_type" class="form-select">
                                <option value="0">Dues Amount</option>
                                <option value="1">Advance Amount</option>
                                <option value="2">Penalty Amount</option>
                            </select>
                        </div>

                        <!-- Transaction Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Type</label>
                            <select name="money_transaction_type" class="form-select">
                                <option value="0">CASH</option>
                                <option value="1">ONLINE</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Select Date</label>
                            <input type="date" name="money_date" class="form-control" required>
                        </div>

                        <!-- Money Amount -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Money Amount</label>
                            <input type="number" step="0.01" name="money_amount" class="form-control" required>
                        </div>

                        <!-- Description -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Other Note</label>
                            <input type="text" name="money_description" class="form-control">
                        </div>

                    </div>
                </div>

                <!-- RIGHT SIDE IMAGE -->
                <div class="col-lg-4 text-center">
                    <label class="form-label fw-semibold">Employee Preview</label><br>

                    <img id="employee_image"
                        src="https://via.placeholder.com/180x180?text=No+Image"
                        class="img-thumbnail shadow-sm"
                        width="180"
                        height="180"
                        style="object-fit: cover; transition:0.3s ease;">
                    
                    <p class="mt-2 text-muted small" id="preview_text">
                        No employee selected
                    </p>
                </div>

            </div>

            <div class="mt-4">
                <button type="submit" name="add_money_detail_data" class="btn btn-primary px-4">
                    Add Money Detail
                </button>
            </div>

        </form>
    </div>
</div>

<script>
document.getElementById('employee_select').addEventListener('change', function() {
    
    let selectedOption = this.options[this.selectedIndex];
    
    let image = selectedOption.getAttribute('data-image');
    let name = selectedOption.getAttribute('data-name');
    
    document.getElementById('employee_name').value = name;
    
    if(image){
        document.getElementById('employee_image').src = "data:image/jpeg;base64," + image;
    } else {
        document.getElementById('employee_image').src = "";
    }
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
