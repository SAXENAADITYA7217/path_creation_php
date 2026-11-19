     <!-- Sidebar Start -->
     <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="index.php" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary"><img src="../img/logo.png" alt="Path Creation Academy Image" style="width: 40px; height: auto;">Path Creation</h3>
                </a>
                <div class="d-flex align-items-center ms-4 mb-4">
                    <div class="position-relative">
                        <?php
                             $user_profilr_pic_id = $_SESSION['auth_user']['user_id'];
                             $user_profilr_pic_query = "SELECT user_photo FROM users WHERE id = '$user_profilr_pic_id' LIMIT 1";
                             $user_result_profilr_pic_query = mysqli_query($con, $user_profilr_pic_query);

                             if ($user_result_profilr_pic_query && mysqli_num_rows($user_result_profilr_pic_query) > 0) {
                             $user_profilr_pic = mysqli_fetch_assoc($user_result_profilr_pic_query);
                            }
                            $photo_source_profilr_pic = $user_profilr_pic['user_photo'];
                        ?>
                        <img class="rounded-circle" src="uploads/profile_image/<?= $photo_source_profilr_pic ?>"  alt="" style="width: 40px; height: 40px;">
                        <div class="bg-success rounded-circle border border-2 border-white position-absolute end-0 bottom-0 p-1"></div>
                    </div>
                    <div class="ms-3">
                        <h6 class="mb-0"><?php echo $full_name; ?></h6>
                        <span>Student</span>
                    </div>
                </div>

                <div class="navbar-nav w-100">
                    <a href="index.php" class="nav-item nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <a href="attendancestudent.php" class="nav-item nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'attendancestudent.php') ? 'active' : ''; ?>"><i class="fa fa-th me-2"></i>Attendance</a>
                    <a href="studymaterial.php" class="nav-item nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'studymaterial.php') ? 'active' : ''; ?>"><i class="fas fa-book"></i>Notes</a>
                    <a href="report.php" class="nav-item nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'studentreport.php') ? 'active' : ''; ?>"><i class="fa fa-table me-2"></i>Student Report</a>
            <!--      <div class="nav-item dropdown">  
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-laptop me-2"></i>Elements</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="button.html" class="dropdown-item">Buttons</a>
                            <a href="typography.html" class="dropdown-item">Typography</a>
                            <a href="element.html" class="dropdown-item">Other Elements</a>
                        </div>
                    </div>
                   <a href="widget.html" class="nav-item nav-link"><i class="fa fa-th me-2"></i>Widgets</a> -->
                    <a href="studentresult.php" class="nav-item nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'studentresult.php') ? 'active' : ''; ?>"><i class="fa fa-keyboard me-2"></i>Result</a>
                    <a href="chart.php" class="nav-item nav-link"><i class="fa fa-chart-bar me-2"></i>Charts</a>
                    <div class="nav-item dropdown">
                        <!-- <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="far fa-file-alt me-2"></i>Pages</a> -->
                        <div class="dropdown-menu bg-transparent border-0">
                         <!--   <a href="signin.html" class="dropdown-item">Sign In</a>
                            <a href="signup.html" class="dropdown-item">Sign Up</a> -->
                            <a href="#404.html" class="dropdown-item">Syllabus</a>
                            <a href="#blank.html" class="dropdown-item">Blank Page</a>
                        </div>  
                    </div>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->