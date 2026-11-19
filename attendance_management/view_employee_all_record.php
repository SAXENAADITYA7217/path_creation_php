<?php
// This script is an improved version of the employee attendance report.
// It has been refactored for better security, performance, and readability.

include('authentication.php');
include('includes/header.php');

// --- 1. GET USER INPUT AND INITIALIZE VARIABLES ---
// Use null coalescing operator to safely get user input
$emp_id = $_GET['id'] ?? null;
$currentMonth = $_GET['month'] ?? date('Y-m');

if (!$emp_id) {
    echo "Employee ID not provided.";
    exit;
}

// Generate month navigation dates
$timestamp = strtotime($currentMonth . '-01');
$prevMonth = date('Y-m', strtotime('-1 month', $timestamp));
$nextMonth = date('Y-m', strtotime('+1 month', $timestamp));
$displayMonthName = date('F Y', $timestamp);
$year = date('Y', $timestamp);
$month = date('m', $timestamp);
$totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// --- 2. FETCH ALL NECESSARY DATA EFFICIENTLY USING PREPARED STATEMENTS ---

// a) Fetch all employee data
$sql_emp = "SELECT * FROM employee_data WHERE id = ?";
$stmt_emp = $con->prepare($sql_emp);
$stmt_emp->bind_param("i", $emp_id);
$stmt_emp->execute();
$employee = $stmt_emp->get_result()->fetch_assoc();

if (!$employee) {
    echo "Employee not found.";
    exit;
}

// b) Fetch all holidays for the month
$sql_holidays = "SELECT holidays_name, date FROM employee_holidays WHERE YEAR(date) = ? AND MONTH(date) = ? ORDER BY date ASC";
$stmt_holidays = $con->prepare($sql_holidays);
$stmt_holidays->bind_param("ii", $year, $month);
$stmt_holidays->execute();
$result_holidays = $stmt_holidays->get_result();

$holidays = [];
while ($row = $result_holidays->fetch_assoc()) {
    $holidays[] = $row;
}
$holidayDates = array_column($holidays, 'date');
$totalHolidays = count($holidays);

// c) Fetch all attendance records for the month
$sql_attendance = "SELECT attendance_date, attendance_time FROM attendance_mark_info WHERE user_id = ? AND YEAR(attendance_date) = ? AND MONTH(attendance_date) = ? ORDER BY attendance_date, attendance_time ASC";
$stmt_attendance = $con->prepare($sql_attendance);
$stmt_attendance->bind_param("sii", $emp_id, $year, $month);
$stmt_attendance->execute();
$result_attendance = $stmt_attendance->get_result();

$attendanceData = [];
while ($row = $result_attendance->fetch_assoc()) {
    $attendanceData[$row['attendance_date']][] = $row['attendance_time'];
}

// --- 3. PERFORM ALL CALCULATIONS ---

// Count Sundays
$totalSundays = 0;
for ($day = 1; $day <= $totalDays; $day++) {
    $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    if (date('w', strtotime($date)) == 0) { // 0 = Sunday
        $totalSundays++;
    }
}

// Count holidays on Sunday
$totalHolidayOnSunday = 0;
foreach ($holidays as $holiday) {
    if (date('w', strtotime($holiday['date'])) == 0) {
        $totalHolidayOnSunday++;
    }
}

// Calculate total working days and hours
$presentDays = 0;
$totalWorkingSeconds = 0;
$dailyWorkingHours = [];

$name = $employee['employee_name'];
$officialStartTime = new DateTime($employee['start_time']);
$officialEnd = new DateTime($employee['end_time']);

$graceTime = 15;

for ($day = 1; $day <= $totalDays; $day++) {
    $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    $isSunday = (date('w', strtotime($date)) == 0);
    $isHoliday = in_array($date, $holidayDates);
    $attendanceRecords = $attendanceData[$date] ?? [];
    $numRecords = count($attendanceRecords);

    // Default status and working hours
    $status = 'Absent';
    $cellClass = 'absent';
    $hoursWorked = '';

    if ($isSunday || $isHoliday) {
        $status = 'Non-working day';
        $cellClass = 'non-working';
    } else {
        if ($numRecords > 0) {
            if ($numRecords % 2 !== 0) {
                $status = 'Only In-time';
                $cellClass = 'only-in';
            } else {
                $status = 'Present';
                $cellClass = 'present';
                $presentDays++;

                $daySeconds = 0;
                $recordedIn = new DateTime($attendanceRecords[0]);
                $extraMinutes = $graceTime + 1;
                $adjustedStart = (clone $officialStartTime)->modify("+{$extraMinutes} minutes");

                if ($recordedIn < $adjustedStart) {
                    $startOfInterval = $officialStartTime;
                } else {
                    $startOfInterval = $recordedIn;
                }
                
                // Calculate time for all pairs of in/out times
                for ($i = 0; $i < $numRecords - 1; $i += 2) {
                    $start = new DateTime($attendanceRecords[$i]);
                    $end = new DateTime($attendanceRecords[$i + 1]);

                    // Use the adjusted start for the first interval
                    if ($i === 0) {
                        $start = $startOfInterval;
                    }

                    // Cap the end time at the official end time
                    if ($end > $officialEnd) {
                        $end = $officialEnd;
                    }

                    // The interval calculation for a working pair of entries
                    if ($end > $start) {
                        $diff = $start->diff($end);
                        $daySeconds += ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                    }
                }
                $totalWorkingSeconds += $daySeconds;
                $dailyHours = floor($daySeconds / 3600);
                $dailyMinutes = floor(($daySeconds % 3600) / 60);
                $hoursWorked = sprintf("%02d:%02d", $dailyHours, $dailyMinutes);
            }
        }
    }
    $dailyWorkingHours[$day] = ['status' => $status, 'class' => $cellClass, 'hours' => $hoursWorked];
}

