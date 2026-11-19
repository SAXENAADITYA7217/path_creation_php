<?php
include('authentication.php');
include('includes/header.php');

$masterid = $_SESSION['auth_user']['user_master_id'];
$table_id = $_SESSION['auth_user']['user_master_id']; // -> ct0002
$attendanceTable = 'z_attendance_' . $table_id;
$attendanceTable2 = 'y_attendance_' . $table_id;


$emp_id = $_GET['id'] ?? null;
// Get selected month from URL or use current
$currentMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Generate previous and next months
$timestamp = strtotime($currentMonth . '-01');
$prevMonth = date('Y-m', strtotime('-1 month', $timestamp));
$nextMonth = date('Y-m', strtotime('+1 month', $timestamp));
$displayMonthName = date('F', $timestamp); // Example: July

// Get year and month for attendance filtering
$year = date('Y', $timestamp);
$month = date('m', $timestamp);
$totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Count Sundays in that month
/*
$totalSundays = 0;
for ($day = 1; $day <= $totalDays; $day++) {
    if (date('w', strtotime("$year-$month-$day")) == 0) { // 0 = Sunday
        $totalSundays++;
    }
}
*/

$totalSundays = 0;
$sundayList = [];

for ($day = 1; $day <= $totalDays; $day++) {
    $date = "$year-$month-$day";
    if (date('w', strtotime($date)) == 0) { // 0 = Sunday
        $totalSundays++;
        $sundayList[] = $date; // Store the Sunday date
    }
}

/* Output  
echo "Total Sundays: " . $totalSundays . "<br>";
echo "List of Sundays:<br>";
foreach ($sundayList as $sunday) {
    echo $sunday . "<br>";
} */

///////////////////////////////////////////////////////////

// Fetch total holidays from employee_holidays table
/*
$sql = "SELECT COUNT(*) AS totalHolidays 
        FROM employee_holidays 
        WHERE YEAR(date) = ? AND MONTH(date) = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $year, $month);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalHolidays = $row['totalHolidays'];
*/

$sql = "SELECT holidays_name, date 
        FROM employee_holidays 
        WHERE masterid = '$masterid' AND YEAR(date) = ? AND MONTH(date) = ? 
        ORDER BY date ASC";

$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $year, $month);
$stmt->execute();
$result = $stmt->get_result();

$holidays = [];
while ($row = $result->fetch_assoc()) {
    $holidays[] = $row;
}

// Count total holidays
$totalHolidays = count($holidays);

// Display total
echo "Total Holidays: " . $totalHolidays . " | ";

// Display holiday list
if ($totalHolidays > 0) {
    foreach ($holidays as $holiday) {
        echo $holiday['holidays_name'] . " - " . $holiday['date'] . " | ";
    }
    echo "<br>";
} else {
    echo "No holidays found for this month.";
    echo "<br>";
}



// Fetch total holidays that fall on Sunday (MySQL: Sunday = 1 in DAYOFWEEK)
$sql = "SELECT COUNT(*) AS totalHolidayOnSunday 
        FROM employee_holidays 
        WHERE masterid = '$masterid' AND YEAR(date) = ? AND MONTH(date) = ? AND DAYOFWEEK(date) = 1";
$stmt = $con->prepare($sql);
$stmt->bind_param("ii", $year, $month);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalHolidayOnSunday = $row['totalHolidayOnSunday'];

