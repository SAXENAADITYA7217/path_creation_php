<?php
include('authentication.php');
include('includes/header.php');

/* ---------- DEFAULT SESSION (DATE BASED) ---------- */
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

/* ---------- FILTERS ---------- */
$selected_session = $_GET['session_id'] ?? $default_session;
$selected_class   = $_GET['class_id'] ?? '';
$selected_month   = $_GET['current_month'] ?? '';

/* ---------- CLASSES ---------- */
$class_q = mysqli_query(
    $con,
    "SELECT * FROM erp_class_data
     WHERE class_status='0'
     ORDER BY class_id ASC"
);

/* ---------- SESSIONS ---------- */
$session_q = mysqli_query(
    $con,
    "SELECT * FROM erp_session
     WHERE session_status='0'
     ORDER BY session_id DESC"
);
?>

<?php include('message.php'); ?>

<div class="container-fluid px-1">
<div class="card">

<div class="card-header">
    <h5>View Fee Slip</h5>
</div>

<div class="card-body">

<!-- ================= FILTERS ================= -->
<div class="row mb-3">

<!-- SESSION -->
<div class="col-md-2">
<label>Session</label>
<select id="session_id" class="form-control form-control-sm">
    <option value="">All Session</option>
    <?php while($s = mysqli_fetch_assoc($session_q)) { ?>
        <option value="<?= $s['session_id']; ?>"
            <?= ($selected_session==$s['session_id'])?'selected':'' ?>>
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
    <option value="">All Class</option>
    <?php while($c = mysqli_fetch_assoc($class_q)) { ?>
        <option value="<?= $c['class_id']; ?>"
            <?= ($selected_class==$c['class_id'])?'selected':'' ?>>
            <?= $c['class_name']; ?>
        </option>
    <?php } ?>
</select>
</div>

</div>

<!-- ================= TABLE ================= -->
                        <div class="table-responsive" style="font-size: 12px; overflow-x: auto;">

                        <table id="example_sort_data" class="table align-middle table-bordered mb-0" style="font-size: 12px; border-collapse: collapse;">

<thead class="table-light">
<tr>
    <th>Date</th>
    <th>Student</th>
    <th>Class</th>
    <th>Month</th>
    <th>Amount</th>
    <th>Payment Type</th>
    <th>Txn ID</th>
    <th>Remark</th>
</tr>
</thead>
<tbody>

<?php
$sql = "
SELECT
    fs.fee_slip_date,
    fs.payment_amount,
    fs.payment_type,
    fs.payment_transaction_id,
    fs.other_remark,
    sd.student_name,
    cd.class_name
FROM erp_fee_slip fs
INNER JOIN erp_student_detail sd ON sd.student_id = fs.student_id
INNER JOIN erp_class_data cd ON cd.class_id = fs.class_id
WHERE fs.fee_slip_status='0'
";

/* SESSION FILTER */
if($selected_session){
    $sql .= " AND fs.session_id='$selected_session'";
}

/* MONTH FILTER */
if($selected_month){
    $sql .= " AND DATE_FORMAT(fs.fee_slip_date,'%Y-%m')='$selected_month'";
}

/* CLASS FILTER */
if($selected_class){
    $sql .= " AND fs.class_id='$selected_class'";
}

$sql .= " ORDER BY fs.fee_slip_date DESC";

$result = mysqli_query($con,$sql);

if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
?>
<tr>
    <td><?= date('d-m-Y', strtotime($row['fee_slip_date'])) ?></td>
    <td><?= $row['student_name'] ?></td>
    <td><?= $row['class_name'] ?></td>
    <td><?= date('F Y', strtotime($row['fee_slip_date'])) ?></td>
    <td>₹<?= $row['payment_amount'] ?></td>
    <td><?= $row['payment_type'] ?></td>
    <td><?= $row['payment_transaction_id'] ?: '-' ?></td>
    <td><?= $row['other_remark'] ?: '-' ?></td>
</tr>
<?php
    }
}else{
?>
No Fee Slip Found
<?php } ?>

</tbody>
</table>

</div>
</div>
</div>
</div>

<!-- ================= SCRIPT ================= -->
<script>
loadSessionMonths('<?= $selected_session ?>','<?= $selected_month ?>');

/* FILTER CHANGE */
$('#session_id, #class_id, #current_month').change(function(){
    reload();
});

function reload(){
    window.location.href =
        "?session_id="+$('#session_id').val()+
        "&class_id="+$('#class_id').val()+
        "&current_month="+$('#current_month').val();
}

/* LOAD MONTHS */
function loadSessionMonths(session_id, selectedMonth){

    $('#current_month').empty();
    $('#current_month').append(`<option value="">All Months</option>`);

    if(!session_id){
        return;
    }

    $.post('get_session_months_slip.php',
        { session_id: session_id },
        function(res){

            res.forEach(m=>{
                let sel = (m.value === selectedMonth) ? 'selected' : '';
                $('#current_month').append(
                    `<option value="${m.value}" ${sel}>${m.label}</option>`
                );
            });

        },
    'json');
}
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
