<?php
include('studentcode.php');
include('includes/header.php');
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card shadow p-4 rounded">
                <h3 class="text-center mb-4 text-primary">📢 Submit a Complaint</h3>

                <?php if (!empty($_SESSION['complaint_success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['complaint_success']; ?></div>
                    <?php unset($_SESSION['complaint_success']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['complaint_error'])): ?>
                    <div class="alert alert-danger"><?= $_SESSION['complaint_error']; ?></div>
                    <?php unset($_SESSION['complaint_error']); ?>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group mb-3">
                        <label for="message" class="form-label">Your Complaint</label>
                        <textarea name="message" id="message" class="form-control" rows="5" placeholder="Write your complaint here..." required></textarea>
                    </div>

                    <div class="text-center d-flex justify-content-between">
                        <button type="submit" name="submit_complaint" class="btn btn-primary px-5">
                            Submit Complaint
                        </button>
                        <a href="viewcomplaints.php" class="btn btn-outline-secondary px-4">
                            View My Complaints
                        </a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