// Display Results
echo "Month: " . date('F Y', $timestamp)." | Total Days: $totalDays | Total Sundays: $totalSundays | Total Official Holidays: $totalHolidays | Total Holidays on Sunday: $totalHolidayOnSunday | Total Non-working Days (Sundays + Holidays): ". ($totalSundays + $totalHolidays - $totalHolidayOnSunday);



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

 $query = "SELECT * FROM employee_data WHERE masterid = '$masterid' AND employee_id = '$emp_id' ORDER BY id ASC";
                    $query_run = mysqli_query($con, $query);

                    if (mysqli_num_rows($query_run) > 0) {
                        foreach ($query_run as $item) {

                            $employeeId = $item['employee_id'];
                            $employeeName = $item['employee_name'];
                            $employeeDesignation = $item['designation'];
                            $employeeMobile = $item['phone_no'];
                            $startTime = $item['start_time'];
                            $endTime = $item['end_time'];
                            $workType = $item['worker_type'];
                            $overtime = $item['over_time'];
                            $basicSalaryAmount = $item['employee_salary'];
                            $officialStartTime = new DateTime($startTime);
                            $officialEnd = new DateTime($endTime);

                            $employee_dues_amount = $item['dues_amount'];
                            $employee_advance_amount = $item['advance_amount'];
                            $employee_penalty_amount = $item['panalty_amount'];

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
                                            // $adjustedEnd = $end;
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
                        //    echo "<td>{$totalLunchTimePassCalculation} mins Lunch pass</td>";
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
                              //      $attendance_query = "SELECT attendance_time 
                              //                          FROM $attendanceTable 
                             //                           WHERE employee_id = '$employeeId' 
                             //                           AND attendance_date = '$extraDate'
                               //                         ORDER BY attendance_time ASC";
                                                        
                                                        
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
                            echo "<td> | {$extraOvertime} mins Overtime</td>";

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

                       //     echo "<td>{$approvedLeave} Approved leave</td>";

                            //////////////////////////////////////////////////////////////////////////////////////////////////////

                            $firstAttendance = null;
                            $lastAttendance = null;

                          //  $attendance_query = "SELECT attendance_date 
                        //                        FROM $attendanceTable 
                         //                       WHERE employee_id = '$employeeId' 
                          //                      AND YEAR(attendance_date) = '$year'
                        //                        AND MONTH(attendance_date) = '$month'
                          //                      ORDER BY attendance_date ASC";
                                                
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
                            
                            echo "<td> | {$sundaySalary} Sunday salary | {$totalPresentDay} Total Present Day | </td>";
                     //       echo "<td>{$countHoliday} Holiday</td>";


                            ///////////////////////////////////////////////////////////////////////////

                            $status = ($presentDays > 0) ? "Present ($totalPresentDay days)" : "Absent (All)";
                            $tatolWorkingDay = $totalDays - ($totalSundays + $countHoliday - $totalHolidayOnSunday);

                            //////////////////////////////////////////////////////////////////////////

                       //     echo "<td>" . sprintf("%02d:%02d", $hoursTimeDiff, $minutesTimeDiff) . " Work Time</td>";

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

                          //  echo "<td> $workingMinutes/$ToatlHolidayWorkingMinutes/$extraOvertime/$totalLunchTimePassCalculation/$GrandTotalWorkinghoursMintes</td>";
                            ///////////////////////////////////////////////////////////////////////////////////

                            $oneMinuteSalaryAmount = $basicSalaryAmount/($totalDays * $totalMinutesTimeDiff);
                           // $oneMinuteSalaryAmount = number_format($oneMinuteSalaryAmount, 2, '.', '');
                           $oneMinuteSalaryAmount = number_format($oneMinuteSalaryAmount, 4, '.', '');

                            $totalEarnSalaryAmount = 0;
                            if($presentDays > 0){
                                $totalEarnSalaryAmount = $GrandTotalWorkinghoursMintes * $oneMinuteSalaryAmount + ($final_total);
                                // Format to 2 decimal places
                                $totalEarnSalaryAmount = number_format($totalEarnSalaryAmount, 2, '.', '');
                            }else{
                                $totalEarnSalaryAmount = 0;
                            }

                            echo "<td> One Minutes Salary $oneMinuteSalaryAmount</td>";

                            ////////////////////////////////////////////////////////////////////////////////////////
                       /*     echo "Employee name:- $employeeName               Month Days        :-          $totalDays <br>";
                            echo "Month        :- $month-$year                Your Working Days :-    $totalPresentDay <br>";
                            echo "Department   :-                             Working Hrs/Days  :-      $hoursTimeDiff <br>";
                            echo "Designation  :- $employeeDesignation        Weekly Off        :-       $sundaySalary <br>";
                            echo "Emp. Mobile  :- $employeeMobile             Holiday off       :-       $countHoliday <br>";
                            echo "                                            EL/CL/SL          :-      $approvedLeave <br>";
                            echo "Office Time  :- $startTime - $endTime       Total CTC/M       :-  $basicSalaryAmount <br>";
                            echo "Grace Time   :- $graceTime                  OverTime          :-                 yes <br>";
                            */
                       ////////////////////////////////////////////////////////////////////////////////////

       }
    } else {

    }

?>

