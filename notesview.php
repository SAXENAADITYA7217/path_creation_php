<?php 
include('includes/header.php');
include('message.php');
include('includes/silder_a.php');
?>

<div class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-9">
                <?php
                if (isset($_GET['title'])) {
                    $slug = mysqli_real_escape_string($con, $_GET['title']);

                    $query = $con->prepare("SELECT * FROM writenotes WHERE slug = ?");
                    $query->bind_param("s", $slug);
                    $query->execute();
                    $result = $query->get_result();

                    if ($result->num_rows > 0) {
                        $postItems = $result->fetch_assoc();
                        $class = $postItems['class'];
                        $subject = $postItems['subject'];
                ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <h3><?= $postItems['name']; ?></h3>
                            </div>
                            <div class="card-body">
                                <label class="text-dark me-2">Posted On: <?= date('d-M-Y', strtotime($postItems['created_at'])); ?></label>
                                <hr>
                                <?php if ($postItems['image'] != null) : ?>
                                    <img src="uploads/writenotes/<?= $postItems['image'] ?>" class="w-25" alt="<?= $postItems['name']; ?>" />
                                <?php endif; ?>
                                <div>
                                    <h5><?= $postItems['description']; ?></h5>
                                </div>
                            </div>
                        </div>
                <?php
                    } else {
                        echo "<h4>No Such Post Found</h4>";
                    }
                } else {
                    echo "<h4>No Such URL Found</h4>";
                }
                ?>
            </div>

            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Advertise Area</h4>
                    </div>
                    <div class="card-body">
                        Your advertisement here
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Get Subject Data</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        if (isset($class) && isset($subject)) {
                            $query = $con->prepare("SELECT name, slug FROM writenotes WHERE class = ? AND subject = ? AND status = 0 AND slug != ?");
                            $query->bind_param("iss", $class, $subject, $slug);
                            $query->execute();
                            $result = $query->get_result();

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<p><a href='notesview.php?title={$row['slug']}'>{$row['name']}</a></p>";
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
                <h3 class="text-dark">Latest Notes</h3>
                <div style="height: 4px; width: 50px; background-color: red; margin-bottom: 20px;"></div>
                <?php
                $homePosts = "SELECT * FROM writenotes WHERE status='0' ORDER BY id DESC LIMIT 12";
                $homePosts_run = mysqli_query($con, $homePosts);

                if (mysqli_num_rows($homePosts_run) > 0) {
                    while ($homePostItems = mysqli_fetch_assoc($homePosts_run)) {
                ?>
                        <div class="mb-4">
                            <a class="text-decoration-none" href="notesview.php?title=<?= $homePostItems['slug'] ?>">
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
