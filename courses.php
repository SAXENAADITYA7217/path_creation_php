<?php 
include('includes/header.php');
include('message.php');
?>

     
        <?php 
        include('includes/card_courses.php');
        ?>

            <div class="card_course">
            <div class="row_courses">


                    <?php
                    $homecourses = "SELECT * FROM add_courses WHERE status='0'";
                    $homecourses_run = mysqli_query($con, $homecourses);

                    if(mysqli_num_rows($homecourses_run) > 0)
                    {
                        foreach($homecourses_run as $homecoursesItems)
                        {
                            ?>
                <div class="column_courses">
                    <div class="content_courses">
                    <a href="coursesshow.php?title=<?= ($homecoursesItems['slug']) ?>" style="text-decoration: none;" >  
                    <img src="uploads/courses/<?= ($homecoursesItems['image']) ?>" alt="Mountains" style="width: 100%">
                    <h4><?= ($homecoursesItems['name']) ?></h4>
                    <p><?= ($homecoursesItems['mode_of_classes']) ?> - 	<?= ($homecoursesItems['class']) ?></p>
                    <div class="cardbottom">
                        <div class="cbottom-a">
                        <h5><s>&#8377;<?= ($homecoursesItems['mrp_price']) ?>/-</s>  <br> &#8377;<?= ($homecoursesItems['discounted_price']) ?>/-</h5>  
                        </div>
                        <div class="cbottom-b">
                        <img src="img/tag_1.png" alt="Description of the image" >
                        </div>           
                    </div>  
                    </a>    
                    </div>
                </div>
                    <?php
                    }
                    }
                ?>
            </div>
            </div>






     
    <?php 
        include('includes/latest_post.php');
    ?>


    <section class="bg-light" id="reviews">
        <?php 
        include('includes/reviews.php');
        ?>
       
    </section>


    <section id="blog">
        <?php 
        include('includes/home_blog.php');
        ?>
    </section>


   

<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>