<div class="card radius-10">
    <div class="card-body">
        <?php include('message.php'); ?>
        <div class="d-flex align-items-center mb-3">
            <div>
                <h5 class="mb-0">Salary Slip (<?= $displayMonthName ?>)</h5>
            </div>
            <div class="ms-auto">
                <div class="ms-auto d-flex align-items-center">
                    <form method="GET">
                        <input type="hidden" name="id" value="<?= $emp_id ?>">
                        <input type="hidden" name="month" value="<?= $prevMonth ?>">
                        <button type="submit" class="btn btn-light btn-sm">&lt;</button>
                    </form>
                    <h5 class="mb-0 mx-2"><?= $displayMonthName ?></h5>
                    <form method="GET">
                        <input type="hidden" name="id" value="<?= $emp_id ?>">
                        <input type="hidden" name="month" value="<?= $nextMonth ?>">
                        <button type="submit" class="btn btn-light btn-sm">&gt;</button>
                    </form>
                </div>
            </div>
        </div>
        <hr>

        <div class="text-center row">
                <div class="col-md-2">
                    <img src="../img/ct-logo.png" alt="" style="width: 100px;">
                </div>
                <div class="col-md-8">
                    <h3 class="mb-1">Competent Technologies</h3>
                    <h6 class="mb-0">Plot No.A33/22, Vishwakarma Compound Site-04 Industrial Area Sahibabad</h6>
                    <h6 class="mb-0">Ghazibad - 201010</h6>
                </div>
                <div class="col-md-2">
                </div>
        </div>
        <div class="text-center">
            <h5 class="mb-1">Employee Monthly Time Sheet</h5>    
        </div>
        <div class="row">
            <div class="col-md-3"><strong>Employee Name</strong></div>
            <div class="col-md-3"><?= $employeeName ?></div>

            <div class="col-md-3"><strong>Month Days</strong></div>
            <div class="col-md-3"><?= $totalDays ?></div>

            <div class="col-md-3"><strong>Month</strong></div>
            <div class="col-md-3"><?= $month ?> - <?= $year ?></div>

            <div class="col-md-3"><strong>Working Days</strong></div>
            <div class="col-md-3"><?= $totalPresentDay ?></div>

            <div class="col-md-3"><strong>Department</strong></div>
            <div class="col-md-3">offline</div>

            <div class="col-md-3"><strong>Working Hrs/Days</strong></div>
            <div class="col-md-3"><?= $hoursTimeDiff ?>:<?= $minutesTimeDiff ?></div>

            <div class="col-md-3"><strong>Designation</strong></div>
            <div class="col-md-3"><?= $employeeDesignation ?></div>

            <div class="col-md-3"><strong>Weekly Off</strong></div>
            <div class="col-md-3"><?= $sundaySalary ?></div>

            <div class="col-md-3"><strong>Emp. Mobile</strong></div>
            <div class="col-md-3"><?= $employeeMobile ?></div>

            <div class="col-md-3"><strong>Holiday Off</strong></div>
            <div class="col-md-3"><?= $countHoliday ?></div>

            <div class="col-md-3"><strong>Office Time</strong></div>
            <div class="col-md-3"><?= $startTime ?> - <?= $endTime ?></div>

            <div class="col-md-3"><strong>EL/CL/SL</strong></div>
            <div class="col-md-3"><?= $approvedLeave ?></div>

            <div class="col-md-3"><strong>Grace Time</strong></div>
            <div class="col-md-3"><?= $graceTime ?></div>

            <div class="col-md-3"><strong>Total CTC/M</strong></div>
            <div class="col-md-3"><?= $basicSalaryAmount ?></div>

            <div class="col-md-3"><strong>OverTime</strong></div>
            <div class="col-md-3"><?php if($overtime === "overtime"){ ?>Yes <?php }else{ ?> No <?php } ?></div>

            <div class="col-md-3">
                <strong>Holiday Management</strong>
            </div>

            <div class="col-md-3">    
                <?php
                echo $holiday_count . " " . ($holiday_status == 1 ? "Add " : 
                    ($holiday_status == 2 ? "Subtract " : ""));
                ?>
                <!-- Edit Icon -->
                <i class="bx bx-edit" 
                style="cursor:pointer; margin-left:10px; color:#0d6efd;"
                onclick="openPasswordModal(
                        '',
                        '',
                        '',
                        '<?= $employeeId ?>',
                        '',
                        'holiday',
                        ''
                    )">
                </i>
            </div>

        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Gate Entry Time</th>
                        <th>In Time</th>
                        <th>Out Gate Time</th>
                        <th>Out Time</th>
                        <th>Total(Hrs)</th>
                        <th>Working(Hrs)</th>
                        <th>Overtime(Hrs)</th>
                        <th>P/W/H/A</th>
                        <th>No. of Punch</th>
                    </tr>
                </thead>
                <tbody>
                   <?php
                        $totalovertimeMinutesCheck = 0;

                        for ($day = 1; $day <= $totalDays; $day++) {

                            $filterDate = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                            $filterDay = date("l", strtotime($filterDate));

                            $attendance_query = "
                                SELECT id, attendance_time, 'table1' as source
                                FROM $attendanceTable
                                WHERE employee_id = '$employeeId'
                                AND attendance_date = '$filterDate'

                                UNION ALL

                                SELECT id, attendance_time, 'table2' as source
                                FROM $attendanceTable2
                                WHERE employee_id = '$employeeId'
                                AND attendance_date = '$filterDate'

                                ORDER BY attendance_time ASC
                            ";

                            $attendance_query_run = mysqli_query($con, $attendance_query);

                            $inTime = $outTime = $status = $workingHours = '---';
                            $inSource = $outSource = '';
                            $totalMinutesCheck = 0;

                            $attendance_records = [];
                            if (mysqli_num_rows($attendance_query_run) > 0) {

                                $status = 'Present';
                                

                                while ($row = mysqli_fetch_assoc($attendance_query_run)) {
                                    $attendance_records[] = [
                                        'id' => $row['id'],            // this is important
                                        'time' => $row['attendance_time'],
                                        'source' => $row['source']
                                    ];
                                }

                                $totalRecords = count($attendance_records);

                                // =============================
                                // IN TIME
                                // =============================
                                //  $inTime = $attendance_records[0]['time'];
                                //  $inSource = $attendance_records[0]['source'];
    
                                // =============================
                                // IN & OUT TIME Assignment
                                // =============================
                                $totalRecords = count($attendance_records);
        
                                // Default values
                                $inTime = $outTime = '---';
                                $inSource = $outSource = '';
        
                                // Single record special case
                                if ($totalRecords == 1) {
                                    $recordTime = $attendance_records[0]['time'];
                                    $recordSource = $attendance_records[0]['source'];
        
                                    // If after 14:00 (2 PM), show in OUT TIME
                                    if (strtotime($recordTime) >= strtotime("14:00:00")) {
                                        $outTime = $recordTime;
                                        $outSource = $recordSource;
                                        $outId = $attendance_records[0]['id']; // Capture the ID
                                    } else {
                                        // Otherwise normal IN TIME
                                        $inTime = $recordTime;
                                        $inSource = $recordSource;
                                        $inId = $attendance_records[0]['id']; // Capture the ID
                                    }
                                } elseif ($totalRecords > 1) {
                                    // Normal behavior
                                    $inTime = $attendance_records[0]['time'];
                                    $inSource = $attendance_records[0]['source'];
                                    $inId = $attendance_records[0]['id']; // Capture the ID
        
                                    if ($totalRecords % 2 == 0) {
                                        $lastIndex = $totalRecords - 1;
                                        $outTime = $attendance_records[$lastIndex]['time'];
                                        $outSource = $attendance_records[$lastIndex]['source'];
                                        $outId = $attendance_records[$lastIndex]['id']; // Capture the ID
                                    }else if($totalRecords > 2){
                                        if(($totalRecords - 1) % 2 == 0){
                                            $lastIndex = $totalRecords - 2;
                                            $outTime = $attendance_records[$lastIndex]['time'];
                                            $outSource = $attendance_records[$lastIndex]['source'];
                                            $outId = $attendance_records[$lastIndex]['id']; // Capture the ID
                                        }
                                    }
                                }


                                // =============================
                                // OUT TIME (Only if Even count)
                                // =============================
                                if ($totalRecords % 2 == 0) {
                                    $lastIndex = $totalRecords - 1;
                                    $outTime = $attendance_records[$lastIndex]['time'];
                                    $outSource = $attendance_records[$lastIndex]['source'];
                                    $outId = $attendance_records[$lastIndex]['id']; // Capture the ID
                                }

                                // =============================
                                // WORKING HOURS CALCULATION
                                // =============================
                                $recordedIn = new DateTime($attendance_records[0]['time']);
                                $officialStart = new DateTime($startTime);

                                $graceLimit = clone $officialStart;
                                $extraMinutes = $graceTime + 1;
                                $graceLimit->modify("+{$extraMinutes} minutes");
                                
                                if ($recordedIn > $officialStart && $recordedIn < $graceLimit) {
                                    $adjustedStart = $officialStart;
                                } elseif ($recordedIn < $officialStart) {
                                    $adjustedStart = $officialStart;
                                } else {
                                    $adjustedStart = $recordedIn;
                                }

                                $totalSeconds = 0;

                                for ($i = 0; $i < $totalRecords - 1; $i += 2) {

                                    $start = new DateTime($attendance_records[$i]['time']);
                                    $end = new DateTime($attendance_records[$i + 1]['time']);

                                    if ($i == 0) {
                                        $start = $adjustedStart;
                                    }

                                    
                                    if($officialEnd > $end){
                                        $graceEndTimeSet = (clone $officialEndTimeSet)->modify('+15 minutes');
                                    
                                        if ($officialEndTimeSet < $end && $graceEndTimeSet > $end) {
                                            $adjustedEnd = clone $officialEndTimeSet;
                                        } else {
                                            $adjustedEnd = $end;
                                        }
                                    } else {
                                        $adjustedEnd = $officialEnd;
                                    }

                                    
                                    $diff = $start->diff($adjustedEnd);
                                    $totalSeconds += ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                                    
                                }

                                $hours = floor($totalSeconds / 3600);
                                $minutes = floor(($totalSeconds % 3600) / 60);
                                $workingHours = sprintf("%02d:%02d", $hours, $minutes);

                                $totalMinutesCheck = floor($totalSeconds / 60);
                            } else {
                                $status = 'Absent';
                            }

                            // =============================
                            // OVERTIME
                            // =============================

                            // Calculate difference in minutes
                            $overtimeMinutes = $totalMinutesCheck - $totalMinutesTimeDiff;

                                
                            // If negative → set to 0
                            if ($overtimeMinutes < 0) {
                                $overtimeMinutes = 0;
                            }

                            $totalovertimeMinutesCheck += $overtimeMinutes;

                            // Convert minutes → HH:MM
                            $overtimeHours = sprintf("%02d:%02d", floor($overtimeMinutes / 60), $overtimeMinutes % 60);

                                
                            // Now punch count will be correct
                            $punchCount = count($attendance_records);
                        ?>
                        <tr>

                            <td><?= $filterDate ?></td>
                            <td><?= $filterDay ?></td>

                        <!-- IN TIME -->
                        <td style="
                            <?= ($inSource == 'table1') ? 'color:green;font-weight:bold;' : '' ?>
                            <?= ($inSource == 'table2') ? 'color:blue;font-weight:bold;' : '' ?>
                        ">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span><?= $inTime ?></span>

                                <i class="bx bx-edit text-dark"
                                    style="cursor:pointer;"
                                    onclick="openPasswordModal(
                                        '<?= $filterDate ?>',
                                        '<?= ($inTime != '---') ? $inTime : '' ?>',
                                        '<?= $inSource ?>',
                                        '<?= $emp_id ?>',
                                        '<?= ($inTime != '---') ? 'update' : 'insert' ?>',
                                        'attendance',
                                        '<?= ($inTime != '---') ? $inId : '' ?>'  // <--- pass attendance_id
                                    )">
                                </i>
                            </div>
                        </td>

                        <td><?= $startTime ?></td>

                        <!-- OUT TIME -->
                        <td style="
                            <?= ($outSource == 'table1') ? 'color:green;font-weight:bold;' : '' ?>
                            <?= ($outSource == 'table2') ? 'color:blue;font-weight:bold;' : '' ?>
                        ">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span><?= $outTime ?></span>

                                <i class="bx bx-edit text-dark"
                                    style="cursor:pointer;"
                                    onclick="openPasswordModal(
                                        '<?= $filterDate ?>',
                                        '<?= ($outTime != '---') ? $outTime : '' ?>',
                                        '<?= $outSource ?>',
                                        '<?= $emp_id ?>',
                                        '<?= ($outTime != '---') ? 'update' : 'insert' ?>',
                                        'attendance',
                                        '<?= ($outTime != '---') ? $outId : '' ?>'  // <--- pass attendance_id
                                    )">
                                </i>
                            </div>
                        </td>



                            <td><?= $endTime ?></td>
                            <td><?= $workingHours ?></td>
                            <td>
                                                    <?php 
                                                    if ($overtimeHours == "00:00") {
                                                        echo $workingHours;
                                                    } else {
                                                        echo sprintf("%02d:%02d", $hoursTimeDiff, $minutesTimeDiff);
                                                    }
                                                    ?>
                                                </td>
                            <td><?= $overtimeHours ?></td>
                            <td><?= $status ?></td>
                            <!-- No. of Punch -->
                            <td>
                                <?= $punchCount ?>
                                
                                <?php if($punchCount > 0): ?>
                                    <button 
                                        type="button"
                                        class="btn btn-sm btn-primary py-0 px-2"
                                        onclick='openPunchModal(<?= json_encode($attendance_records) ?>)'>
                                        View
                                    </button>
                                <?php endif; ?>
                            </td>

                        </tr>
                        <?php
                        }
                        ?>



                      <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="3">Add Lanch Time with Pass in This Month</td>
                        <td></td>
                        <td><?= sprintf("%02d:%02d", floor($totalLunchTimePassCalculation / 60), $totalLunchTimePassCalculation % 60); ?></td>
                        <td></td>
                        <td>D = <?= $total_due ?> / <?= $employee_dues_amount ?></td>
                     </tr>
                     <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="3">Extra Over Time in This Month</td>
                        <td></td>
                        <td><?= sprintf("%02d:%02d", floor($extraOvertime / 60), $extraOvertime % 60); ?></td>
                        <td></td>
                        <td>A = <?= $total_advance ?> / <?= $employee_advance_amount ?></td>
                    </tr>
                  
                     <tr> 
                        <td></td>
                        <td>Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><?= sprintf("%02d:%02d", floor(($workingMinutes - $totalovertimeMinutesCheck) / 60), ($workingMinutes - $totalovertimeMinutesCheck) % 60);?></td>
                        <td><?= sprintf("%02d:%02d", floor(($totalovertimeMinutesCheck + $extraOvertime + $totalLunchTimePassCalculation) / 60), ($totalovertimeMinutesCheck + $extraOvertime + $totalLunchTimePassCalculation) % 60); ?></td>
                        <td><?= $totalPresentDay + $sundaySalary ?></td>
                        <td>P = <?= $total_penalty ?> / <?= $employee_penalty_amount ?></td>
                     </tr>

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="3">Total Pay</td>
                        <td><?= ($workingMinutes - $totalovertimeMinutesCheck) * $oneMinuteSalaryAmount ?></td>
                        <td><?= ($totalovertimeMinutesCheck + $extraOvertime + $totalLunchTimePassCalculation) * $oneMinuteSalaryAmount ?></td>
                        <td><?= $ToatlHolidayWorkingMinutes * $oneMinuteSalaryAmount ?></td>
                        <td>F = <?= $final_total ?> / <?= $employee_dues_amount - ($employee_advance_amount + $employee_penalty_amount) ?></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td colspan="3" class="fw-bold">Net Pay Amount</td>
                        <td colspan="3" class="text-center fw-bold">₹<?= $totalEarnSalaryAmount ?></td>
                        <td></td>
                    </tr>

                </tbody>
            </table>
        </div>
        <br>
        <div class="d-flex align-items-center mb-3 mx-5">
            <div>
                <p class="mb-0">-------------------------------</p>
                <p class="mb-0">Employee Signature</p>
            </div>
            <div class="ms-auto">
                <p class="mb-0">-------------------------------</p>
                <p class="mb-0">Manager Signature</p>
            </div>
        </div>

    </div>
