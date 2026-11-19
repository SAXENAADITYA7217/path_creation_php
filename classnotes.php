<?php 
include('includes/header.php');
include('message.php');
?>

<div class="py-5 bg-light">
    <div class="container">
        <div class="row">

        <div class="col-md-12">
            <h3 class="text-black">Notes</h3>
            <div style="height: 4px; width: 50px; background-color: red; margin-bottom: 20px;"></div>
        </div>

        <!-- Class Links -->
        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=6">
                <div class="card card-body">Class 6</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=7">
                <div class="card card-body">Class 7</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=8">
                <div class="card card-body">Class 8</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=9">
                <div class="card card-body">Class 9</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=10">
                <div class="card card-body">Class 10</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=11">
                <div class="card card-body">Class 11 Sci (PCB)</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=12">
                <div class="card card-body">Class 11 Sci (PCM)</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=13">
                <div class="card card-body">Class 11 Comm.</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=14">
                <div class="card card-body">Class 11 Arts</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=15">
                <div class="card card-body">Class 12 Sci (PCB)</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=16">
                <div class="card card-body">Class 12 Sci (PCM)</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=17">
                <div class="card card-body">Class 12 Comm.</div>
            </a>
        </div>

        <div class="col-md-3 mb-4">
            <a class="text-decoration-none" href="cnotes.php?class=18">
                <div class="card card-body">Class 12 Arts</div>
            </a>
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
