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
                    <h4>View Courses
                        <a href="courses-add.php" class="btn btn-primary float-end">Add Course</a>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>MRP Price</th>
                                    <th>Discounted Price</th>
                                    <th>Mode of Classes</th>
                                    <th>Slug</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $courses = "SELECT * FROM add_courses";
                                $courses_run = mysqli_query($con, $courses);

                                if(mysqli_num_rows($courses_run) > 0) {
                                    foreach($courses_run as $course) {
                                        ?>
                                        <tr>
                                            <td><?= $course['id'] ?></td>
                                            <td><?= $course['name'] ?></td>
                                            <td><?= $course['class'] ?></td>
                                            <td><?= $course['mrp_price'] ?></td>
                                            <td><?= $course['discounted_price'] ?></td>
                                            <td><?= $course['mode_of_classes'] ?></td>
                                            <td><?= $course['slug'] ?></td>
                                            <td><img src="../uploads/courses/<?= $course['image'] ?>" width="60px" height="60px"/></td>
                                            <td><?= $course['status'] == '1' ? 'Hidden' : 'Visible' ?></td>
                                            <td>
                                                <a href="courses-edit.php?id=<?= $course['id'] ?>" class="btn btn-success">Edit</a>
                                            </td>
                                            <td>
                                                <form action="code.php" method="POST">
                                                    <button type="submit" name="course_delete_btn" value="<?= $course['id'] ?>" class="btn btn-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="15">No Record Found</td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
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
