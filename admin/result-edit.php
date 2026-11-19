<?php
include('authentication.php');
include('includes/header.php');

// Check if the ID is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the existing result data based on the ID
    $query = "SELECT * FROM result WHERE id='$id'";
    $result = mysqli_query($con, $query);

    // Check if the result exists
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_array($result);
    } else {
        $_SESSION['message'] = "Result not found.";
        header("Location: result-view.php");
        exit(0);
    }
} else {
    $_SESSION['message'] = "Invalid request.";
    header("Location: result-view.php");
    exit(0);
}
?>

<div class="container-fluid px-4">
    <div class="row mt-4">
        <div class="col-md-12">
            <?php include('message.php'); ?>

            <div class="card">
                <div class="card-header">
                    <h4>Edit Result
                        <a href="result-view.php" class="btn btn-danger float-end">Back</a>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $data['id']; ?>">
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="class">Class</label>
                                <input type="text" name="class" value="<?= $data['class']; ?>" class="form-control" readonly>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="user_id">Username</label>
                                <input type="text" name="user_id" value="<?= $data['user_id']; ?>" class="form-control" readonly>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="exam_type">Exam Type</label>
                                <input type="text" name="exam_type" value="<?= $data['exam_type']; ?>" class="form-control" readonly>
                            </div>

                            <?php
                            // Dynamically generate input fields based on class subjects
                            $class = $data['class'];
                            $subjects = [];

                            if ($class === '8' || $class === '9' || $class === '10') {
                                $subjects = ['english', 'hindi', 'maths', 'science', 'sst', 'computer_it'];
                            } elseif ($class === '11' || $class === '15') {
                                $subjects = ['english', 'hindi', 'physics', 'chemistry', 'biology', 'physical_education'];
                            } elseif ($class === '12' || $class === '16') {
                                $subjects = ['english', 'hindi', 'physics', 'chemistry', 'maths', 'physical_education'];
                            } elseif ($class === '13' || $class === '17') {
                                $subjects = ['english', 'hindi', 'account', 'economics', 'business', 'physical_education'];
                            } elseif ($class === '14' || $class === '18') {
                                $subjects = ['english', 'hindi', 'pol_science', 'economics', 'history', 'physical_education'];
                            }

                            foreach ($subjects as $subject) {
                                echo "
                                <div class='col-md-4 mb-3'>
                                    <label for='$subject'>" . ucfirst(str_replace('_', ' ', $subject)) . "</label>
                                    <input type='text' name='$subject' value='" . $data[$subject] . "' class='form-control'>
                                </div>";
                            }
                            ?>

                            <div class="col-md-12 mb-3">
                                <label for="date">Date</label>
                                <input type="date" id="date" name="date" value="<?= $data['date']; ?>" class="form-control" required>
                            </div>

                            <div class="col-md-12 mb-3">
                                <button type="submit" name="marks_update" class="btn btn-primary">Update Data</button>
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
