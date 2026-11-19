<?php  
include('includes/header.php');
include('message.php');

// Check if encrypted ID is passed
if (isset($_GET['usGSDsjnwSG'])) {

    // Decode and extract real ID
    $encoded_id = $_GET['usGSDsjnwSG'];
    $decoded = base64_decode($encoded_id);

    // Your secret key must match the one used during encoding
    $secret_key = "DBHS54864asb#@W()";
    $id = str_replace([$secret_key, "ENDKEY"], "", $decoded);

    // Fetch the form details from database
    $query = "SELECT * FROM form_data WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
?>

        <div class="container mt-5 mb-5">
            <div class="card shadow-lg border-success">
                <div class="card-header bg-success text-white text-center">
                    <h3>🎉 Form Submitted Successfully!</h3>
                    <p>Your form has been successfully recorded.</p>
                </div>
                <div class="card-body">
                    <h5 class="text-primary mb-3">Form Details</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>Student Name</th>
                            <td><?= htmlspecialchars($data['student_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Father's Name</th>
                            <td><?= htmlspecialchars($data['student_father_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Date of Birth</th>
                            <td><?= htmlspecialchars($data['student_dob']) ?></td>
                        </tr>
                        <tr>
                            <th>School Name</th>
                            <td><?= htmlspecialchars($data['student_school_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Coaching Name</th>
                            <td><?= htmlspecialchars($data['student_coaching_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Phone No. 1</th>
                            <td><?= htmlspecialchars($data['phone_no_1']) ?></td>
                        </tr>
                        <tr>
                            <th>Phone No. 2</th>
                            <td><?= htmlspecialchars($data['phone_no_2']) ?></td>
                        </tr>
                        <tr>
                            <th>Class</th>
                            <td><?= htmlspecialchars($data['student_class']) ?></td>
                        </tr>
                    </table>

                    <div class="text-center mt-4">
                        <a href="exam_syllabus.php" class="btn btn-primary">View Exam Syllabus</a>
                    </div>
                </div>
            </div>
        </div>

    <?php
        } else {
            echo "<div class='alert alert-danger text-center mt-5'>Invalid Form ID or Record not found.</div>";
        }

        $stmt->close();
    } else {
        echo "<div class='alert alert-warning text-center mt-5'>No form data available.</div>";
    }    
   ?>

    <?php 
        include('includes/layout_ashna_ai.php');
    ?>


<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
