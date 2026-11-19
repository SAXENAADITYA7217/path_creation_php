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
                    <h4>Add Student Profile
                        <a href="profile-view.php" class="btn btn-danger float-end">View Profiles</a>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="class">Class</label>
                                <select name="class" id="class" required class="form-control">
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
                                    <!-- Add more classes as needed -->
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="user_id">Student Name</label>
                                <select name="user_id" id="user_id" required class="form-control">
                                    <option value="">-- Select User --</option>
                                </select>
                            </div>
                        
                            <div class="col-md-6 mb-3">
                                <label for="father_name">Father's Name</label>
                                <input type="text" name="father_name" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email">Email ID</label>
                                <input type="email" name="student_email" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="photo">Photo (200x200 jpeg)</label>
                                <input type="file" name="photo" accept="image/jpeg" required class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="address">Address</label>
                                <textarea name="address" required class="form-control" rows="4"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender">Gender</label>
                                <select name="gender" required class="form-control">
                                    <option value="">-- Select Gender --</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="dob">Date of Birth</label>
                                <input type="date" name="dob" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nationality">Nationality</label>
                                <input type="text" name="nationality" required class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <button type="submit" name="profile_add" class="btn btn-primary">Save Profile</button>
                            </div>
                        </div>
                    </form>
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
