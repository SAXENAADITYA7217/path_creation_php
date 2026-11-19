<?php
include('authentication.php');
include('includes/header.php');

if(isset($_GET['id'])) {
    $course_id = $_GET['id'];
    $course_query = "SELECT * FROM add_courses WHERE id='$course_id'";
    $course_query_run = mysqli_query($con, $course_query);

    if(mysqli_num_rows($course_query_run) > 0) {
        $course = mysqli_fetch_array($course_query_run);
    } else {
        $_SESSION['message'] = "No Course Found";
        header('Location: courses-view.php');
        exit(0);
    }
} else {
    $_SESSION['message'] = "No Course ID Provided";
    header('Location: courses-view.php');
    exit(0);
}
?>

<div class="container-fluid px-4">
    <div class="row mt-4">
        <div class="col-md-12">
            <?php include('message.php'); ?>
            <div class="card">
                <div class="card-header">
                    <h4>Edit Course
                        <a href="courses-view.php" class="btn btn-danger float-end">Back</a>
                    </h4>
                </div>
                <div class="card-body">
                    <form action="code.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                        <input type="hidden" name="old_image" value="<?= $course['image'] ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="">Course Name</label>
                                <input type="text" name="name" value="<?= $course['name'] ?>" required class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="">Class</label>
                                <input type="text" name="class" value="<?= $course['class'] ?>" required class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="">MRP Price</label>
                                <input type="text" name="mrp_price" value="<?= $course['mrp_price'] ?>" required class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="">Discounted Price</label>
                                <input type="text" name="discounted_price" value="<?= $course['discounted_price'] ?>" required class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="">Mode of Classes</label>
                                <input type="text" name="mode_of_classes" value="<?= $course['mode_of_classes'] ?>" required class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="">Slug (url)</label>
                                <input type="text" name="slug" value="<?= $course['slug'] ?>" required class="form-control">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="">Description</label>
                                <textarea name="description" required class="form-control" rows="4"><?= $course['description'] ?></textarea>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="">Meta Title</label>
                                <input type="text" name="meta_title" value="<?= $course['meta_title'] ?>" max="191" required class="form-control">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="">Meta Description</label>
                                <textarea name="meta_description" required class="form-control" rows="4"><?= $course['meta_description'] ?></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="">Meta Keyword</label>
                                <textarea name="meta_keyword" required class="form-control" rows="4"><?= $course['meta_keyword'] ?></textarea>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="">Image</label>
                                <input type="file" name="image" class="form-control">
                                <img src="../uploads/courses/<?= $course['image'] ?>" width="60px" height="60px" alt="Current Image">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="">Status</label>
                                <input type="checkbox" name="status" <?= $course['status'] == '1' ? 'checked' : '' ?> width="70px" height="70px"/>
                            </div>

                            <div class="col-md-12 mb-3">
                                <button type="submit" name="course_update" class="btn btn-primary">Update Course</button>
                            </div> 
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
