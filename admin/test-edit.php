<?php
include('authentication.php');
include('includes/header.php');
?>

<div class="container-fluid px-4">
    <div class="row mt-4">
        <div class="col-md-12">
            <?php include('message.php'); ?>
            <div class="card">
                <div class="card-header">
                    <h4>Edit Test
                        <a href="test-view.php" class="btn btn-danger float-end">Back</a>
                    </h4>
                </div>
                <div class="card-body">
                    <?php 
                    if(isset($_GET['id']))
                    {
                        $post_id = $_GET['id'];
                        $post_query = "SELECT * FROM test WHERE id='$post_id' LIMIT 1";
                        $post_query_res = mysqli_query($con, $post_query);

                        if(mysqli_num_rows($post_query_res) > 0)
                        {
                            $post_row = mysqli_fetch_array($post_query_res);
                            ?>
                            <form action="code.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="post_id" value="<?= $post_row['id'] ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="class">Class</label>
                                        <select name="class" required class="form-control">
                                            <option value="">Select Class</option>
                                            <option value="6" <?= $post_row['class'] == '6' ? 'selected' : '' ?>>Class 6</option>
                                            <option value="7" <?= $post_row['class'] == '7' ? 'selected' : '' ?>>Class 7</option>
                                            <option value="8" <?= $post_row['class'] == '8' ? 'selected' : '' ?>>Class 8</option>
                                            <option value="9" <?= $post_row['class'] == '9' ? 'selected' : '' ?>>Class 9</option>
                                            <option value="10" <?= $post_row['class'] == '10' ? 'selected' : '' ?>>Class 10</option>
                                            <option value="11" <?= $post_row['class'] == '11' ? 'selected' : '' ?>>Class 11 Sci (PCB)</option>
                                            <option value="12" <?= $post_row['class'] == '12' ? 'selected' : '' ?>>Class 11 Sci (PCM)</option>
                                            <option value="13" <?= $post_row['class'] == '13' ? 'selected' : '' ?>>Class 11 Comm.</option>
                                            <option value="14" <?= $post_row['class'] == '14' ? 'selected' : '' ?>>Class 11 Arts</option>
                                            <option value="15" <?= $post_row['class'] == '15' ? 'selected' : '' ?>>Class 12 Sci (PCB)</option>
                                            <option value="16" <?= $post_row['class'] == '16' ? 'selected' : '' ?>>Class 12 Sci (PCM)</option>
                                            <option value="17" <?= $post_row['class'] == '17' ? 'selected' : '' ?>>Class 12 Comm.</option>
                                            <option value="18" <?= $post_row['class'] == '18' ? 'selected' : '' ?>>Class 12 Arts</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="subject">Subject</label>
                                        <select name="subject" required class="form-control">
                                            <option value="">Select Subject</option>
                                            <option value="Mathematics" <?= $post_row['subject'] == 'Mathematics' ? 'selected' : '' ?>>Mathematics</option>
                                            <option value="Science" <?= $post_row['subject'] == 'Science' ? 'selected' : '' ?>>Science</option>
                                            <option value="Hindi" <?= $post_row['subject'] == 'Hindi' ? 'selected' : '' ?>>Hindi</option>
                                            <option value="English" <?= $post_row['subject'] == 'English' ? 'selected' : '' ?>>English</option>
                                            <option value="History" <?= $post_row['subject'] == 'History' ? 'selected' : '' ?>>History</option>
                                            <option value="Economics" <?= $post_row['subject'] == 'Economics' ? 'selected' : '' ?>>Economics</option>
                                            <option value="Civics" <?= $post_row['subject'] == 'Civics' ? 'selected' : '' ?>>Civics</option>
                                            <option value="Physics" <?= $post_row['subject'] == 'Physics' ? 'selected' : '' ?>>Physics</option>
                                            <option value="Chemistry" <?= $post_row['subject'] == 'Chemistry' ? 'selected' : '' ?>>Chemistry</option>
                                            <option value="Maths" <?= $post_row['subject'] == 'Maths' ? 'selected' : '' ?>>Maths</option>
                                            <option value="Biology" <?= $post_row['subject'] == 'Biology' ? 'selected' : '' ?>>Biology</option>
                                            <option value="Physical" <?= $post_row['subject'] == 'Physical' ? 'selected' : '' ?>>Physical Education</option>
                                            <option value="Accounts" <?= $post_row['subject'] == 'Accounts' ? 'selected' : '' ?>>Accounts</option>
                                            <option value="Business" <?= $post_row['subject'] == 'Business' ? 'selected' : '' ?>>Business Studies</option>
                                            <option value="Political" <?= $post_row['subject'] == 'Political' ? 'selected' : '' ?>>Political Science</option>
                                            <option value="IT" <?= $post_row['subject'] == 'IT' ? 'selected' : '' ?>>IT (Information Technology)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="">Name</label>
                                        <input type="text" name="name" value="<?= $post_row['name'] ?>" required class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="">Slug (url)</label>
                                        <input type="text" name="slug" value="<?= $post_row['slug'] ?>" required class="form-control">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="">Description</label>
                                        <textarea name="description" id="summernote" required class="form-control" rows="4"><?= $post_row['description'] ?></textarea>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="">Meta Title</label>
                                        <input type="text" name="meta_title" value="<?= $post_row['meta_title'] ?>" max="191" required class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="">Meta Description</label>
                                        <textarea name="meta_description" required class="form-control" rows="4"><?= $post_row['meta_description'] ?></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="">Meta Keyword</label>
                                        <textarea name="meta_keyword" required class="form-control" rows="4"><?= $post_row['meta_keyword'] ?></textarea>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="">Image</label>
                                        <input type="hidden" name="old_image" value="<?= $post_row['image'] ?>">
                                        <?php if (!empty($post_row['image'])) { ?>
                                            <img src="../uploads/test/<?= $post_row['image'] ?>" alt="<?= $post_row['name'] ?>" width="100" height="100">
                                        <?php } ?>
                                        <input type="file" name="image" class="form-control">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="">Status</label>
                                        <input type="checkbox" name="status" <?= $post_row['status'] == '1' ? 'checked':'' ?> width="70px" height="70px"/>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <button type="submit" name="test_update" class="btn btn-primary">Update Post</button>
                                    </div> 
                                </div>
                            </form>
                            <?php
                        }
                        else
                        {
                            ?>
                            <h4>No Record Found</h4>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
