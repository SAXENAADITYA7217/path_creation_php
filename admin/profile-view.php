<?php
include('authentication.php');
include('includes/header.php');
?>

<div class="container-fluid px-4">
    <div class="row mt-4">
        <div class="col-md-12">
            <?php include('message.php'); ?>

            <div class="card">
                <div class="card-header">
                    <h4>Profile View
                        <a href="profile-add.php" class="btn btn-primary float-end">Add Profile</a>
                    </h4>
                </div>
                <div class="card-body">

                    <form id="profileForm" method="GET">
                        <div class="mb-3">
                            <label for="class">Select Class:</label>
                            <select name="class" id="class" class="form-control" required>
                                <option value="">-- Select Class --</option>
                                    <option value="6">Class 6</option>
                                    <option value="7">Class 7</option>
                                    <option value="8">Class 8</option>
                                    <option value="9">Class 9</option>
                                    <option value="10">Class 10</option>
                                    <option value="11">Class 11 Sci (PCB)</option>
                                    <option value="12">Class 11 Sci (PCM)</option>
                                    <option value="13">Class 11 Comm.</option>
                                    <option value="14">Class 11 Arts</option>
                                    <option value="15">Class 12 Sci (PCB)</option>
                                    <option value="16">Class 12 Sci (PCM)</option>
                                    <option value="17">Class 12 Comm.</option>
                                    <option value="18">Class 12 Arts</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="user_id">Select User:</label>
                            <select name="id" id="user_id" class="form-control" required>
                                <option value="">-- Select User --</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Show Data</button>
                    </form>

                    <?php
                    if (isset($_GET['id'])) {
                        $selectedUserId = $_GET['id'];
                        $profileShowQuery = "SELECT * FROM student WHERE user_id = '$selectedUserId'";
                        $profileShowRun = mysqli_query($con, $profileShowQuery);

                        if (mysqli_num_rows($profileShowRun) > 0) {
                            while ($profile = mysqli_fetch_assoc($profileShowRun)) {
                                ?>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <img src="../uploads/students/<?= $profile['photo'] ?>" class="img-fluid rounded" alt="Profile Photo">
                                            <div class="mt-3 text-left">
                                                <h5>Name: <?= $profile['name'] ?></h5>
                                                <p class="text-muted">Class: <?= $profile['class'] ?>th</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label>Father's Name</label>
                                            <p class="form-control"><?= $profile['father_name'] ?></p>
                                        </div>
                                        <div class="mb-3">
                                            <label>Email ID</label>
                                            <p class="form-control"><?= $profile['student_email'] ?></p>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label>Gender</label>
                                                <p class="form-control"><?= $profile['gender'] ?></p>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Date of Birth</label>
                                                <p class="form-control"><?= date('d-m-Y', strtotime($profile['dob'])) ?></p>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Nationality</label>
                                                <p class="form-control"><?= $profile['nationality'] ?></p>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label>Address</label>
                                            <p class="form-control"><?= $profile['address'] ?></p>
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <a href="profile-edit.php?id=<?= $profile['id'] ?>" class="btn btn-success me-2">Edit Profile</a>
                                            <form action="code.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this profile?');">
                                                <input type="hidden" name="profile_delete" value="<?= $profile['id'] ?>">
                                                <button type="submit" class="btn btn-danger">Delete Profile</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p>No profiles found for the selected user ID.</p>';
                        }
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('#class').change(function() {
            var selectedClass = $(this).val();
            $.ajax({
                url: 'code.php',
                type: 'POST',
                data: {class: selectedClass},
                success: function(response) {
                    $('#user_id').html(response);
                }
            });
        });
    });
</script>
