<?php 
include('includes/header.php');
include('message.php');
?>


<div class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php
                if (isset($_GET['class'])) {
                    $class = intval($_GET['class']);
                    $hometests = "SELECT subject, name, slug FROM test WHERE class = $class AND status = 0 ORDER BY subject";
                    $hometests_run = mysqli_query($con, $hometests);

                    if (mysqli_num_rows($hometests_run) > 0) {
                        $current_subject = '';
                        ?>
                        <h3 class="text-black">Tests for Class <?= $class ?></h3>
                        <div style="height: 4px; width: 50px; background-color: red; margin-bottom: 20px;"></div>
                        <?php
                        foreach ($hometests_run as $hometestsItems) {
                            if ($hometestsItems['subject'] != $current_subject) {
                                if ($current_subject != '') {
                                    echo '</div></div>'; // Close previous subject divs
                                }
                                $current_subject = $hometestsItems['subject'];
                                ?>
                                <div class="col-md-12 mb-4">
                                    <h4 class="text-black"><?= $hometestsItems['subject'] ?></h4>
                                </div>
                                <div class="row">
                                <?php
                            }
                           
                            ?>
                           <div class="col-md-3 mb-4">
                                <a class="text-decoration-none" href="testshow.php?title=<?= $hometestsItems['slug'] ?>">
                                <div class="card card-body">
                                    <?= $hometestsItems['name'] ?>
                                </div>
                                </a>
                            </div>
                            <?php
                        }
                        echo '</div></div>'; // Close last subject divs
                    } else {
                        ?>
                        <p class="text-center text-muted">No tests found for this class.</p>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>




     
    <?php 
        include('includes/latest_post.php');
    ?>


    
    <section id="blog">
        <?php 
        include('includes/home_blog.php');
        ?>
    </section>
       
   



<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>