</div>



<div id="punchModal"
    style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); backdrop-filter:blur(4px); z-index:9999; justify-content:center; align-items:center;">

    <div style="background:#ffffff; width:420px;
              border-radius:12px;
              box-shadow:0 15px 35px rgba(0,0,0,0.25);
              overflow:hidden;
              font-family:Arial, sans-serif;
              animation:fadeIn 0.2s ease-in-out;">

        <!-- Header -->
        <div style="background:#000000;
                    color:#ffffff;
                    padding:14px 16px;
                    display:flex;
                    justify-content:space-between;
                    align-items:center;">

            <h5 style="margin:0; font-size:16px; color:#ffffff;">
                📋 Attendance Punch List
            </h5>

            <span onclick="closePunchModal()"
                style="cursor:pointer;
                        font-size:18px;
                        color:#ffffff;
                        transition:0.2s;"
                onmouseover="this.style.transform='scale(1.2)'"
                onmouseout="this.style.transform='scale(1)'">
                ✖
            </span>
        </div>

        <!-- Body -->
        <div id="punchList" style="padding:15px; max-height:300px; overflow-y:auto;">
        </div>

    </div>
</div>

<div id="holidayModal" 
     class="modal" 
     style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; 
            background-color: rgba(0,0,0,0.5);">
  <div class="modal-content" 
       style="background:#fff; padding:20px; width:350px; border-radius:8px; position:relative; 
              box-shadow:0 5px 15px rgba(0,0,0,0.3);">

    <span onclick="closeHolidayModal()" 
          style="position:absolute; top:8px; right:12px; font-size:18px; cursor:pointer;">✖</span>

    <h5>Set Extra Holiday</h5>

    <form method="POST" action="update_holiday.php">

        <input type="hidden" name="employee_id" id="holidayEmployee">

        <label>Select Holiday Date</label>
        <input type="date" name="holiday_date" class="form-control" required>

        <br>

        <label>Holiday Count</label>
        <input type="number" name="holiday_count" class="form-control" min="0" required>

        <br>

        <label>Action</label>
        <select name="holiday_status" id="holidayStatus" class="form-control" required>
            <option value="1">Add Holiday</option>
            <option value="2">Subtract Holiday</option>
        </select>

        <br>

        <button type="submit" 
                style="width:100%; padding:10px; background:#198754; color:#fff; border:none;">
            Save
        </button>
    </form>

  </div>
