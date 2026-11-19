<?php 
include('includes/header.php');
include('message.php');
include('includes/silder_a.php');
?>
<br>
<div class="container">
        <div class="row">     
            <div class="col-md-4" style="margin-bottom: 20px;">
                <div class="section" id="daily-news" style="background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 20px;">
                    <div class="section-header" style="border-bottom: 2px solid #42b883; margin-bottom: 10px;">
                        <h2 style="margin: 0; font-size: 1.2em; color: #333;">Daily News</h2>
                    </div>
                    <ul style="list-style-type: none; padding: 0;">
                    <?php
                      $posts = "SELECT * FROM posts WHERE category_id='7'";
                      $posts_run = mysqli_query($con, $posts);          
                    if (mysqli_num_rows($posts_run) > 0) {
                        while ($postItems = mysqli_fetch_assoc($posts_run)) {
                            ?>
                                    <li style="margin: 10px 0;"><a href="post.php?title=<?=$postItems['slug'];?>" style="text-decoration: none; color: #3498db; display: block;"><?= date('d-M-Y', strtotime($postItems['created_at'])); ?></a></li>
                        <?php }
                        } else {
                            echo "<p>No posts found.</p>";
                        } ?>  
                                        
                    </ul>
                </div>
            </div>
            
            <div class="col-md-4" style="margin-bottom: 20px;">
                <div class="section" id="important-editorials" style="background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 20px;">
                    <div class="section-header" style="border-bottom: 2px solid #b19cd9; margin-bottom: 10px;">
                        <h2 style="margin: 0; font-size: 1.2em; color: #333;">Important Editorials</h2>
                    </div>
                    <ul style="list-style-type: none; padding: 0;">
                            <?php
                            $posts = "SELECT * FROM posts WHERE category_id='6'";
                            $posts_run = mysqli_query($con, $posts);          
                            if (mysqli_num_rows($posts_run) > 0) {
                                while ($postItems = mysqli_fetch_assoc($posts_run)) {
                                    ?>
                                            <li style="margin: 10px 0;"><a href="post.php?title=<?=$postItems['slug'];?>" style="text-decoration: none; color: #3498db; display: block;"><?php echo $postItems['name']; ?></a></li>
                                <?php }
                                } else {
                                    echo "<p>No posts found.</p>";
                                } ?>  
                    </ul>
                </div>
            </div>
            <div class="col-md-4" style="margin-bottom: 20px;">
                <div class="section" id="more-links" style="background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); padding: 20px;">
                    <div class="section-header" style="border-bottom: 2px solid #ff6f61; margin-bottom: 10px;">
                        <h2 style="margin: 0; font-size: 1.2em; color: #333;">More Links</h2>
                    </div>
                    <ul style="list-style-type: none; padding: 0;">
                            <?php
                           $posts = "SELECT * FROM posts ORDER BY created_at DESC LIMIT 10;";
                            $posts_run = mysqli_query($con, $posts);          
                            if (mysqli_num_rows($posts_run) > 0) {
                                while ($postItems = mysqli_fetch_assoc($posts_run)) {
                                    ?>
                                            <li style="margin: 10px 0;"><a href="post.php?title=<?=$postItems['slug'];?>" style="text-decoration: none; color: #3498db; display: block;"><?php echo $postItems['name']; ?></a></li>
                                <?php }
                                } else {
                                    echo "<p>No posts found.</p>";
                                } ?>  
                    </ul>
                </div>
            </div>
        </div>
    </div>


<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>
