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
                    <h4>View Results
                        <a href="print_report_card.php" class="btn btn-outline-success float-end ms-2"><i class="fas fa-file-invoice me-1"></i> Print Report Cards</a>
                        <a href="result-add.php" class="btn btn-primary float-end">Add Result</a>
                    </h4>
                </div>
                <div class="card-body">
                    <form id="resultForm" method="GET" class="mb-4">
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

                        <div class="mb-3">
                            <label for="user_id" class="form-label">Select User:</label>
                            <select name="id" id="user_id" class="form-control" required>
                                <option value="">-- Select User --</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Show Results</button>
                    </form>

                    <?php
                    if (isset($_GET['id']) && isset($_GET['class'])) {
                        $class = $_GET['class'];
                        $userId = $_GET['id'];

                        // Determine the table based on the selected class
                        $table = '';
                        $subjects = [];
                        switch ($class) {
                            case '8':
                            case '9':
                            case '10':
                                $table = 'result';
                                $subjects = ['english', 'hindi', 'maths', 'science', 'sst', 'computer_it'];
                                break;
                            case '11':
                            case '15':
                                $table = 'results_pcb';
                                $subjects = ['english', 'hindi', 'physics', 'chemistry', 'biology', 'physical_education'];
                                break;
                            case '12':
                            case '16':
                                $table = 'results_pcm';
                                $subjects = ['english', 'hindi', 'physics', 'chemistry', 'maths', 'physical_education'];
                                break;
                            case '13':
                            case '17':
                                $table = 'results_comm';
                                $subjects = ['english', 'hindi', 'account', 'economics', 'business', 'physical_education'];
                                break;
                            case '14':
                            case '18':
                                $table = 'results_arts';
                                $subjects = ['english', 'hindi', 'pol_science', 'economics', 'history', 'physical_education'];
                                break;
                            default:
                                echo "Invalid class selection";
                                break;
                        }

                        // Fetch data from the appropriate table
                        $query = "SELECT u.fname, u.lname, u.class, r.*, r.exam_type FROM $table r JOIN users u ON r.user_id = u.id WHERE r.user_id = '$userId'";
                        $result = mysqli_query($con, $query);

                        if ($result && mysqli_num_rows($result) > 0) {
                            $classMapping = [
                                '6' => 'Class 6',
                                '7' => 'Class 7',
                                '8' => 'Class 8',
                                '9' => 'Class 9',
                                '10' => 'Class 10',
                                '11' => 'Class 11 Sci (PCB)',
                                '12' => 'Class 11 Sci (PCM)',
                                '13' => 'Class 11 Comm.',
                                '14' => 'Class 11 Arts',
                                '15' => 'Class 12 Sci (PCB)',
                                '16' => 'Class 12 Sci (PCM)',
                                '17' => 'Class 12 Comm.',
                                '18' => 'Class 12 Arts'
                            ];

                            // Display the results
                            echo "<div class='table-responsive'>";
                            echo "<table class='table table-bordered'>";
                            echo "<thead>";
                            echo "<tr>";
                            echo "<th>Name</th>";
                            echo "<th>Class</th>";
                            echo "<th>Exam Type</th>";
                            foreach ($subjects as $subject) {
                                echo "<th>" . ucfirst(str_replace('_', ' ', $subject)) . "</th>";
                            }
                            echo "<th>Edit</th>"; // Add header for edit button
                            echo "<th>Delete</th>"; // Add header for delete button
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                            while ($row = mysqli_fetch_assoc($result)) {
                                $fullName = $row['fname'] . ' ' . $row['lname'];
                                echo "<tr>";
                                echo "<td>$fullName</td>";
                                echo "<td>" . (isset($classMapping[$row['class']]) ? $classMapping[$row['class']] : 'Unknown') . "</td>";
                                echo "<td>{$row['exam_type']}</td>";
                                foreach ($subjects as $subject) {
                                    echo "<td>{$row[$subject]}</td>";
                                }
                                echo "<td><a href='result-edit.php?id={$row['id']}' class='btn btn-success'>Edit</a></td>"; // Edit button
                                echo "<td>
                                        <form action='code.php' method='POST' onsubmit='return confirm(\"Are you sure you want to delete this result?\");'>
                                            <input type='hidden' name='id' value='{$row['id']}'>
                                            <button type='submit' name='delete_result_btn' class='btn btn-danger'>Delete</button>
                                        </form>
                                      </td>"; // Delete button
                                echo "</tr>";
                            }
                            echo "</tbody>";
                            echo "</table>";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-warning' role='alert'>No results found.</div>";
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
                url: 'code.php', // Change this to the appropriate PHP script to fetch users based on class
                type: 'POST',
                data: {class: selectedClass},
                success: function(response) {
                    $('#user_id').html(response);
                }
            });
        });
    });
</script>
