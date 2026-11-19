<?php
include('authentication.php');
include('includes/header.php');

/* ---------- DEFAULT SESSION ----------
$default_session_q = mysqli_query(
    $con,
    "SELECT session_id FROM erp_session WHERE session_status='0' ORDER BY session_id DESC LIMIT 1"
);
$default_session = mysqli_fetch_assoc($default_session_q)['session_id'] ?? '';
*/
$today = date('Y-m-d');

$default_session_q = mysqli_query(
    $con,
    "SELECT session_id 
     FROM erp_session 
     WHERE session_status='0'
     AND '$today' BETWEEN session_start_date AND session_end_date
     LIMIT 1"
);

$default_session = mysqli_fetch_assoc($default_session_q)['session_id'] ?? '';


// ---------- CLASSES ----------
$class_q = mysqli_query($con, "SELECT * FROM erp_class_data WHERE class_status='0' ORDER BY class_id ASC");

// ---------- SESSIONS ----------
$session_q = mysqli_query($con, "SELECT * FROM erp_session WHERE session_status='0' ORDER BY session_id DESC");
?>

<?php include('message.php'); ?>

<div class="container-fluid px-1">
    <div class="card">
        <div class="card-header">
            <h5>Add Fee Slip</h5>
        </div>

        <div class="card-body">
            <div class="row">

                <!-- SESSION -->
                <div class="col-md-2">
                    <label>Session</label>
                    <select id="session_id" class="form-control form-control-sm">
                        <?php while($s = mysqli_fetch_assoc($session_q)) { ?>
                            <option value="<?= $s['session_id']; ?>" <?= ($s['session_id']==$default_session)?'selected':'' ?>>
                                <?= $s['session_name']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- MONTH -->
                <div class="col-md-2">
                    <label>Month</label>
                    <select id="current_month" class="form-control form-control-sm"></select>
                </div>

                <!-- CLASS -->
                <div class="col-md-2">
                    <label>Class</label>
                    <select id="class_id" class="form-control form-control-sm">
                        <option value="">-- Select --</option>
                        <?php while($c = mysqli_fetch_assoc($class_q)) { ?>
                            <option value="<?= $c['class_id']; ?>"><?= $c['class_name']; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <!-- STUDENT -->
                <div class="col-md-4">
                    <label>Student</label>
                    <select id="student_id" class="form-control form-control-sm"></select>
                </div>

            </div>

            <hr>

            <!-- FEE BOX -->
            <div id="feeBox" class="alert alert-info d-none"></div>

            <!-- SLIP BOX -->
            <div id="slipBox" class="alert alert-warning d-none"></div>

            <!-- FORM -->
            <form action="master_code.php" method="POST">

                <input type="hidden" name="student_id" id="f_student_id">
                <input type="hidden" name="class_id" id="f_class_id">
                <input type="hidden" name="session_id" id="f_session_id">
                <input type="hidden" name="grand_total" id="f_grand_total">

                <div class="row mt-3">

                    <div class="col-md-2">
                        <label>Payment Type</label>
                        <select name="payment_type" class="form-control form-control-sm" required>
                            <option value="">-- Select --</option>
                            <option value="Cash">Cash</option>
                            <option value="UPI">UPI</option>
                            <option value="Bank">Bank</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Transaction ID</label>
                        <input type="text" name="payment_transaction_id" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2">
                        <label>Paid Amount</label>
                        <input type="number" name="payment_amount" class="form-control form-control-sm" required>
                    </div>

                    <div class="col-md-4">
                        <label>Remark</label>
                        <input type="text" name="other_remark" class="form-control form-control-sm">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" name="save_fee_slip" class="btn btn-success btn-sm w-100">
                            Save Slip
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>
</div>

<script>
$(function(){

$('#student_id').select2({ placeholder:"Select Student", width:'100%' });

/* LOAD MONTHS ON PAGE LOAD */
loadSessionMonths($('#session_id').val());

/* SESSION CHANGE */
$('#session_id').change(function(){

    // load months
    loadSessionMonths($(this).val());

    // RESET CLASS & STUDENT
    $('#class_id').val('');
    $('#student_id').empty().trigger('change');

    // HIDE OLD DATA
    $('#feeBox, #slipBox').addClass('d-none');

});


/* MONTH CHANGE */
$('#current_month').change(function(){
    $('#student_id').trigger('change');
});

/* LOAD STUDENTS */
$('#class_id').change(function(){

    $('#student_id').empty().trigger('change');
    $('#feeBox, #slipBox').addClass('d-none');

    let class_id = $(this).val();
    let session_id = $('#session_id').val();

    if(!class_id) return;

    $.post('get_students_by_class.php',
        { class_id, session_id },
        function(res){

            $('#student_id').append('<option></option>');

            res.forEach(s=>{
                $('#student_id').append(
                    `<option value="${s.student_id}">${s.student_name}</option>`
                );
            });

        }, 'json'
    );
});



/* LOAD FEE */
$('#student_id').change(function(){

    let data = {
        student_id: $(this).val(),
        class_id: $('#class_id').val(),
        session_id: $('#session_id').val(),
        current_month: $('#current_month').val()
    };

    if(!data.student_id) return;

    $.post('get_student_fee.php', data, function(f){

        // ----- FEE BOX TABLE -----
        let feeHtml = `
        <table class="table table-bordered table-sm mb-0">
            <tbody>
                <tr><th>Store Fee</th><td>₹${f.store_fee}</td></tr>
                <tr><th>Yearly Fee</th><td>₹${f.yearly_fee}</td></tr>
                <tr><th>Monthly Fee</th><td>₹${f.monthly_fee}</td></tr>
                <tr><th>Total Monthly Fee</th><td>₹${f.total_monthly_fee}</td></tr>
                <tr><th>Previous</th><td>₹${f.previous}</td></tr>
                <tr><th>Discount</th><td>₹${f.discount}</td></tr>
                <tr><th>Advance</th><td>₹${f.advance}</td></tr>
                <tr class="table-success"><th>GRAND TOTAL</th><td>₹${f.grand_total}</td></tr>
                <tr><th>Total Paid</th><td>₹${f.paid_total}</td></tr>
                <tr class="table-danger"><th>Balance</th><td>₹${f.balance}</td></tr>
            </tbody>
        </table>`;

        $('#feeBox').removeClass('d-none').html(feeHtml);

        // ----- PAYMENT HISTORY TABLE -----
        let slipHtml = `<table class="table table-striped table-sm"><thead><tr>
            <th>Date</th><th>Amount</th><th>Type</th><th>Remark</th>
        </tr></thead><tbody>`;

        if (!f.slips.length) slipHtml += `<tr><td colspan="4" class="text-center">No payment done yet.</td></tr>`;
        else f.slips.forEach(s=>{
            slipHtml += `<tr>
                <td>${s.fee_slip_date}</td>
                <td>₹${s.payment_amount}</td>
                <td>${s.payment_type}</td>
                <td>${s.other_remark || '-'}</td>
            </tr>`;
        });

        slipHtml += `</tbody></table>`;
        $('#slipBox').removeClass('d-none').html(slipHtml);

        // Set hidden form values
        $('#f_student_id').val(data.student_id);
        $('#f_class_id').val(data.class_id);
        $('#f_session_id').val(data.session_id);
        $('#f_grand_total').val(f.grand_total);

    }, 'json');
});


/* PAYMENT LIMIT 
$('input[name="payment_amount"]').on('input', function(){
    let pay = parseInt($(this).val() || 0);
    let bal = $('#feeBox').text().match(/Balance:\s₹(\d+)/);
    let balance = bal ? parseInt(bal[1]) : 0;
    if(pay > balance){
        alert('Payment cannot be greater than balance');
        $(this).val(balance);
    }
});
*/
});


function loadSessionMonths(session_id){

    let todayMonth = new Date().toISOString().slice(0,7); // YYYY-MM

    $.post('get_session_months.php',{session_id},function(res){

        $('#current_month').empty();

        let matched = false;

        res.forEach(m=>{
            let selected = (m.value === todayMonth) ? 'selected' : '';
            if(selected) matched = true;
            $('#current_month').append(
                `<option value="${m.value}" ${selected}>${m.label}</option>`
            );
        });

        // fallback → select last month if today not in range
        if(!matched && res.length){
            $('#current_month option:last').prop('selected', true);
        }

        $('#current_month').trigger('change');

    },'json');
}

</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
