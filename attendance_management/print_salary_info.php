<?php
include('authentication.php');

$masterid = $_SESSION['auth_user']['user_master_id'];
$table_id = $_SESSION['auth_user']['user_master_id'];

$attendanceTable = 'z_attendance_' . $table_id;
$attendanceTable2 = 'y_attendance_' . $table_id;

$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

$timestamp = strtotime($currentMonth . '-01');
$year = date('Y', $timestamp);
$month = date('m', $timestamp);
$displayMonthName = date('F Y', $timestamp);

$totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

/* ------------------ Sundays ------------------ */
$totalSundays = 0;
for ($day = 1; $day <= $totalDays; $day++) {
    if (date('w', strtotime("$year-$month-$day")) == 0) {
        $totalSundays++;
    }
}

/* ------------------ Holidays ------------------ */
$sql = "SELECT holidays_name, date 
        FROM employee_holidays 
        WHERE masterid = '$masterid' 
        AND YEAR(date) = '$year' 
        AND MONTH(date) = '$month'";
$result = mysqli_query($con, $sql);

$holidays = [];
while ($row = mysqli_fetch_assoc($result)) {
    $holidays[] = $row;
}
$totalHolidays = count($holidays);

$sqlSundayHoliday = "SELECT COUNT(*) as totalHolidayOnSunday 
        FROM employee_holidays 
        WHERE masterid = '$masterid' 
        AND YEAR(date) = '$year' 
        AND MONTH(date) = '$month'
        AND DAYOFWEEK(date) = 1";

$resSundayHoliday = mysqli_query($con, $sqlSundayHoliday);
$rowSundayHoliday = mysqli_fetch_assoc($resSundayHoliday);
$totalHolidayOnSunday = $rowSundayHoliday['totalHolidayOnSunday'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Salary Print</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            padding:20px;
        }
        h2{
            text-align:center;
            margin-bottom:20px;
        }
        table{
            width:100%;
            border-collapse:collapse;
            font-size:12px;
        }
        table, th, td{
            border:1px solid #000;
        }
        th, td{
            padding:6px;
            text-align:center;
        }
        @media print {
            .no-print{
                display:none;
            }
        }
    </style>
</head>
<body onload="window.print()">

<h2>Salary Information - <?= $displayMonthName ?></h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Designation</th>
            <th>Basic Salary</th>
            <th>Present Days</th>
            <th>Total Working Hours</th>
            <th>Total Income</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>

<?php
$query = "SELECT * FROM employee_data WHERE masterid = '$masterid'";
$query_run = mysqli_query($con, $query);

$totalSalaryPayAmount = 0;
$netTotalSalaryPayAmount = 0;

