<?php
include('authentication.php');


if(isset($_POST['add_money_settlement_data']))
{
    $masterid = $_SESSION['auth_user']['user_master_id'];
    $employee_id = $_POST['employee_id'];
    $employee_name = $_POST['employee_name'];
    $money_type = $_POST['money_type'];
    $money_transaction_type = $_POST['money_transaction_type'];
    $money_amount = $_POST['money_amount'];
    $money_description = $_POST['money_description'];
    $money_date = $_POST['money_date'];

    date_default_timezone_set('Asia/Kolkata');
    $create_date = date('Y-m-d');

    // 🔹 Get employee current balance
    $check_query = "SELECT dues_amount, advance_amount, panalty_amount 
                    FROM employee_data 
                    WHERE employee_id='$employee_id' 
                    AND masterid='$masterid'";

    $check_run = mysqli_query($con,$check_query);
    $emp_data = mysqli_fetch_assoc($check_run);

    $allowed_amount = 0;

    if($money_type == 0){
        $allowed_amount = $emp_data['dues_amount'];
    }
    elseif($money_type == 1){
        $allowed_amount = $emp_data['advance_amount'];
    }
    elseif($money_type == 2){
        $allowed_amount = $emp_data['panalty_amount'];
    }

    // ❌ Stop if amount greater than available
    if($money_amount > $allowed_amount)
    {
        $_SESSION['message'] = "Amount cannot exceed available balance!";
        header("Location: add_money_settlement.php");
        exit();
    }

    // 🔹 Insert settlement record
    $insert_query = "INSERT INTO employee_money_settlement 
    (masterid, employee_id, employee_name, money_type, money_transaction_type, money_amount, money_description, money_date, create_date)
    VALUES 
    ('$masterid','$employee_id','$employee_name','$money_type','$money_transaction_type','$money_amount','$money_description','$money_date','$create_date')";

    $insert_query_run = mysqli_query($con, $insert_query);

    if($insert_query_run)
    {
        // 🔹 Update employee balance
        if($money_type == 0)
        {
            $update = "UPDATE employee_data 
                       SET dues_amount = dues_amount - '$money_amount'
                       WHERE employee_id='$employee_id' AND masterid='$masterid'";
        }
        elseif($money_type == 1)
        {
            $update = "UPDATE employee_data 
                       SET advance_amount = advance_amount - '$money_amount'
                       WHERE employee_id='$employee_id' AND masterid='$masterid'";
        }
        elseif($money_type == 2)
        {
            $update = "UPDATE employee_data 
                       SET panalty_amount = panalty_amount - '$money_amount'
                       WHERE employee_id='$employee_id' AND masterid='$masterid'";
        }

        mysqli_query($con,$update);

        $_SESSION['message'] = "Money Settlement Added Successfully";
        header("Location: add_money_settlement.php");
        exit();
    }
    else
    {
        $_SESSION['message'] = "Something Went Wrong";
        header("Location: add_money_settlement.php");
        exit();
    }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////

if(isset($_POST['update_money_detail']))
{
    $masterid = $_SESSION['auth_user']['user_master_id'];

    $money_id = $_POST['money_id'];
    $money_type = $_POST['money_type'];
    $money_amount = $_POST['money_amount'];
    $money_description = $_POST['money_description'];
    $money_date = $_POST['money_date'];

    $update_query = "UPDATE employee_money_detail SET
                        money_type='$money_type',
                        money_amount='$money_amount',
                        money_description='$money_description',
                        money_date='$money_date'
                     WHERE id='$money_id' AND masterid='$masterid'";

    $update_query_run = mysqli_query($con, $update_query);

    if($update_query_run){
        $_SESSION['message'] = "Money Detail Updated Successfully";
    } else {
        $_SESSION['message'] = "Something Went Wrong";
    }

    header("Location: view_money_detail.php");
    exit(0);
}


if(isset($_POST['add_money_detail_data']))
{
    $masterid = $_SESSION['auth_user']['user_master_id'];
    $employee_id = $_POST['employee_id'];
    $employee_name = $_POST['employee_name'];
    $money_type = $_POST['money_type'];
    $money_transaction_type = $_POST['money_transaction_type'];
    $money_amount = $_POST['money_amount'];
    $money_description = $_POST['money_description'];
    $money_date = $_POST['money_date'];

    date_default_timezone_set('Asia/Kolkata');
    $create_date = date('Y-m-d');

    // Insert money detail
    $insert_query = "INSERT INTO employee_money_detail 
    (masterid, employee_id, employee_name, money_type, money_transaction_type, money_amount, money_description, money_date, create_date)
    VALUES 
    ('$masterid','$employee_id','$employee_name','$money_type','$money_transaction_type','$money_amount','$money_description','$money_date','$create_date')";

    $insert_query_run = mysqli_query($con, $insert_query);

    if($insert_query_run)
    {

        // UPDATE employee total amounts
        if($money_type == 0)
        {
            mysqli_query($con,"UPDATE employee_data 
            SET dues_amount = dues_amount + '$money_amount'
            WHERE employee_id='$employee_id'");
        }
        elseif($money_type == 1)
        {
            mysqli_query($con,"UPDATE employee_data 
            SET advance_amount = advance_amount + '$money_amount'
            WHERE employee_id='$employee_id'");
        }
        elseif($money_type == 2)
        {
            mysqli_query($con,"UPDATE employee_data 
            SET panalty_amount = panalty_amount + '$money_amount'
            WHERE employee_id='$employee_id'");
        }

        $_SESSION['message'] = "Money Detail Added Successfully";
        header("Location: add_money_detail.php");
        exit(0);
    }
    else
    {
        $_SESSION['message'] = "Something Went Wrong";
        header("Location: add_money_detail.php");
        exit(0);
    }
}


if(isset($_POST['save_attendance_password']))
{
    $masterid = $_SESSION['auth_user']['user_master_id'];
    $password = mysqli_real_escape_string($con, $_POST['edit_password']);
    $today = date('Y-m-d');

    // Check if password already exists
    $check_query = "SELECT * FROM attendance_edit_password 
                    WHERE masterid='$masterid'";
    $check_result = mysqli_query($con, $check_query);

    if(mysqli_num_rows($check_result) > 0)
    {
        // UPDATE
        $update_query = "UPDATE attendance_edit_password 
                         SET edit_password='$password',
                             password_change_date='$today'
                         WHERE masterid='$masterid'";
                         
        mysqli_query($con, $update_query);

        $_SESSION['message'] = "Attendance Edit Password Updated Successfully";
    }
    else
    {
        // INSERT
        $insert_query = "INSERT INTO attendance_edit_password 
                        (masterid, edit_password, password_change_date)
                        VALUES ('$masterid', '$password', '$today')";
                        
        mysqli_query($con, $insert_query);

        $_SESSION['message'] = "Attendance Edit Password Created Successfully";
    }

    header("Location: set_attendance_password.php");
    exit();
}


if(isset($_POST['update_company_detail']))
{
    $masterid = mysqli_real_escape_string($con, $_POST['masterid']);

    $company_name = $_POST['company_name'];
    $selection_method = $_POST['selection_method'];
    $debit_account_no = $_POST['debit_account_no'];
    $info_line = $_POST['info_line'];
    $sender_name = $_POST['sender_name'];
    $sender_address = $_POST['sender_address'];
    $department_code = $_POST['department_code'];
    $Instrument_type = $_POST['Instrument_type'];
    $Instrument_no = $_POST['Instrument_no'];
    $purpose_code = $_POST['purpose_code'];
    $customer_reference_no = $_POST['customer_reference_no'];
    $request_mode = $_POST['request_mode'];

    // Check if record already exists
    $check_query = "SELECT id FROM attendance_company_detail WHERE masterid = '$masterid' LIMIT 1";
    $check_run = mysqli_query($con, $check_query);

    if(mysqli_num_rows($check_run) > 0)
    {
        // UPDATE
        $update_query = "UPDATE attendance_company_detail SET
            company_name='$company_name',
            selection_method='$selection_method',
            debit_account_no='$debit_account_no',
            info_line='$info_line',
            sender_name='$sender_name',
            sender_address='$sender_address',
            department_code='$department_code',
            Instrument_type='$Instrument_type',
            Instrument_no='$Instrument_no',
            purpose_code='$purpose_code',
            customer_reference_no='$customer_reference_no',
            request_mode='$request_mode'
            WHERE masterid='$masterid'";

        mysqli_query($con, $update_query);
    }
    else
    {
        // INSERT
        $insert_query = "INSERT INTO attendance_company_detail
        (masterid, company_name, selection_method, debit_account_no, info_line, sender_name, sender_address,
         department_code, Instrument_type, Instrument_no, purpose_code, customer_reference_no, request_mode)
        VALUES
        ('$masterid','$company_name','$selection_method','$debit_account_no','$info_line','$sender_name','$sender_address',
         '$department_code','$Instrument_type','$Instrument_no','$purpose_code','$customer_reference_no','$request_mode')";

        mysqli_query($con, $insert_query);
    }

    $_SESSION['message'] = "Updated successfully!";
    header("Location: add_company_profile.php");
    exit(0);
}

if (isset($_POST['add_extra_time_data'])) {
    $masterid = $_SESSION['auth_user']['user_master_id'];
    $selection = $_POST['employee_id']; // Could be single ID or all_select/overtime/non_overtime
    $extraDate = $_POST['extra_date'];
    $extraTime = $_POST['extra_time'];
    

    $table_id = strtolower($_SESSION['auth_user']['user_master_id']); // -> ct0002
    $attendanceTable = 'z_attendance_' . $table_id;

    if ($selection === "all_select" || $selection === "overtime" || $selection === "non_overtime") {
        
        // Build query based on selection
        $whereCondition = "";
        if ($selection === "overtime") {
            $whereCondition = "AND ed.worker_type = 'overtime'";
        } elseif ($selection === "non_overtime") {
            $whereCondition = "AND ed.worker_type = 'non_overtime'";
        }

        // Fetch matching employees
        $fetchQuery = "
            SELECT ed.employee_id 
            FROM employee_data ed
            INNER JOIN $attendanceTable am ON ed.employee_id = am.employee_id
            WHERE am.attendance_date = '$extraDate'
              AND am.attendance_status = 'Present'
              AND ed.masterid = '$masterid'
              $whereCondition
            GROUP BY ed.employee_id
        ";
        $result = mysqli_query($con, $fetchQuery);

        while ($row = mysqli_fetch_assoc($result)) {
            $userId = $row['employee_id'];
            mysqli_query($con, "
                INSERT INTO employee_extra_work (masterid, employee_id, extra_date, extra_time, server_update_status) 
                VALUES ('$masterid', '$userId', '$extraDate', '$extraTime', '0')
            ");
        }

    } else {
        // Single employee selection
        $userId = $selection;
        mysqli_query($con, "
            INSERT INTO employee_extra_work (masterid, employee_id, extra_date, extra_time, server_update_status) 
            VALUES ('$masterid', '$userId', '$extraDate', '$extraTime', '0')
        ");
    }

    $_SESSION['message'] = "Extra time added successfully!";
    header("Location: add_extra_overtime.php");
    exit(0);
}


if (isset($_POST['save_leave'])) {

    $masterid = $_SESSION['auth_user']['user_master_id'];

    $employee_id  = trim($_POST['employee_id']);
    $leave_type   = trim($_POST['leave_type']);
    $leave_date   = trim($_POST['leave_date']);
    $leave_reason = trim($_POST['leave_reason'] ?? '');
    $document_link = trim($_POST['document_link'] ?? '');

    // ==============================
    // Validate Leave Type
    // ==============================
    switch ($leave_type) {
        case 'sick_leave':
            $leaveColumn = 'employee_sl';
            break;
        case 'earned_leave':
            $leaveColumn = 'employee_el';
            break;
        case 'casual_leave':
            $leaveColumn = 'employee_cl';
            break;
        default:
            $_SESSION['message'] = "Invalid Leave Type.";
            header("Location: add_employee_leave.php");
            exit();
    }

    // ==============================
    // Prevent Past Date Leave (Optional)
    // ==============================
 //   if ($leave_date < date('Y-m-d')) {
   //     $_SESSION['message'] = "Cannot apply leave for past dates.";
  //      header("Location: add_employee_leave.php");
//        exit();
 //   }

    // ==============================
    // 1️⃣ Check Duplicate Leave
    // ==============================
    $stmt = $con->prepare("SELECT id FROM employee_leave_use_data 
                           WHERE employee_id = ? 
                           AND leave_date = ? 
                           AND masterid = ?
                           LIMIT 1");
    $stmt->bind_param("sss", $employee_id, $leave_date, $masterid);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['message'] = "Leave already applied for this date.";
        header("Location: add_employee_leave.php");
        exit();
    }
    $stmt->close();

    // ==============================
    // 2️⃣ Get Assigned Leaves
    // ==============================
    $stmt = $con->prepare("SELECT $leaveColumn 
                           FROM employee_leave 
                           WHERE employee_id = ? 
                           AND masterid = ?
                           LIMIT 1");
    $stmt->bind_param("ss", $employee_id, $masterid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['message'] = "Leave configuration not found for employee.";
        header("Location: add_employee_leave.php");
        exit();
    }

    $row = $result->fetch_assoc();
    $assignedLeaves = (int)$row[$leaveColumn];
    $stmt->close();

    // ==============================
    // 3️⃣ Get Used Approved Leaves
    // ==============================
    $stmt = $con->prepare("SELECT COUNT(*) as used 
                           FROM employee_leave_use_data 
                           WHERE employee_id = ?
                           AND leave_type = ?
                           AND leave_status = 1
                           AND masterid = ?");
    $stmt->bind_param("sss", $employee_id, $leave_type, $masterid);
    $stmt->execute();
    $result = $stmt->get_result();
    $usedRow = $result->fetch_assoc();
    $usedLeaves = (int)$usedRow['used'];
    $stmt->close();

    $remainingLeaves = $assignedLeaves - $usedLeaves;

    if ($remainingLeaves <= 0) {
        $_SESSION['message'] = "No remaining leaves available.";
        header("Location: add_employee_leave.php");
        exit();
    }

    // ==============================
    // 4️⃣ Leave Document 
    // ==============================

    $document_link = '';

    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {

        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

        $file_name = $_FILES['document_file']['name'];
        $file_tmp  = $_FILES['document_file']['tmp_name'];
        $file_size = $_FILES['document_file']['size'];

        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate extension
        if (!in_array($file_ext, $allowed_extensions)) {
            $_SESSION['message'] = "Only JPG, PNG, and PDF files allowed.";
            header("Location: add_employee_leave.php");
            exit();
        }

        // Validate size (2MB max)
        if ($file_size > 2 * 1024 * 1024) {
            $_SESSION['message'] = "File size must be less than 2MB.";
            header("Location: add_employee_leave.php");
            exit();
        }

        // Create unique filename
        $new_file_name = "leave_" . time() . "_" . rand(1000,9999) . "." . $file_ext;

        $upload_path = "leave/" . $new_file_name;

        if (move_uploaded_file($file_tmp, $upload_path)) {
            $document_link = $new_file_name;
        } else {
            $_SESSION['message'] = "File upload failed.";
            header("Location: add_employee_leave.php");
            exit();
        }
    }

    // ==============================
    // 4️⃣ Insert Leave (Pending = 0)
    // ==============================
    $stmt = $con->prepare("INSERT INTO employee_leave_use_data 
        (masterid, employee_id, leave_type, leave_date, leave_status, leave_reason, document_link)
        VALUES (?, ?, ?, ?, 0, ?, ?)");

    $stmt->bind_param(
        "ssssss",
        $masterid,
        $employee_id,
        $leave_type,
        $leave_date,
        $leave_reason,
        $document_link
    );

    if ($stmt->execute()) {
        $_SESSION['message'] = "Leave request submitted successfully.";
        header("Location: view_employee_approval.php");
        exit();
    } else {
        $_SESSION['message'] = "Something went wrong. Please try again.";
        header("Location: add_employee_leave.php");
        exit();
    }
}


if (isset($_POST['add_attendance'])) {

    $masterid = $_SESSION['auth_user']['user_master_id'];
    $employee_id = $_POST['employee_id'];

    $dates = $_POST['attendance_date'];
    $times = $_POST['attendance_time'];

    // Dynamic table name
    $table_name = "y_attendance_" . $masterid;

    // Optional default values
    $attendance_status = "Present";
    $face_image = ''; // ✅ EMPTY BLOB (NOT NULL)

    // Validate
    if (empty($employee_id) || empty($dates) || empty($times)) {
        $_SESSION['message'] = "Invalid attendance data";
        header("Location: view_attendance_approval.php");
        exit;
    }

    // Prepare insert query
    $sql = "INSERT INTO `$table_name`
            (employee_id, attendance_date, attendance_time, attendance_status, face_image)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $con->prepare($sql);

    if (!$stmt) {
        $_SESSION['message'] = "Database error: Unable to prepare statement";
        header("Location: view_attendance_approval.php");
        exit;
    }

    // Loop through multiple rows
    foreach ($dates as $index => $date) {

        $time = $times[$index];

        if (empty($date) || empty($time)) {
            continue; // skip empty rows
        }

        $stmt->bind_param(
            "sssss",
            $employee_id,
            $date,
            $time,
            $attendance_status,
            $face_image
        );

        $stmt->execute();
    }

    $stmt->close();

    $_SESSION['message'] = "Attendance added successfully";
    header("Location: view_attendance_approval.php");
    exit;
}


if (isset($_POST['save_multiple_holidays'])) {
    $masterid = $_SESSION['auth_user']['user_master_id'];
    $names = $_POST['holidays_name'];
    $dates = $_POST['holiday_date'];
    $types = $_POST['holiday_type'];

    $success = true;

    for ($i = 0; $i < count($names); $i++) {
        $holiday_name = mysqli_real_escape_string($con, $names[$i]);
        $holiday_date = mysqli_real_escape_string($con, $dates[$i]);
        $holiday_type = mysqli_real_escape_string($con, $types[$i]);

        if (!empty($holiday_name) && !empty($holiday_date) && !empty($holiday_type)) {
            $query = "INSERT INTO employee_holidays (masterid, holidays_name, date, type) 
                      VALUES ('$masterid', '$holiday_name', '$holiday_date', '$holiday_type')";
            $query_run = mysqli_query($con, $query);

            if (!$query_run) {
                $success = false;
            }
        }
    }

    if ($success) {
        $_SESSION['message'] = "All holidays added successfully!";
        header("Location: holidays_list.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Error saving some holidays!";
        header("Location: holidays_list.php");
        exit(0);
    }

    
}


if (isset($_POST['extra_time_data'])) {
    $masterid = $_SESSION['auth_user']['user_master_id'];
    $selection = $_POST['employee_id']; // Could be single ID or all_select/overtime/non_overtime
    $extraDate = date('Y-m-d');
    $extraTime = $_POST['extra_time'];

    $table_id = strtolower($_SESSION['auth_user']['user_master_id']); // -> ct0002
    $attendanceTable = 'z_attendance_' . $table_id;

    if ($selection === "all_select" || $selection === "overtime" || $selection === "non_overtime") {
        
        // Build query based on selection
        $whereCondition = "";
        if ($selection === "overtime") {
            $whereCondition = "AND ed.worker_type = 'overtime'";
        } elseif ($selection === "non_overtime") {
            $whereCondition = "AND ed.worker_type = 'non_overtime'";
        }

        // Fetch matching employees
        $fetchQuery = "
            SELECT ed.employee_id 
            FROM employee_data ed
            INNER JOIN $attendanceTable am ON ed.employee_id = am.employee_id
            WHERE am.attendance_date = '$extraDate'
              AND am.attendance_status = 'Present'
              AND ed.masterid = '$masterid'
              $whereCondition
            GROUP BY ed.employee_id
        ";
        $result = mysqli_query($con, $fetchQuery);

        while ($row = mysqli_fetch_assoc($result)) {
            $userId = $row['employee_id'];
            mysqli_query($con, "
                INSERT INTO employee_extra_work (masterid, employee_id, extra_date, extra_time, server_update_status) 
                VALUES ('$masterid', '$userId', '$extraDate', '$extraTime', '1')
            ");
        }

    } else {
        // Single employee selection
        $userId = $selection;
        mysqli_query($con, "
            INSERT INTO employee_extra_work (masterid, employee_id, extra_date, extra_time, server_update_status) 
            VALUES ('$masterid', '$userId', '$extraDate', '$extraTime', '1')
        ");
    }

    $_SESSION['message'] = "Extra time added successfully!";
    header("Location: extra_overtime.php");
    exit(0);
}




if (isset($_POST['update_employee_btn'])) {
    $masterid = $_SESSION['auth_user']['user_master_id'];
    $employee_id = $_POST['employee_id'];
    $employee_name = $_POST['employee_name'];
    $phone_no = $_POST['phone_no'];
    $designation = $_POST['designation'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $worker_type = $_POST['worker_type'];
    $grace_time = $_POST['grace_time'];
    $over_time = $_POST['over_time'];
    $employee_status = $_POST['employee_status'];
    $employee_salary = $_POST['employee_salary'];

    $employee_sick_leave = $_POST['employee_sick_leave'];
    $employee_earned_leave = $_POST['employee_earned_leave'];
    $employee_casual_leave = $_POST['employee_casual_leave'];

    $employee_salary_type = $_POST['employee_salary_type'];
    $beneficiary_name = $_POST['beneficiary_name'];
    $beneficiary_address = $_POST['beneficiary_address'];
    $beneficiary_account_type = $_POST['beneficiary_account_type'];
    $beneficiary_account_no = $_POST['beneficiary_account_no'];
    $beneficiary_ifsc_code = $_POST['beneficiary_ifsc_code'];

    // Update employee_data
    $query = "UPDATE employee_data SET 
                employee_name = '$employee_name',
                phone_no = '$phone_no',
                designation = '$designation',
                start_time = '$start_time',
                end_time = '$end_time',
                worker_type = '$worker_type',
                grace_time = '$grace_time',
                over_time = '$over_time',
                beneficiary_name = '$beneficiary_name',
                beneficiary_address = '$beneficiary_address',
                beneficiary_account_type = '$beneficiary_account_type',
                beneficiary_account_no = '$beneficiary_account_no',
                beneficiary_ifsc_code = '$beneficiary_ifsc_code',
                employee_salary_type = '$employee_salary_type',
                employee_status = '$employee_status',
                server_update = '1',
                employee_salary = '$employee_salary'
              WHERE  masterid = '$masterid' 
              AND employee_id = '$employee_id'";

    $query_run = mysqli_query($con, $query);

    if ($query_run) {
        
        // Check if employee_leave record exists
        $checkLeave = mysqli_query($con, "SELECT id FROM employee_leave WHERE masterid = '$masterid' AND employee_id = '$employee_id' LIMIT 1");

        if (mysqli_num_rows($checkLeave) > 0) {
            // Update existing record
            $updateLeave = "UPDATE employee_leave SET 
                                employee_sl = '$employee_sick_leave',
                                employee_el = '$employee_earned_leave',
                                employee_cl = '$employee_casual_leave'
                            WHERE masterid = '$masterid' 
                            AND employee_id = '$employee_id'";
            mysqli_query($con, $updateLeave);
        } else {
            // Check if any leave values > 0
            if ($employee_sick_leave > 0 || $employee_earned_leave > 0 || $employee_casual_leave > 0) {
                // Insert new record only if values > 0
                $insertLeave = "INSERT INTO employee_leave (masterid, employee_id, employee_sl, employee_el, employee_cl) 
                                VALUES ('$masterid', '$employee_id', '$employee_sick_leave', '$employee_earned_leave', '$employee_casual_leave')";
                mysqli_query($con, $insertLeave);
            }    
        }

        $_SESSION['message'] = "Employee Updated Successfully!";
        header("Location: view_employee.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Update Failed";
        header("Location: view_employee.php");
        exit(0);
    }
}




if (isset($_POST['update_generate_pass'])) {
    $masterid = $_SESSION['auth_user']['user_master_id'];
    $passId = $_POST['pass_id'];
    $employeeId = $_POST['employee_id'];
    $passDate = date('Y-m-d'); // Today's date
    $passTime = $_POST['pass_time'];

    // Combine date and time to check if in past
    $selectedDateTime = strtotime("$passDate $passTime");
    $currentDateTime = time();

    if ($selectedDateTime < $currentDateTime) {
        $_SESSION['message'] = "You cannot select a past time.";
        header("Location: employee_pass.php");
        exit(0);
    }

    // Check if a record exists today with status 0
    $checkQuery = "SELECT * FROM employee_pass_info 
                   WHERE id = '$passId'
                   AND masterid = '$masterid' 
                   AND employee_id = '$employeeId' 
                   AND employee_pass_date = '$passDate' 
                   AND employee_pass_status = 0";
    $checkResult = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // Convert to 24-hour format
        $formattedTime = date("H:i:s", strtotime($passTime));

        $updateQuery = "UPDATE employee_pass_info 
                        SET employee_pass_time = '$formattedTime' 
                        WHERE id = '$passId'";
        $runUpdate = mysqli_query($con, $updateQuery);

        if ($runUpdate) {
            $_SESSION['message'] = "Pass time updated successfully!";
        } else {
            $_SESSION['message'] = "Failed to update pass time.";
        }
    } else {
        $_SESSION['message'] = "No active pass found to update.";
    }

    header("Location: employee_pass.php");
    exit(0);
}



if (isset($_POST['generate_pass'])) {
    $masterid = $_SESSION['auth_user']['user_master_id'];
    $employeeId = $_POST['employee_id'];
    $passDate = date('Y-m-d'); // current date
    $passTime = $_POST['pass_time']; // in HH:MM format
    $passType = $_POST['pass_type'];


    // Combine date and time and check if it's in the past
    $selectedDateTime = strtotime("$passDate $passTime");
    $currentDateTime = time();

    if ($selectedDateTime < $currentDateTime) {
        $_SESSION['message'] = "You cannot select a past time.";
        header("Location: employee_pass.php");
        exit(0);
    }

    // Convert to 24-hour format (already should be if using <input type="time">)
    $formattedTime = date("H:i:s", strtotime($passTime));

    $insert = "INSERT INTO employee_pass_info (masterid, employee_id, employee_pass_date, employee_pass_time, employee_pass_type) 
               VALUES ('$masterid', '$employeeId', '$passDate', '$formattedTime', '$passType')";
    $run = mysqli_query($con, $insert);

    if ($run) {
        $_SESSION['message'] = "Pass generated successfully!";
        header("Location: employee_pass.php");
        exit(0);
    } else {
        $_SESSION['message'] = "Failed to generate pass.";
        header("Location: employee_pass.php");
        exit(0);
    }
}


?>