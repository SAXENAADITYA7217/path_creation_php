<?php
session_start();
include('../admin/config/dbcon.php');


if (!isset($_SESSION['auth_user'])) {
    header("Location: ../index.php"); // Redirect to index page if not logged in
    exit();
}

if($_SESSION['auth_role'] != "3"){
    $_SESSION['message'] = "You are not Authorised as ADMIN";
    header("Location: ../index.php");
    exit(0);
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
  
    // Add more fields as needed
} else {
    // Handle the case where the user is not found in the database
    echo "User not found.";
    exit();
}


?>