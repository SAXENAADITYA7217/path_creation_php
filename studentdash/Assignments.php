<?php
include('studentcode.php');
include('includes/header.php');

?>

<div class="container my-5">
    <h3 class="text-center mb-5 fw-light text-primary border-bottom border-3 border-primary pb-2">📘 View Assignments by Subject</h3>

    <?php
    $class_code = mysqli_real_escape_string($con, $class);

    // ✅ Fetch all active subjects for this class
    $subject_query = "SELECT subject FROM class_subject_info 
                     WHERE class_code = '$class_code' AND status = 1 
                     ORDER BY subject ASC";
    $subject_result = mysqli_query($con, $subject_query);
    ?>

    <div class="card shadow-sm mb-4 border-start border-4 border-info">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row align-items-end">
                    <div class="col-md-9">
                        <div class="form-group">
                            <label for="subject" class="form-label fw-bold text-dark">Select Subject:</label>
                            <select name="subject" id="subject" class="form-select" required>
                                <option value="">-- Select Subject --</option>
                                <?php
                                if (mysqli_num_rows($subject_result) > 0) {
                                    while ($row = mysqli_fetch_assoc($subject_result)) {
                                        $selected = (isset($_GET['subject']) && $_GET['subject'] == $row['subject']) ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($row['subject']) . '" ' . $selected . '>' . htmlspecialchars($row['subject']) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No subjects found</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mt-3 mt-md-0">
                        <button type="submit" class="btn btn-primary w-100">Show Assignments</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <hr class="my-5 border-info">

    <?php
    if (isset($_GET['subject']) && $_GET['subject'] != '') {
        $selected_subject = mysqli_real_escape_string($con, $_GET['subject']);

        echo "<h4 class='mt-4 text-center fw-bold text-success'>Showing Assignments for: <span class='text-decoration-underline'>" . htmlspecialchars($selected_subject) . "</span></h4>";

        // ✅ Fetch assignments for this subject
        $assign_query = "SELECT * FROM study_materials 
                          WHERE class_name = '$class_code' 
                          AND subject = '$selected_subject' 
                          AND material_type = 'assignment' 
                          AND status = 1 
                          ORDER BY id DESC";

        $assign_result = mysqli_query($con, $assign_query);

        if (mysqli_num_rows($assign_result) > 0) {
            echo '<div class="row mt-5">';
            while ($data = mysqli_fetch_assoc($assign_result)) {

                // ✅ Fix file path (correct teacher-admin folder)
                $file_path = '../teacher-admin/' . htmlspecialchars($data['file_path']);
                $file_type = htmlspecialchars($data['file_type']);
                $preview_html = '';

                // ✅ Preview logic
                if ($file_type === 'image') {
                    // Image preview
                    $preview_html = '<img src="' . $file_path . '" class="img-fluid rounded border border-light shadow-sm" style="height:220px; width:100%; object-fit:cover;">';
                } elseif ($file_type === 'pdf') {
                    // PDF preview
                    $preview_html = '<div class="ratio ratio-16x9" style="height:220px;"><iframe src="' . $file_path . '" class="w-100 border border-light shadow-sm rounded"></iframe></div>';
                } else {
                    // File icon placeholder (Using a generic document icon for assignments)
                    $preview_html = '<div class="d-flex align-items-center justify-content-center bg-light rounded" style="height:220px;"><img src="assets/images/file_icon.png" class="img-fluid" style="height:100px;"></div>';
                }

                // Classic/Elegant Card Style (Subtle gold/cream color scheme)
                echo '
                <div class="col-lg-4 col-md-6 mb-5">
                    <div class="card shadow-lg border-2 border-primary h-100" style="background-color: #f8f9fa;"> <div class="card-body text-center d-flex flex-column">
                            ' . $preview_html . '
                            
                            <h5 class="card-title text-dark fw-bold mt-3 mb-1 text-uppercase border-bottom border-secondary pb-1">' . htmlspecialchars($data['title']) . '</h5>
                            <p class="small text-secondary mb-3 fst-italic">Teacher: <span class="fw-bolder text-info">' . htmlspecialchars($data['teacher_name']) . '</span></p>

                            <p class="card-text small text-muted flex-grow-1">' . htmlspecialchars($data['description']) . '</p>
                            
                            <div class="d-flex justify-content-center gap-3 mt-3">
                                <a href="' . $file_path . '" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-4">
                                    <i class="bi bi-file-earmark-text"></i> 📂 View
                                </a>
                                <a href="' . $file_path . '" download class="btn btn-success btn-sm rounded-pill px-4">
                                    <i class="bi bi-download"></i> ⬇️ Download
                                </a>
                            </div>
                        </div>
                        <div class="card-footer text-muted small text-center bg-light">
                            <small>Uploaded on: ' . date('d M Y, h:i A', strtotime($data['created_at'])) . '</small>
                        </div>
                    </div>
                </div>';
            }
            echo '</div>';
        } else {
            echo '<p class="text-center alert alert-warning mt-5">No assignments were found for the selected subject.</p>';
        }
    }
    ?>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>