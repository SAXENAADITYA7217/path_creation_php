<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <!-- Sidebar Brand Header matching image -->
            <div class="sidebar-brand-wrapper">
                <a href="index.php" class="sidebar-brand-content">
                    <div class="sidebar-brand-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="sidebar-brand-title">
                        Path Creation<br>Academy
                    </div>
                </a>
                <div class="sidebar-collapse-toggle" id="sidebarCollapseArrow" title="Collapse sidebar">
                    <i class="fas fa-angle-double-left"></i>
                </div>
            </div>

            <div class="nav pt-2">
                <!-- Top Main Items -->
                <a class="nav-link <?= ($currentPage === 'index.php') ? 'active-pill' : '' ?>" href="index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                    Dashboard
                </a>
                <a class="nav-link <?= ($currentPage === 'view-register.php' || $currentPage === 'register-add.php') ? 'active-pill' : '' ?>" href="view-register.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-user-friends"></i></div>
                    Registered Users
                </a>

                <!-- Section: ACADEMIC -->
                <div class="sb-sidenav-menu-heading">ACADEMIC</div>

                <!-- Category -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCategory" aria-expanded="false" aria-controls="collapseCategory">
                    <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                    Category
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
                <div class="collapse" id="collapseCategory" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="category-view.php">View Category</a>
                        <a class="nav-link" href="category-add.php">Add Category</a>
                    </nav>
                </div>

                <!-- Course -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCourses" aria-expanded="false" aria-controls="collapseCourses">
                    <div class="sb-nav-link-icon"><i class="fas fa-graduation-cap"></i></div>
                    Course
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
                <div class="collapse" id="collapseCourses" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="courses-view.php">View Courses</a>
                        <a class="nav-link" href="courses-add.php">Add Course</a>
                    </nav>
                </div>

                <!-- Test Series -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTests" aria-expanded="false" aria-controls="collapseTests">
                    <div class="sb-nav-link-icon"><i class="far fa-clipboard"></i></div>
                    Test Series
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
                <div class="collapse" id="collapseTests" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="test-view.php">View Test Series</a>
                        <a class="nav-link" href="test-add.php">Add Test</a>
                    </nav>
                </div>

                <!-- Result -->
                <?php $isResultActive = in_array($currentPage, ['result-view.php', 'result-add.php', 'result-show.php', 'print_report_card.php', 'report_card_print.php', 'report_card_select.php']); ?>
                <a class="nav-link <?= $isResultActive ? '' : 'collapsed' ?>" href="#" data-bs-toggle="collapse" data-bs-target="#collapseResult" aria-expanded="<?= $isResultActive ? 'true' : 'false' ?>" aria-controls="collapseResult">
                    <div class="sb-nav-link-icon"><i class="fas fa-chart-bar"></i></div>
                    Result
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
                <div class="collapse <?= $isResultActive ? 'show' : '' ?>" id="collapseResult" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?= ($currentPage === 'result-view.php') ? 'active-pill' : '' ?>" href="result-view.php">View Result</a>
                        <a class="nav-link <?= ($currentPage === 'result-add.php') ? 'active-pill' : '' ?>" href="result-add.php">Add Result</a>
                        <a class="nav-link <?= ($currentPage === 'result-show.php') ? 'active-pill' : '' ?>" href="result-show.php">Show Result</a>
                        <a class="nav-link <?= in_array($currentPage, ['print_report_card.php', 'report_card_print.php', 'report_card_select.php']) ? 'active-pill' : '' ?>" href="print_report_card.php">
                            <i class="fas fa-file-invoice me-1"></i> Print Report Card
                        </a>
                    </nav>
                </div>

                <!-- Attendance -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAttendance" aria-expanded="false" aria-controls="collapseAttendance">
                    <div class="sb-nav-link-icon"><i class="far fa-calendar-check"></i></div>
                    Attendance
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
                <div class="collapse" id="collapseAttendance" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="attendance-view.php">View Attendance</a>
                        <a class="nav-link" href="attendance-add.php">Add Attendance</a>
                    </nav>
                </div>

                <!-- Monthly Report -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReport" aria-expanded="false" aria-controls="collapseReport">
                    <div class="sb-nav-link-icon"><i class="far fa-file-alt"></i></div>
                    Monthly Report
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
                <div class="collapse" id="collapseReport" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="report-view.php">View Report</a>
                        <a class="nav-link" href="report-add.php">Add Report</a>
                    </nav>
                </div>

                <!-- Section: CONTENT -->
                <div class="sb-sidenav-menu-heading">CONTENT</div>

                <!-- Notes -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseNotes" aria-expanded="false" aria-controls="collapseNotes">
                    <div class="sb-nav-link-icon"><i class="far fa-sticky-note"></i></div>
                    Notes
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
                <div class="collapse" id="collapseNotes" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="notes-view.php">View Notes</a>
                        <a class="nav-link" href="notes-add.php">Add Notes</a>
                        <a class="nav-link" href="writenotes-view.php">Written Notes</a>
                    </nav>
                </div>

                <!-- Blog Posts -->
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePosts" aria-expanded="false" aria-controls="collapsePosts">
                    <div class="sb-nav-link-icon"><i class="far fa-newspaper"></i></div>
                    Blog Posts
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
                <div class="collapse" id="collapsePosts" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="post-view.php">View Posts</a>
                        <a class="nav-link" href="post-add.php">Add Post</a>
                    </nav>
                </div>

                <!-- Add Element in Website -->
                <a class="nav-link <?= ($currentPage === 'webelement.php') ? 'active-pill' : '' ?>" href="webelement.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-plus-circle"></i></div>
                    Add Element
                </a>

                <!-- Profile & Settings -->
                <div class="pt-3">
                    <a class="nav-link <?= ($currentPage === 'profile-view.php') ? 'active-pill' : '' ?>" href="profile-view.php">
                        <div class="sb-nav-link-icon"><i class="far fa-user"></i></div>
                        Profile
                    </a>
                    <a class="nav-link" href="#!">
                        <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                        Settings
                    </a>
                </div>

                <!-- Bottom Badge Card from Image -->
                <div class="sidebar-bottom-badge">
                    <div class="badge-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div>
                        <div class="badge-text-title">Path Creation Academy</div>
                        <div class="badge-text-sub">Admin Panel v2.0</div>
                    </div>
                </div>

            </div>
        </div>
    </nav>
</div>
