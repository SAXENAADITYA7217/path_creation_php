<?php
include('authentication.php');

$student_id    = mysqli_real_escape_string($con, $_POST['student_id']);
$class_id      = mysqli_real_escape_string($con, $_POST['class_id']);
$session_id    = mysqli_real_escape_string($con, $_POST['session_id']);
$current_month = mysqli_real_escape_string($con, $_POST['current_month']);

// ---------------- SESSION START DATE ----------------
$session_q = mysqli_query(
    $con,
    "SELECT session_start_date 
     FROM erp_session 
     WHERE session_id='$session_id' 
     AND session_status='0'
     LIMIT 1"
);

$session = mysqli_fetch_assoc($session_q);
$session_start = strtotime($session['session_start_date']);
$selected_month = strtotime($current_month . "-01");

// MONTH COUNT
$months_passed = ((date("Y",$selected_month) - date("Y",$session_start)) * 12)
               + (date("m",$selected_month) - date("m",$session_start)) + 1;

if ($months_passed < 1) $months_passed = 1;

// ---------------- STUDENT FEE META ----------------
$fee_meta_q = mysqli_query(
    $con,
    "SELECT 
        student_previous_fee,
        student_discount_fee,
        student_advance_fee
     FROM erp_student_fee_detail
     WHERE student_id='$student_id'
       AND class_id='$class_id'
       AND session_id='$session_id'
       AND student_fee_status='0'
     LIMIT 1"
);

$fee_meta = mysqli_fetch_assoc($fee_meta_q);

$previous = (int)($fee_meta['student_previous_fee'] ?? 0);
$discount = (int)($fee_meta['student_discount_fee'] ?? 0);
$advance  = (int)($fee_meta['student_advance_fee'] ?? 0);

// ---------------- CLASS FEES ----------------
$yearly = 0;
$store  = 0;
$monthly = 0;

$class_fee_q = mysqli_query(
    $con,
    "SELECT 
        cf.class_fee_amount,
        ftd.fee_detail_type_status
     FROM erp_class_fee cf
     INNER JOIN erp_fee_type_detail ftd 
        ON ftd.fee_type_id = cf.fee_type_id
     WHERE cf.class_id='$class_id'
       AND cf.session_id='$session_id'
       AND cf.class_fee_status='0'
       AND ftd.fee_type_status='0'"
);

while ($row = mysqli_fetch_assoc($class_fee_q)) {

    $amount = (int)$row['class_fee_amount'];

    if ($row['fee_detail_type_status'] == 1) {
        $yearly += $amount;
    }
    elseif ($row['fee_detail_type_status'] == 2) {
        $store += $amount;
    }
    elseif ($row['fee_detail_type_status'] == 3) {
        $monthly += $amount;
    }
}

// ---------------- FINAL TOTAL ----------------
$total_monthly = $monthly * $months_passed;

$grand_total = (
    $yearly +
    $total_monthly +
    $previous
) - (
    $discount +
    $advance
);

// ---------------- PAID SLIPS ----------------
$paid_total = 0;
$slips = [];

$slip_q = mysqli_query(
    $con,
    "SELECT 
        fee_slip_id,
        payment_type,
        payment_amount,
        other_remark,
        fee_slip_date
     FROM erp_fee_slip
     WHERE student_id='$student_id'
       AND class_id='$class_id'
       AND session_id='$session_id'
       AND fee_slip_status='0'
     ORDER BY fee_slip_date ASC"
);

while ($row = mysqli_fetch_assoc($slip_q)) {
    $paid_total += (int)$row['payment_amount'];
    $slips[] = $row;
}

// ---------------- BALANCE ----------------
$balance = $grand_total - $paid_total;


echo json_encode([
    "store_fee"         => $store,
    "yearly_fee"        => $yearly,
    "monthly_fee"       => $monthly,
    "total_monthly_fee" => $total_monthly,
    "previous"          => $previous,
    "discount"          => $discount,
    "advance"           => $advance,
    "grand_total"       => $grand_total,

     // NEW
    "paid_total"        => $paid_total,
    "balance"           => $balance,
    "slips"             => $slips
]);
