    <!-- TOP NAV -->
    <div class="top-nav" id="home">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-auto">
                    <p> <i class='bx bxs-envelope'></i> pathcreationacademy@gmail.com</p>
                    <p> <i class='bx bxs-phone-call'></i> 9560104951</p>
                </div>
                <div class="col-auto social-icons">
                    <a href="https://www.facebook.com/thelokender"><i class='bx bxl-facebook'></i></a>
                  <!--  <a href="#"><i class='bx bxl-twitter'></i></a>  -->
                    <a href="https://www.instagram.com/path_creation_academy?igsh=djhoNmVobG53bnV1"><i class='bx bxl-instagram'></i></a>
                    <a href="https://www.youtube.com/@pathcreationacademy"><i class='bx bxl-youtube'></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- BOTTOM NAV -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#"><span class="dot"><img src="img/logo.png" alt="Path Creation Academy Image" style="width: 48px; height: auto;"></span>Path Creation Academy</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'courses.php') ? 'active' : ''; ?>" href="courses.php">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'classnotes.php') ? 'active' : ''; ?>" href="classnotes.php">Class Notes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'testseries.php') ? 'active' : ''; ?>" href="testseries.php">Practice Paper</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'currentaffairs.php') ? 'active' : ''; ?>" href="currentaffairs.php">Current affairs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'store.php') ? 'active' : ''; ?>" href="store.php">Store</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'blog.php') ? 'active' : ''; ?>" href="blog.php">Blog</a>
                    </li>
                


     <?php if(isset($_SESSION['auth_user'])) : ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle btn btn-brand ms-lg-3" href="#" role="button"  data-bs-toggle="dropdown" aria-expanded="false" style="color: white;">
            <?= $_SESSION['auth_user']['user_name'];  ?>
          </a>
          <ul class="dropdown-menu">

            <li><a class="dropdown-item" 
                    <?php 
                            if ($_SESSION['auth_role'] == "1") {
                                echo 'href="admin/index.php"';
                            } elseif ($_SESSION['auth_role'] == "0") {
                                echo 'href="studentdash/index.php"';
                            } elseif ($_SESSION['auth_role'] == "2") {
                                echo 'href="teacher-admin/index.php"';
                            }
                            ?>>
                            Dashboard</a></li>
            
            <form action="allcode.php" method="POST">
                <button type="submit" name="logout_btn" class="dropdown-item">Logout</button>
            </form>

            </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Upcoming feature...</a></li>
          </ul>
        </li>

        <?php else : ?>

            <a href="#" data-bs-toggle="modal" data-bs-target="#exampleModal"
                    class="btn btn-brand ms-lg-3">Login</a>
      
        <?php endif; ?>
        </ul>
            </div>
        </div>
    </nav>