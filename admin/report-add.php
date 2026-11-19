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
                    <h4>Add Report
                        <a href="result-view.php" class="btn btn-danger float-end">View Report</a>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="class" class="form-label">Select Class:</label>
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
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="user" class="form-label">Username</label>
                                <select name="id" id="user" class="form-control" required>
                                    <option value="">-- Select User --</option>
                                    <!-- Options will be populated dynamically based on the selected class -->
                                </select>
                            </div>
                        
                            <div class="col-md-6 mb-3">
                                <label>Aptitude IQ</label>
                                <input type="text" name="mathmarks" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Physical Strength</label>
                                <input type="text" name="sciencemarks" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Hindi Reading Skill Marks</label>
                                <input type="text" name="hindireadmarks" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Hindi Writing Skill Marks</label>
                                <input type="text" name="hindiwritemarks" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>English Reading Skill Marks</label>
                                <input type="text" name="englishreadmarks" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>English Writing Skill Marks</label>
                                <input type="text" name="englishwritemarks" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Typing Speed (WPM)</label>
                                <input type="text" name="typingspeed" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Student Rating (1-5)</label>
                                <input type="number" min="1" max="5" name="studentrating" required class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date">Date:</label>
                                <input type="date" id="date" name="date" required class="form-control">
                            </div>
                            <div class="col-md-12 mb-3">
                                <button type="submit" name="report_add" class="btn btn-primary">Save Data</button>
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
                    $('#user').html(response);
                }
            });
        });
    });
</script>
