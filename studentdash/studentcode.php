<?php
session_start();
include('../admin/config/dbcon.php');


if (!isset($_SESSION['auth_user'])) {
    header("Location: ../profile.php"); // Redirect to index page if not logged in
    exit();
}

// Assuming you've already connected to the database ($con)

$user_id = $_SESSION['auth_user']['user_id'];

// Fetch user details
$user_query = "SELECT * FROM users WHERE id='$user_id'";
$user_result = mysqli_query($con, $user_query);

if (mysqli_num_rows($user_result) == 1) {
    $user_row = mysqli_fetch_assoc($user_result);
    $full_name = $user_row['fname'] . ' ' . $user_row['lname'];
    $email = $user_row['email'];
    $class = $user_row['class'];
    // Add more fields as needed
} else {
    // Handle the case where the user is not found in the database
    echo "User not found.";
    exit();
}

// Fetch user results
$result_query = "SELECT * FROM results WHERE id='$user_id'";
$result_result = mysqli_query($con, $result_query);



// Fetch user attendance 
/*
$attendance_query = "SELECT * FROM attendance WHERE user_id='$user_id'";
$user_attendance = mysqli_query($con, $attendance_query); */

if (isset($_POST['upload_photo']) && isset($_FILES['user_photo']) && $_FILES['user_photo']['error'] === UPLOAD_ERR_OK) {

    $user_id = $_SESSION['auth_user']['user_id'];

    // Allowed types and max size
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $file_tmp = $_FILES['user_photo']['tmp_name'];
    $file_name = $_FILES['user_photo']['name'];
    $mime_type = mime_content_type($file_tmp);
    $file_size = $_FILES['user_photo']['size'];

    // ✅ Validate type and size
    if (!in_array($mime_type, $allowed_types)) {
        $_SESSION['message'] = "🚫 Invalid file type. Only JPG, PNG, GIF allowed.";
        header("Location: student_profile.php");
        exit();
    }
    if ($file_size > $max_size) {
        $_SESSION['message'] = "🚫 File too large. Max size 5MB.";
        header("Location: student_profile.php");
        exit();
    }

    // ✅ Generate unique name
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;

    $upload_dir = 'uploads/profile_image/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $upload_path = $upload_dir . $new_filename;

    // ✅ Get old photo
    $query = "SELECT user_photo FROM users WHERE id = ?";
    $stmt_old = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt_old, "s", $user_id);
    mysqli_stmt_execute($stmt_old);
    mysqli_stmt_bind_result($stmt_old, $old_photo);
    mysqli_stmt_fetch($stmt_old);
    mysqli_stmt_close($stmt_old);

    // ✅ Move file
    if (move_uploaded_file($file_tmp, $upload_path)) {
        // Delete old
        if (!empty($old_photo) && file_exists($upload_dir . $old_photo)) {
            unlink($upload_dir . $old_photo);
        }

        // ✅ Update DB
        $stmt = mysqli_prepare($con, "UPDATE users SET user_photo = ?, user_photo_type = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sss", $new_filename, $mime_type, $user_id);
        $executed = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $_SESSION['message'] = $executed
            ? "✅ Profile photo uploaded successfully!"
            : "🚫 Database error: " . mysqli_error($con);
    } else {
        $_SESSION['message'] = "🚫 Failed to move uploaded file.";
    }

    header("Location: profile.php");
    exit();
}



// ================================
// 📩 Handle Complaint Submission
// ================================
if (isset($_POST['submit_complaint'])) {

    // Database connection already available as $con
    $id = $_SESSION['auth_user']['user_id'] ?? null; // from auth (this is the "id" column)
    $message = trim($_POST['message'] ?? '');
    $role_as = 0; // student role

    if ($id && !empty($message)) {

        // ✅ Step 1: Fetch user_id and class using id
        $query = "SELECT user_id, class FROM users WHERE id = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $user_id, $class_code);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        // ✅ Step 2: Check if we got valid user data
        if (!empty($user_id) && !empty($class_code)) {
            $insert = "INSERT INTO complaints (user_id, message, class_code, role_as) VALUES (?, ?, ?, ?)";
            $stmt2 = mysqli_prepare($con, $insert);
            mysqli_stmt_bind_param($stmt2, "ssii", $user_id, $message, $class_code, $role_as);

            if (mysqli_stmt_execute($stmt2)) {
                $_SESSION['complaint_success'] = "✅ Your complaint has been submitted successfully!";
            } else {
                $_SESSION['complaint_error'] = "⚠️ Failed to submit complaint. Please try again.";
            }

            mysqli_stmt_close($stmt2);
        } else {
            $_SESSION['complaint_error'] = "⚠️ Unable to find your user details. Please re-login.";
        }

    } else {
        $_SESSION['complaint_error'] = "⚠️ Please write your complaint before submitting.";
    }

    // ✅ Redirect to avoid resubmission
    header("Location: viewcomplaints.php");
    exit();
}





// ================================
// 🚮 Delete Complaint Logic
// ================================
if (isset($_POST['delete_complaint_btn'])) {
    $complaint_id = mysqli_real_escape_string($con, $_POST['delete_complaint_id']);
    
    // Optional: check ownership — only delete if it belongs to this user
    $auth_id = $_SESSION['auth_user']['user_id'];
    $userQuery = "SELECT user_id FROM users WHERE id = '$auth_id' LIMIT 1";
    $userResult = mysqli_query($con, $userQuery);
    $userData = mysqli_fetch_assoc($userResult);
    $user_unique_id = $userData['user_id'];

    // Ensure the complaint belongs to the current user
    $checkQuery = "SELECT * FROM complaints WHERE id='$complaint_id' AND user_id='$user_unique_id' LIMIT 1";
    $checkResult = mysqli_query($con, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        $deleteQuery = "DELETE FROM complaints WHERE id='$complaint_id'";
        $deleteResult = mysqli_query($con, $deleteQuery);

        if ($deleteResult) {
            $_SESSION['message'] = "Complaint deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Something went wrong while deleting!";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Unauthorized delete attempt!";
        $_SESSION['message_type'] = "warning";
    }

    header("Location: viewcomplaints.php");
    exit();
}



















?>
