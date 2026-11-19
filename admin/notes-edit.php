<?php
include('authentication.php');
include('includes/header.php');

if(isset($_GET['id'])) {
    $note_id = $_GET['id'];
    $query = "SELECT * FROM notes WHERE id='$note_id' LIMIT 1";
    $query_run = mysqli_query($con, $query);

    if(mysqli_num_rows($query_run) > 0) {
        $note = mysqli_fetch_array($query_run);
    } else {
        $_SESSION['message'] = "No Note Found with this ID.";
        header('Location: notes-view.php');
        exit(0);
    }
} else {
    $_SESSION['message'] = "ID Not Provided.";
    header('Location: notes-view.php');
    exit(0);
}
?>

<div class="container-fluid px-4">
    <div class="row mt-4">
        <div class="col-md-12">
            <?php include('message.php'); ?>
            <div class="card">
                <div class="card-header">
                    <h4>Edit Note
                        <a href="notes-view.php" class="btn btn-danger float-end">Back</a>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="pdf_name">PDF Name:</label>
                                <input type="text" name="pdf_name" value="<?= htmlspecialchars($note['pdf_name']) ?>" required class="form-control" placeholder="Enter PDF name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="class">Class</label>
                                <select name="class" required class="form-control">
                                    <option value="">Select Class</option>
                                    <option value="6" <?= $note['class'] == '6' ? 'selected' : '' ?>>Class 6</option>
                                    <option value="7" <?= $note['class'] == '7' ? 'selected' : '' ?>>Class 7</option>
                                    <option value="8" <?= $note['class'] == '8' ? 'selected' : '' ?>>Class 8</option>
                                    <option value="9" <?= $note['class'] == '9' ? 'selected' : '' ?>>Class 9</option>
                                    <option value="10" <?= $note['class'] == '10' ? 'selected' : '' ?>>Class 10</option>
                                    <option value="11" <?= $note['class'] == '11' ? 'selected' : '' ?>>Class 11 Sci (PCB)</option>
                                    <option value="12" <?= $note['class'] == '12' ? 'selected' : '' ?>>Class 11 Sci (PCM)</option>
                                    <option value="13" <?= $note['class'] == '13' ? 'selected' : '' ?>>Class 11 Comm.</option>
                                    <option value="14" <?= $note['class'] == '14' ? 'selected' : '' ?>>Class 11 Arts</option>
                                    <option value="15" <?= $note['class'] == '15' ? 'selected' : '' ?>>Class 12 Sci (PCB)</option>
                                    <option value="16" <?= $note['class'] == '16' ? 'selected' : '' ?>>Class 12 Sci (PCM)</option>
                                    <option value="17" <?= $note['class'] == '17' ? 'selected' : '' ?>>Class 12 Comm.</option>
                                    <option value="18" <?= $note['class'] == '18' ? 'selected' : '' ?>>Class 12 Arts</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="subject">Subject</label>
                                <select name="subject" required class="form-control">
                                    <option value="">Select Subject</option>
                                    <option value="Mathematics" <?= $note['subject'] == 'Mathematics' ? 'selected' : '' ?>>Mathematics</option>
                                    <option value="Science" <?= $note['subject'] == 'Science' ? 'selected' : '' ?>>Science</option>
                                    <option value="Hindi" <?= $note['subject'] == 'Hindi' ? 'selected' : '' ?>>Hindi</option>
                                    <option value="English" <?= $note['subject'] == 'English' ? 'selected' : '' ?>>English</option>
                                    <option value="History" <?= $note['subject'] == 'History' ? 'selected' : '' ?>>History</option>
                                    <option value="Economics" <?= $note['subject'] == 'Economics' ? 'selected' : '' ?>>Economics</option>
                                    <option value="Civics" <?= $note['subject'] == 'Civics' ? 'selected' : '' ?>>Civics</option>
                                    <option value="Physics" <?= $note['subject'] == 'Physics' ? 'selected' : '' ?>>Physics</option>
                                    <option value="Chemistry" <?= $note['subject'] == 'Chemistry' ? 'selected' : '' ?>>Chemistry</option>
                                    <option value="Maths" <?= $note['subject'] == 'Maths' ? 'selected' : '' ?>>Maths</option>
                                    <option value="Biology" <?= $note['subject'] == 'Biology' ? 'selected' : '' ?>>Biology</option>
                                    <option value="Physical" <?= $note['subject'] == 'Physical' ? 'selected' : '' ?>>Physical Education</option>
                                    <option value="Accounts" <?= $note['subject'] == 'Accounts' ? 'selected' : '' ?>>Accounts</option>
                                    <option value="Business" <?= $note['subject'] == 'Business' ? 'selected' : '' ?>>Business Studies</option>
                                    <option value="Political" <?= $note['subject'] == 'Political' ? 'selected' : '' ?>>Political Science</option>
                                    <option value="IT" <?= $note['subject'] == 'IT' ? 'selected' : '' ?>>IT (Information Technology)</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="file">Upload New PDF (Optional):</label>
                                <input type="file" name="result_file" accept=".pdf" class="form-control">
                                <small>Current File: <a href="<?= htmlspecialchars($note['file_path']) ?>" target="_blank">View PDF</a></small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status">Status</label>
                                <input type="checkbox" name="status" <?= $note['status'] == '1' ? 'checked' : '' ?> width="70px" height="70px"/>
                            </div>

                            <div class="col-md-12 mb-3">
                                <button type="submit" name="update_pdf" class="btn btn-primary">Update</button>
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
