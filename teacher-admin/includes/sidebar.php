<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// Active status checks for parent dropdowns
$isStudentsActive = in_array($currentPage, ['get-student.php', 'add-student.php', 'edit-student.php']);
$isAttendanceActive = in_array($currentPage, ['attendance-add.php', 'attendance-view.php', 'attendence-percentage.php']);
$isTheoryActive = in_array($currentPage, ['add-report.php', 'view-report.php', 'edit-report.php', 'edit-all-report.php']);
$isPracticalActive = in_array($currentPage, ['add-partical-marks.php', 'view-partical-marks.php', 'edit_partical_marks.php', 'edit-all-practical-marks.php']);
$isMaterialActive = in_array($currentPage, ['studymaterial.php', 'view-study-material.php', 'edit-study-material.php']);
?>
<div class="sidebar-wrapper">
    
    <!-- Sidebar Brand Header -->
    <div class="sidebar-header">
        <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none">
            <div class="sidebar-brand-icon">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="brand-text-wrap">
                <h4 class="logo-text">Path Creation</h4>
                <span class="sidebar-brand-badge">Teacher Panel</span>
            </div>
        </a>
        <div class="toggle-icon ms-auto" title="Toggle Sidebar" role="button" aria-label="Toggle Sidebar">
            <i class='bx bx-arrow-to-left'></i>
        </div>
    </div>

    <!-- Scrollable Navigation Menu -->
    <div class="sidebar-menu-wrapper">
        <ul class="metismenu" id="menu">
        
        <!-- SECTION: MAIN -->
        <li class="menu-label">Main Menu</li>
        
        <li class="<?= ($currentPage === 'index.php') ? 'mm-active' : '' ?>">
            <a href="index.php" class="<?= ($currentPage === 'index.php') ? 'active-pill' : '' ?>">
                <div class="parent-icon"><i class="fa-solid fa-house"></i></div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        <!-- SECTION: STUDENTS & ATTENDANCE -->
        <li class="menu-label">Students &amp; Attendance</li>
        
        <!-- Students Dropdown -->
        <li class="has-sub <?= $isStudentsActive ? 'mm-active' : '' ?>">
            <a href="javascript:;" class="has-arrow <?= $isStudentsActive ? 'active-parent' : '' ?>">
                <div class="parent-icon"><i class="fa-solid fa-users"></i></div>
                <div class="menu-title">Students</div>
            </a>
            <ul class="<?= $isStudentsActive ? 'mm-show' : '' ?>">
                <li class="<?= in_array($currentPage, ['get-student.php', 'edit-student.php']) ? 'mm-active' : '' ?>">
                    <a href="get-student.php" class="<?= in_array($currentPage, ['get-student.php', 'edit-student.php']) ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> Show Students
                    </a>
                </li>
                <li class="<?= ($currentPage === 'add-student.php') ? 'mm-active' : '' ?>">
                    <a href="add-student.php" class="<?= ($currentPage === 'add-student.php') ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> Add Student
                    </a>
                </li>
            </ul>
        </li>

        <!-- Attendance Dropdown -->
        <li class="has-sub <?= $isAttendanceActive ? 'mm-active' : '' ?>">
            <a href="javascript:;" class="has-arrow <?= $isAttendanceActive ? 'active-parent' : '' ?>">
                <div class="parent-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div class="menu-title">Attendance</div>
            </a>
            <ul class="<?= $isAttendanceActive ? 'mm-show' : '' ?>">
                <li class="<?= ($currentPage === 'attendance-add.php') ? 'mm-active' : '' ?>">
                    <a href="attendance-add.php" class="<?= ($currentPage === 'attendance-add.php') ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> Mark Attendance
                    </a>
                </li>
                <li class="<?= ($currentPage === 'attendance-view.php') ? 'mm-active' : '' ?>">
                    <a href="attendance-view.php" class="<?= ($currentPage === 'attendance-view.php') ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> View Attendance
                    </a>
                </li>
                <li class="<?= ($currentPage === 'attendence-percentage.php') ? 'mm-active' : '' ?>">
                    <a href="attendence-percentage.php" class="<?= ($currentPage === 'attendence-percentage.php') ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> Attendance %
                    </a>
                </li>
            </ul>
        </li>

        <!-- SECTION: EXAMINATIONS & MARKS -->
        <li class="menu-label">Examinations &amp; Marks</li>

        <!-- Theory Marks Dropdown -->
        <li class="has-sub <?= $isTheoryActive ? 'mm-active' : '' ?>">
            <a href="javascript:;" class="has-arrow <?= $isTheoryActive ? 'active-parent' : '' ?>">
                <div class="parent-icon"><i class="fa-solid fa-pen-to-square"></i></div>
                <div class="menu-title">Theory Marks</div>
            </a>
            <ul class="<?= $isTheoryActive ? 'mm-show' : '' ?>">
                <li class="<?= ($currentPage === 'add-report.php') ? 'mm-active' : '' ?>">
                    <a href="add-report.php" class="<?= ($currentPage === 'add-report.php') ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> Add Theory Marks
                    </a>
                </li>
                <li class="<?= in_array($currentPage, ['view-report.php', 'edit-report.php', 'edit-all-report.php']) ? 'mm-active' : '' ?>">
                    <a href="view-report.php" class="<?= in_array($currentPage, ['view-report.php', 'edit-report.php', 'edit-all-report.php']) ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> View Theory Marks
                    </a>
                </li>
            </ul>
        </li>

        <!-- Practical Marks Dropdown -->
        <li class="has-sub <?= $isPracticalActive ? 'mm-active' : '' ?>">
            <a href="javascript:;" class="has-arrow <?= $isPracticalActive ? 'active-parent' : '' ?>">
                <div class="parent-icon"><i class="fa-solid fa-flask"></i></div>
                <div class="menu-title">Practical Marks</div>
            </a>
            <ul class="<?= $isPracticalActive ? 'mm-show' : '' ?>">
                <li class="<?= ($currentPage === 'add-partical-marks.php') ? 'mm-active' : '' ?>">
                    <a href="add-partical-marks.php" class="<?= ($currentPage === 'add-partical-marks.php') ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> Add Practical Marks
                    </a>
                </li>
                <li class="<?= in_array($currentPage, ['view-partical-marks.php', 'edit_partical_marks.php', 'edit-all-practical-marks.php']) ? 'mm-active' : '' ?>">
                    <a href="view-partical-marks.php" class="<?= in_array($currentPage, ['view-partical-marks.php', 'edit_partical_marks.php', 'edit-all-practical-marks.php']) ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> View Practical Marks
                    </a>
                </li>
            </ul>
        </li>

        <!-- Online Tests -->
        

        <!-- SECTION: REPORTS & PERFORMANCE -->
        <li class="menu-label">Reports &amp; Performance</li>

        <!-- Report Card -->
        <li class="<?= in_array($currentPage, ['report_card_select.php', 'print_report_card.php']) ? 'mm-active' : '' ?>">
            <a href="report_card_select.php" class="<?= in_array($currentPage, ['report_card_select.php', 'print_report_card.php']) ? 'active-pill' : '' ?>">
                <div class="parent-icon"><i class="fa-solid fa-id-card"></i></div>
                <div class="menu-title">Report Card</div>
            </a>
        </li>

        <!-- Class Ranking -->
        <li class="<?= in_array($currentPage, ['view_class_rank.php', 'ranking.php']) ? 'mm-active' : '' ?>">
            <a href="view_class_rank.php" class="<?= in_array($currentPage, ['view_class_rank.php', 'ranking.php']) ? 'active-pill' : '' ?>">
                <div class="parent-icon"><i class="fa-solid fa-medal"></i></div>
                <div class="menu-title">Class Ranking</div>
            </a>
        </li>

        <!-- SECTION: STUDY CONTENT -->
        <li class="menu-label">Study Content</li>

        <!-- Study Material Dropdown -->
        <li class="has-sub <?= $isMaterialActive ? 'mm-active' : '' ?>">
            <a href="javascript:;" class="has-arrow <?= $isMaterialActive ? 'active-parent' : '' ?>">
                <div class="parent-icon"><i class="fa-solid fa-book-open"></i></div>
                <div class="menu-title">Study Material</div>
            </a>
            <ul class="<?= $isMaterialActive ? 'mm-show' : '' ?>">
                <li class="<?= in_array($currentPage, ['studymaterial.php', 'edit-study-material.php']) ? 'mm-active' : '' ?>">
                    <a href="studymaterial.php" class="<?= in_array($currentPage, ['studymaterial.php', 'edit-study-material.php']) ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> Upload Material
                    </a>
                </li>
                <li class="<?= ($currentPage === 'view-study-material.php') ? 'mm-active' : '' ?>">
                    <a href="view-study-material.php" class="<?= ($currentPage === 'view-study-material.php') ? 'active-pill' : '' ?>">
                        <i class="fa-solid fa-circle"></i> View Materials
                    </a>
                </li>
            </ul>
        </li>

        <!-- SECTION: ACCOUNT & SUPPORT -->
        <li class="menu-label">Account &amp; Support</li>

        <!-- Complaints -->
        <li class="<?= ($currentPage === 'viewcomplaints.php') ? 'mm-active' : '' ?>">
            <a href="viewcomplaints.php" class="<?= ($currentPage === 'viewcomplaints.php') ? 'active-pill' : '' ?>">
                <div class="parent-icon"><i class="fa-solid fa-comments"></i></div>
                <div class="menu-title">Complaints</div>
            </a>
        </li>

        <!-- Profile -->
        <li class="<?= ($currentPage === 'profile.php') ? 'mm-active' : '' ?>">
            <a href="profile.php" class="<?= ($currentPage === 'profile.php') ? 'active-pill' : '' ?>">
                <div class="parent-icon"><i class="fa-solid fa-user-gear"></i></div>
                <div class="menu-title">My Profile</div>
            </a>
        </li>

    </ul>

    <!-- Bottom Badge Card -->
    <div class="sidebar-bottom-badge">
        <div class="badge-icon">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="badge-text-wrap">
            <div class="badge-text-title">Path Creation Academy</div>
            <div class="badge-text-sub">Teacher Panel v2.0</div>
        </div>
    </div>

    </div><!-- /.sidebar-menu-wrapper -->

</div>
