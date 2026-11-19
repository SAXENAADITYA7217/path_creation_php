<?php
session_start();
include('../admin/config/dbcon.php');

if (isset($_POST['email'], $_POST['password'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $login_query = "SELECT * FROM users WHERE email='$email' AND password='$password' LIMIT 1";
    $login_query_run = mysqli_query($con, $login_query);

    if (mysqli_num_rows($login_query_run) > 0) {
        $data = mysqli_fetch_assoc($login_query_run); // Fetch the data

        $user_id = $data['id'];
        $user_name = $data['fname'] . ' ' . $data['lname'];
        $user_email = $data['email'];
        $role_as = $data['role_as'];

        $_SESSION['auth'] = true;
        $_SESSION['auth_role'] = $role_as;  // 1-admin, 0-user
        $_SESSION['auth_user'] = [
            'user_id' => $user_id,
            'user_name' => $user_name,
            'user_email' => $user_email,
        ];

        $response = []; // Define $response array

        if ($_SESSION['auth_role'] == '1') {
            $response['message'] = "Welcome to the dashboard";
        } elseif ($_SESSION['auth_role'] == '0') {
            $response['message'] = "Welcome to logged in";
        }
        $response['user_info'] = [
            'user_id' => $user_id,
            'user_name' => $user_name,
            'user_email' => $user_email,
            'role_as' => $role_as
        ];
        echo json_encode($response);
    } else {
        $response['error'] = "001";
        $response['message'] = "Invalid Email or Password";
        echo json_encode($response);
    }
} else {
    echo "You are not allowed to access this file";
    exit(0);
}
?>