</div>


<div id="passwordModal" 
     class="modal" 
     style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; 
            background-color: rgba(0,0,0,0.5);">
  <div class="modal-content" 
       style="background:#fff; padding:20px; width:350px; border-radius:8px; position:relative; 
              box-shadow:0 5px 15px rgba(0,0,0,0.3);">

    <span class="close-btn" 
          onclick="closePasswordModal()" 
          style="position:absolute; top:8px; right:12px; font-size:18px; cursor:pointer; color:#999;"
          onmouseover="this.style.color='red'" onmouseout="this.style.color='#999'">✖</span>

    <h5>Enter Edit Password</h5>

    <input type="password" id="editPassword" class="form-control" style="width:100%; padding:8px; margin-top:10px;">

    <input type="hidden" id="editDate">
    <input type="hidden" id="editTime">
    <input type="hidden" id="editSource">
    <input type="hidden" id="editEmployee">
    <input type="hidden" id="editAction"> <!-- update / insert -->
    <input type="hidden" id="editType"> 

    <br>
    <button onclick="checkPassword()" class="btn btn-primary w-100" style="width:100%; padding:10px; margin-top:15px; background:#0d6efd; color:#fff; border:none; border-radius:4px; cursor:pointer;">
      Submit
    </button>
  </div>
</div>

