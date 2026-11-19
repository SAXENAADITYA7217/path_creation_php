<nav class="sb-topnav navbar navbar-expand d-flex align-items-center justify-content-between">
    <!-- Left: Sidebar Toggle -->
    <div class="d-flex align-items-center">
        <button class="btn btn-link btn-sm me-3" id="sidebarToggle" href="#!">
            <i class="fas fa-bars fs-5"></i>
        </button>
    </div>

    <!-- Center: Search Pill Bar -->
    <div class="top-search-bar d-none d-md-block">
        <i class="fas fa-search search-icon"></i>
        <input type="text" placeholder="Search for students, courses, notes..." aria-label="Search" />
    </div>

    <!-- Right: Notification & Profile -->
    <div class="d-flex align-items-center gap-3 pe-2">
        <!-- Notification Bell -->
        <a href="#!" class="notification-bell-btn" title="Notifications">
            <i class="far fa-bell fs-6"></i>
            <span class="bell-badge"></span>
        </a>

        <!-- Admin Profile Dropdown -->
        <div class="dropdown">
            <div class="admin-profile-pill dropdown-toggle" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="admin-avatar-circle">
                    <?= isset($_SESSION['auth_user']['user_name']) ? strtoupper(substr($_SESSION['auth_user']['user_name'], 0, 1)) : 'A'; ?>
                </div>
                <span class="admin-profile-name d-none d-sm-inline">
                    <?= isset($_SESSION['auth_user']['user_name']) ? htmlspecialchars($_SESSION['auth_user']['user_name']) : 'Admin'; ?>
                </span>
            </div>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2" aria-labelledby="navbarUserDropdown" style="min-width: 190px;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-dark small"><?= isset($_SESSION['auth_user']['user_name']) ? htmlspecialchars($_SESSION['auth_user']['user_name']) : 'Administrator'; ?></div>
                    <div class="text-muted" style="font-size: 0.75rem;">Path Creation Academy</div>
                </li>
                <li><a class="dropdown-item py-2" href="profile-view.php"><i class="far fa-user me-2 text-muted"></i> Profile</a></li>
                <li><a class="dropdown-item py-2" href="index.php"><i class="fas fa-th-large me-2 text-muted"></i> Dashboard</a></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form action="../allcode.php" method="POST" class="m-0 p-0">
                        <button type="submit" name="logout_btn" class="dropdown-item py-2 text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