// Calculate total working hours in HH:MM format
$totalHours = floor($totalWorkingSeconds / 3600);
$totalMinutes = floor(($totalWorkingSeconds % 3600) / 60);
$totalWorkingTimeFormatted = sprintf("%02d:%02d", $totalHours, $totalMinutes);
?>
<div class="card radius-10">
    <div class="card-body">
        <?php include('message.php'); ?>
        <div class="d-flex align-items-center mb-3">
            <div>
                <h5 class="mb-0">Salary Info (<?= $displayMonthName ?>)</h5>
            </div>
            <div class="ms-auto">
                <div class="ms-auto d-flex align-items-center">
                    <a href="?id=<?= $emp_id ?>&month=<?= $prevMonth ?>" class="btn btn-light btn-sm me-2">&lt;</a>
                    <h5 class="mb-0 mx-2"><?= date('F', $timestamp) ?></h5>
                    <a href="?id=<?= $emp_id ?>&month=<?= $nextMonth ?>" class="btn btn-light btn-sm ms-2">&gt;</a>
                </div>
            </div>
        </div>
        <hr>

        <!-- Display Summary Info -->
        <div class="mb-4">
            <p>
                <strong>Total Days in Month:</strong> <?= $totalDays ?> |
                <strong>Total Sundays:</strong> <?= $totalSundays ?> |
                <strong>Total Official Holidays:</strong> <?= $totalHolidays ?> |
                <strong>Holidays on Sunday:</strong> <?= $totalHolidayOnSunday ?> |
                <strong>Total Present Days:</strong> <?= $presentDays ?> |
                <strong>Total Working Hours:</strong> <?= $totalWorkingTimeFormatted ?>
            </p>
            <p>
                <strong>Holidays:</strong>
                <?php if ($totalHolidays > 0): ?>
                    <?php foreach ($holidays as $holiday): ?>
                        <?= $holiday['holidays_name'] ?> (<?= $holiday['date'] ?>) |
                    <?php endforeach; ?>
                <?php else: ?>
                    No holidays found for this month.
                <?php endif; ?>
            </p>
        </div>

        <!-- Attendance Calendar Section -->
        <div class="employee-calendar-section mb-5">
            <h5 class="mb-3">Attendance Calendar for Employee ID: <?= $emp_id ?> ( <?= $name ?>  )</h5>
            <table class="calendar-table">
                <thead>
                    <tr>
                        <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php
                        $firstDayOfMonth = date('w', strtotime("$year-$month-01"));
                        for ($i = 0; $i < $firstDayOfMonth; $i++) {
                            echo '<td></td>';
                        }
                        
                        $dayCounter = 1;
                        while ($dayCounter <= $totalDays) {
                            $cellClass = $dailyWorkingHours[$dayCounter]['class'];
                            $dailyStatus = $dailyWorkingHours[$dayCounter]['status'];
                            $hoursWorked = $dailyWorkingHours[$dayCounter]['hours'];

                            echo '<td class="' . $cellClass . '">';
                            echo '<div class="date-number">' . $dayCounter . '</div>';
                            echo '<div class="status-text">' . $dailyStatus . '</div>';
                            if (!empty($hoursWorked)) {
                                echo '<div class="working-hours-text">' . $hoursWorked . ' hrs</div>';
                            }
                            echo '</td>';

                            // Start a new row every 7 days (including empty leading cells)
                            if (($dayCounter + $firstDayOfMonth) % 7 === 0) {
                                echo '</tr><tr>';
                            }
                            $dayCounter++;
                        }

                        // Fill in remaining empty cells in the last row
                        while (($dayCounter + $firstDayOfMonth) % 7 !== 1) {
                            if (($dayCounter + $firstDayOfMonth - 1) % 7 === 0) break;
                            echo '<td></td>';
                            $dayCounter++;
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .calendar-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .calendar-table th, .calendar-table td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: center;
        height: 100px;
        vertical-align: top;
    }
    .calendar-table th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
    .date-number {
        font-size: 1.2em;
        font-weight: bold;
        text-align: right;
        color: white;
        margin-bottom: 5px;
    }
    .status-text, .working-hours-text {
        font-size: 0.9em;
        color: white;
    }
    .present {
        background-color: #16ec1dff; /* Green */
    }
    .only-in {
        background-color: #dac616ff; /* Yellow */
    }
    .absent {
        background-color: #f00920ff; /* Red */
    }
    .non-working {
        background-color: #9c9292ff; /* Grey */
        color: #757575;
    }
    .employee-calendar-section {
        border: 1px solid #ccc;
        padding: 20px;
        margin-bottom: 30px;
        border-radius: 8px;
    }
</style>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
