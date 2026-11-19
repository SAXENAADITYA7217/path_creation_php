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
                    <h4>Edit Profile
                        <a href="profile-view.php" class="btn btn-danger float-end">Back</a>
                    </h4>
                </div>
                <div class="card-body">
                    <?php 
                    if(isset($_GET['id'])) {
                        $profile_id = $_GET['id'];
                        $profile_query = "SELECT * FROM student WHERE id='$profile_id' LIMIT 1";
                        $profile_query_res = mysqli_query($con, $profile_query);

                        if(mysqli_num_rows($profile_query_res) > 0) {
                            $profile_row = mysqli_fetch_array($profile_query_res);
                            ?>
                            <form action="code.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="profile_id" value="<?= $profile_row['id'] ?>">
                                <input type="hidden" name="user_id" value="<?= $profile_row['user_id'] ?>">
                                <input type="hidden" name="old_photo" value="<?= $profile_row['photo'] ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="">Name</label>
                                        <input type="text" name="name" value="<?= $profile_row['name'] ?>" required class="form-control" readonly>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="">Father's Name</label>
                                        <input type="text" name="father_name" value="<?= $profile_row['father_name'] ?>" required class="form-control">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="">Email ID</label>
                                        <input type="email" name="student_email" value="<?= $profile_row['student_email'] ?>" required class="form-control">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="">Gender</label>
                                        <select name="gender" class="form-control" required>
                                            <option value="Male" <?= $profile_row['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                                            <option value="Female" <?= $profile_row['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                                            <option value="Other" <?= $profile_row['gender'] == 'Other' ? 'selected' : '' ?>>Other</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="">Date of Birth</label>
                                        <input type="date" name="dob" value="<?= $profile_row['dob'] ?>" required class="form-control">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="">Nationality</label>
                                        <input type="text" name="nationality" value="<?= $profile_row['nationality'] ?>" required class="form-control">
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label for="">Address</label>
                                        <textarea name="address" required class="form-control" rows="4"><?= $profile_row['address'] ?></textarea>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="">Profile Photo</label>
                                        <input type="file" name="photo" class="form-control">
                                        <img src="../uploads/students/<?= $profile_row['photo'] ?>" width="100" class="mt-2" alt="Profile Photo">
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <button type="submit" name="profile_update" class="btn btn-primary">Update Profile</button>
                                    </div>
                                </div>
                            </form>
                            <?php
                        } else {
                            ?>
                            <h4>No Record Found</h4>
                            <?php
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
