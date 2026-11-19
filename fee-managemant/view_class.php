<?php
include('authentication.php');
include('includes/header.php');
?>

<?php include('message.php'); ?>


<div class="container-fluid px-1">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Class Data
                        <a href="add_class.php" class="btn btn-danger btn-sm float-end">Add New Class</a>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="font-size: 12px; overflow-x: auto;">
                        <table class="table align-middle mb-0" style="font-size: 12px; border-collapse: collapse;">
                            <thead class="table-light">
                                <tr>
                                    <th style="padding: 4px;">S.No</th>
                                    <th style="padding: 4px;">Class Name</th>
                                    <th style="padding: 4px;">Action</th>
                                </tr>
                            </thead>
                            <?php
                                // Corrected query to order by fee_type_id (or another field if necessary)
                                $academy_class_query = "SELECT * FROM erp_class_data WHERE class_status = '0' ORDER BY class_id ASC";
                                $result_academy_class = mysqli_query($con, $academy_class_query);
        
                                if (mysqli_num_rows($result_academy_class) > 0) {
                                    $sno = 1; // Initialize serial number counter
                                    while ($row_academy_class = mysqli_fetch_assoc($result_academy_class)) {

                            ?>
                            <tbody>
                                <tr>
                                    <td style="padding: 3px;"><?= $sno++; ?></td> <!-- Display Serial Number -->
                                    <td style="padding: 3px;"><?= $row_academy_class['class_name']; ?></td>
                                    <td style="padding: 3px;">
                                        <a href="edit_fee_type_data.php?id=<?= $row_academy_class['class_id']; ?>" class="btn btn-primary btn-sm px-2 py-0">
                                            Edit
                                        </a>
                                        <a href="delete_fee_type_data.php?id=<?= $row_academy_class['class_id']; ?>" class="btn btn-danger btn-sm px-2 py-0">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                            <?php } } else { ?>
                            <tbody>
                                <tr>
                                    <td colspan="3" style="padding: 3px; text-align: center;">No Fee Types Found</td>
                                </tr>
                            </tbody>
                            <?php } ?>
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
