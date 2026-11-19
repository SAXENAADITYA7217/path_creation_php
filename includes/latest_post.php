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