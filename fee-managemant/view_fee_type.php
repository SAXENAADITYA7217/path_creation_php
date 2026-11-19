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
                    <h5>Monthly Fee
                        <a href="add_fee_type.php" class="btn btn-danger btn-sm float-end">Add Fee Type</a>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="font-size: 12px; overflow-x: auto;">
                        <table class="table align-middle mb-0" style="font-size: 12px; border-collapse: collapse;">
                            <thead class="table-light">
                                <tr>
                                    <th style="padding: 4px;">S.No</th>
                                    <th style="padding: 4px;">Fee Name</th>
                                    <th style="padding: 4px;">Action</th>
                                </tr>
                            </thead>
                            <?php
                                // Corrected query to order by fee_type_id (or another field if necessary)
                                $academy_fee_query = "SELECT * FROM erp_fee_type_detail WHERE fee_detail_type_status = '3' ORDER BY fee_type_id ASC";
                                $result_academy_fee = mysqli_query($con, $academy_fee_query);
        
                                if (mysqli_num_rows($result_academy_fee) > 0) {
                                    $sno = 1; // Initialize serial number counter
                                    while ($row_academy_fee = mysqli_fetch_assoc($result_academy_fee)) {

                            ?>
                            <tbody>
                                <tr>
                                    <td style="padding: 3px;"><?= $sno++; ?></td> <!-- Display Serial Number -->
                                    <td style="padding: 3px;"><?= $row_academy_fee['fee_type_name']; ?></td>
                                    <td style="padding: 3px;">
                                        <a href="edit_fee_type_data.php?id=<?= $row_academy_fee['fee_type_id']; ?>" class="btn btn-primary btn-sm px-2 py-0">
                                            Edit
                                        </a>
                                        <a href="delete_fee_type_data.php?id=<?= $row_academy_fee['fee_type_id']; ?>" class="btn btn-danger btn-sm px-2 py-0">
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


<div class="container-fluid px-1">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Academy Fee Type
                        <a href="add_fee_type.php" class="btn btn-danger btn-sm float-end">Add Fee Type</a>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="font-size: 12px; overflow-x: auto;">
                        <table class="table align-middle mb-0" style="font-size: 12px; border-collapse: collapse;">
                            <thead class="table-light">
                                <tr>
                                    <th style="padding: 4px;">S.No</th>
                                    <th style="padding: 4px;">Fee Name</th>
                                    <th style="padding: 4px;">Action</th>
                                </tr>
                            </thead>
                            <?php
                                // Corrected query to order by fee_type_id (or another field if necessary)
                                $academy_fee_query = "SELECT * FROM erp_fee_type_detail WHERE fee_detail_type_status = '1' ORDER BY fee_type_id ASC";
                                $result_academy_fee = mysqli_query($con, $academy_fee_query);
        
                                if (mysqli_num_rows($result_academy_fee) > 0) {
                                    $sno = 1; // Initialize serial number counter
                                    while ($row_academy_fee = mysqli_fetch_assoc($result_academy_fee)) {

                            ?>
                            <tbody>
                                <tr>
                                    <td style="padding: 3px;"><?= $sno++; ?></td> <!-- Display Serial Number -->
                                    <td style="padding: 3px;"><?= $row_academy_fee['fee_type_name']; ?></td>
                                    <td style="padding: 3px;">
                                        <a href="edit_fee_type_data.php?id=<?= $row_academy_fee['fee_type_id']; ?>" class="btn btn-primary btn-sm px-2 py-0">
                                            Edit
                                        </a>
                                        <a href="delete_fee_type_data.php?id=<?= $row_academy_fee['fee_type_id']; ?>" class="btn btn-danger btn-sm px-2 py-0">
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


<div class="container-fluid px-1">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Store Fee Type
                        <a href="add_fee_type.php" class="btn btn-danger btn-sm float-end">Add Fee Type</a>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="font-size: 12px; overflow-x: auto;">
                        <table class="table align-middle mb-0" style="font-size: 12px; border-collapse: collapse;">
                            <thead class="table-light">
                                <tr>
                                    <th style="padding: 4px;">S.No</th>
                                    <th style="padding: 4px;">Fee Name</th>
                                    <th style="padding: 4px;">Action</th>
                                </tr>
                            </thead>
                            <?php
                                // Corrected query to order by fee_type_id (or another field if necessary)
                                $academy_fee_query = "SELECT * FROM erp_fee_type_detail WHERE fee_detail_type_status = '2' ORDER BY fee_type_id ASC";
                                $result_academy_fee = mysqli_query($con, $academy_fee_query);
        
                                if (mysqli_num_rows($result_academy_fee) > 0) {
                                    $sno = 1; // Initialize serial number counter
                                    while ($row_academy_fee = mysqli_fetch_assoc($result_academy_fee)) {

                            ?>
                            <tbody>
                                <tr>
                                    <td style="padding: 3px;"><?= $sno++; ?></td> <!-- Display Serial Number -->
                                    <td style="padding: 3px;"><?= $row_academy_fee['fee_type_name']; ?></td>
                                    <td style="padding: 3px;">
                                        <a href="edit_fee_type_data.php?id=<?= $row_academy_fee['fee_type_id']; ?>" class="btn btn-primary btn-sm px-2 py-0">
                                            Edit
                                        </a>
                                        <a href="delete_fee_type_data.php?id=<?= $row_academy_fee['fee_type_id']; ?>" class="btn btn-danger btn-sm px-2 py-0">
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
