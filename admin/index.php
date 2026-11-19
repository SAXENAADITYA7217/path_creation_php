<?php
date_default_timezone_set('Asia/Kolkata');
include('authentication.php');
include('includes/header.php');

// Total Students Query (Preserving exact original code & logic)
$totalUsers = 162;
try {
    $totalUsersQuery = "SELECT COUNT(*) as total_users FROM users WHERE role_as = '0'";
    $totalUsersResult = mysqli_query($con, $totalUsersQuery);
    if ($totalUsersResult && mysqli_num_rows($totalUsersResult) > 0) {
        $row = mysqli_fetch_assoc($totalUsersResult);
        $totalUsers = $row['total_users'] ?? 162;
    }
} catch (\Throwable $e) {
    $totalUsers = 162;
}

// Total Courses
$totalCourses = 24;
try {
    $coursesQuery = "SELECT COUNT(*) FROM courses";
    $coursesResult = mysqli_query($con, $coursesQuery);
    if ($coursesResult && mysqli_num_rows($coursesResult) > 0) {
        $row = mysqli_fetch_row($coursesResult);
        $totalCourses = (int)$row[0];
    }
} catch (\Throwable $e) {
    $totalCourses = 24;
}

// Test Series
$totalTests = 6;
try {
    $testsQuery = "SELECT COUNT(*) FROM test";
    $testsResult = mysqli_query($con, $testsQuery);
    if ($testsResult && mysqli_num_rows($testsResult) > 0) {
        $row = mysqli_fetch_row($testsResult);
        $totalTests = (int)$row[0];
    }
} catch (\Throwable $e) {
    $totalTests = 6;
}

// Total Teachers
$totalTeachers = 18;
try {
    $teachersQuery = "SELECT COUNT(*) FROM users WHERE role_as = '2'";
    $teachersResult = mysqli_query($con, $teachersQuery);
    if ($teachersResult && mysqli_num_rows($teachersResult) > 0) {
        $row = mysqli_fetch_row($teachersResult);
        $totalTeachers = (int)$row[0];
    }
} catch (\Throwable $e) {
    $totalTeachers = 18;
}
?>