if (mysqli_num_rows($query_run) > 0) {

    foreach ($query_run as $item) {

        /* ===============================
           COPY YOUR FULL CALCULATION HERE
           =============================== */

        $employeeId = $item['employee_id'];
        $basicSalaryAmount = $item['employee_salary'];
        $employeeId = $item['employee_id'];
                            $startTime = $item['start_time'];
                            $endTime = $item['end_time'];
                            $workType = $item['worker_type'];
                            $overtime = $item['over_time'];
                            $basicSalaryAmount = $item['employee_salary'];
                            $officialStartTime = new DateTime($startTime);
                            $officialEnd = new DateTime($endTime);

                            ///////////////////////////////////////////////////////////////////////////
                            $intervalTimeDiff = $officialStartTime->diff($officialEnd);

                            // Get total hours and minutes separately
                            $hoursTimeDiff = $intervalTimeDiff->h + ($intervalTimeDiff->days * 24);
                            $minutesTimeDiff = $intervalTimeDiff->i;

                            // If you want to get total minutes difference
                            $totalMinutesTimeDiff = ($hoursTimeDiff * 60) + $minutesTimeDiff;
                            //////////////////////////////////////////////////////////////////////////
                            
                            $officialEndTimeSet = new DateTime($endTime);

                            if($workType == "overtime"){
                                $officialEnd->modify("+{$overtime} minutes");
                            }else{
                                $officialEnd = $officialEnd;
                            }


                            $graceTime = $item['grace_time'];

                            $totalWorkingSeconds = 0;
                            $presentDays = 0;


                            // Loop through all days of the selected month
                            for ($day = 1; $day <= $totalDays; $day++) {
                                $filterDate = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);

                             /*   $attendance_query = "SELECT * FROM $attendanceTable 
                                                     WHERE employee_id = '$employeeId' 
                                                     AND attendance_date = '$filterDate' 
                                                     ORDER BY attendance_time ASC";
                             */
                                $attendance_query = "
                                        SELECT attendance_time 
                                        FROM $attendanceTable 
                                        WHERE employee_id = '$employeeId' 
                                        AND attendance_date = '$filterDate'

                                        UNION ALL

                                        SELECT attendance_time 
                                        FROM $attendanceTable2 
                                        WHERE employee_id = '$employeeId' 
                                        AND attendance_date = '$filterDate'

                                        ORDER BY attendance_time ASC
                                    ";
                        
                                $attendance_query_run = mysqli_query($con, $attendance_query);

                                if (mysqli_num_rows($attendance_query_run) > 0) {
                                    $presentDays++;
                                    $attendance_records = [];

                                    while ($row = mysqli_fetch_assoc($attendance_query_run)) {
                                        $attendance_records[] = $row['attendance_time'];
                                    }

                                    // Adjust start time with grace
                                    $recordedIn = new DateTime($attendance_records[0]);
                                    $officialStart = new DateTime($startTime);
                                    $graceLimit = clone $officialStart;
                                    $extraMinutes = $graceTime + 1;
                                    $graceLimit->modify("+{$extraMinutes} minutes");
                                    //$graceLimit->modify("+{$graceTime} minutes");

                                    if ($recordedIn > $officialStart && $recordedIn < $graceLimit) {
                                        $adjustedStart = $officialStart;
                                    } elseif ($recordedIn < $officialStart) {
                                        $adjustedStart = $officialStart;
                                    } else {
                                        $adjustedStart = $recordedIn;
                                    }

                                    // Calculate daily working hours
                                    $daySeconds = 0;
                                    for ($i = 0; $i < count($attendance_records) - 1; $i += 2) {
                                        $start = new DateTime($attendance_records[$i]);
                                        $end = new DateTime($attendance_records[$i + 1]);

                                        if ($i === 0) {
                                            $start = $adjustedStart;
                                        }

                                   
                                        if($officialEnd > $end){
                                            $graceEndTimeSet = (clone $officialEndTimeSet)->modify('+15 minutes');
                                    
                                            if ($officialEndTimeSet < $end && $graceEndTimeSet > $end) {
                                                $adjustedEnd = clone $officialEndTimeSet;
                                            } else {
                                                $adjustedEnd = $end;
                                            }
                                            //$adjustedEnd = $end;
                                        } else {
                                            $adjustedEnd = $officialEnd;
                                        }

                                        $diff = $start->diff($adjustedEnd);
                                        $daySeconds += ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                                    }

                                    $totalWorkingSeconds += $daySeconds;
                                }
                            }

                            // Convert total working seconds to hours:minutes
                            $hours = floor($totalWorkingSeconds / 3600);
                            $minutes = floor(($totalWorkingSeconds % 3600) / 60);
                            $workingHours = sprintf("%02d:%02d", $hours, $minutes);
                            $workingMinutes = floor($totalWorkingSeconds / 60);   // Total minutes
                            
                           // echo "--------$workingMinutes------------";

                            //////////////////////////////////////////////////////////////////////////////
                            $totalLunchTimePassCalculation = 0;
                            $totalLunchTimePassMinutes = 0;
                            $workingLunchTime = 0;
                            $leftTimeEntryValue = 0;                            

                            // (Optional) Only consider dates that have an approved pass before 13:30
                            $pass_query = "SELECT DISTINCT employee_pass_date 
                                        FROM employee_pass_info 
                                        WHERE masterid = '$masterid'
                                            AND employee_id = '$employeeId'
                                            AND YEAR(employee_pass_date) = '$year'
                                            AND MONTH(employee_pass_date) = '$month'
                                            AND employee_pass_status = 1
                                            AND employee_pass_time <= '13:30:00'";
                            $pass_query_run = mysqli_query($con, $pass_query);

                            if (mysqli_num_rows($pass_query_run) > 0) {
                                while ($pass = mysqli_fetch_assoc($pass_query_run)) {
                                    $passDate = $pass['employee_pass_date'];
                                    $totalLunchTimePassMinutes = 0;
                                    $workingLunchTime = 0;
                                    $leftTimeEntryValue = 0;
                                    // Get all punches on that date (ascending time)
                                    $attendance_query = "SELECT attendance_time 
                                                        FROM $attendanceTable 
                                                        WHERE employee_id = '$employeeId' 
                                                        AND attendance_date = '$passDate'
                                                        ORDER BY attendance_time ASC";
                                    $attendance_run = mysqli_query($con, $attendance_query);

                                    if (mysqli_num_rows($attendance_run) > 0) {
                                        $times = [];
                                        while ($row = mysqli_fetch_assoc($attendance_run)) {
                                            $times[] = $row['attendance_time']; // "HH:MM:SS"
                                        }

                                        // Pair OUT->IN: indices 1,3,5,... are OUT ; next indices 2,4,6,... are IN
                                        for ($i = 1; $i < count($times); $i += 2) {
                                            $outPunch = $times[$i];
                                            $inPunch  = $times[$i + 1] ?? null; // next IN (if exists)

                                            if ($inPunch === null) {
                                                // No matching IN after this OUT; skip
                                                continue;
                                            }

                                            // Apply your window:
                                            // - OUT must be before 13:30:00
                                            // - IN must be after 13:00:00
                                            if ($outPunch < "13:30:00" && $inPunch > "13:00:00") {
                                                $start = new DateTime("$passDate $outPunch");
                                                $end   = new DateTime("$passDate $inPunch");
                                                $diff  = $start->diff($end);
                                                $mins  = ($diff->h * 60) + $diff->i;
                                                $totalLunchTimePassMinutes = $mins;
                                            }

                                            // Additional Working Lunch Time:
                                            // If OUT punch happens after 13:00, calculate minutes from 13:00 to OUT
                                            if ($outPunch > "13:00:00" && $outPunch < "13:30:00") {
                                                $lunchStart = new DateTime("$passDate 13:00:00");
                                                $outTime    = new DateTime("$passDate $outPunch");
                                                $diff       = $lunchStart->diff($outTime);
                                                $workingLunchTime = ($diff->h * 60) + $diff->i;
                                            }

                                            if ($inPunch < "13:30:00") {
                                                // OUT punch is before 13:30 and IN punch is after 13:00
                                                $inTime = strtotime($inPunch);
                                                $outTime = strtotime("13:30:00");
                                                $leftTimeEntryValue = ($outTime - $inTime) / 60; // Difference in minutes
                                            }else{
                                                $leftTimeEntryValue = 0;
                                            }

                                            $notWorkingLunchTime = max(0, 30 - $workingLunchTime); // Ensure not negative

                                            if ($workingLunchTime > 0 && $totalLunchTimePassMinutes >= $notWorkingLunchTime){
                                                // Deduct remaining allowed time (or actual if exceeded)
                                                $totalLunchTimePassCalculation += $notWorkingLunchTime;
                                            } else if ($workingLunchTime > 0 && $totalLunchTimePassMinutes <= $notWorkingLunchTime) {
                                                // Employee took less or equal to allowed lunch time
                                                $totalLunchTimePassCalculation += $totalLunchTimePassMinutes;
                                            } else if ($totalLunchTimePassMinutes > 0 && $leftTimeEntryValue > 0) {
                                                $totalLunchTimePassCalculation += max(0, $totalLunchTimePassMinutes - $leftTimeEntryValue);
                                            } else if ($totalLunchTimePassMinutes > 0){
                                                $totalLunchTimePassCalculation += 30;
                                            } else {
                                                $totalLunchTimePassCalculation += 0;
                                            }
                                        }
                                    }
                                 
                                }
                            }

                            


                            // Show in table cell
                           // echo "<td>{$totalLunchTimePassCalculation} mins Lunch pass</td>";
                            // echo "<td>{$workingLunchTime} mins (Working Lunch After 13:00)</td>";
                            // echo "<td>{$totalLunchTimePassMinutes} mins</td>";
                            // echo "<td>{$leftTimeEntryValue} mins Before</td>";

                            //////////////////////////////////////////////////////////////////////////////////////////////////

                            $extraOvertime = 0;

                            // Find extra over time data
                            $extra_overtime_query = "SELECT * FROM employee_extra_work 
                                           WHERE masterid = '$masterid'
                                            AND employee_id = '$employeeId'
                                            AND YEAR(extra_date) = '$year'
                                            AND MONTH(extra_date) = '$month'
                                            AND extra_work_status = 0";
                            $extra_overtime_query_run = mysqli_query($con, $extra_overtime_query);

                            if (mysqli_num_rows($extra_overtime_query_run) > 0) {
                                while ($extra_overtime = mysqli_fetch_assoc($extra_overtime_query_run)) {
                                    $extraTime = $extra_overtime['extra_time'];
                                    $extraDate = $extra_overtime['extra_date'];

                                    

                                    // Get all punches on that date (ascending time)
//                                    $attendance_query = "SELECT attendance_time 
//                                                        FROM $attendanceTable 
//                                                        WHERE employee_id = '$employeeId' 
//                                                        AND attendance_date = '$extraDate'
//                                                        ORDER BY attendance_time ASC";
                                                        
                                    $attendance_query = "
                                        SELECT attendance_time 
                                        FROM $attendanceTable 
                                        WHERE employee_id = '$employeeId' 
                                        AND attendance_date = '$extraDate'

                                        UNION ALL

                                        SELECT attendance_time 
                                        FROM $attendanceTable2 
                                        WHERE employee_id = '$employeeId' 
                                        AND attendance_date = '$extraDate'

                                        ORDER BY attendance_time ASC
                                    ";
                                    $attendance_run = mysqli_query($con, $attendance_query);

                                    if (mysqli_num_rows($attendance_run) > 0) {
                                        $times = [];

                                        while ($row = mysqli_fetch_assoc($attendance_run)) {
                                            $times[] = $row['attendance_time']; // "HH:MM:SS"
                                        }

                                        $lastOutPunchTime = null;

                                        foreach ($times as $index => $time) {
                                            if (($index + 1) % 2 == 0) {  
                                                // (index+1) because array starts at 0, but you want 2nd, 4th...
                                                $lastOutPunchTime = $time;  // keep updating, last even row remains
                                            }
                                        }  
                                        
                                        $lastPunchTime = new DateTime($lastOutPunchTime);

                                        $outTimeWithExtra = clone $officialEnd;
                                        $outTimeWithExtra->modify("+{$extraTime} minutes");

                                        if ($lastOutPunchTime !== null && $outTimeWithExtra <= $lastPunchTime) {
                                            $extraOvertime += $extraTime;
                                        } else if($lastOutPunchTime !== null && $outTimeWithExtra > $lastPunchTime && $officialEnd < $lastPunchTime){
                                            // Get difference in minutes
                                            $diffMinutes = $lastPunchTime->diff($officialEnd)->i 
                                                        + ($lastPunchTime->diff($officialEnd)->h * 60);
                                            
                                            $extraOvertime += $diffMinutes;
                                        }


                                    }    
                                }
                            }
                           // echo "<td>{$extraOvertime} mins Overtime</td>";

                            ////////////////////////////////////////////////////////////////////////////////////////////////////

                            $approvedLeave = 0;

                            // Find extra over time data
                            $approved_leave_query = "SELECT * FROM employee_leave_use_data 
                                           WHERE masterid = '$masterid'
                                            AND employee_id = '$employeeId'
                                            AND YEAR(leave_date) = '$year'
                                            AND MONTH(leave_date) = '$month'
                                            AND leave_status = 1";
                            $approved_leave_query_run = mysqli_query($con, $approved_leave_query);

                            if ($approved_leave_query_run) {
                                $approvedLeave = mysqli_num_rows($approved_leave_query_run);
                            }    

                          //  echo "<td>{$approvedLeave} Approved leave</td>";

                            //////////////////////////////////////////////////////////////////////////////////////////////////////

                            $firstAttendance = null;
                            $lastAttendance = null;

                         //   $attendance_query = "SELECT attendance_date 
                         //                       FROM $attendanceTable 
                         //                       WHERE employee_id = '$employeeId' 
                         //                       AND YEAR(attendance_date) = '$year'
                         //                       AND MONTH(attendance_date) = '$month'
                         //                       ORDER BY attendance_date ASC";
                                                
                            $attendance_query = "
                                        SELECT attendance_date 
                                        FROM $attendanceTable 
                                        WHERE employee_id = '$employeeId' 
                                        AND YEAR(attendance_date) = '$year'
                                        AND MONTH(attendance_date) = '$month'

                                        UNION ALL

                                        SELECT attendance_date 
                                        FROM $attendanceTable2 
                                        WHERE employee_id = '$employeeId' 
                                        AND YEAR(attendance_date) = '$year'
                                        AND MONTH(attendance_date) = '$month'

                                        ORDER BY attendance_date ASC
                                    ";

                            $attendance_run = mysqli_query($con, $attendance_query);

                            if ($attendance_run && mysqli_num_rows($attendance_run) > 0) {
                                // First record (earliest)
                                $rowFirst = mysqli_fetch_assoc($attendance_run);
                                $firstAttendance = $rowFirst['attendance_date'];

                                // Move pointer to the last record
                                mysqli_data_seek($attendance_run, mysqli_num_rows($attendance_run) - 1);
                                $rowLast = mysqli_fetch_assoc($attendance_run);
                                $lastAttendance = $rowLast['attendance_date'];
                            }

                            //echo "First Attendance: " . $firstAttendance . "<br>";
                            //echo "Last Attendance: " . $lastAttendance . "<br>";


                            $countHoliday = 0;

                            if ($firstAttendance && $lastAttendance) {
                                foreach ($holidays as $holiday) {
                                    // Check if holiday is between first and last attendance
                                    if ($holiday['date'] >= date('Y-m-d', strtotime($firstAttendance)) && 
                                        $holiday['date'] <= date('Y-m-d', strtotime($lastAttendance))) {
                                        $countHoliday++;
                                    }
                                }
                            }



                            ////////////////////////////////////////////////////////////////////////////////////////////////////////

                            $sundaySalary = 0;
                            $totalPresentDay = $presentDays + $countHoliday + $approvedLeave;

                            $sundayWithoutHoilday = $totalSundays - $totalHolidayOnSunday;

                            if($presentDays > 3){
                                
                                if ($month == '02') {   // February condition
        
                                    // Your February logic here
                                    if($totalPresentDay < 6){
                                        
                                        $sundaySalary = max(0, $sundayWithoutHoilday - 4);
                                        
                                    }else if($totalPresentDay < 11) {
                                        
                                        $sundaySalary = max(0, $sundayWithoutHoilday - 3);
                                        
                                    }else if($totalPresentDay < 16) {
                                        
                                        $sundaySalary = max(0, $sundayWithoutHoilday - 2);
                                        
                                    }else if($totalPresentDay < 22) {
                                        
                                        $sundaySalary = max(0, $sundayWithoutHoilday - 1);
                                        
                                    }else if($totalPresentDay >= 18){
                                        
                                       $sundaySalary= $sundayWithoutHoilday;
                                    }
                                    
                            
                                } else {
                                    
                                
                                    if($totalPresentDay < 6){
                                        
                                        $sundaySalary = max(0, $sundayWithoutHoilday - 4);
                                        
                                    }else if($totalPresentDay < 12) {
                                        
                                        $sundaySalary = max(0, $sundayWithoutHoilday - 3);
                                        
                                    }else if($totalPresentDay < 18) {
                                        
                                        $sundaySalary = max(0, $sundayWithoutHoilday - 2);
                                        
                                    }else if($totalPresentDay < 24) {
                                        
                                        $sundaySalary = max(0, $sundayWithoutHoilday - 1);
                                        
                                    }else if($totalPresentDay >= 20){
                                        
                                       $sundaySalary= $sundayWithoutHoilday;
                                    }
                                    
                                }
                            }
                            
                          //  echo "<td>{$sundaySalary} Sunday salary</td>";
                          //  echo "<td>{$countHoliday} Holiday</td>";


                            ///////////////////////////////////////////////////////////////////////////

                            $status = ($presentDays > 0) ? "Present ($totalPresentDay days)" : "Absent (All)";
                            $tatolWorkingDay = $totalDays - ($totalSundays + $countHoliday - $totalHolidayOnSunday);

                            //////////////////////////////////////////////////////////////////////////

                          //  echo "<td>" . sprintf("%02d:%02d", $hoursTimeDiff, $minutesTimeDiff) . " Work Time</td>";

                            ////////////////////////////////////////////////////////////////////////////////////////////

                            $extra_holiday_query = "SELECT holiday_count, holiday_status 
                                FROM employee_extra_holiday 
                                WHERE masterid = '$masterid' 
                                AND employee_id = '$employeeId' 
                                AND YEAR(holiday_set_date) = '$year'
                                AND MONTH(holiday_set_date) = '$month'
                                LIMIT 1";

                            $extra_holiday_query_run = mysqli_query($con, $extra_holiday_query);

                            $holiday_count = 0;
                            $holiday_status = 0;
                            $holiday_extra_salary = 0;
                            if(mysqli_num_rows($extra_holiday_query_run) > 0){
                                $row = mysqli_fetch_assoc($extra_holiday_query_run);
                                $holiday_count = $row['holiday_count'];
                                $holiday_status = $row['holiday_status'];
                            }

                            if($holiday_status == 1){
                               $holiday_extra_salary = $holiday_count * $totalMinutesTimeDiff;
                            }else if($holiday_status == 2){
                               $holiday_extra_salary = -($holiday_count * $totalMinutesTimeDiff);
                            }
                            //////////////////////////////////////////////////////////////////////////////////////////////////////////
                                                                                                                
                            $money_info_query = "SELECT money_amount, money_type 
                                 FROM employee_money_settlement 
                                 WHERE masterid = '$masterid' 
                                 AND employee_id = '$employeeId' 
                                 AND money_transaction_type = '0'
                                 AND YEAR(money_date) = '$year'
                                 AND MONTH(money_date) = '$month'";
                                        
                            $money_info_query_run = mysqli_query($con, $money_info_query);
                            
                            $total_due = 0;
                            $total_advance = 0;
                            $total_penalty = 0;
                            
                            if(mysqli_num_rows($money_info_query_run) > 0)
                            {
                                while($money = mysqli_fetch_assoc($money_info_query_run))
                                {
                                    if($money['money_type'] == 0)
                                    {
                                        $total_due += $money['money_amount']; // Add Dues
                                    }
                                    elseif($money['money_type'] == 1)
                                    {
                                        $total_advance += $money['money_amount']; // Advance
                                    }
                                    elseif($money['money_type'] == 2)
                                    {
                                        $total_penalty += $money['money_amount']; // Penalty
                                    }
                                }
                            }
                            
                            $final_total = $total_due - ($total_advance + $total_penalty);
                            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



                            $ToatlHolidayWorkingMinutes = ($sundaySalary * $totalMinutesTimeDiff) + ($countHoliday * $totalMinutesTimeDiff) + ($approvedLeave * $totalMinutesTimeDiff);
                            $GrandTotalWorkinghoursMintes = $workingMinutes + $ToatlHolidayWorkingMinutes + $extraOvertime + $totalLunchTimePassCalculation + $holiday_extra_salary;

                         //   echo "<td> $workingMinutes/$ToatlHolidayWorkingMinutes/$extraOvertime/$totalLunchTimePassCalculation/$GrandTotalWorkinghoursMintes</td>";
                            ///////////////////////////////////////////////////////////////////////////////////

                            $oneMinuteSalaryAmount = $basicSalaryAmount/($totalDays * $totalMinutesTimeDiff);
                         //   $oneMinuteSalaryAmount = number_format($oneMinuteSalaryAmount, 2, '.', '');
                            $oneMinuteSalaryAmount = number_format($oneMinuteSalaryAmount, 4, '.', '');
                            $totalEarnSalaryAmount = 0;
                            if($presentDays > 0){
                                $totalEarnSalaryAmount = $GrandTotalWorkinghoursMintes * $oneMinuteSalaryAmount + ($final_total);
                                // Format to 2 decimal places
                                $totalEarnSalaryAmount = number_format($totalEarnSalaryAmount, 0, '.', '');
                                
                                $totalSalaryPayAmount += $totalEarnSalaryAmount;
                                $netTotalSalaryPayAmount += $basicSalaryAmount;
                            }else{
                                $totalEarnSalaryAmount = 0;
                            }

   if($presentDays > 0){                     

?>

<tr>
    <td><?= $item['employee_id'] ?></td>
    <td><?= $item['employee_name'] ?></td>
    <td><?= $item['designation'] ?></td>
    <td><?= $basicSalaryAmount ?></td>
    <td><?= $presentDays ?>/<?= $tatolWorkingDay ?></td>
    <td><?= $workingHours ?></td>
    <td>
        <?= number_format($totalEarnSalaryAmount, 2, '.', ',') ?>
    </td>
    <td><?= $status ?></td>
</tr>

<?php
}

    }
} else {
?>
<tr>
    <td colspan="7">No Records Found</td>
</tr>
<?php } ?>

  
<tr>
    <td colspan="6" style="text-align:right; font-weight:bold; font-size:16px;">
        Net Total Salary
    </td>
    <td style="font-weight:bold; font-size:16px;">
        <?= number_format($netTotalSalaryPayAmount, 2, '.', '') ?>
    </td>
</tr>

<tr>
    <td colspan="6" style="text-align:right; font-weight:bold; font-size:16px;">
        Grand Total Salary
    </td>
    <td style="font-weight:bold; font-size:16px;">
        <?= number_format($totalSalaryPayAmount, 2, '.', '') ?>
    </td>
    <td></td>
    
</tr>


    </tbody>
</table>


</body>
</html>

<?php
include('includes/footer.php');
?>
