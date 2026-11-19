<?php
include('authentication.php');
include('includes/header.php');

// Fetch classes
$academy_class_query = "SELECT * FROM erp_class_data WHERE class_status = '0' ORDER BY class_id ASC";
$result_academy_class = mysqli_query($con, $academy_class_query);

// Fetch sessions
$session_query = "SELECT * FROM erp_session WHERE session_status = '0' ORDER BY session_id ASC";
$result_session = mysqli_query($con, $session_query);

// GET selected values from dropdown
$selected_class = $_GET['class_id'] ?? '';
$selected_session = $_GET['session_id'] ?? '';

?>

<?php include('message.php'); ?>

<div class="container-fluid px-1">
    <div class="row">
        <div class="col-md-12">

            <div class="card">

                <div class="card-header">
                    <div class="row align-items-center">

                        <div class="col-md-4">
                            <h5 class="mb-0">Class Fee</h5>
                        </div>

                        <div class="col-md-8 text-end">

                            <div class="d-inline-flex gap-2">

                                <!-- Class Filter -->
                                <select id="class_id" class="form-control form-control-sm" style="width:180px;">
                                    <option value="">All Class</option>
                                    <?php while ($row_class = mysqli_fetch_assoc($result_academy_class)) { ?>
                                        <option value="<?= $row_class['class_id']; ?>"
                                            <?= ($selected_class == $row_class['class_id']) ? 'selected' : ''; ?>>
                                            <?= $row_class['class_name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <!-- Session Filter -->
                                <select id="session_id" class="form-control form-control-sm" style="width:180px;">
                                    <option value="">All Session</option>
                                    <?php while ($row_session = mysqli_fetch_assoc($result_session)) { ?>
                                        <option value="<?= $row_session['session_id']; ?>"
                                            <?= ($selected_session == $row_session['session_id']) ? 'selected' : ''; ?>>
                                            <?= $row_session['session_name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>

                                <a href="add_class_fee.php" class="btn btn-danger btn-sm">
                                    Add Class Fee
                                </a>

                            </div>
                        </div>

                    </div>
                </div>


                <div class="card-body mb-0">
                    <div class="table-responsive" style="font-size: 12px; overflow-x: auto;">

                        <table id="example_sort_data" class="table align-middle mb-0" style="font-size: 12px; border-collapse: collapse;">

                            <thead class="table-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Class</th>
                                    <th>Fee Name</th>
                                    <th>Fee Amount</th>
                                    <th>Session</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // BUILD MAIN QUERY
                                $query = "SELECT * FROM erp_class_fee WHERE class_fee_status='0'";

                                if ($selected_class != "") {
                                    $query .= " AND class_id = '$selected_class'";
                                }
                                if ($selected_session != "") {
                                    $query .= " AND session_id = '$selected_session'";
                                }

                                $query .= " ORDER BY class_fee_id DESC";

                                $result_class_fee = mysqli_query($con, $query);

                                if (mysqli_num_rows($result_class_fee) > 0) {
                                    $sno = 1;
                                    while ($row_class_fee = mysqli_fetch_assoc($result_class_fee)) {

                                        $class_id = $row_class_fee['class_id'];
                                        $fee_type_id = $row_class_fee['fee_type_id'];
                                        $session_id = $row_class_fee['session_id'];

                                        // Class Name
                                        $class_q = mysqli_query($con, "SELECT class_name FROM erp_class_data WHERE class_id='$class_id' LIMIT 1");
                                        $class_name = mysqli_fetch_assoc($class_q)['class_name'] ?? 'N/A';

                                        // Fee Type Name
                                        $fee_q = mysqli_query($con, "SELECT fee_type_name FROM erp_fee_type_detail WHERE fee_type_id='$fee_type_id' LIMIT 1");
                                        $fee_type_name = mysqli_fetch_assoc($fee_q)['fee_type_name'] ?? 'N/A';

                                        // Session Name
                                        $ses_q = mysqli_query($con, "SELECT session_name FROM erp_session WHERE session_id='$session_id' LIMIT 1");
                                        $session_name = mysqli_fetch_assoc($ses_q)['session_name'] ?? 'N/A';
                                ?>

                                    
                                        <tr>
                                            <td><?= $sno++; ?></td>
                                            <td><?= $class_name; ?></td>
                                            <td><?= $fee_type_name; ?></td>
                                            <td><?= $row_class_fee['class_fee_amount']; ?></td>
                                            <td><?= $session_name; ?></td>

                                            <td>
                                                <a href="edit_class_fee_data.php?id=<?= $row_class_fee['class_fee_id']; ?>" class="btn btn-primary btn-sm px-2 py-0">
                                                    Edit
                                                </a>
                                                <a href="delete_class_fee_data.php?id=<?= $row_class_fee['class_fee_id']; ?>" class="btn btn-danger btn-sm px-2 py-0">
                                                    Delete
                                                </a>
                                            </td>
                                        </tr>
                                    
                                <?php
                                    }
                                } else { ?>

                                    No Fee Records Found

                                <?php } ?>
                            </tbody>
                        </table>

                    </div>
                </div>

            </div>

        </div>
    </div>
</div>


<!-- FILTER SCRIPT -->
<script>
    document.getElementById('class_id').addEventListener('change', function () {
        let class_id = this.value;
        let session_id = document.getElementById('session_id').value;
        window.location.href = "?class_id=" + class_id + "&session_id=" + session_id;
    });

    document.getElementById('session_id').addEventListener('change', function () {
        let session_id = this.value;
        let class_id = document.getElementById('class_id').value;
        window.location.href = "?class_id=" + class_id + "&session_id=" + session_id;
    });
</script>


<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
