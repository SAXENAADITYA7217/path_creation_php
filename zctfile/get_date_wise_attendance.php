<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
include('../admin/config/dbcon.php');

if (!$con) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (
        isset($data['get_employee_attendance_data']) &&
        $data['get_employee_attendance_data'] === 'aded64894asf8s2s4w8'
    ) {

        // Get filter date from request or default to today
        $filterDate = !empty($data['filter_date']) ? $data['filter_date'] : date('Y-m-d');

        $query = "SELECT * FROM employee_data ORDER BY id ASC";
        $query_run = mysqli_query($con, $query);

        $response = [];

        if (mysqli_num_rows($query_run) > 0) {
            foreach ($query_run as $item) {

                $employeeId   = $item['id'];
                $employeeName = $item['employee_name'];
                $startTime    = $item['start_time'];
                $endTime      = $item['end_time'];
                $overTime     = $item['over_time']; // overtime in minutes
                $workType     = $item['worker_type'];
                $profileImage = base64_encode($item['employee_profile']);
                $graceTime    = $item['grace_time']; // grace in minutes 

                $attendance_query = "SELECT * FROM attendance_mark_info 
                                      WHERE user_id = '$employeeId' 
                                        AND attendance_date = '$filterDate' 
                                      ORDER BY attendance_time ASC";
                $attendance_query_run = mysqli_query($con, $attendance_query);

                $inTime = $outTime = $status = $workingHours = '---';

                if (mysqli_num_rows($attendance_query_run) > 0) {
                    $status = 'Present';
                    $attendance_records = [];

                    while ($row = mysqli_fetch_assoc($attendance_query_run)) {
                        $attendance_records[] = $row['attendance_time'];
                    }

                    $inTime  = $attendance_records[0];
                    //$outTime = end($attendance_records);

                    // Only set outTime if there is more than one record
                    //if (count($attendance_records) > 1) {
                    //    $outTime = end($attendance_records);
                    //}

                    // Only set outTime if there is more than one record AND count is even
                    if (count($attendance_records) > 1 && count($attendance_records) % 2 === 0) {
                        $outTime = end($attendance_records);
                    }


                    // Adjusted Start Time Logic with Grace
                    $recordedIn    = new DateTime($attendance_records[0]);
                    $officialStart = new DateTime($startTime);

                    $graceLimit = clone $officialStart;
                    $graceLimit->modify("+{$graceTime} minutes");

                    if ($recordedIn > $officialStart && $recordedIn < $graceLimit) {
                        $adjustedStart = $officialStart;
                    } elseif ($recordedIn < $officialStart) {
                        $adjustedStart = $officialStart;
                    } else {
                        $adjustedStart = $recordedIn;
                    }

                    // Total Working Time from In-Out Pairs
                    $totalSeconds = 0;

                    // Official shift end time logic
                    $officialEnd = new DateTime($endTime);
                    if (strtolower($workType) === "overtime") {
                        $officialEnd->modify("+{$overTime} minutes");
                    }

                    for ($i = 0; $i < count($attendance_records) - 1; $i += 2) {
                        $start = new DateTime($attendance_records[$i]);
                        $end   = new DateTime($attendance_records[$i + 1]);

                        // First session adjustment (Grace time logic)
                        if ($i === 0) {
                            $start = $adjustedStart;
                        }
                        
                        // 🚫 Skip if the start is after or equal to official end time (+ overtime if applicable)
                        if ($start >= $officialEnd) {
                            continue;
                        }

                        // ⛔ Limit to official end time (+ overtime if applicable)
                        if ($end > $officialEnd) {
                            $end = $officialEnd;
                        }

                        // Only add positive durations (in case start > end after adjustment)
                        if ($end > $start) {
                            $diff         = $start->diff($end);
                            $totalSeconds += ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                        }
                    }

                    // Convert total seconds to H:i format
                    $hours        = floor($totalSeconds / 3600);
                    $minutes      = floor(($totalSeconds % 3600) / 60);
                    $workingHours = sprintf("%02d:%02d", $hours, $minutes);

                } else {
                    $status = 'Absent';
                }

                // Push each employee data to response array
                $response[] = [
                    "id"              => $employeeId,
                    "employee_name"   => $employeeName,
                    "employee_profile"=> $profileImage,
                    "status"          => $status,
                    "in_time"         => $inTime,
                    "out_time"        => $outTime,
                    "working_hours"   => $workingHours
                ];
            }

            echo json_encode([
                "status" => "success",
                "filter_date" => $filterDate,
                "data"   => $response
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "No employees found"]);
        }

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid customer ID"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
