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
                                     <h4>Add Notes
                                        <a href="notes-view.php" class="btn btn-danger float-end">Notes View</a>
                                     </h4>
                                </div>
                                <div class="card-body">

                                <form action="code.php" method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                    <div class="col-md-12 mb-3">
                                            <label for="pdf_name">PDF Name:</label>
                                            <input type="text" name="pdf_name" required class="form-control" placeholder="Enter PDF name">
                                    </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="class">Class</label>
                                            <select name="class" required class="form-control">
                                                <option value="">Select Class</option>
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

                                        <div class="col-md-6 mb-3">
                                            <label for="subject">Subject</label>
                                            <select name="subject" required class="form-control">
                                                <option value="">Select Subject</option>
                                                <option value="Mathematics">Mathematics</option>
                                                <option value="Science">Science</option>
                                                <option value="Hindi">Hindi</option>
                                                <option value="English">English</option>
                                                <option value="History">History</option>
                                                <option value="Economics">Economics</option>
                                                <option value="Civics">Civics</option>
                                                <option value="Science">Physics</option>
                                                <option value="Chemistry">Chemistry</option>
                                                <option value="Maths">Maths</option>
                                                <option value="Biology">Biology</option>
                                                <option value="Physical">Physical Education</option>
                                                <option value="Accounts">Accounts</option>
                                                <option value="Business">Business Studies</option>
                                                <option value="Political">Political Science</option>
                                                <option value="Political">IT (Information Technology)</option>
                                                <!-- Add more subjects as needed -->
                                            </select>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <label for="file">Upload Result PDF:</label>
                                            <input type="file" name="result_file" accept=".pdf" required class="form-control">
                                        </div>

                                        <div class="col-md-6 mb-3">
                                                  <label for="">Status</label>
                                                  <input type="checkbox" name="status" width="70px" height="70px"/>
                                        </div>


                                        <div class="col-md-12 mb-3">
                                            <button type="submit" name="upload_pdf" class="btn btn-primary">Upload</button>
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