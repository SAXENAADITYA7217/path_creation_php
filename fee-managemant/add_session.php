<?php
include('authentication.php');
include('includes/header.php');
?>

<?php include('message.php'); ?>

<div class="container-fluid px-1">
    <div class="row mt-1">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header mb-0">
                    <h5>Add Session
                        <a href="#" class="btn btn-primary btn-sm float-end" id="addButton">Back</a>
                    </h5>
                </div>
                <div class="card-body mb-0">
                    <form action="master_code.php" method="POST">

                        <div class="row">

                            <div class="col-md-3 mb-3">
                                <label for="">Add Session</label>
                                <input type="text" id="session" name="session_name" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="">Session Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control form-control-sm" required>
                            </div>


                            <div class="col-md-3 mb-3">
                                <label for="">Session End Date</label>
                                <input type="date" name="end_date" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <br>
                                <button type="submit" name="add_session_data" class="btn btn-primary btn-sm">Add Session Data</button>
                            </div> 

                        </div>    

                    </form>
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
                    <h5>Session</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive" style="font-size: 12px; overflow-x: auto;">
                        <table class="table align-middle mb-0" style="font-size: 12px; border-collapse: collapse;">
                            <thead class="table-light">
                                <tr>
                                    <th style="padding: 4px;">S.No</th>
                                    <th style="padding: 4px;">Session Name</th>
                                    <th style="padding: 4px;">Session Start Date</th>
                                    <th style="padding: 4px;">Session End Date</th>
                                    <th style="padding: 4px;">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php
                                $academy_session_query = "SELECT * FROM erp_session WHERE session_status = '0' ORDER BY id ASC";
                                $result_session = mysqli_query($con, $academy_session_query);

                                if (mysqli_num_rows($result_session) > 0) {
                                    $sno = 1;
                                    while ($row_session = mysqli_fetch_assoc($result_session)) {
                            ?>
                                <tr>
                                    <td style="padding: 3px;"><?= $sno++; ?></td>
                                    <td style="padding: 3px;"><?= $row_session['session_name']; ?></td>
                                    <td style="padding: 3px;"><?= $row_session['session_start_date']; ?></td>
                                    <td style="padding: 3px;"><?= $row_session['session_end_date']; ?></td>
                                    <td style="padding: 3px;">
                                        <a href="edit_session_data.php?id=<?= $row_session['id']; ?>" 
                                           class="btn btn-primary btn-sm px-2 py-0">
                                            Edit
                                        </a>

                                        <a href="delete_session_data.php?id=<?= $row_session['id']; ?>" 
                                           class="btn btn-danger btn-sm px-2 py-0"
                                           onclick="return confirm('Are you sure you want to delete this session?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php 
                                    } 
                                } else { 
                            ?>
                                <tr>
                                    <td colspan="5" style="padding: 3px; text-align: center;">No Session Found</td>
                                </tr>
                            <?php } ?>
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