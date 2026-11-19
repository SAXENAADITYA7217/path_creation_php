<?php 
include('includes/header.php');
include('message.php');
?>

     
        <?php 
        include('includes/silder_a.php');
        ?>


<div class="py-5">
    <div class="container">
        <div class="row">

            <div class="col-md-9">
                <?php
                    if(isset($_GET['title']))
                    {
                        $slug = mysqli_real_escape_string($con, $_GET['title']);

                        $posts = "SELECT * FROM posts WHERE slug='$slug' ";
                        $posts_run = mysqli_query($con, $posts);

                        if(mysqli_num_rows($posts_run) > 0)
                        {

                                foreach($posts_run as $postItems)
                                {
                                    ?>
                                            
                                            
                                            <div class="card shadow-sm mb-4">
                                                        <div class="card-header">
                                                            <h5><?= $postItems['name']; ?></h5>

                                                        </div>

                                                        <div class="card-body">
                                                            <label class="text-dark me-2">Posted On: <?= date('d-M-Y', strtotime($postItems['created_at'])); ?></label>
                                                             <hr>
                                                             <?php if($postItems['image']!=null) : ?>
                                                             <img src="uploads/posts/<?= $postItems['image'] ?>" class="w-25" alt="<?=$postItems['name']; ?>" />
                                                             <?php endif; ?>
                                                            <div>
                                                               <h5><?= $postItems['description']; ?></h5>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                    <?php
                                }
                           
                        }
                        else
                        {
                            ?>
                            <h4>No Such Post Found</h4>
                            <?php
                        }
                    }else
                    {
                        ?>
                        <h4>No Such URL Found</h4>
                        <?php
                    }
                    ?>  
             </div>
 
             <div class="col-md-3">
             <div class="card">
                        <div class="card-header">
                            <h4>Advertise Area</h4>
                        </div>
                        <div class="card-body">
                            your advertise
                        </div>
                    </div>
                  <br> 

                    <div class="card mb-4">
                    <div class="card-header">
                        <h4>Related Post</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        // Display related posts
                        if (isset($_GET['title'])) {
                            $query = $con->prepare("SELECT name, slug FROM posts WHERE category_id = (SELECT category_id FROM posts WHERE slug = ?) AND status = 0 AND slug != ?");
                            $query->bind_param("ss", $slug, $slug);
                            $query->execute();
                            $result = $query->get_result();

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<p><a href='post.php?title={$row['slug']}'>{$row['name']}</a></p>";
                                }
                            } else {
                                echo "<p>No related notes found.</p>";
                            }
                        } else {
                            echo "<p>Invalid title format.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
           
        </div>
    </div>
</div>


<div class="py-5 bg-white">
    <div class="container">
        <div class="row">

        <div class="col-md-9">
                  <h3 class="text-dark">Latest Posts</h3>
                  <div style="height: 4px; width: 50px; background-color: red; margin-bottom: 20px;"></div>


                  <?php
           $homePosts = "SELECT * FROM posts WHERE status='0' ORDER BY id DESC LIMIT 12";
           $homePosts_run = mysqli_query($con, $homePosts);

           if(mysqli_num_rows($homePosts_run) > 0)
           {
              foreach($homePosts_run as $homePostItems)
              {
                ?>
                    <div class="mb-4">
                      <a class="text-decoration-none" href="post.php?title=<?= $homePostItems['slug'] ?>">
                      <div class="card card-body bg-light">
                        <?= $homePostItems['name'] ?>
                        
                      </div>
                       </a>
                    </div>
                <?php
              }
           }
        ?>
        </div>
       <div class="col-md-3">
        <div class="card">
          <div class="card-header">
            <h4>Reach Us</h4>
          </div>
          <div class="card-body">
            info@example.com
          </div>
        </div>
       </div>


        </div>
    </div>
</div>

<?php 
include('includes/footer.php');
include('includes/scripts.php');
?>
