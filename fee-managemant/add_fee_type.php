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
                    <h5>Add Fee Type
                        <a href="javascript:void(0);" class="btn btn-primary btn-sm float-end" id="addButton">Add Fee Type</a>
                    </h5>
                </div>
                <div class="card-body mb-0">
                    <form action="master_code.php" method="POST">
                        <div class="row" id="feeTypeContainer">

                            <!-- Initially No Input Fields are Present -->
                            
                        </div>

                        <div class="col-md-12 mb-3">
                            <button type="submit" name="add_fee_type" class="btn btn-primary btn-sm">Add Fee Types</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let feeTypeCount = 0;

    function addFeeTypeBox() {
        feeTypeCount++;
        const container = document.getElementById('feeTypeContainer');

        const newField = document.createElement('div');
        newField.classList.add('col-md-6', 'mb-2');  // wider column for all items in one row
        newField.id = 'feeTypeField' + feeTypeCount;

        newField.innerHTML = `
            <label>Fee Type ${feeTypeCount}</label>

            <div class="d-flex align-items-center gap-2">

                <!-- Input -->
                <input type="text" 
                    name="fee_type_name[]" 
                    class="form-control form-control-sm"
                    placeholder="Enter Fee Type"
                    required>

                <!-- Selector -->
                <select name="fee_detail_type_status[]" 
                        class="form-select form-select-sm" 
                        style="width:140px;">
                    <option value="1">Academic Fee</option>
                    <option value="2">Store Fee</option>
                    <option value="3">Monthly Fee</option>
                </select>

                <!-- Remove button -->
                <button type="button" 
                    class="btn btn-sm btn-light p-1"
                    onclick="removeFeeType(${feeTypeCount})"
                    style="border-radius:50%; width:28px; height:28px;">
                    &times;
                </button>
            </div>
        `;

        container.appendChild(newField);
    }

    document.getElementById('addButton').addEventListener('click', function() {
        addFeeTypeBox();
    });

    function removeFeeType(id) {
        const field = document.getElementById('feeTypeField' + id);
        field.remove();
    }

    window.onload = function() {
        addFeeTypeBox();
    };
</script>


<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
