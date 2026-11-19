<!--start header -->
<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand">
            <div class="mobile-toggle-menu" role="button" aria-label="Toggle Navigation Menu"><i class='bx bx-menu'></i></div>

            <div class="top-menu ms-auto">
                <ul class="navbar-nav align-items-center">
                    <!-- Removed notifications, messages, and dropdown icons -->
                </ul>
            </div>

            <div class="user-box dropdown">
                <?php
                $user_profilr_pic_id = $_SESSION['auth_user']['user_id'];
                $user_profilr_pic_query = "SELECT user_photo FROM users WHERE id = '$user_profilr_pic_id' LIMIT 1";
                $user_result_profilr_pic_query = mysqli_query($con, $user_profilr_pic_query);

                if ($user_result_profilr_pic_query && mysqli_num_rows($user_result_profilr_pic_query) > 0) {
                    $user_profilr_pic = mysqli_fetch_assoc($user_result_profilr_pic_query);
                }
                 $photo_source_profilr_pic = $user_profilr_pic['user_photo'];
                ?>
                <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="uploads/profile_image/<?= $photo_source_profilr_pic ?>" class="user-img" alt="user avatar"> 
                    <div class="user-info ps-3">
                        <p class="user-name mb-0"><?= $_SESSION['auth_user']['user_name']; ?></p>
                        <p class="designattion mb-0" style="color: #5e48eb; font-weight: 600;">Teacher</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php"><i class="bx bx-user"></i><span>Profile</span></a></li>
                    <!-- <li><a class="dropdown-item" href="javascript:;"><i class="bx bx-cog"></i><span>Settings</span></a></li> -->
                    <li><a class="dropdown-item" href="index.php"><i class='bx bx-home-circle'></i><span>Dashboard</span></a></li>
                    <!-- <li><a class="dropdown-item" href="javascript:;"><i class='bx bx-dollar-circle'></i><span>Earnings</span></a></li>
                    <li><a class="dropdown-item" href="javascript:;"><i class='bx bx-download'></i><span>Downloads</span></a></li> -->
                    <li><div class="dropdown-divider mb-0"></div></li>
                    <li>
                        <form action="../allcode.php" method="POST">
                            <button type="submit" name="logout_btn" class="dropdown-item"><i class='bx bx-log-out-circle'></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>
