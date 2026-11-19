<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="intro">
                <h6>Blog</h6>
                <h1>Blog Posts</h1>
                <p class="mx-auto">Learning to adapt to new situations can lead to personal growth and open doors to unexpected opportunities.</p>
            </div>
        </div>
    </div>
    <div class="row">
        <?php
            $posts = "SELECT * FROM posts WHERE slug='Knowledge'";
            $posts_run = mysqli_query($con, $posts);

            if (mysqli_num_rows($posts_run) > 0) {
                while ($postItems = mysqli_fetch_assoc($posts_run)) {
                    ?>
                    <div class="col-md-4">
                        <article class="blog-post">
                            <img src="uploads/posts/<?= ($postItems['image']) ?>" alt="">
                            <a href="post.php?title=<?=$postItems['slug'];?>" class="tag"><?= ($postItems['name']) ?></a>
                            <div class="content">
                            <small><?= date('d M, Y', strtotime($postItems['created_at'])) ?></small>
                                <h5><?= ($postItems['name']) ?> </h5>	
                                <p><?= ($postItems['meta_description']) ?></p>
                            </div>
                        </article>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No posts found</p>";
            }
        ?>
             <?php
            $posts = "SELECT * FROM posts WHERE slug='Board-Exam'";
            $posts_run = mysqli_query($con, $posts);

            if (mysqli_num_rows($posts_run) > 0) {
                while ($postItems = mysqli_fetch_assoc($posts_run)) {
                    ?>
                    <div class="col-md-4">
                        <article class="blog-post">
                            <img src="uploads/posts/<?= ($postItems['image']) ?>" alt="">
                            <a href="post.php?title=<?=$postItems['slug'];?>" class="tag"><?= ($postItems['name']) ?></a>
                            <div class="content">
                            <small><?= date('d M, Y', strtotime($postItems['created_at'])) ?></small>
                                <h5><?= ($postItems['name']) ?></h5>	
                                <p><?= ($postItems['meta_description']) ?></p>
                            </div>
                        </article>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No posts found</p>";
            }
        ?>
             <?php
            $posts = "SELECT * FROM posts WHERE slug='Indian-Force'";
            $posts_run = mysqli_query($con, $posts);

            if (mysqli_num_rows($posts_run) > 0) {
                while ($postItems = mysqli_fetch_assoc($posts_run)) {
                    ?>
                    <div class="col-md-4">
                        <article class="blog-post">
                            <img src="uploads/posts/<?= ($postItems['image']) ?>" alt="">
                            <a href="post.php?title=<?=$postItems['slug'];?>" class="tag"><?= ($postItems['name']) ?></a>
                            <div class="content">
                            <small><?= date('d M, Y', strtotime($postItems['created_at'])) ?></small>
                                <h5><?= ($postItems['name']) ?> in 2024</h5>	
                                <p><?= ($postItems['meta_description']) ?></p>
                            </div>
                        </article>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No posts found</p>";
            }
        ?>

        <!--
        <div class="col-md-4">
            <article class="blog-post">
                <img src="img/project4.jpg" alt="">
                <a href="#" class="tag">Programming</a>
                <div class="content">
                    <small>01 Dec, 2022</small>
                    <h5>Web Design trends in 2022</h5>
                    <p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from</p>
                </div>
            </article>
        </div>
        <div class="col-md-4">
            <article class="blog-post">
                <img src="img/project2.jpg" alt="">
                <a href="#" class="tag">Marketing</a>
                <div class="content">
                    <small>01 Dec, 2022</small>
                    <h5>Web Design trends in 2022</h5>
                    <p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from</p>
                </div>
            </article>
        </div>
    </div>
</div>  -->
