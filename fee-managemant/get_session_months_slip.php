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

if(!$q || mysqli_num_rows($q) == 0){
    echo json_encode([]);
    exit;
}

$s = mysqli_fetch_assoc($q);

$start = strtotime(date('Y-m-01', strtotime($s['session_start_date'])));
$end   = strtotime(date('Y-m-01', strtotime($s['session_end_date'])));

$months = [];

/* 🔹 ALL MONTHS (PAST → FUTURE) */
while ($start <= $end) {

    $months[] = [
        'value' => date('Y-m', $start),
        'label' => date('F Y', $start)
    ];

    $start = strtotime("+1 month", $start);
}

echo json_encode($months);
