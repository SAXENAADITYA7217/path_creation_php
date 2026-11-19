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
                    <h4>Add Result
                        <a href="result-view.php" class="btn btn-danger float-end">View Result</a>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        <div class="row">
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
                                <label for="username">Username</label>
                                <select name="id" id="user_id" required class="form-control">
                                    <option value="">-- Select User --</option>
                                    <!-- Users will be dynamically loaded here based on class selection -->
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="exam_type">Exam Type:</label>
                                <select name="exam_type" id="exam_type" class="form-control" required>
                                    <option value="">-- Select Exam Type --</option>
                                    <option value="unit_test_1">Unit Test 1</option>
                                    <option value="unit_test_2">Unit Test 2</option>
                                    <option value="unit_test_3">Unit Test 3</option>
                                    <option value="unit_test_4">Unit Test 4</option>
                                    <option value="half_year">Half Year Exam</option>
                                    <option value="final">Final Exam</option>
                                    <option value="class_test">Class Test</option>
                                </select>
                            </div>

                            <div id="subjects-container">
                                <!-- Subjects will be dynamically added here based on class selection -->
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="date">Date:</label>
                                <input type="date" id="date" name="date" required class="form-control">
                            </div>

                            <div class="col-md-12 mb-3">
                                <button type="submit" name="marks_add" class="btn btn-primary">Save Data</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const classSelect = document.getElementById('class');
    const subjectsContainer = document.getElementById('subjects-container');

    classSelect.addEventListener('change', function() {
        const selectedClass = this.value;
        let subjectsHtml = '';

        if (selectedClass === '8' || selectedClass === '9' || selectedClass === '10') {
            subjectsHtml = `
              <div class="row">
                <div class="col-md-4 mb-3"><label for="english">English</label><input type="text" name="english" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="hindi">Hindi</label><input type="text" name="hindi" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="maths">Maths</label><input type="text" name="maths" class="form-control"></div>
              </div>
              <div class="row">  
                <div class="col-md-4 mb-3"><label for="science">Science</label><input type="text" name="science" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="sst">S.S.T</label><input type="text" name="sst" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="computer_it">Computer IT</label><input type="text" name="computer_it" class="form-control"></div></div>`;
        } else if (selectedClass === '11' || selectedClass === '15') {
            subjectsHtml = `
              <div class="row">
                <div class="col-md-4 mb-3"><label for="english">English</label><input type="text" name="english" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="hindi">Hindi</label><input type="text" name="hindi" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="physics">Physics</label><input type="text" name="physics" class="form-control"></div>
              </div>
              <div class="row">   
                <div class="col-md-4 mb-3"><label for="chemistry">Chemistry</label><input type="text" name="chemistry" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="biology">Biology</label><input type="text" name="biology" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="physical_education">Physical Education</label><input type="text" name="physical_education" class="form-control"></div></div>`;
        } else if (selectedClass === '12' || selectedClass === '16') {
            subjectsHtml = `
              <div class="row">
                <div class="col-md-4 mb-3"><label for="english">English</label><input type="text" name="english" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="hindi">Hindi</label><input type="text" name="hindi" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="physics">Physics</label><input type="text" name="physics" class="form-control"></div>
              </div>
              <div class="row">  
                <div class="col-md-4 mb-3"><label for="chemistry">Chemistry</label><input type="text" name="chemistry" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="maths">Maths</label><input type="text" name="maths" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="physical_education">Physical Education</label><input type="text" name="physical_education" class="form-control"></div></div>`;
        } else if (selectedClass === '13' || selectedClass === '17') {
            subjectsHtml = `
              <div class="row">
                <div class="col-md-4 mb-3"><label for="english">English</label><input type="text" name="english" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="hindi">Hindi</label><input type="text" name="hindi" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="account">Account</label><input type="text" name="account" class="form-control"></div>
              </div>
              <div class="row">  
                <div class="col-md-4 mb-3"><label for="economics">Economics</label><input type="text" name="economics" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="business">Business</label><input type="text" name="business" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="physical_education">Physical Education</label><input type="text" name="physical_education" class="form-control"></div></div>`;
        } else if (selectedClass === '14' || selectedClass === '18') {
            subjectsHtml = `
              <div class="row">
                <div class="col-md-4 mb-3"><label for="english">English</label><input type="text" name="english" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="hindi">Hindi</label><input type="text" name="hindi" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="pol_science">Political Science</label><input type="text" name="pol_science" class="form-control"></div>
              </div>
              <div class="row">  
                <div class="col-md-4 mb-3"><label for="economics">Economics</label><input type="text" name="economics" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="history">History</label><input type="text" name="history" class="form-control"></div>
                <div class="col-md-4 mb-3"><label for="physical_education">Physical Education</label><input type="text" name="physical_education" class="form-control"></div></div>`;
        }

        subjectsContainer.innerHTML = subjectsHtml;

        // Load users based on the selected class
        $.ajax({
            url: 'code.php',
            type: 'POST',
            data: { class: selectedClass },
            success: function(response) {
                $('#user_id').html(response);
            }
        });
    });
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
