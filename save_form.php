<?php
// Database connection
session_start();
include('admin/config/dbcon.php');

if(isset($_POST['save_from_data']))
{
// Generate unique form_id (for example, using timestamp)
$form_id = "F001";

// Get form data from POST
$student_name = $_POST['name'];
$student_father_name = $_POST['father_name'];
$student_dob = $_POST['dob'];
$student_school_name = $_POST['school_name'];
$student_coaching_name = $_POST['coaching_name'];
$phone_no_1 = $_POST['phoneno_1'];
$phone_no_2 = $_POST['phoneno_2'];
$student_class = $_POST['class'];

// Prepare SQL query
$sql = "INSERT INTO form_data (form_id, student_name, student_father_name, student_dob, student_coaching_name, student_school_name, phone_no_1, phone_no_2, student_class) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $con->prepare($sql);
$stmt->bind_param("sssssssss", $form_id, $student_name, $student_father_name, $student_dob, $student_coaching_name, $student_school_name, $phone_no_1, $phone_no_2, $student_class);

// Execute query
if ($stmt->execute()) {

    // Get inserted ID
    $last_id = $stmt->insert_id;

    // Custom encryption key/salt
    $secret_key = "DBHS54864asb#@W()";

    // Combine key with ID
    $raw_value = $secret_key . $last_id . "ENDKEY";

    // Encode it
    $encoded_id = base64_encode($raw_value);

    $_SESSION['message'] = "Form submitted successfully!";
    header("Location: form_confirmation.php?usGSDsjnwSG=" . urlencode($encoded_id));
    exit(0);
} else {
    $_SESSION['message'] = "Your from is not fill";
    header("Location: index.php");
    exit(0);
}

$stmt->close();
$con->close();

}else{
    $_SESSION['message'] = "Your from is not fill";
    header("Location: index.php");
    exit(0);
}

?>
