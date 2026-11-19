<?php
include('authentication.php');

if(isset($_POST['promote_students'])){

    $students       = $_POST['students'];
    $old_class_id   = $_POST['old_class_id'];
    $old_session_id = $_POST['old_session_id'];
    $new_class_id   = $_POST['new_class_id'];
    $new_session_id = $_POST['new_session_id'];

    date_default_timezone_set("Asia/Kolkata");
    $today = date('Y-m-d');

    foreach($students as $student_id){

        // 1️⃣ Update current class
        mysqli_query($con,"
            UPDATE erp_student_detail
            SET student_current_class_id='$new_class_id'
            WHERE student_id='$student_id'
        ");

        // 2️⃣ Insert class history
        mysqli_query($con,"
            INSERT INTO erp_student_class_data
            (student_id, class_id, session_id, class_status)
            VALUES
            ('$student_id','$new_class_id','$new_session_id','0')
        ");

        // 3️⃣ Insert fee row (new session)
        mysqli_query($con,"
            INSERT INTO erp_student_fee_detail
            (student_id,class_id,session_id,
             student_previous_fee,student_discount_fee,student_advance_fee,
             student_fee_date,student_fee_status)
            VALUES
            ('$student_id','$new_class_id','$new_session_id',
             '0','0','0','$today','0')
        ");
    }

    $_SESSION['message'] = "Students Promoted Successfully";
    header("Location: update_student_detail.php");
    exit();
}

/////////////////////////////////////////////////////////////////////////////////
if (isset($_POST['update_student_detail'])) {

    $student_id = $_POST['student_id'];
    $class_id   = $_POST['class_id'];
    $session_id = $_POST['session_id'];

    // student detail update
    mysqli_query($con, "
        UPDATE erp_student_detail SET
            student_name = '{$_POST['student_name']}',
            student_father_name = '{$_POST['student_father_name']}',
            student_phone_no = '{$_POST['student_phone']}',
            student_phone_no_2 = '{$_POST['student_second_phone']}',
            aadhaar_card_no = '{$_POST['student_aadhaar_number']}',
            student_pen_no = '{$_POST['student_pen_number']}'
        WHERE student_id = '$student_id'
    ");

    // fee update (VERY IMPORTANT)
    mysqli_query($con, "
        UPDATE erp_student_fee_detail SET
            student_previous_fee = '{$_POST['previous_balance']}',
            student_discount_fee = '{$_POST['fee_discount']}',
            student_advance_fee = '{$_POST['advance_fee']}'
        WHERE student_id = '$student_id'
          AND class_id = '$class_id'
          AND session_id = '$session_id'
    ");

    $_SESSION['message'] = "Student Updated Successfully";
    header("Location: view_student_detail.php");
    exit();
}


/////////////////////////////////////////////////////////////
if (isset($_POST['save_fee_slip'])) {

    $student_id  = mysqli_real_escape_string($con, $_POST['student_id']);
    $class_id    = mysqli_real_escape_string($con, $_POST['class_id']);
    $session_id  = mysqli_real_escape_string($con, $_POST['session_id']);

    $payment_type  = mysqli_real_escape_string($con, $_POST['payment_type']);
    $transaction_id = mysqli_real_escape_string($con, $_POST['payment_transaction_id']);
    $payment_amount = mysqli_real_escape_string($con, $_POST['payment_amount']);
    $remark         = mysqli_real_escape_string($con, $_POST['other_remark']);

    /*
    if ($old_session_id == $new_session_id) {
    $_SESSION['message'] = "Promotion to the same session is not allowed.";
    header("Location: update_student_detail.php?class_id=$old_class_id&session_id=$old_session_id");
    exit();
    }  */

    $fee_slip_id = 'FS-' . date('YmdHis'); // auto slip number
    $date = date('Y-m-d H:i:s');

    $sql = "
        INSERT INTO erp_fee_slip (
            fee_slip_id,
            student_id,
            class_id,
            session_id,
            payment_type,
            payment_transaction_id,
            payment_amount,
            other_remark,
            fee_slip_status,
            fee_slip_date
        ) VALUES (
            '$fee_slip_id',
            '$student_id',
            '$class_id',
            '$session_id',
            '$payment_type',
            '$transaction_id',
            '$payment_amount',
            '$remark',
            '0',
            '$date'
        )
    ";

    if (mysqli_query($con, $sql)) {
        $_SESSION['message'] = "Fee Slip Saved Successfully";
        header("Location: add_fee_slip.php");
        exit();
    } else {
        $_SESSION['message'] = "Something went wrong!";
        header("Location: add_fee_slip.php");
        exit();
    }
}
///////////////////////////////////////////////////////////////////////////////

if(isset($_POST['add_student_detail']))
{
    // 1. Generate New Student ID (ST00001, ST00002...)
    $id_query = "SELECT student_id FROM erp_student_detail ORDER BY student_id DESC LIMIT 1";
    $id_result = mysqli_query($con, $id_query);

    if(mysqli_num_rows($id_result) > 0) {
        $row = mysqli_fetch_assoc($id_result);
        $last_id = intval(substr($row['student_id'], 2));  // Remove ST
        $new_id = "ST" . str_pad($last_id + 1, 5, '0', STR_PAD_LEFT);
    } else {
        $new_id = "ST00001";
    }

    // 2. Receive form data
    $student_id             = $new_id;
    $student_name           = mysqli_real_escape_string($con, $_POST['student_name']);
    $student_father_name    = mysqli_real_escape_string($con, $_POST['student_father_name']);
    $class_id               = mysqli_real_escape_string($con, $_POST['class_id']);
    $session_id             = mysqli_real_escape_string($con, $_POST['session_id']);
    $phone                  = mysqli_real_escape_string($con, $_POST['student_phone']);
    $phone2                 = mysqli_real_escape_string($con, $_POST['student_second_phone']);
    $aadhaar                = mysqli_real_escape_string($con, $_POST['student_aadhaar_number']);
    $pen                    = mysqli_real_escape_string($con, $_POST['student_pen_number']);
    $previous_balance       = mysqli_real_escape_string($con, $_POST['previous_balance']);
    $fee_discount           = mysqli_real_escape_string($con, $_POST['fee_discount']);
    $advance_fee            = mysqli_real_escape_string($con, $_POST['advance_fee']);
    date_default_timezone_set("Asia/Kolkata");
    $current_date           = date('Y-m-d');

    // Remove spaces from Aadhaar & PAN before saving
    $aadhaar = str_replace(" ", "", $aadhaar);
    $pen = str_replace(" ", "", $pen);

    // check aadhaar number 
    $check_aadhaar_query = "SELECT aadhaar_card_no FROM erp_student_detail WHERE aadhaar_card_no='$aadhaar' LIMIT 1";
    $check_aadhaar_run = mysqli_query($con, $check_aadhaar_query);

    if(mysqli_num_rows($check_aadhaar_run) > 0) {
        $_SESSION['message'] = "Aadhaar Number Already Exists!";
        header("Location: add_student_detail.php");
        exit();
    }

    // 3. Insert into erp_student_detail
    $student_query = "
        INSERT INTO erp_student_detail
        (student_id, student_current_class_id, student_name, student_father_name, student_phone_no, student_phone_no_2, aadhaar_card_no, student_pen_no, student_create_date)
        VALUES
        ('$student_id', '$class_id', '$student_name', '$student_father_name', '$phone', '$phone2', '$aadhaar', '$pen', '$current_date')
    ";
    mysqli_query($con, $student_query);

    // 4. Insert into erp_student_fee_detail
    $fee_query = "
        INSERT INTO erp_student_fee_detail
        (student_id, class_id, session_id, student_previous_fee, student_discount_fee, student_advance_fee, student_fee_date)
        VALUES
        ('$student_id', '$class_id', '$session_id', '$previous_balance', '$fee_discount', '$advance_fee', '$current_date')
    ";
    mysqli_query($con, $fee_query);

    // 5. Insert into erp_student_class_data
    $class_insert_query = "
        INSERT INTO erp_student_class_data
        (student_id, class_id, session_id)
        VALUES
        ('$student_id', '$class_id', '$session_id')
    ";
    mysqli_query($con, $class_insert_query);

    // 6. Redirect with message
    $_SESSION['message'] = "Student Added Successfully (ID: $new_id)";
    header("Location: add_student_detail.php");
    exit();
}


if(isset($_POST['add_class_fee_Data']))
{
    date_default_timezone_set("Asia/Kolkata");
    $today = date("Y-m-d");

    $class_ids     = $_POST['class_id'];      // class_id[cardIndex]
    $fee_types     = $_POST['fee_type_id'];   // flat array
    $fee_amounts   = $_POST['fee_amount'];    // flat array
    $sessions      = $_POST['session_id'];    // flat array
    $class_index   = $_POST['class_index'];   // shows which row belongs to which class card

    // AUTO GENERATE CF ID
    function generateCFID($con) {
        $q = mysqli_query($con, "SELECT class_fee_id FROM erp_class_fee ORDER BY id DESC LIMIT 1");

        if(mysqli_num_rows($q) > 0){
            $data = mysqli_fetch_assoc($q);
            $last = intval(substr($data['class_fee_id'], 2));
            return "CF" . str_pad($last+1, 4, "0", STR_PAD_LEFT);
        }
        return "CF0001";
    }

    foreach($fee_types as $i => $ft)
    {
        $index = $class_index[$i];

        if(
            $class_ids[$index] == "" ||
            $fee_types[$i] == "" ||
            $fee_amounts[$i] == "" ||
            $sessions[$i] == ""
        ){
            continue;
        }

        $class_id = mysqli_real_escape_string($con, $class_ids[$index]);
        $fee_type = mysqli_real_escape_string($con, $fee_types[$i]);
        $amount   = mysqli_real_escape_string($con, $fee_amounts[$i]);
        $session  = mysqli_real_escape_string($con, $sessions[$i]);

        $cf_id = generateCFID($con);

        mysqli_query($con, "
            INSERT INTO erp_class_fee 
            (class_fee_id, class_id, fee_type_id, session_id, class_fee_amount, class_fee_date)
            VALUES
            ('$cf_id', '$class_id', '$fee_type', '$session', '$amount', '$today')
        ");
    }

    $_SESSION['message'] = "Class Fee Data Added Successfully";
    header("Location: add_class_fee.php");
    exit(0);
}


if(isset($_POST['add_session_data']))
{
    // Fetch form values
    $session_name = mysqli_real_escape_string($con, $_POST['session_name']);
    $start_date   = mysqli_real_escape_string($con, $_POST['start_date']);
    $end_date     = mysqli_real_escape_string($con, $_POST['end_date']);

    // ============================
    // AUTO GENERATE SESSION ID
    // ============================
    $id_query = "SELECT session_id FROM erp_session ORDER BY id DESC LIMIT 1";
    $id_run = mysqli_query($con, $id_query);

    if(mysqli_num_rows($id_run) > 0){
        $last_data = mysqli_fetch_assoc($id_run);
        $last_id = $last_data['session_id'];  // e.g., SES005

        // Extract numeric part (005)
        $num = intval(substr($last_id, 3)); 

        // Increase by 1
        $next_num = $num + 1;

        // Format with leading zeros (3 digits)
        $session_id = "SES" . str_pad($next_num, 3, "0", STR_PAD_LEFT);

    } else {
        // If no records found, start from SES001
        $session_id = "SES001";
    }

    // Insert query
    $query = "INSERT INTO erp_session 
                (session_id, session_name, session_start_date, session_end_date) 
              VALUES 
                ('$session_id', '$session_name', '$start_date', '$end_date')";
    
    $query_run = mysqli_query($con, $query);

    if($query_run)
    {
        $_SESSION['message'] = "Session Added Successfully";
        header("Location: add_session.php");
        exit(0);
    }
    else
    {
        $_SESSION['message'] = "Failed to Add Session";
        header("Location: add_session.php");
        exit(0);
    }
}



if(isset($_POST['add_class_Data']))
{
    $classNames = $_POST['class_type_name'];  // Array of class names

    if(empty($classNames)){
        $_SESSION['message'] = "No Class Name Added!";
        header("Location: add_class.php");
        exit(0);
    }

    foreach($classNames as $className)
    {
        if(trim($className) == "") continue;

        // Get last class_id
        $query = "SELECT class_id FROM erp_class_data ORDER BY id DESC LIMIT 1";
        $result = mysqli_query($con, $query);

        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_assoc($result);
            
            // Extract number part: C001 → 1
            $lastNumber = intval(substr($row['class_id'], 1));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1; // First entry
        }

        // Generate new class_id (e.g., C001, C002)
        $newClassID = "C" . str_pad($newNumber, 3, "0", STR_PAD_LEFT);

        // Insert record
        $insertQuery = "INSERT INTO erp_class_data (class_id, class_name) 
                        VALUES ('$newClassID', '$className')";
        mysqli_query($con, $insertQuery);
    }

    $_SESSION['message'] = "Class Data Added Successfully!";
    header("Location: add_class.php");
    exit(0);
}


if (isset($_POST['add_fee_type'])) {

    $feeNames = $_POST['fee_type_name'];
    $feeStatus = $_POST['fee_detail_type_status'];  // New spinner values

    foreach ($feeNames as $index => $feeName) {

        if (trim($feeName) == "") continue;

        // --- Get status (1 = Academic, 2 = Store) ---
        $status = $feeStatus[$index];

        // ---- Get Last Fee Type ID ----
        $last = mysqli_query($con, "SELECT fee_type_id FROM erp_fee_type_detail ORDER BY id DESC LIMIT 1");

        if (mysqli_num_rows($last) > 0) {
            $row = mysqli_fetch_assoc($last);
            $lastId = $row['fee_type_id'];  // Example FTY009

            $num = intval(substr($lastId, 3));  // Extract 009
            $num++;
            $newId = "FTY" . str_pad($num, 3, "0", STR_PAD_LEFT);
        } else {
            $newId = "FTY001";
        }

        // ---- Insert ----
        $insert = "
            INSERT INTO erp_fee_type_detail 
            (fee_type_id, fee_type_name, fee_detail_type_status)
            VALUES ('$newId', '$feeName', '$status')
        ";

        mysqli_query($con, $insert);
    }

    $_SESSION['message'] = "Fee Types Added Successfully!";
    header("Location: add_fee_type.php");
    exit;
}

?>