<div id="editModal" 
     class="modal" 
     style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; 
            background-color: rgba(0,0,0,0.5);">
  <div class="modal-content" 
       style="background:#fff; padding:20px; width:350px; border-radius:8px; position:relative; 
              box-shadow:0 5px 15px rgba(0,0,0,0.3);">

    <span class="close-btn" 
          onclick="closeEditModal()" 
          style="position:absolute; top:8px; right:12px; font-size:18px; cursor:pointer; color:#999;"
          onmouseover="this.style.color='red'" onmouseout="this.style.color='#999'">✖</span>

    <h5>Edit Attendance</h5>

    <form method="POST" action="update_attendance.php">

      <input type="hidden" name="date" id="finalDate">
      <input type="hidden" name="source" id="finalSource">
      <input type="hidden" name="employee_id" id="finalEmployee">
      <input type="hidden" name="action" id="finalAction"> <!-- insert / update -->
      <input type="hidden" name="attendance_id" id="finalId">


      <label>Time</label>
      <input type="time" name="time" id="finalTime" class="form-control" style="width:100%; padding:8px; margin-top:5px;">

      <br>
      <button type="submit" class="btn btn-success w-100" style="width:100%; padding:10px; margin-top:15px; background:#198754; color:#fff; border:none; border-radius:4px; cursor:pointer;">
        Update
      </button>
    </form>
  </div>
