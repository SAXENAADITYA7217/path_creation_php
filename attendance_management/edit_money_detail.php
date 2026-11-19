<?php
include('authentication.php');
include('includes/header.php');

date_default_timezone_set('Asia/Kolkata');

$masterid = $_SESSION['auth_user']['user_master_id'];

if(isset($_GET['id']))
{
    $id = $_GET['id'];

    $query = "SELECT * FROM employee_money_detail 
              WHERE id='$id' AND masterid='$masterid' LIMIT 1";

    $query_run = mysqli_query($con, $query);

    if(mysqli_num_rows($query_run) > 0)
    {
        $row = mysqli_fetch_assoc($query_run);
    }
    else
    {
        $_SESSION['message'] = "Record Not Found";
        header("Location: view_money_detail.php");
        exit(0);
    }
}
else
{
    header("Location: view_money_detail.php");
    exit(0);
}
?>

<?php include('message.php'); ?>

<div class="col-12">
    <div class="card radius-10 shadow-sm">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5>Edit Money Detail</h5>
                <a href="view_money_detail.php" class="btn btn-secondary btn-sm">
                    ← Back
                </a>
            </div>

            <hr>

            <form action="attendance_code.php" method="POST">

                <input type="hidden" name="money_id" value="<?= $row['id']; ?>">

                <div class="row g-3">

                    <!-- Employee -->
                    <div class="col-md-6">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" 
                               value="<?= $row['employee_name']; ?> (<?= $row['employee_id']; ?>)" 
                               readonly>
                    </div>

                    <!-- Money Type -->
                    <div class="col-md-6">
                        <label class="form-label">Money Type</label>
                        <select name="money_type" class="form-select" required>
                            <option value="0" <?= ($row['money_type']==0)?'selected':''; ?>>Dues</option>
                            <option value="1" <?= ($row['money_type']==1)?'selected':''; ?>>Advance</option>
                            <option value="2" <?= ($row['money_type']==2)?'selected':''; ?>>Penalty</option>
                        </select>
                    </div>

                    <!-- Amount -->
                    <div class="col-md-6">
                        <label class="form-label">Money Amount</label>
                        <input type="number" step="0.01" name="money_amount" 
                               class="form-control"
                               value="<?= $row['money_amount']; ?>" required>
                    </div>

                    <!-- Date -->
                    <div class="col-md-6">
                        <label class="form-label">Money Date</label>
                        <input type="date" name="money_date" 
                               class="form-control"
                               value="<?= $row['money_date']; ?>" required>
                    </div>

                    <!-- Description -->
                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                        <textarea name="money_description" 
                                  class="form-control" rows="3"><?= $row['money_description']; ?></textarea>
                    </div>

                </div>

                <div class="mt-4 text-end">
                    <button type="submit" name="update_money_detail" class="btn btn-primary px-4">
                        Update
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>