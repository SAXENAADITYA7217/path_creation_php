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
                    <h4>Report View
                        <a href="report-add.php" class="btn btn-primary float-end">Add Report</a>
                    </h4>
                </div>
                <div class="card-body">

                    <form action="report-view.php" method="GET">
                        <div class="col-md-12 mb-3">
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
                        <div class="col-md-12 mb-3">
                            <label for="id">Select User ID:</label>
                            <select name="id" id="user_id" class="form-control" required>
                                <option value="">-- Select User --</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Show Data</button>
                    </form>

                    <?php
                    if (isset($_GET['id']) && !empty($_GET['id'])) {
                        $selectedUserId = mysqli_real_escape_string($con, $_GET['id']);
                        $reportShowQuery = "
                            SELECT reports.*, users.rollno, users.fname, users.lname 
                            FROM reports 
                            JOIN users ON reports.id = users.id 
                            WHERE reports.id = '$selectedUserId'
                        ";
                        $reportShowRun = mysqli_query($con, $reportShowQuery);

                        if (mysqli_num_rows($reportShowRun) > 0) {
                            echo '<div class="table-responsive">';
                            echo '<table class="table table-bordered table-striped">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th>Roll no.</th>';
                            echo '<th>Name</th>';
                            echo '<th>Aptitude IQ</th>';
                            echo '<th>Physical Strength</th>';
                            echo '<th>English Reading</th>';
                            echo '<th>English Writing</th>';
                            echo '<th>Hindi Reading</th>';
                            echo '<th>Hindi Writing</th>';
                            echo '<th>Typing Speed</th>';
                            echo '<th>Rating</th>';
                            echo '<th>Date</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody>';

                            while ($report = mysqli_fetch_assoc($reportShowRun)) {
                                echo '<tr>';
                                echo '<td>'.htmlspecialchars($report['rollno']).'</td>';
                                echo '<td>'.htmlspecialchars($report['fname']).' '.htmlspecialchars($report['lname']).'</td>';
                                echo '<td>'.htmlspecialchars($report['math_marks']).'</td>';
                                echo '<td>'.htmlspecialchars($report['science_marks']).'</td>';
                                echo '<td>'.htmlspecialchars($report['english_read_marks']).'</td>';
                                echo '<td>'.htmlspecialchars($report['english_write_marks']).'</td>';
                                echo '<td>'.htmlspecialchars($report['hindi_read_marks']).'</td>';
                                echo '<td>'.htmlspecialchars($report['hindi_write_marks']).'</td>';
                                echo '<td>'.htmlspecialchars($report['typing_speed']).'</td>';
                                echo '<td>'.htmlspecialchars($report['student_rating']).'</td>';
                                echo '<td>'.date('d-m-y', strtotime($report['date'])).'</td>';
                                echo '</tr>';
                            }

                            echo '</tbody>';
                            echo '</table>';
                            echo '</div>';
                        } else {
                            echo '<p>No reports found for the selected user ID.</p>';
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