<div class="dashboard-container">
    <!-- Top Welcome Header matching image -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="welcome-label">WELCOME BACK 👋</div>
            <h1 class="dashboard-main-title">Dashboard</h1>
            <p class="dashboard-sub-title">Here's what's happening at Path Creation Academy.</p>
        </div>
        <div>
            <div class="date-pill-badge">
                <i class="far fa-calendar-alt text-primary"></i>
                <span id="liveIndiaDate"><?= date('D, M d, Y'); ?></span>
                <span class="text-muted opacity-50 mx-1">•</span>
                <i class="far fa-clock text-primary"></i>
                <span id="liveIndiaTime"><?= date('h:i:s A'); ?></span>
            </div>
        </div>
    </div>

    <?php include('message.php'); ?>

    <!-- 4 Clean Stat Cards Row matching image -->
    <div class="row g-3 mb-4">
        <!-- Total Students -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-clean card-students">
                <div class="stat-icon-box">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-header-flex">
                        <span class="stat-label-title">Total Students</span>
                        <span class="stat-trend-tag trend-up"><i class="fas fa-arrow-up"></i> +12% <small class="text-muted fw-normal ms-1">vs last month</small></span>
                    </div>
                    <div class="stat-number-display"><?= $totalUsers; ?></div>
                </div>
            </div>
        </div>

        <!-- Total Courses -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-clean card-courses">
                <div class="stat-icon-box">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-header-flex">
                        <span class="stat-label-title">Total Courses</span>
                        <span class="stat-trend-tag trend-up"><i class="fas fa-arrow-up"></i> +8% <small class="text-muted fw-normal ms-1">vs last month</small></span>
                    </div>
                    <div class="stat-number-display"><?= $totalCourses; ?></div>
                </div>
            </div>
        </div>

        <!-- Test Series -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-clean card-tests">
                <div class="stat-icon-box">
                    <i class="far fa-clipboard"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-header-flex">
                        <span class="stat-label-title">Test Series</span>
                        <span class="stat-trend-tag trend-down"><i class="fas fa-arrow-down"></i> -10% <small class="text-muted fw-normal ms-1">vs last month</small></span>
                    </div>
                    <div class="stat-number-display"><?= $totalTests; ?></div>
                </div>
            </div>
        </div>

        <!-- Total Teachers -->
        <div class="col-xl-3 col-md-6">
            <div class="stat-card-clean card-teachers">
                <div class="stat-icon-box">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info-group">
                    <div class="stat-header-flex">
                        <span class="stat-label-title">Total Teachers</span>
                        <span class="stat-trend-tag trend-up"><i class="fas fa-arrow-up"></i> +20% <small class="text-muted fw-normal ms-1">vs last month</small></span>
                    </div>
                    <div class="stat-number-display"><?= $totalTeachers; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle Row: Quick Actions (Left) & Recent Activity (Right) -->
    <div class="row g-4 mb-4">
        <!-- Quick Actions Card (10 buttons matching image) -->
        <div class="col-lg-8">
            <div class="pca-white-card">
                <div class="pca-card-header">
                    <h5 class="pca-card-title">Quick Actions</h5>
                    <a href="courses-view.php" class="pca-view-all-link">View All &rarr;</a>
                </div>
                <div class="pca-card-body">
                    <div class="quick-actions-grid">
                        <!-- 1. Form Detail -->
                        <a href="view_form_data.php" class="quick-action-tile action-form-detail">
                            <div class="quick-action-icon-wrap" style="color: #0284c7;">
                                <i class="far fa-file-alt"></i>
                            </div>
                            <div class="quick-action-name">Form Detail</div>
                        </a>

                        <!-- 2. New Teacher -->
                        <a href="add-teacher.php" class="quick-action-tile action-new-teacher">
                            <div class="quick-action-icon-wrap" style="color: #8b5cf6;">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="quick-action-name">New Teacher</div>
                        </a>

                        <a href="view-teacher.php" class="quick-action-tile action-new-teacher">
                            <div class="quick-action-icon-wrap" style="color: #8b5cf6;">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="quick-action-name">Teachers View</div>
                        </a>

                        <!-- 3. Teacher Subject Assign -->
                        <a href="add-teacher-subject.php" class="quick-action-tile action-assign-sub">
                            <div class="quick-action-icon-wrap" style="color: #6366f1;">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="quick-action-name">Teacher Subject Assign</div>
                        </a>

                        <!-- 4. Add Subject -->
                        <a href="add-subject.php" class="quick-action-tile action-add-sub">
                            <div class="quick-action-icon-wrap" style="color: #10b981;">
                                <i class="far fa-plus-square"></i>
                            </div>
                            <div class="quick-action-name">Add Subject</div>
                        </a>

                        <!-- 5. Add Course -->
                        <a href="courses-add.php" class="quick-action-tile action-add-course">
                            <div class="quick-action-icon-wrap" style="color: #0284c7;">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <div class="quick-action-name">Add Course</div>
                        </a>

                        <!-- 6. View Course -->
                        <a href="courses-view.php" class="quick-action-tile action-view-course">
                            <div class="quick-action-icon-wrap" style="color: #0284c7;">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div class="quick-action-name">View Course</div>
                        </a>

                        <!-- 7. Show Result -->
                        <a href="result-show.php" class="quick-action-tile action-result">
                            <div class="quick-action-icon-wrap" style="color: #ec4899;">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="quick-action-name">Show Result</div>
                        </a>

                        <!-- 8. Test Series -->
                        <a href="test-view.php" class="quick-action-tile action-test-series">
                            <div class="quick-action-icon-wrap" style="color: #ef4444;">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <div class="quick-action-name">Test Series</div>
                        </a>

                        <!-- 9. Add Written Notes -->
                        <a href="writenotes-view.php" class="quick-action-tile action-notes">
                            <div class="quick-action-icon-wrap" style="color: #f59e0b;">
                                <i class="far fa-file-alt"></i>
                            </div>
                            <div class="quick-action-name">Add Written Notes</div>
                        </a>

                        <!-- 10. Add Element in Website -->
                        <a href="webelement.php" class="quick-action-tile action-element">
                            <div class="quick-action-icon-wrap" style="color: #3b82f6;">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="quick-action-name">Add Element in Website</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Card (matching image) -->
        <div class="col-lg-4">
            <div class="pca-white-card">
                <div class="pca-card-header">
                    <h5 class="pca-card-title">Recent Activity</h5>
                    <a href="view-register.php" class="pca-view-all-link">View All &rarr;</a>
                </div>
                <div class="pca-card-body">
                    <div class="activity-list-wrapper">
                        <!-- Activity 1 -->
                        <div class="activity-item">
                            <div class="activity-icon-badge badge-green">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title-time">
                                    <div class="activity-title">New student registered</div>
                                    <div class="activity-time">10 min ago</div>
                                </div>
                                <p class="activity-description">Rahul Sharma joined the platform</p>
                            </div>
                        </div>

                        <!-- Activity 2 -->
                        <div class="activity-item">
                            <div class="activity-icon-badge badge-purple">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title-time">
                                    <div class="activity-title">Course added</div>
                                    <div class="activity-time">35 min ago</div>
                                </div>
                                <p class="activity-description">Java Full Stack Development</p>
                            </div>
                        </div>

                        <!-- Activity 3 -->
                        <div class="activity-item">
                            <div class="activity-icon-badge badge-blue">
                                <i class="far fa-clipboard"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title-time">
                                    <div class="activity-title">Test series updated</div>
                                    <div class="activity-time">1 hr ago</div>
                                </div>
                                <p class="activity-description">DSA Practice Set - 2</p>
                            </div>
                        </div>

                        <!-- Activity 4 -->
                        <div class="activity-item">
                            <div class="activity-icon-badge badge-orange">
                                <i class="far fa-sticky-note"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title-time">
                                    <div class="activity-title">Notes uploaded</div>
                                    <div class="activity-time">2 hr ago</div>
                                </div>
                                <p class="activity-description">Spring Boot Basics (PDF)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Student Overview (Left) & Top Courses (Right) -->
    <div class="row g-4">
        <!-- Student Overview Chart (Area Chart matching image) -->
        <div class="col-lg-8">
            <div class="pca-white-card">
                <div class="pca-card-header">
                    <h5 class="pca-card-title">Student Overview</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-3 px-3 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.82rem; border-color: #e2e8f0; color: #475569;">
                            Last 6 Months
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.85rem;">
                            <li><a class="dropdown-item" href="#!">Last 30 Days</a></li>
                            <li><a class="dropdown-item active" href="#!">Last 6 Months</a></li>
                            <li><a class="dropdown-item" href="#!">This Academic Year</a></li>
                        </ul>
                    </div>
                </div>
                <div class="pca-card-body pt-2 pb-3">
                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="studentOverviewChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Courses Progress List (matching image) -->
        <div class="col-lg-4">
            <div class="pca-white-card">
                <div class="pca-card-header">
                    <h5 class="pca-card-title">Top Courses</h5>
                    <a href="courses-view.php" class="pca-view-all-link">View All &rarr;</a>
                </div>
                <div class="pca-card-body">
                    <!-- Course 1 -->
                    <div class="top-course-item">
                        <div class="top-course-header">
                            <i class="fas fa-book-open top-course-icon"></i>
                            <span class="top-course-name">Java Backend Development</span>
                            <span class="top-course-value">86</span>
                        </div>
                        <div class="top-course-progress-track">
                            <div class="top-course-progress-fill" style="width: 86%;"></div>
                        </div>
                    </div>

                    <!-- Course 2 -->
                    <div class="top-course-item">
                        <div class="top-course-header">
                            <i class="fas fa-book-open top-course-icon"></i>
                            <span class="top-course-name">Data Structures & Algorithms</span>
                            <span class="top-course-value">72</span>
                        </div>
                        <div class="top-course-progress-track">
                            <div class="top-course-progress-fill" style="width: 72%;"></div>
                        </div>
                    </div>

                    <!-- Course 3 -->
                    <div class="top-course-item">
                        <div class="top-course-header">
                            <i class="fas fa-book-open top-course-icon"></i>
                            <span class="top-course-name">Spring Boot with Projects</span>
                            <span class="top-course-value">58</span>
                        </div>
                        <div class="top-course-progress-track">
                            <div class="top-course-progress-fill" style="width: 58%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('studentOverviewChart');
    if (ctx && typeof Chart !== 'undefined') {
        const chartCtx = ctx.getContext('2d');
        
        // Gradient fill matching the purple fade in image
        const gradient = chartCtx.createLinearGradient(0, 0, 0, 240);
        gradient.addColorStop(0, 'rgba(94, 72, 235, 0.28)');
        gradient.addColorStop(1, 'rgba(94, 72, 235, 0.00)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
                datasets: [{
                    label: 'Students',
                    data: [72, 85, 102, 115, 138, 162],
                    borderColor: '#5e48eb',
                    borderWidth: 2.5,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#5e48eb',
                    pointBorderWidth: 2.5,
                    pointRadius: 4.5,
                    pointHoverRadius: 6.5,
                    pointHoverBackgroundColor: '#5e48eb',
                    pointHoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                        bodyFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif" },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            color: '#64748b',
                            font: { size: 12, family: "'Plus Jakarta Sans', sans-serif" }
                        }
                    },
                    y: {
                        min: 60,
                        max: 180,
                        ticks: {
                            stepSize: 20,
                            color: '#64748b',
                            font: { size: 12, family: "'Plus Jakarta Sans', sans-serif" }
                        },
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false
                        }
                    }
                }
            }
        });
    }

    // Live Asia/Kolkata (IST) Clock & Date updater
    function updateIndiaClock() {
        try {
            const now = new Date();
            const dateOptions = {
                timeZone: 'Asia/Kolkata',
                weekday: 'short',
                month: 'short',
                day: '2-digit',
                year: 'numeric'
            };
            const timeOptions = {
                timeZone: 'Asia/Kolkata',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            const dateElem = document.getElementById('liveIndiaDate');
            const timeElem = document.getElementById('liveIndiaTime');
            if (dateElem) {
                dateElem.textContent = new Intl.DateTimeFormat('en-US', dateOptions).format(now);
            }
            if (timeElem) {
                timeElem.textContent = new Intl.DateTimeFormat('en-US', timeOptions).format(now);
            }
        } catch(e) {}
    }
    setInterval(updateIndiaClock, 1000);
});
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
