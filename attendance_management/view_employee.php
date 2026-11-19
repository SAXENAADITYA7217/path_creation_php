<?php
include('authentication.php');
include('includes/header.php');
?>

<?php include('message.php'); ?>
<div class="card radius-10">
    <div class="card-body">
        <div class="d-flex align-items-center">
            <div>
                <h5 class="mb-0">Employee List</h5>
            </div>
            <div class="dropdown options ms-auto">
                <div class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                    <i class='bx bx-dots-horizontal-rounded'></i>
                </div>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="add_employee.php">Add New Employee</a></li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee ID</th>
                        <th>Profile</th>
                        <th>Name</th>
                        <th>Phone No</th>
                        <th>Designation</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Worker Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $masterid = $_SESSION['auth_user']['user_master_id'];
                        $query = "SELECT * FROM employee_data WHERE masterid = '$masterid' ORDER BY CAST(employee_id AS UNSIGNED) ASC";
                        $query_run = mysqli_query($con, $query);

                        if(mysqli_num_rows($query_run) > 0) {
                            foreach($query_run as $item) {
                    ?>
                    <tr>
                        <td><?= $item['employee_id'] ?></td>
                        <td>
                            <?php
                                $profileImage = base64_encode($item['employee_profile']);
                                echo '<img src="data:image/jpeg;base64,'.$profileImage.'" width="50" height="50" style="object-fit: cover; border-radius: 50%;">';
                            ?>
                        </td>
                        <td><?= $item['employee_name'] ?></td>
                        <td><?= $item['phone_no'] ?></td>
                        <td><?= $item['designation'] ?></td>
                        <td><?= date("g:i A", strtotime($item['start_time'])) ?></td>
                        <td><?= date("g:i A", strtotime($item['end_time'])) ?></td>
                        <td><?= $item['worker_type'] ?></td>
                        <td>
                            <div class="badge rounded-pill w-100 
                                <?= $item['employee_status'] == '0' ? 'bg-light-success text-success' : 'bg-light-danger text-danger' ?>">
                                <?= $item['employee_status'] == '0' ? 'Active' : 'Blocked' ?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex order-actions">
                                <a href="edit_employee.php?id=<?= $item['employee_id'] ?>"><i class="bx bx-edit-alt"></i></a>
                                <a href="delete_employee.php?id=<?= $item['id'] ?>" class="ms-4 text-danger" onclick="return confirm('Are you sure you want to delete this employee?')">
                                    <i class="bx bx-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php
                            }
                        } else {
                    ?>
                    <tr>
                        <td colspan="10" class="text-center">No Records Found</td>
                    </tr>
                    <?php
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
