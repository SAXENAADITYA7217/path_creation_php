<?php
include('authentication.php');

$session_id = mysqli_real_escape_string($con, $_POST['session_id']);

$q = mysqli_query($con, "
    SELECT session_start_date, session_end_date
    FROM erp_session
    WHERE session_id='$session_id'
    AND session_status='0'
    LIMIT 1
");

$s = mysqli_fetch_assoc($q);

$start = strtotime(date('Y-m-01', strtotime($s['session_start_date'])));
$end   = strtotime(date('Y-m-01', strtotime($s['session_end_date'])));
$today = strtotime(date('Y-m-01'));

$months = [];

/* 🔹 CHECK IF CURRENT SESSION */
$is_current_session = ($today >= $start && $today <= $end);

/* 🔹 START POINT */
if ($is_current_session) {
    $loop_start = $today;   // current → future
} else {
    $loop_start = $end;     // ONLY last month
}

/* 🔹 LOOP */
while ($loop_start <= $end) {

    $months[] = [
        'value' => date('Y-m', $loop_start),
        'label' => date('F Y', $loop_start)
    ];

    $loop_start = strtotime("+1 month", $loop_start);
}

echo json_encode($months);
