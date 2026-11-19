<?php 
include('studentcode.php');
include('includes/header.php');
?>

<!-- ============================= -->
<!-- 🌈 Modern Student Dashboard -->
<!-- ============================= -->
<style>
    body {
        background: linear-gradient(135deg, #e3f2fd, #ffffff);
        font-family: "Poppins", sans-serif;
    }

    .dashboard-container {
        padding: 2rem;
    }

    /* ===== Calendar & Complaint Cards ===== */
    .calendar-card, .complaint-card {
        background: #ffffff;
        border-radius: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        height: 100%;
    }

    .calendar-card:hover, .complaint-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }

    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #1976d2;
    }

    /* ===== Info Cards ===== */
    .info-card {
        background: rgba(255,255,255,0.9);
        border-radius: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        cursor: pointer;
        backdrop-filter: blur(10px);
    }

    .info-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }

    .info-card i {
        font-size: 3rem;
        margin-bottom: 0.8rem;
        transition: transform 0.3s ease;
    }

    .info-card:hover i {
        transform: rotate(10deg) scale(1.1);
    }

    .info-card h5 {
        font-weight: 600;
    }

    .info-card p {
        color: #666;
        font-size: 0.9rem;
    }

    .btn-custom {
        border-radius: 50px;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .btn-custom:hover {
        transform: scale(1.05);
        color: #fff !important;
    }

    /* Colors */
    .info-blue i, .btn-outline-primary:hover {
        color: #1976d2;
        background-color: #1976d2;
    }

    .info-green i, .btn-outline-success:hover {
        color: #388e3c;
        background-color: #388e3c;
    }

    .info-orange i, .btn-outline-warning:hover {
        color: #f57c00;
        background-color: #f57c00;
    }

    .btn-outline-primary:hover,
    .btn-outline-success:hover,
    .btn-outline-warning:hover {
        color: white !important;
        border: none;
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }
    }
</style>

<section id="student-dashboard" class="dashboard-container">
    <div class="container-fluid">

        <!-- ============================= -->
        <!-- 📅 Calendar + 🗣 Complaint Side by Side -->
        <!-- ============================= -->
        <div class="row mb-5">
            <div class="col-md-6 mb-4">
                <div class="complaint-card p-4 h-100">
                    <h5 class="text-center mb-4 text-primary">📢 Submit a Complaint</h5>

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
                            <label for="message" class="form-label fw-semibold">Your Complaint</label>
                            <textarea name="message" id="message" class="form-control" rows="6" placeholder="Write your complaint here..." required></textarea>
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




            <!-- Calendar -->
            <div class="col-md-6 mb-4">
                <div class="calendar-card p-4 h-100">
                    <div class="calendar-header mb-3">
                        <h5 class="fw-bold mb-0"><i class="fa fa-calendar-alt me-2"></i> Calendar</h5>
                        <small class="text-muted">Stay updated with upcoming classes & events</small>
                    </div>
                    <div id="calender" style="min-height: 400px;"></div>
                </div>
            </div>

            <!-- Complaint Box -->
            
        </div>

        <!-- ============================= -->
        <!-- 📦 Info Cards Section -->
        <!-- ============================= -->
        <div class="row g-4">

            <!-- 🧾 Attendance -->
            <div class="col-sm-12 col-md-6 col-xl-3">
                <div class="info-card text-center p-4 h-100 info-blue">
                    <i class="fa fa-user-check text-primary"></i>
                    <h5>Attendance</h5>
                    <p>Track your daily attendance and view your presence record.</p>
                    <a href="attendancestudent.php" class="btn btn-outline-primary btn-custom">View Attendance</a>
                </div>
            </div>

            <!-- 📝 Study Material -->
            <div class="col-sm-12 col-md-6 col-xl-3">
                <div class="info-card text-center p-4 h-100 info-green">
                    <i class="fa fa-sticky-note text-success"></i>
                    <h5>Study Material</h5>
                    <p>Access your notes, assignments, and learning materials easily.</p>
                    <a href="studymaterial.php" class="btn btn-outline-success btn-custom">View Notes</a>
                </div>
            </div>

            <!-- 📊 Report -->
            <div class="col-sm-12 col-md-6 col-xl-3">
                <div class="info-card text-center p-4 h-100 info-orange">
                    <i class="fa fa-chart-pie text-warning"></i>
                    <h5>Student Report</h5>
                    <p>Analyze your performance with test scores and progress reports.</p>
                    <a href="report.php" class="btn btn-outline-warning btn-custom">View Report</a>
                </div>
            </div>

            <!-- 🏆 Result -->
            <div class="col-sm-12 col-md-6 col-xl-3">
                <div class="info-card text-center p-4 h-100 info-orange">
                    <i class="fa fa-chart-bar text-warning"></i>
                    <h5>Result</h5>
                    <p>Check your final exam scores and detailed academic results.</p>
                    <a href="report.php" class="btn btn-outline-warning btn-custom">View Result</a>
                </div>
            </div>

        </div>

    </div>
</section>

<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>
