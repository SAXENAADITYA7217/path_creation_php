<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];

$query = "SELECT * FROM attendance_company_detail WHERE masterid = '$masterid' LIMIT 1";
$query_run = mysqli_query($con, $query);
$company_data = mysqli_fetch_assoc($query_run);
?>

<?php include('message.php'); ?>

<div class="card radius-10">
    <div class="card-body">
        <h5>Company Detail</h5>
        <hr>

        <form action="attendance_code.php" method="POST">
            <input type="hidden" name="masterid" value="<?= $masterid ?>">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control form-control-sm"
                        value="<?= $company_data['company_name'] ?? '' ?>" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Selection Method</label>
                    <input type="text" name="selection_method" class="form-control form-control-sm"
                        value="<?= $company_data['selection_method'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Debit Account No</label>
                    <input type="text" name="debit_account_no" class="form-control form-control-sm"
                        value="<?= $company_data['debit_account_no'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Info Line</label>
                    <input type="text" name="info_line" class="form-control form-control-sm"
                        value="<?= $company_data['info_line'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Sender Name</label>
                    <input type="text" name="sender_name" class="form-control form-control-sm"
                        value="<?= $company_data['sender_name'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Sender Address</label>
                    <textarea name="sender_address" class="form-control form-control-sm"><?= $company_data['sender_address'] ?? '' ?></textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Department Code</label>
                    <input type="text" name="department_code" class="form-control form-control-sm"
                        value="<?= $company_data['department_code'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Instrument Type</label>
                    <input type="text" name="Instrument_type" class="form-control form-control-sm"
                        value="<?= $company_data['Instrument_type'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Instrument No</label>
                    <input type="text" name="Instrument_no" class="form-control form-control-sm"
                        value="<?= $company_data['Instrument_no'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Purpose Code</label>
                    <input type="text" name="purpose_code" class="form-control form-control-sm"
                        value="<?= $company_data['purpose_code'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Customer Reference No</label>
                    <input type="text" name="customer_reference_no" class="form-control form-control-sm"
                        value="<?= $company_data['customer_reference_no'] ?? '' ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Request Mode</label>
                    <input type="text" name="request_mode" class="form-control form-control-sm"
                        value="<?= $company_data['request_mode'] ?? '' ?>">
                </div>

            </div>

            <button type="submit" name="update_company_detail" class="btn btn-primary">
                Update Detail
            </button>
        </form>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