</div>

<script>
document.getElementById('editPassword').addEventListener("keypress", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
        checkPassword();
    }
});

// Helper function to show modal with flex for centering
function showModal(id){
    const modal = document.getElementById(id);
    modal.style.display = 'flex';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';
}

function closePasswordModal(){
    document.getElementById('passwordModal').style.display = 'none';
}

function closeEditModal(){
    document.getElementById('editModal').style.display = 'none';
}

function openPasswordModal(date, time, source, empid, action, type, recordId){
    document.getElementById('editDate').value = date;
    document.getElementById('editTime').value = time;
    document.getElementById('editSource').value = source;
    document.getElementById('editEmployee').value = empid;
    document.getElementById('editAction').value = action; // hidden input to track insert/update
    document.getElementById('editType').value = type;  // NEW
    document.getElementById('finalId').value = recordId;

    showModal('passwordModal'); // center modal

    // 🔥 Focus cursor inside password field
    setTimeout(function(){
        var passwordInput = document.getElementById('editPassword');
        passwordInput.value = "";   // clear old password (optional but recommended)
        passwordInput.focus();      // set cursor
    }, 100); // small delay to ensure modal is visible
}


function checkPassword(){
    var password = document.getElementById('editPassword').value;
    var date = document.getElementById('editDate').value;
    var time = document.getElementById('editTime').value;
    var source = document.getElementById('editSource').value;
    var empid = document.getElementById('editEmployee').value;
    var action = document.getElementById('editAction').value;
    var recordId = document.getElementById('finalId').value;

    var xhr = new XMLHttpRequest();
    xhr.open("POST","check_password.php",true);
    xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");

    xhr.onload = function(){
        if(this.responseText.trim() === 'success'){

            var type = document.getElementById('editType').value;

            closePasswordModal();

            if(type === 'attendance'){

                document.getElementById('finalDate').value = date;
                document.getElementById('finalTime').value = time.substring(0,5);  // HH:MM only
                document.getElementById('finalSource').value = source;
                document.getElementById('finalEmployee').value = empid;
                document.getElementById('finalAction').value = action;
                document.getElementById('finalId').value = recordId; // <-- NEW

                showModal('editModal');  // Show edit modal with centering

                // 🔥 Focus on time input
                setTimeout(function(){
                    var timeInput = document.getElementById('finalTime');
                    timeInput.focus();
                    timeInput.select(); // optional (works in some browsers)
                }, 100);

            } else if(type === 'holiday') {

                document.getElementById('holidayEmployee').value = empid;

                showModal('holidayModal');
            }

        } else {
            alert("Wrong Password");
        }
    };

    xhr.send("password="+encodeURIComponent(password));
}

