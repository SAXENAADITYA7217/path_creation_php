<?php
include('studentcode.php');
include('includes/header.php');

// ✅ Step 1: Check if user is logged in
if (!isset($_SESSION['auth_user'])) {
    header("Location: ../profile.php");
    exit();
}

// ✅ Step 2: Get the user's ID from session (this is users.id)
$auth_id = $_SESSION['auth_user']['user_id'];

// ✅ Step 3: Fetch the user_id (varchar) from users table
$userQuery = "SELECT user_id FROM users WHERE id = '$auth_id' LIMIT 1";
$userResult = mysqli_query($con, $userQuery);

if (mysqli_num_rows($userResult) > 0) {
    $userData = mysqli_fetch_assoc($userResult);
    $user_unique_id = $userData['user_id']; // the varchar user_id from users table

    // ✅ Step 4: Fetch complaints using the user_id
    $complaintQuery = "SELECT * FROM complaints WHERE user_id = '$user_unique_id' ORDER BY id DESC";
    $result = mysqli_query($con, $complaintQuery);
} else {
    $result = false;
}
?>

<div class="container-fluid pt-4 px-4">
    <div class="bg-light rounded p-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold"><i class="fa fa-comments text-primary me-2"></i>Your Submitted Complaints</h4>
        </div>

        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle table-hover">
                    <thead class="bg-primary text-white">
                        <tr>
                            <th scope="col" class="text-center">#</th>
                            <th scope="col" class="text-start">Message</th>
                            <th scope="col" class="text-center">Date Submitted</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td class="text-center"><?= $i++; ?></td>
                            
                            <td class="text-start" style="min-width: 250px; word-break: break-word;">
                                <?= htmlspecialchars($row['message']); ?>
                            </td>
                            
                            <td class="text-center" style="min-width: 150px;">
                                <?= isset($row['created_at']) ? date('d M Y, h:i A', strtotime($row['created_at'])) : 'N/A'; ?>
                            </td>
                            
                            <td class="text-center">
                                <form action="studentcode.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this complaint?');">
                                    <input type="hidden" name="delete_complaint_id" value="<?= $row['id']; ?>">
                                    <button type="submit" name="delete_complaint_btn" class="btn btn-danger btn-sm">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center p-4 p-md-5">
                <i class="fa fa-folder-open fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Complaints Found</h5>
                <p class="text-muted">You have not submitted any complaints yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>