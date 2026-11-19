<?php
include('authentication.php');
include('includes/header.php');
?>

<div class="card radius-10">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div>
                <h5 class="mb-0">Employee List</h5>
            </div>
            <div class="dropdown options ms-auto">
                <div class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                    <i class='bx bx-dots-horizontal-rounded'></i>
                </div>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="add_employee.php">Add New Employee</a></li>
                </ul>
            </div>
        </div>
        <form method="GET">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Select Date:</label>
                    <input type="date" name="filter_date" class="form-control" value="<?= $_GET['filter_date'] ?? date('Y-m-d') ?>">
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <hr>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Profile</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Attendance Status</th>
                        <th>In Time</th>
                        <th>Out Time</th>
                        <th>Working Hours</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        
                        
                        $masterid = $_SESSION['auth_user']['user_master_id'];

                        $table_id = $_SESSION['auth_user']['user_master_id']; // -> ct0002
                        $attendanceTable = 'z_attendance_' . $table_id;
                        $attendanceTable2 = 'y_attendance_' . $table_id;

                        $query = "SELECT * FROM employee_data WHERE masterid = '$masterid' ORDER BY CAST(employee_id AS UNSIGNED) ASC";
                        $query_run = mysqli_query($con, $query);

                        $filterDate = $_GET['filter_date'] ?? date('Y-m-d');


                        if(mysqli_num_rows($query_run) > 0) {
                            foreach($query_run as $item) {

                                
                                $employeeId = $item['employee_id'];
                                $startTime = $item['start_time'];
                                $endTime = $item['end_time'];
                                $workType = $item['worker_type'];

                                $overtime = $item['over_time'];

                                $officialEnd = new DateTime($endTime);

                                if($workType == "overtime"){
                                    $officialEnd->modify("+{$overtime} minutes");
                                }else{
                                    $officialEnd = $officialEnd;
                                }


                                $graceTime = 15;

                           //     $attendance_query = "SELECT * FROM $attendanceTable WHERE employee_id = '$employeeId' AND attendance_date = '$filterDate' ORDER BY attendance_time ASC";
                             
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

                                $inTime = $outTime = $status = $workingHours = '---';
                                

                                if(mysqli_num_rows($attendance_query_run) > 0) {
                                    
                                /*    $status = 'Present';
                                    $attendance_records = [];

                                    while($row = mysqli_fetch_assoc($attendance_query_run)) {

                                        $attendance_records[] = $row['attendance_time'];
                                        
                                    }

                                    $inTime = $attendance_records[0];
                                    $outTime = end($attendance_records);

                                    // Calculate working hours
                                    $start = new DateTime($inTime);
                                    $end = new DateTime($outTime);
                                    $interval = $start->diff($end);
                                    $workingHours = $interval->format('%h hrs %i mins');

                                 */




                                    $status = 'Present';
                                    $attendance_records = [];

                                    while ($row = mysqli_fetch_assoc($attendance_query_run)) {
                                        $attendance_records[] = $row['attendance_time'];
                                    }

                                    $inTime = $attendance_records[0];
                                    $outTime = end($attendance_records);
                                    

                                    // Adjusted Start Time Logic with Grace
                                    $recordedIn = new DateTime($attendance_records[0]);
                                    $officialStart = new DateTime($startTime);

                                    $graceLimit = clone $officialStart;
                                    $extraMinutes = $graceTime + 1;
                                    $graceLimit->modify("+{$extraMinutes} minutes");
                                   // $graceLimit->modify("+{$graceTime} minutes");

                                    if ($recordedIn > $officialStart && $recordedIn < $graceLimit) {
                                        $adjustedStart = $officialStart;
                                    } elseif ($recordedIn < $officialStart) {
                                        $adjustedStart = $officialStart;
                                    } else {
                                        $adjustedStart = $recordedIn;
                                    }

                                    // Total Working Time from In-Out Pairs
                                    $totalSeconds = 0;
                                    for ($i = 0; $i < count($attendance_records) - 1; $i += 2) {
                                        $start = new DateTime($attendance_records[$i]);
                                        $end = new DateTime($attendance_records[$i + 1]);

                                        // First session adjustment
                                        if ($i === 0) {
                                            $start = $adjustedStart;
                                        }

                                   
                                        if($officialEnd > $end){
                                            $adjustedEnd = $end;
                                        } else {
                                            $adjustedEnd = $officialEnd;
                                        }  

                                        $diff = $start->diff($adjustedEnd);
                                        $totalSeconds += ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                                    }

                                    // Convert total seconds to H:i format
                                    $hours = floor($totalSeconds / 3600);
                                    $minutes = floor(($totalSeconds % 3600) / 60);
                                    $workingHours = sprintf("%02d:%02d", $hours, $minutes);


                                } else {
                                    $status = 'Absent';
                                }


                    ?>
                    <tr>
                        <td><?= $item['id'] ?></td>
                        <td>
                            <?php
                                $profileImage = base64_encode($item['employee_profile']);
                                echo '<img src="data:image/jpeg;base64,'.$profileImage.'" width="50" height="50" style="object-fit: cover; border-radius: 50%;">';
                            ?>
                        </td>
                        <td><?= $item['employee_id'] ?></td>
                        <td><?= $item['employee_name'] ?></td>
                        <td><?= $status ?></td>
                        <td><?= $inTime ?></td>
                        <td><?= $outTime ?></td>
                        <td><?= $workingHours ?></td>

                    </tr>
                    <?php
                            }
                        } else {
                    ?>
                    <tr>
                        <td colspan="10" class="text-center">No Records Found</td>
                    </tr>
                    <?php
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
