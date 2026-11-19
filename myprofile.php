<?php
session_start();
include('admin/config/dbcon.php');

if (!isset($_SESSION['auth_user'])) {
    header("Location: index.php"); // Redirect to index page if not logged in
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
    // Add more fields as needed
} else {
    // Handle the case where the user is not found in the database
    echo "User not found.";
    exit();
}

// Fetch user results
$result_query = "SELECT * FROM results WHERE id='$user_id'";
$result_result = mysqli_query($con, $result_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <!-- Add Bootstrap CSS link -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add any additional styles or scripts here -->
</head>

<body>

    <!-- Your navigation or header section goes here -->

    <div class="container mt-5">
        <h1>Welcome, <?php echo $full_name; ?>!</h1>
        <p>Email: <?php echo $email; ?></p>

        <h2>Result Details</h2>

        <?php if (mysqli_num_rows($result_result) > 0) { ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Roll no</th>
                        <th>Math Marks</th>
                        <th>Science Marks</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($result_row = mysqli_fetch_assoc($result_result)) { ?>
                        <tr>
                            <td><?php echo ($result_row['id'] - 1); ?></td>
                            <td><?php echo $result_row['mathmarks']; ?></td>
                            <td><?php echo $result_row['sciencemarks']; ?></td>
                            <td><?php echo $result_row['date']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No results found for the user.</p>
        <?php } ?>

        <!-- Add any additional content or features for the profile page -->

        <a href="logout.php" class="btn btn-danger">Logout</a> <!-- You can create a logout page to destroy the session -->

    </div>

    <!-- Your footer section goes here -->

    <!-- Add Bootstrap JS and Popper.js scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