function openHolidayPassword(empid){
    document.getElementById('editEmployee').value = empid;
    showModal('passwordModal');
}

function openHolidayModal(){
    closePasswordModal();
    document.getElementById('holidayEmployee').value =
        document.getElementById('editEmployee').value;

    showModal('holidayModal');
}

function closeHolidayModal(){
    document.getElementById('holidayModal').style.display = 'none';
}

//////////////////////////////////////////////////////////////////////////////

function openPunchModal(records) {

    let html = "<table style='width:100%; border-collapse:collapse; font-size:14px;'>";

    html += "<tr style='background:#f1f3f5; text-align:center;'>";
    html += "<th style='padding:8px;'>#</th>";
    html += "<th style='padding:8px;'>Time</th>";
    html += "<th style='padding:8px;'>Type</th>";
    html += "</tr>";

    for (let i = 0; i < records.length; i++) {

        let badge = "";

        if (records[i].source === "table1") {
            badge = "<span style='background:#198754; color:white; padding:4px 8px; border-radius:20px; font-size:12px;'>Auto</span>";
        } else if (records[i].source === "table2") {
            badge = "<span style='background:#0d6efd; color:white; padding:4px 8px; border-radius:20px; font-size:12px;'>Manual</span>";
        }

        html += "<tr style='text-align:center;' onmouseover=\"this.style.background='#f8f9fa'\" onmouseout=\"this.style.background=''\">";
        html += "<td style='padding:8px;'>" + (i + 1) + "</td>";
        html += "<td style='padding:8px; font-weight:bold;'>" + records[i].time + "</td>";
        html += "<td style='padding:8px;'>" + badge + "</td>";
        html += "</tr>";
    }

    html += "</table>";

    document.getElementById("punchList").innerHTML = html;

    document.getElementById("punchModal").style.display = "flex";
}


function closePunchModal(){
    document.getElementById('punchModal').style.display = 'none';
}
/////////////////////////////////////////////////////////////////////////////
</script>


<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
