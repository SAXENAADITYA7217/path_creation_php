<?php
include('authentication.php');
include('includes/header.php');
?>

<div class="card radius-10">
    <div class="card-body">
        <?php include('message.php'); ?>
        <div class="d-flex align-items-center">
            <div>
                <h5 class="mb-0">Holidays / Events</h5>
            </div>
            <div class="dropdown options ms-auto">
                <div class="dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">
                    <i class='bx bx-dots-horizontal-rounded'></i>
                </div>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="javascript:;">Action</a></li>
                    <li><a class="dropdown-item" href="javascript:;">Another action</a></li>
                    <li><a class="dropdown-item" href="javascript:;">Something else here</a></li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="card-body">
            <form action="attendance_code.php" method="POST" enctype="multipart/form-data">
                <table class="table table-bordered" id="holidayTable">
                    <thead>
                        <tr>
                            <th>Holiday Name</th>
                            <th>Holiday Date</th>
                            <th>Holiday Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="holidays_name[]" class="form-control" required></td>
                            <td><input type="date" name="holiday_date[]" class="form-control" required></td>
                            <td>
                                <select name="holiday_type[]" class="form-control" required>
                                    <option value="all">All</option>
                                    <option value="overtime">Overtime</option>
                                    <option value="non_overtime">Non Overtime</option>
                                </select>
                            </td>
                            <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" id="addRow" class="btn btn-success">+ Add Row</button>
                <button type="submit" name="save_multiple_holidays" class="btn btn-primary">Save All</button>
            </form>
        </div>
    </div>
</div>

<script>
    // Add new row dynamically
    document.getElementById('addRow').addEventListener('click', function() {
        var table = document.getElementById('holidayTable').getElementsByTagName('tbody')[0];
        var newRow = document.createElement('tr');

        newRow.innerHTML = `
            <td><input type="text" name="holidays_name[]" class="form-control" required></td>
            <td><input type="date" name="holiday_date[]" class="form-control" required></td>
            <td>
                <select name="holiday_type[]" class="form-control" required>
                    <option value="all">All</option>
                    <option value="overtime">Overtime</option>
                    <option value="non_overtime">Non Overtime</option>
                </select>
            </td>
            <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
        `;

        table.appendChild(newRow);
    });

    // Remove row dynamically
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('removeRow')) {
            e.target.closest('tr').remove();
        }
    });
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
